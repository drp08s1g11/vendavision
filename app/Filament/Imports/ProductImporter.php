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

        // Caso contenha sinal negativo no início (ex: "-0,49" ou "- 5,00")
        $isNegative = str_starts_with($cleaned, '-');
        $cleaned = ltrim($cleaned, '-');

        // Caso contenha ponto de milhar e vírgula decimal (ex: 1.250,50)
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            // Caso contenha apenas vírgula decimal (ex: 58,00)
            $cleaned = str_replace(',', '.', $cleaned);
        }

        if (!is_numeric($cleaned)) {
            return null;
        }

        $val = round((float) $cleaned, 2);
        return $isNegative ? 0.00 : $val;
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sku')
                ->label('Código do Produto (SKU)')
                ->guess([
                    'codigo_do_produto_sku',
                    'código do produto (sku)',
                    'codigo do produto (sku)',
                    'codigo_produto_sku',
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

            ImportColumn::make('name')
                ->label('Nome do Produto')
                ->guess([
                    'nome_do_produto',
                    'nome do produto',
                    'nome',
                    'produto',
                    'titulo',
                    'name',
                ])
                ->requiredMapping()
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
                ->rules(['required', 'max:255']),

            ImportColumn::make('category')
                ->label('Categoria')
                ->guess([
                    'categoria',
                    'categoria_do_item',
                    'categoria do item',
                    'tipo',
                    'departamento',
                    'category',
                ])
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : 'Geral')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('cost_price')
                ->label('Preço de Custo')
                ->guess([
                    'preco_de_custo',
                    'preço de custo',
                    'preco de custo',
                    'preço de custo (r$)',
                    'preco de custo (r$)',
                    'preco_de_custo_r',
                    'preco_de_custo_fornecedor',
                    'custo_fornecedor',
                    'custo',
                    'cost_price',
                ])
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::sanitizePrice($state))
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('sale_price')
                ->label('Preço de Venda')
                ->guess([
                    'preco_de_venda',
                    'preço de venda',
                    'preco de venda',
                    'preço de venda (r$)',
                    'preco de venda (r$)',
                    'preco_de_venda_r',
                    'preco_venda',
                    'valor_de_venda',
                    'preco',
                    'sale_price',
                ])
                ->requiredMapping()
                ->castStateUsing(fn ($state) => self::sanitizePrice($state))
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('stock_reference')
                ->label('Fornecedor Parceiro')
                ->guess([
                    'fornecedor_parceiro',
                    'fornecedor parceiro',
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
                    'descricao_e_detalhes',
                    'descrição e detalhes',
                    'descricao e detalhes',
                    'descricao_detalhada',
                    'descricao',
                    'detalhes',
                    'description',
                ])
                ->castStateUsing(fn ($state) => filled($state) ? trim((string) $state) : null)
                ->rules(['nullable']),

            ImportColumn::make('is_active')
                ->label('Status Ativo')
                ->guess([
                    'status_ativo',
                    'status ativo',
                    'produto_ativo',
                    'produto ativo',
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
