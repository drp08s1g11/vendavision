<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('sku')
                ->label('Código do Produto (SKU)'),

            ExportColumn::make('name')
                ->label('Nome do Produto'),

            ExportColumn::make('category')
                ->label('Categoria'),

            ExportColumn::make('cost_price')
                ->label('Preço de Custo')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('sale_price')
                ->label('Preço de Venda')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),

            ExportColumn::make('stock_reference')
                ->label('Fornecedor Parceiro'),

            ExportColumn::make('description')
                ->label('Descrição e Detalhes'),

            ExportColumn::make('is_active')
                ->label('Status Ativo')
                ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'O catálogo de produtos foi exportado com sucesso! ' . number_format($export->successful_rows) . ' produtos processados.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Houve falha em ' . number_format($failedRowsCount) . ' registros.';
        }

        return $body;
    }
}
