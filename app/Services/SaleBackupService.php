<?php

namespace App\Services;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SaleBackupService
{
    /**
     * Sanitiza valores monetários e numéricos
     */
    public function sanitizeNumber(mixed $val): float
    {
        if (blank($val)) {
            return 0.0;
        }

        if (is_numeric($val)) {
            return round((float) $val, 2);
        }

        $cleaned = trim((string) $val);
        $cleaned = preg_replace('/[^\d,.-]/', '', $cleaned);
        if ($cleaned === '') {
            return 0.0;
        }

        $isNegative = str_starts_with($cleaned, '-');
        $cleaned = ltrim($cleaned, '-');

        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        if (!is_numeric($cleaned)) {
            return 0.0;
        }

        $num = round((float) $cleaned, 2);
        return $isNegative ? -$num : $num;
    }

    /**
     * Converte strings de data em formato d/m/Y ou Y-m-d para Y-m-d
     */
    public function parseDate(mixed $val): string
    {
        if (blank($val)) {
            return Carbon::today()->toDateString();
        }

        $val = trim((string) $val);
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        try {
            return Carbon::parse($val)->toDateString();
        } catch (\Throwable) {
            return Carbon::today()->toDateString();
        }
    }

    public function parseChannel(mixed $val): SaleChannel
    {
        $normalized = strtolower(trim((string) $val));
        return match (true) {
            str_contains($normalized, 'shopee') => SaleChannel::Shopee,
            str_contains($normalized, 'mercado') || str_contains($normalized, 'meli') => SaleChannel::MercadoLivre,
            str_contains($normalized, 'amazon') => SaleChannel::Amazon,
            default => SaleChannel::Direct,
        };
    }

    public function parseStatus(mixed $val): SaleStatus
    {
        $normalized = strtolower(trim((string) $val));
        return match (true) {
            str_contains($normalized, 'pago') || str_contains($normalized, 'paid') || str_contains($normalized, 'concluído') => SaleStatus::Paid,
            str_contains($normalized, 'enviado') || str_contains($normalized, 'shipped') => SaleStatus::Shipped,
            str_contains($normalized, 'cancel') => SaleStatus::Cancelled,
            str_contains($normalized, 'reembol') || str_contains($normalized, 'refund') => SaleStatus::Refunded,
            default => SaleStatus::Pending,
        };
    }

    /**
     * Importa vendas de arquivo CSV com detecção de duplicatas e comparativo rigoroso.
     */
    public function importFromCsvContent(string $csvContent): array
    {
        // Remove BOM UTF-8 se houver
        $bom = pack('H*', 'EFBBBF');
        $csvContent = preg_replace("/^{$bom}/", '', $csvContent);

        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (count($lines) < 2) {
            return [
                'success' => false,
                'message' => 'O arquivo enviado não contém linhas de dados suficientes.',
            ];
        }

        // Detecta delimitador (, ; ou \t)
        $firstLine = $lines[0];
        $delimiter = ';';
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        if ($tabCount > $semicolonCount && $tabCount > $commaCount) {
            $delimiter = "\t";
        } elseif ($commaCount > $semicolonCount) {
            $delimiter = ',';
        }

        $headerRow = str_getcsv($lines[0], $delimiter);
        $headerMap = [];
        foreach ($headerRow as $index => $col) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', Str::ascii($col))));
            $headerMap[$normalized] = $index;
        }

        // Mapeia colunas conhecidas
        $findCol = function (array $candidates) use ($headerMap): ?int {
            foreach ($candidates as $candidate) {
                $norm = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', Str::ascii($candidate))));
                if (isset($headerMap[$norm])) {
                    return $headerMap[$norm];
                }
            }
            return null;
        };

        $colOrderCode = $findCol(['order_code', 'codigodopedido', 'iddopedido', 'pedido', 'codigo']);
        $colSoldAt = $findCol(['sold_at', 'datadavenda', 'datadecriacaodopedido', 'data']);
        $colChannel = $findCol(['channel', 'canalmarketplace', 'canal', 'marketplace']);
        $colStatus = $findCol(['status', 'statusdopedido']);
        $colTotalAmount = $findCol(['total_amount', 'faturamentor', 'faturamento', 'precodoproduto', 'total']);
        $colTotalCost = $findCol(['total_cost', 'custototalr', 'custototal', 'custo']);
        $colGrossProfit = $findCol(['gross_profit', 'lucrobrutor', 'lucrobruto', 'lucro']);
        $colProfitMargin = $findCol(['profit_margin', 'margemreal', 'margem']);
        $colMarketplaceFee = $findCol(['marketplace_fee', 'comissaomarketplacer', 'comissao', 'taxas']);
        $colShippingCost = $findCol(['shipping_cost', 'custodefreter', 'frete']);

