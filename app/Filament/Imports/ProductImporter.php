<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    /**
     * Limpa e padroniza strings monetárias para float numérico válido.
     * Suporta formato brasileiro (1.250,50 ou 58,00 ou R$ 58,00) e americano (58.00).
     */
    public static function sanitizePrice(mixed $state): ?float
    {
        if (blank($state)) {
            return null;
        }

        if (is_numeric($state)) {
            return round((float) $state, 2);
        }

        $cleaned = trim((string) $state);
        // Remove símbolos de moeda como R$, US$, espaços, etc.
        $cleaned = preg_replace('/[^\d,.-]/', '', $cleaned);

        if ($cleaned === '') {
            return null;
        }

        // Caso contenha ponto de milhar e vírgula decimal (ex: 1.250,50)
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            // Caso contenha apenas vírgula decimal (ex: 58,00)
            $cleaned = str_replace(',', '.', $cleaned);
        }

        return is_numeric($cleaned) ? round((float) $cleaned, 2) : null;
    }

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
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
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
                ->castStateUsing(function ($state) {
                    $cleaned = trim((string) $state);
                    return filled($cleaned) ? $cleaned : 'SKU-' . strtoupper(Str::random(8));
                })
                ->rules(['required', 'max:64']),

            ImportColumn::make('category')
                ->label('Categoria do Item')
                ->guess([
                    'categoria',
                    'tipo',
                    'departamento',
                    'category',
                ])
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : 'Geral')
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
                ->castStateUsing(fn ($state) => self::sanitizePrice($state))
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
                ->castStateUsing(fn ($state) => self::sanitizePrice($state))
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
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('description')
                ->label('Descrição e Detalhes')
                ->guess([
                    'descricao_detalhada',
                    'descricao',
                    'detalhes',
                    'description',
                ])
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
                ->rules(['nullable']),

            ImportColumn::make('is_active')
                ->label('Produto Ativo (1=Sim / 0=Não)')
                ->guess([
                    'produto_ativo',
                    'ativo',
                    'status',
                    'disponivel',
                    'is_active',
                ])
                ->castStateUsing(function ($state) {
                    if (blank($state)) {
                        return true;
                    }
                    $normalized = strtolower(trim((string) $state));
                    return in_array($normalized, ['1', 'true', 'sim', 's', 'yes', 'ativo'], true);
                })
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $sku = trim((string) ($this->data['sku'] ?? ''));

        if (filled($sku)) {
            // Busca produto existente pelo SKU para atualizar, ou instancia novo
            $existing = Product::withTrashed()->where('sku', $sku)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                return $existing;
            }

            return new Product(['sku' => $sku]);
        }

        return new Product();
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
