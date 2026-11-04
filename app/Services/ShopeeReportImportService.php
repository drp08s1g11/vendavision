<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopeeReportImportService
{
    /**
     * Faz o parser do CSV/XLSX exportado do portal da Shopee (Relatório de Rendimento)
     * e sincroniza pedidos, taxas reais da Shopee e margem de lucro.
     * Suporta arquivos anuais que contêm seções intermediárias de taxas e ajustes.
     */
    public function importFromCsvContent(string $csvContent): array
    {
        // Converte quebras de linha e remove caracteres nulos
        $cleanContent = str_replace("\0", '', $csvContent);
        $lines = preg_split('/\r\n|\r|\n/', $cleanContent);

        // Encontra o cabeçalho oficial da tabela de pedidos da Shopee.
        // Em relatórios anuais, a Shopee insere tabelas de resumo no topo e ajustes no meio.
        // O cabeçalho real possui colunas essenciais como 'ID do pedido', 'Preço do produto' e 'Nome do produto'.
        $headerRowIndex = null;
        $headers = [];

        foreach ($lines as $idx => $line) {
            $cols = str_getcsv($line, ',');
            $lineLower = mb_strtolower($line);

            if (
                str_contains($lineLower, 'id do pedido') &&
                (str_contains($lineLower, 'preço do produto') || str_contains($lineLower, 'nome do produto') || str_contains($lineLower, 'quantia total lançada'))
            ) {
                // Seleciona essa linha como cabeçalho
                $headerRowIndex = $idx;
                $headers = $cols;
                // Continua procurando se houver mais de uma ocorrência, ou usa a primeira que encontrar dados logo em seguida
                break;
            }
        }

        if ($headerRowIndex === null) {
            return [
                'success' => false,
                'message' => 'Não foi possível localizar o cabeçalho da tabela de pedidos no arquivo. Verifique se o arquivo é o relatório de rendimento/pedidos oficial da Shopee.',
            ];
        }

        // Mapeia os índices das colunas relevantes
        $headerMap = [];
        foreach ($headers as $colIdx => $colName) {
            $normalized = trim(mb_strtolower($colName));
            $headerMap[$normalized] = $colIdx;
        }

        $getCol = function (array $row, array $possibleNames) use ($headerMap) {
            foreach ($possibleNames as $name) {
                $n = mb_strtolower($name);
                if (isset($headerMap[$n]) && isset($row[$headerMap[$n]])) {
                    return trim($row[$headerMap[$n]]);
                }
            }
            return null;
        };

        $ordersToProcess = [];

        // 2. Itera sobre as linhas de dados a partir do cabeçalho
        for ($i = $headerRowIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            // Se encontrar nova seção de cabeçalho idêntico ou avisos no meio do arquivo anual, ignora
            if (str_contains(mb_strtolower($line), 'id do pedido') && str_contains(mb_strtolower($line), 'nome do produto')) {
                continue;
            }
            if (str_starts_with($line, 'Nota:') || str_starts_with($line, 'Informações do pedido') || str_starts_with($line, 'Resumo')) {
                continue;
            }

            $row = str_getcsv($line, ',');
            if (count($row) < 5) {
                continue;
            }

            $orderCode = $getCol($row, ['id do pedido', 'order_sn', 'pedido']);
            if (empty($orderCode) || $orderCode === '-' || !preg_match('/^[0-9A-Za-z]+$/', $orderCode)) {
                continue;
            }

            $sku = $getCol($row, ['sku', 'código sku', 'item_sku']);
            $productName = $getCol($row, ['nome do produto', 'produto', 'item_name']);
            $createdAt = $getCol($row, ['data de criação do pedido', 'data do pedido', 'data']);
            $buyerUsername = $getCol($row, ['nome de usuário (comprador)', 'comprador']);
            $paymentMethod = $getCol($row, ['método de pagamento do comprador', 'pagamento']);
            
            // Valores financeiros
            $netReleased = (float) str_replace(',', '.', (string) $getCol($row, ['quantia total lançada (r$)', 'renda do pedido', 'valor liberado']));
            $grossProductPrice = (float) str_replace(',', '.', (string) $getCol($row, ['preço do produto', 'valor do produto', 'subtotal de mercadoria']));
            $refundAmount = (float) str_replace(',', '.', (string) $getCol($row, ['valor do reembolso', 'reembolso']));
            
            // Taxas oficiais da Shopee
            $commissionFee = abs((float) str_replace(',', '.', (string) $getCol($row, ['taxa de comissão líquida', 'taxa de comissão'])));
            $serviceFee = abs((float) str_replace(',', '.', (string) $getCol($row, ['taxa de serviço líquida', 'taxa de serviço'])));
            $returnFee = abs((float) str_replace(',', '.', (string) $getCol($row, ['taxa de devolução fácil shopee', 'taxa de devolução'])));
            $totalShopeeFee = round($commissionFee + $serviceFee + $returnFee, 2);

            if (!isset($ordersToProcess[$orderCode])) {
                $ordersToProcess[$orderCode] = [
                    'order_code' => $orderCode,
                    'created_at' => $createdAt,
                    'buyer' => $buyerUsername,
                    'payment_method' => $paymentMethod,
                    'net_released' => $netReleased,
                    'gross_amount' => $grossProductPrice,
                    'refund_amount' => $refundAmount,
                    'shopee_fee' => $totalShopeeFee,
                    'items' => [],
                ];
            } else {
                // Atualiza valores caso a primeira linha tenha sido resumida
                if ($ordersToProcess[$orderCode]['gross_amount'] <= 0 && $grossProductPrice > 0) {
                    $ordersToProcess[$orderCode]['gross_amount'] = $grossProductPrice;
                }
                if ($ordersToProcess[$orderCode]['shopee_fee'] <= 0 && $totalShopeeFee > 0) {
                    $ordersToProcess[$orderCode]['shopee_fee'] = $totalShopeeFee;
                }
            }

            // Adiciona item se tiver produto preenchido
            if (filled($productName) && $productName !== '-') {
                $ordersToProcess[$orderCode]['items'][] = [
                    'sku' => ($sku !== '-' && filled($sku)) ? $sku : null,
                    'name' => $productName,
                    'price' => $grossProductPrice > 0 ? $grossProductPrice : $netReleased,
                ];
            }
        }

        if (empty($ordersToProcess)) {
            return [
                'success' => false,
                'message' => 'Nenhum pedido válido com "ID do pedido" foi identificado para importação.',
            ];
        }

        // 3. Persiste no banco de dados com amarração inteligente aos produtos do catálogo
        $importedCount = 0;

        DB::transaction(function () use ($ordersToProcess, &$importedCount) {
            foreach ($ordersToProcess as $orderCode => $orderData) {
                // Determina status
                $status = SaleStatus::Paid;
                if ($orderData['refund_amount'] < 0 || $orderData['net_released'] <= 0) {
                    $status = SaleStatus::Cancelled;
                }

                $soldDate = now()->toDateString();
                if (!empty($orderData['created_at'])) {
                    try {
                        $soldDate = Carbon::parse($orderData['created_at'])->toDateString();
                    } catch (\Exception $e) {
                        $soldDate = now()->toDateString();
                    }
                }

                $sale = Sale::firstOrNew(['order_code' => $orderCode]);
                $sale->channel = SaleChannel::Shopee;
                $sale->status = $status;
                $sale->sold_at = $soldDate;
                $sale->shipping_cost = 0.00;
                $sale->notes = "Cliente: " . ($orderData['buyer'] ?? 'Shopee') . " | Pgto: " . ($orderData['payment_method'] ?? 'Shopee');

                $totalRevenue = $orderData['gross_amount'] > 0 ? $orderData['gross_amount'] : max($orderData['net_released'], 0);
                $marketplaceFee = $orderData['shopee_fee'] > 0 ? $orderData['shopee_fee'] : round($totalRevenue * 0.20, 2);

                $totalCost = 0.0;
                $itemsToPersist = [];

                $items = !empty($orderData['items']) ? $orderData['items'] : [['sku' => null, 'name' => 'Item Shopee', 'price' => $totalRevenue]];

                foreach ($items as $item) {
                    $itemSku = $item['sku'];
                    $itemName = $item['name'];
                    $unitPrice = (float) $item['price'];

                    // Busca correspondência no Catálogo de Produtos
                    $product = null;
                    if ($itemSku) {
                        $product = Product::where('sku', $itemSku)->first();
                    }
                    if (!$product && filled($itemName)) {
                        $product = Product::where('name', 'LIKE', '%' . substr($itemName, 0, 30) . '%')->first();
                    }
                    if (!$product) {
                        $product = Product::first();
                    }

                    $unitCost = $product ? (float) $product->cost_price : round($unitPrice * 0.50, 2);
                    $subtotal = $unitPrice;
                    $costSubtotal = $unitCost;

                    $totalCost += $costSubtotal;

                    $itemsToPersist[] = [
                        'product_id' => $product?->id,
                        'item_name' => $itemName,
                        'quantity' => 1,
                        'unit_price' => $unitPrice,
                        'unit_cost' => $unitCost,
                        'item_fee' => 0.00,
                        'subtotal' => $subtotal,
                        'cost_subtotal' => $costSubtotal,
                    ];
                }

                $grossProfit = round($totalRevenue - $totalCost - $marketplaceFee, 2);
                $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0.0;

                $sale->total_amount = $totalRevenue;
                $sale->total_cost = $totalCost;
                $sale->marketplace_fee = $marketplaceFee;
                $sale->gross_profit = $grossProfit;
                $sale->profit_margin = $profitMargin;
                $sale->save();

                $sale->items()->delete();
                foreach ($itemsToPersist as $itemAttributes) {
                    $sale->items()->create($itemAttributes);
                }

                $importedCount++;
            }
        });

        return [
            'success' => true,
            'count' => $importedCount,
            'message' => "{$importedCount} vendas do Relatório de Rendimento da Shopee foram importadas com sucesso!",
        ];
    }
}
