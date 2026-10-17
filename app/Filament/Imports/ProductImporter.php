<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome do Produto')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('sku')
                ->label('Código / SKU')
                ->requiredMapping()
                ->rules(['required', 'max:64']),

            ImportColumn::make('category')
                ->label('Categoria')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('cost_price')
                ->label('Custo Fornecedor (R$)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('sale_price')
                ->label('Preço de Venda (R$)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('stock_reference')
                ->label('Fornecedor / Referência')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['nullable']),

            ImportColumn::make('status')
                ->label('Ativo')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        return Product::firstOrNew([
            'sku' => $this->data['sku'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'A importação de produtos foi concluída com sucesso! ' . number_format($import->successful_rows) . ' produtos importados/atualizados.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Houve falha em ' . number_format($failedRowsCount) . ' registros.';
        }

        return $body;
    }
}