        if ($colOrderCode === null) {
            return [
                'success' => false,
                'message' => 'Coluna identificadora do pedido (Código do Pedido ou ID do Pedido) não foi localizada no arquivo.',
            ];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $processedTotal = 0;

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if ($line === '') {
                    continue;
                }

                $row = str_getcsv($line, $delimiter);
                $orderCode = trim((string) ($row[$colOrderCode] ?? ''));

                if ($orderCode === '' || $orderCode === '-') {
                    continue;
                }

                $processedTotal++;

                $soldAt = $colSoldAt !== null ? $this->parseDate($row[$colSoldAt] ?? '') : Carbon::today()->toDateString();
                $channel = $colChannel !== null ? $this->parseChannel($row[$colChannel] ?? '') : SaleChannel::Shopee;
                $status = $colStatus !== null ? $this->parseStatus($row[$colStatus] ?? '') : SaleStatus::Paid;
                $totalAmount = $colTotalAmount !== null ? $this->sanitizeNumber($row[$colTotalAmount] ?? 0) : 0.0;
                $totalCost = $colTotalCost !== null ? $this->sanitizeNumber($row[$colTotalCost] ?? 0) : 0.0;
                $grossProfit = $colGrossProfit !== null ? $this->sanitizeNumber($row[$colGrossProfit] ?? 0) : round($totalAmount - $totalCost, 2);
                $profitMargin = $colProfitMargin !== null ? $this->sanitizeNumber($row[$colProfitMargin] ?? 0) : ($totalAmount > 0 ? round(($grossProfit / $totalAmount) * 100, 2) : 0.0);
                $marketplaceFee = $colMarketplaceFee !== null ? $this->sanitizeNumber($row[$colMarketplaceFee] ?? 0) : 0.0;
                $shippingCost = $colShippingCost !== null ? $this->sanitizeNumber($row[$colShippingCost] ?? 0) : 0.0;

                // Comparativo com a base existente para não duplicar
                $existing = Sale::where('order_code', $orderCode)->first();

                if ($existing) {
                    // Verifica se os valores são iguais aos já registrados
                    $isIdentical = (
                        (float) $existing->total_amount === (float) $totalAmount &&
                        (float) $existing->gross_profit === (float) $grossProfit &&
                        $existing->sold_at?->toDateString() === $soldAt
                    );

                    if ($isIdentical) {
                        $skipped++;
                    } else {
                        // Sincroniza dados atualizados do pedido
                        $existing->update([
                            'sold_at' => $soldAt,
                            'channel' => $channel,
                            'status' => $status,
                            'total_amount' => $totalAmount,
                            'total_cost' => $totalCost,
                            'gross_profit' => $grossProfit,
                            'profit_margin' => $profitMargin,
                            'marketplace_fee' => $marketplaceFee,
                            'shipping_cost' => $shippingCost,
                        ]);
                        $updated++;
                    }
                } else {
                    // Cria nova venda sem duplicidade
                    Sale::create([
                        'order_code' => $orderCode,
                        'sold_at' => $soldAt,
                        'channel' => $channel,
                        'status' => $status,
                        'total_amount' => $totalAmount,
                        'total_cost' => $totalCost,
                        'gross_profit' => $grossProfit,
                        'profit_margin' => $profitMargin,
                        'marketplace_fee' => $marketplaceFee,
                        'shipping_cost' => $shippingCost,
                    ]);
                    $created++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'total' => $processedTotal,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'message' => "Backup processado com sucesso! {$created} vendas novas cadastradas. {$skipped} vendas idênticas mantidas sem duplicar. {$updated} pedidos atualizados.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao importar backup de vendas: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Falha ao processar arquivo de backup: ' . $e->getMessage(),
            ];
        }
    }
}
