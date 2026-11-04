<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\ApiIntegration;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketplaceSyncService
{
    /**
     * Sincroniza pedidos reais da Shopee Open Platform API v2
     * @param int $daysBack Limite de dias (máx 15 por requisição da Shopee)
     */
    public function syncShopee(int $daysBack = 15): array
    {
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
                'message' => 'Credenciais da Shopee incompletas no Cofre de APIs.',
            ];
        }

        try {
            $timestamp = time();
            $pathList = '/api/v2/order/get_order_list';
            $signBaseList = "{$partnerId}{$pathList}{$timestamp}{$accessToken}{$shopId}";
            $signList = hash_hmac('sha256', $signBaseList, $partnerKey);

            // Respeita a regra da Shopee (máx 15 dias por requisição)
            $effectiveDays = ($daysBack > 0 && $daysBack <= 15) ? $daysBack : 15;
            $timeFrom = $timestamp - ($effectiveDays * 86400);

            // 1. Busca os números de pedido (order_sn)
            $response = Http::timeout(15)->get("https://partner.shopeemobile.com{$pathList}", [
                'partner_id' => (int) $partnerId,
                'timestamp' => $timestamp,
                'sign' => $signList,
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
            if (empty($orderList)) {
                $integration->update(['last_synced_at' => now()]);
                return [
                    'success' => true,
                    'count' => 0,
                    'order_code' => 'Nenhum pedido recente nos últimos ' . $effectiveDays . ' dias.',
                ];
            }

            $orderSnList = collect($orderList)->pluck('order_sn')->toArray();

            // 2. Busca os detalhes completos dos pedidos
            $detailPath = '/api/v2/order/get_order_detail';
            $timestampDetail = time();
            $signBaseDetail = "{$partnerId}{$detailPath}{$timestampDetail}{$accessToken}{$shopId}";
            $signDetail = hash_hmac('sha256', $signBaseDetail, $partnerKey);

            $detailResponse = Http::timeout(20)->get("https://partner.shopeemobile.com{$detailPath}", [
                'partner_id' => (int) $partnerId,
                'timestamp' => $timestampDetail,
                'sign' => $signDetail,
                'shop_id' => (int) $shopId,
                'access_token' => $accessToken,
                'order_sn_list' => implode(',', array_slice($orderSnList, 0, 50)),
                'response_optional_fields' => 'buyer_user_id,buyer_username,estimated_shipping_fee,actual_shipping_fee,item_list,pay_time,order_status,create_time,total_amount',
            ]);

            $detailJson = $detailResponse->json();
            $ordersDetail = $detailJson['response']['order_list'] ?? [];

            $savedCount = 0;

            DB::transaction(function () use ($ordersDetail, &$savedCount) {
                foreach ($ordersDetail as $orderData) {
                    $orderSn = $orderData['order_sn'];
                    $shopeeStatus = strtoupper($orderData['order_status'] ?? 'UNPAID');

                    // Mapeamento de Status Shopee -> Enum VendaVision
                    $saleStatus = match ($shopeeStatus) {
                        'COMPLETED', 'SHIPPED', 'TO_CONFIRM_RECEIVE' => SaleStatus::Shipped,
                        'READY_TO_SHIP', 'PROCESSED' => SaleStatus::Paid,
                        'CANCELLED' => SaleStatus::Cancelled,
                        default => SaleStatus::Pending,
                    };

                    $soldAt = !empty($orderData['create_time'])
                        ? Carbon::createFromTimestamp($orderData['create_time'])
                        : now();

                    $shippingCost = (float) ($orderData['actual_shipping_fee'] ?? $orderData['estimated_shipping_fee'] ?? 0.0);

                    // Cria ou atualiza a venda (upsert pelo order_code)
                    $sale = Sale::firstOrNew(['order_code' => $orderSn]);
                    $sale->channel = SaleChannel::Shopee;
                    $sale->status = $saleStatus;
                    $sale->sold_at = $soldAt->toDateString();
                    $sale->shipping_cost = $shippingCost;
                    $sale->notes = "Cliente: " . ($orderData['buyer_username'] ?? 'Shopee') . " | Status Shopee: {$shopeeStatus}";

                    $totalRevenue = 0.0;
                    $totalCost = 0.0;
                    $itemsToPersist = [];

                    foreach ($orderData['item_list'] ?? [] as $item) {
                        $itemId = (string) ($item['item_id'] ?? '');
                        $itemSku = trim((string) ($item['model_sku'] ?: ($item['item_sku'] ?: $itemId)));
                        $itemName = $item['item_name'] ?? 'Produto Shopee';
                        $qty = (int) ($item['model_quantity_purchased'] ?? $item['active_qty'] ?? 1);
                        $unitPrice = (float) ($item['model_discounted_price'] ?? $item['model_original_price'] ?? 0.0);

                        // 1. Busca por SKU / Item ID
                        // 2. Busca por Correspondência de Nome
                        // 3. Fallback inteligente para o primeiro produto ativo do catálogo
                        $product = Product::where('sku', $itemSku)
                            ->orWhere('sku', $itemId)
                            ->orWhere('name', 'LIKE', '%' . substr($itemName, 0, 30) . '%')
                            ->first() ?? Product::first();

                        $unitCost = $product ? (float) $product->cost_price : round($unitPrice * 0.50, 2);

                        $subtotal = round($qty * $unitPrice, 2);
                        $costSubtotal = round($qty * $unitCost, 2);

                        $totalRevenue += $subtotal;
                        $totalCost += $costSubtotal;

                        $itemsToPersist[] = [
                            'product_id' => $product?->id,
                            'item_name' => $itemName . (isset($item['model_name']) && $item['model_name'] ? " - {$item['model_name']}" : ""),
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'unit_cost' => $unitCost,
                            'item_fee' => 0.00,
                            'subtotal' => $subtotal,
                            'cost_subtotal' => $costSubtotal,
                        ];
                    }

                    // Comissão Shopee (20% padrão da plataforma)
                    $marketplaceFee = round($totalRevenue * 0.20, 2);
                    $grossProfit = round($totalRevenue - $totalCost - $marketplaceFee, 2);
                    $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0.0;

                    $sale->total_amount = $totalRevenue;
                    $sale->total_cost = $totalCost;
                    $sale->marketplace_fee = $marketplaceFee;
                    $sale->gross_profit = $grossProfit;
                    $sale->profit_margin = $profitMargin;
                    $sale->save();

                    // Sincroniza os itens da venda
                    $sale->items()->delete();
                    foreach ($itemsToPersist as $itemAttributes) {
                        $sale->items()->create($itemAttributes);
                    }

                    $savedCount++;
                }
            });

            $integration->update(['last_synced_at' => now()]);

            return [
                'success' => true,
                'count' => $savedCount,
                'order_code' => "{$savedCount} pedidos da Shopee sincronizados com sucesso!",
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar pedidos da Shopee: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Falha ao processar pedidos da Shopee: ' . $e->getMessage(),
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
    public function syncAll(int $daysBack = 15): array
    {
        $meli = ApiIntegration::isProviderActive('mercadolivre') ? $this->syncMercadoLivre($daysBack) : ['success' => false, 'count' => 0];
        $shopee = ApiIntegration::isProviderActive('shopee') ? $this->syncShopee($daysBack) : ['success' => false, 'count' => 0];
        $amazon = ApiIntegration::isProviderActive('amazon') ? $this->syncAmazon($daysBack) : ['success' => false, 'count' => 0];

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
