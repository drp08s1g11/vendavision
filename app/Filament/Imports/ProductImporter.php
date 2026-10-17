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
                ->guess([
                    'nome_do_produto',
                    'nome',
                    'produto',
                    'titulo',
                    'nome do produto',
                    'name',
                ])
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('sku')
                ->label('Código do Produto (SKU)')
                ->guess([
                    'codigo_produto_sku',
                    'codigo_do_produto_sku',
                    'codigo_sku',
                    'codigo',
                    'sku',
                    'referencia',
                    'código do produto',
                ])
                ->requiredMapping()
                ->rules(['required', 'max:64']),

            ImportColumn::make('category')
                ->label('Categoria do Item')
                ->guess([
                    'categoria',
                    'tipo',
                    'departamento',
                    'category',
                ])
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('cost_price')
                ->label('Preço de Custo (Fornecedor)')
                ->guess([
                    'preco_de_custo_fornecedor',
                    'preco_de_custo',
                    'custo_fornecedor',
                    'custo',
                    'preco de custo',
                    'cost_price',
                ])
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('sale_price')
                ->label('Preço de Venda')
                ->guess([
                    'preco_de_venda',
                    'preco_venda',
                    'valor_de_venda',
                    'preco',
                    'preco de venda',
                    'sale_price',
                ])
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('stock_reference')
                ->label('Fornecedor Parceiro')
                ->guess([
                    'fornecedor_parceiro',
                    'fornecedor',
                    'fabricante',
                    'parceiro',
                    'stock_reference',
                ])
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('description')
                ->label('Descrição e Detalhes')
                ->guess([
                    'descricao_detalhada',
                    'descricao',
                    'detalhes',
                    'description',
                ])
                ->rules(['nullable']),

            ImportColumn::make('status')
                ->label('Produto Ativo (1=Sim / 0=Não)')
                ->guess([
                    'produto_ativo',
                    'ativo',
                    'status',
                    'disponivel',
                    'is_active',
                ])
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
        $body = 'A importação de produtos foi concluída com sucesso! ' . number_format($import->successful_rows) . ' produtos processados e sincronizados na base.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Houve falha em ' . number_format($failedRowsCount) . ' registros (verifique formatação).';
        }

        return $body;
    }
}
