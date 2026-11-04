<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_code')
                ->label('Código do Pedido'),

            ExportColumn::make('sold_at')
                ->label('Data da Venda')
                ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('d/m/Y') : ''),

            ExportColumn::make('channel')
                ->label('Canal / Marketplace')
                ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\SaleChannel ? $state->getLabel() : (string) $state),

            ExportColumn::make('status')
                ->label('Status do Pedido')
                ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\SaleStatus ? $state->getLabel() : (string) $state),

            ExportColumn::make('product_names')
                ->label('Nome do Produto')
                ->state(function (Sale $record): string {
                    return $record->items->map(fn ($item) => $item->item_name ?? $item->product?->name)->filter()->unique()->implode(' + ') ?: 'Item Venda';
                }),

            ExportColumn::make('product_skus')
                ->label('Código SKU')
                ->state(function (Sale $record): string {
                    return $record->items->map(fn ($item) => $item->product?->sku)->filter()->unique()->implode(' + ') ?: '';
                }),

            ExportColumn::make('items_quantity')
                ->label('Quantidade de Itens')
                ->state(function (Sale $record): int {
                    return (int) ($record->items->sum('quantity') ?: 1);
                }),

            ExportColumn::make('total_amount')
                ->label('Faturamento (R$)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('total_cost')
                ->label('Custo Total (R$)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('gross_profit')
                ->label('Lucro Bruto (R$)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('profit_margin')
                ->label('Margem Real (%)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.') . '%'),

            ExportColumn::make('marketplace_fee')
                ->label('Comissão Marketplace (R$)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('shipping_cost')
                ->label('Custo de Frete (R$)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'O relatório de vendas foi exportado com sucesso! ' . number_format($export->successful_rows) . ' vendas processadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Houve falha em ' . number_format($failedRowsCount) . ' registros.';
        }

        return $body;
    }
}
