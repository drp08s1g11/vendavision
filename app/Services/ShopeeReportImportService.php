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
        // Remove BOM UTF-8 e caracteres nulos
        $cleanContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);
        $cleanContent = str_replace("\0", '', $cleanContent);
        $lines = preg_split('/\r\n|\r|\n/', $cleanContent);

        // Auto-detecta delimitador baseado no cabeçalho ou primeiras linhas (ponto-e-vírgula vs vírgula vs tab)
        $sample = substr($cleanContent, 0, 3000);
        $countSemicolon = substr_count($sample, ';');
        $countComma = substr_count($sample, ',');
        $countTab = substr_count($sample, "\t");

        $delimiter = ',';
        if ($countSemicolon > $countComma && $countSemicolon > $countTab) {
            $delimiter = ';';
        } elseif ($countTab > $countComma && $countTab > $countSemicolon) {
            $delimiter = "\t";
        }

        // Função robusta para interpretar números em formato brasileiro (ex: 60,82 ou 1.250,50 ou -4.49)
        $parseNumber = function ($val): float {
            if ($val === null || $val === '' || $val === '-') {
                return 0.0;
            }
            $val = trim((string) $val);
            $val = preg_replace('/[^\d,.-]/', '', $val);
            if (empty($val) || $val === '-' || $val === '.') {
                return 0.0;
            }
            if (str_contains($val, '.') && str_contains($val, ',')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } elseif (str_contains($val, ',')) {
                $val = str_replace(',', '.', $val);
            }
            return (float) $val;
        };

        // Encontra o cabeçalho oficial da tabela de pedidos da Shopee.
        $headerRowIndex = null;
        $headers = [];

        foreach ($lines as $idx => $line) {
            $cols = str_getcsv($line, $delimiter);
            $lineLower = mb_strtolower($line);

            if (
                str_contains($lineLower, 'id do pedido') &&
                (str_contains($lineLower, 'preço do produto') || str_contains($lineLower, 'nome do produto') || str_contains($lineLower, 'quantia total lançada') || str_contains($lineLower, 'sku'))
            ) {
                $headerRowIndex = $idx;
                $headers = $cols;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return [
                'success' => false,
                'message' => 'Não foi possível localizar o cabeçalho com "ID do pedido" no arquivo. Verifique se exportou a aba "Renda" da Shopee.',
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

            $row = str_getcsv($line, $delimiter);
            if (count($row) < 3) {
                continue;
            }

            $orderCode = $getCol($row, ['id do pedido', 'order_sn', 'pedido']);
            if (empty($orderCode) || $orderCode === '-' || !preg_match('/^[0-9A-Za-z]+$/', $orderCode)) {
                continue;
            }

            $sku = $getCol($row, ['sku', 'código sku', 'item_sku']);
            $productName = $getCol($row, ['nome do produto', 'produto', 'item_name']);
            $createdAt = $getCol($row, ['data de criação do pedido', 'data do pedido', 'data']);
            $buyerUsername = $getCol($row, ['nome de usuário (comprador)', 'comprador', 'nome de usuário']);
            $paymentMethod = $getCol($row, ['método de pagamento do comprador', 'pagamento', 'método de pagamento']);
            
            // Valores financeiros
            $netReleased = $parseNumber($getCol($row, ['quantia total lançada (r$)', 'renda do pedido', 'valor liberado']));
            $grossProductPrice = $parseNumber($getCol($row, ['preço do produto', 'valor do produto', 'subtotal de mercadoria']));
            $refundAmount = $parseNumber($getCol($row, ['valor do reembolso', 'reembolso']));
            
            // Taxas oficiais da Shopee
            $commissionFee = abs($parseNumber($getCol($row, ['taxa de comissão líquida', 'taxa de comissão', 'taxa de comissão bruta'])));
            $serviceFee = abs($parseNumber($getCol($row, ['taxa de serviço líquida', 'taxa de serviço', 'taxa de serviço bruta'])));
            $transactionFee = abs($parseNumber($getCol($row, ['taxa de transação'])));
            $easyReturnFee = abs($parseNumber($getCol($row, ['taxa de devolução fácil shopee', 'taxa de devolução'])));
            $affiliateFee = abs($parseNumber($getCol($row, ['taxa de comissão afiliados do vendedor'])));

            $totalShopeeFee = round($commissionFee + $serviceFee + $transactionFee + $easyReturnFee + $affiliateFee, 2);

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
                // Atualiza dados cadastrais e financeiros caso tenham vindo na outra linha
                if (empty($ordersToProcess[$orderCode]['buyer']) && filled($buyerUsername) && $buyerUsername !== '-') {
                    $ordersToProcess[$orderCode]['buyer'] = $buyerUsername;
                }
                if (empty($ordersToProcess[$orderCode]['payment_method']) && filled($paymentMethod) && $paymentMethod !== '-') {
                    $ordersToProcess[$orderCode]['payment_method'] = $paymentMethod;
                }
                if ($ordersToProcess[$orderCode]['gross_amount'] <= 0 && $grossProductPrice > 0) {
                    $ordersToProcess[$orderCode]['gross_amount'] = $grossProductPrice;
                }
                if ($ordersToProcess[$orderCode]['shopee_fee'] <= 0 && $totalShopeeFee > 0) {
                    $ordersToProcess[$orderCode]['shopee_fee'] = $totalShopeeFee;
                }
                if ($ordersToProcess[$orderCode]['net_released'] <= 0 && $netReleased > 0) {
                    $ordersToProcess[$orderCode]['net_released'] = $netReleased;
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
                $netReleased = max($orderData['net_released'], 0);

                // Taxas da Shopee: diferença exata entre Preço do Produto e Quantia Lançada
                $marketplaceFee = max(round($totalRevenue - $netReleased, 2), 0.0);
                if ($marketplaceFee <= 0 && $orderData['shopee_fee'] > 0) {
                    $marketplaceFee = $orderData['shopee_fee'];
                }

                // Lucro Líquido Oficial da Shopee = Quantia total lançada (o dinheiro que caiu na carteira)
                $grossProfit = $netReleased > 0 ? $netReleased : max(round($totalRevenue - $marketplaceFee, 2), 0.0);
                $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0.0;

                $totalCost = 0.0;
                $itemsToPersist = [];

                $items = !empty($orderData['items']) ? $orderData['items'] : [['sku' => null, 'name' => 'Item Shopee', 'price' => $totalRevenue]];
                $itemFeeRatio = count($items) > 0 ? round($marketplaceFee / count($items), 2) : 0.0;

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

                    $itemsToPersist[] = [
                        'product_id' => $product?->id,
                        'item_name' => $itemName,
                        'quantity' => 1,
                        'unit_price' => $unitPrice,
                        'unit_cost' => 0.00,
                        'item_fee' => $itemFeeRatio,
                        'subtotal' => $unitPrice,
                        'cost_subtotal' => 0.00,
                    ];
                }

                $sale->total_amount = $totalRevenue;
                $sale->total_cost = 0.00;
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
