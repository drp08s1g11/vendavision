<?php

namespace App\Filament\Imports;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

class SaleImporter extends Importer
{
    protected static ?string $model = Sale::class;

    /**
     * Sanitiza valores numéricos e monetários (moeda brasileira ou americana).
     */
    public static function sanitizePrice(mixed $state): float
    {
        if (blank($state)) {
            return 0.0;
        }

        if (is_numeric($state)) {
            return round((float) $state, 2);
        }

        $cleaned = trim((string) $state);
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

        $val = round((float) $cleaned, 2);
        return $isNegative ? -$val : $val;
    }

    /**
     * Converte datas em d/m/Y ou Y-m-d para Y-m-d.
     */
    public static function parseDate(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $state = trim((string) $state);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $state, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        try {
            return Carbon::parse($state)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function parseChannel(mixed $state): string
    {
        $normalized = strtolower(trim((string) $state));
        return match (true) {
            str_contains($normalized, 'shopee') => SaleChannel::Shopee->value,
            str_contains($normalized, 'mercado') || str_contains($normalized, 'meli') => SaleChannel::MercadoLivre->value,
            str_contains($normalized, 'amazon') => SaleChannel::Amazon->value,
            default => SaleChannel::Direct->value,
        };
    }

    public static function parseStatus(mixed $state): string
    {
        $normalized = strtolower(trim((string) $state));
        return match (true) {
            str_contains($normalized, 'pago') || str_contains($normalized, 'paid') || str_contains($normalized, 'concluído') => SaleStatus::Paid->value,
            str_contains($normalized, 'enviado') || str_contains($normalized, 'shipped') => SaleStatus::Shipped->value,
            str_contains($normalized, 'cancel') => SaleStatus::Cancelled->value,
            str_contains($normalized, 'reembol') || str_contains($normalized, 'refund') => SaleStatus::Refunded->value,
            default => SaleStatus::Pending->value,
        };
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('order_code')
                ->label('Código do Pedido')
                ->guess(['codigo_do_pedido', 'id_do_pedido', 'order_code', 'pedido', 'codigo'])
                ->requiredMapping()
                ->rules(['required', 'max:64']),

            ImportColumn::make('sold_at')
                ->label('Data da Venda')
                ->guess(['data_da_venda', 'data_de_criacao_do_pedido', 'sold_at', 'data'])
                ->castStateUsing(fn ($state) => self::parseDate($state))
                ->rules(['required', 'date']),

            ImportColumn::make('channel')
                ->label('Canal / Marketplace')
                ->guess(['canal_marketplace', 'canal', 'marketplace', 'channel'])
                ->castStateUsing(fn ($state) => self::parseChannel($state)),

            ImportColumn::make('status')
                ->label('Status do Pedido')
                ->guess(['status_do_pedido', 'status'])
                ->castStateUsing(fn ($state) => self::parseStatus($state)),

            ImportColumn::make('total_amount')
                ->label('Faturamento (R$)')
                ->guess(['faturamento_r', 'faturamento', 'total_amount', 'preco_do_produto', 'total'])
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::sanitizePrice($state))
                ->rules(['required', 'numeric']),

            ImportColumn::make('total_cost')
                ->label('Custo Total (R$)')
                ->guess(['custo_total_r', 'custo_total', 'total_cost', 'custo'])
                ->castStateUsing(fn ($state) => self::sanitizePrice($state)),

            ImportColumn::make('gross_profit')
                ->label('Lucro Bruto (R$)')
                ->guess(['lucro_bruto_r', 'lucro_bruto', 'gross_profit', 'lucro'])
                ->castStateUsing(fn ($state) => self::sanitizePrice($state)),

            ImportColumn::make('profit_margin')
                ->label('Margem Real (%)')
                ->guess(['margem_real', 'margem', 'profit_margin'])
                ->castStateUsing(fn ($state) => self::sanitizePrice($state)),

            ImportColumn::make('marketplace_fee')
                ->label('Comissão Marketplace (R$)')
                ->guess(['comissao_marketplace_r', 'comissao', 'marketplace_fee', 'taxas'])
                ->castStateUsing(fn ($state) => self::sanitizePrice($state)),

            ImportColumn::make('shipping_cost')
                ->label('Custo de Frete (R$)')
                ->guess(['custo_de_frete_r', 'frete', 'shipping_cost'])
                ->castStateUsing(fn ($state) => self::sanitizePrice($state)),
        ];
    }

    /**
     * Compara com a base de dados pelo order_code para impedir registros duplicados.
     */
    public function resolveRecord(): ?Sale
    {
        $orderCode = trim((string) ($this->data['order_code'] ?? ''));

        if (filled($orderCode)) {
            $existing = Sale::where('order_code', $orderCode)->first();
            if ($existing) {
                // Atualiza o registro existente sem duplicar
                return $existing;
            }

            return new Sale(['order_code' => $orderCode]);
        }

        return new Sale();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de vendas concluída! ' . number_format($import->successful_rows) . ' vendas sincronizadas. Registros idênticos foram comparados pelo Código do Pedido para não duplicar.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Houve falha em ' . number_format($failedRowsCount) . ' linhas.';
        }

        return $body;
    }
}
