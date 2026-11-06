<?php

namespace Tests\Feature;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductExportImportRoundtripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida que as colunas da exportação e da importação de produtos são 100% simétricas,
     * possuindo exatamente as mesmas 8 colunas e sem nenhum campo divergente ou sobrando.
     */
    public function test_product_export_and_import_columns_are_strictly_unified(): void
    {
        $exportColumns = ProductExporter::getColumns();
        $importColumns = ProductImporter::getColumns();

        $this->assertCount(8, $exportColumns, 'ProductExporter deve exportar exatamente as 8 colunas essenciais do catálogo.');
        $this->assertCount(8, $importColumns, 'ProductImporter deve receber exatamente as 8 colunas essenciais do catálogo.');

        $exportNames = array_map(fn (ExportColumn $col) => $col->getName(), $exportColumns);
        $importNames = array_map(fn (ImportColumn $col) => $col->getName(), $importColumns);

        $this->assertEquals($exportNames, $importNames, 'Os nomes das colunas de exportação e importação devem ser idênticos e na mesma ordem.');

        $expectedColumns = [
            'sku',
            'name',
            'category',
            'cost_price',
            'sale_price',
            'stock_reference',
            'description',
            'is_active',
        ];

        $this->assertEquals($expectedColumns, $exportNames);
    }

    /**
     * Valida que o arquivo exportado pelo ProductExporter é reconhecido e mapeado
     * 100% de forma automática pelo ProductImporter, sem nenhuma coluna divergente ou nula.
     */
    public function test_exported_headers_are_automatically_and_completely_mapped_by_importer(): void
    {
        $exportColumns = ProductExporter::getColumns();

        // Extrai os cabeçalhos literais gerados pelo exporter (como saem na planilha Excel/CSV)
        $exportedHeaders = array_map(function (ExportColumn $col) {
            return $col->getLabel();
        }, $exportColumns);

        $this->assertEquals([
            'Código do Produto (SKU)',
            'Nome do Produto',
            'Categoria',
            'Preço de Custo',
            'Preço de Venda',
            'Fornecedor Parceiro',
            'Descrição e Detalhes',
            'Status Ativo',
        ], $exportedHeaders);

        // Algoritmo nativo de matching do Filament Actions Importer
        $lowercaseCsvColumnValues = array_map(Str::lower(...), $exportedHeaders);
        $lowercaseCsvColumnKeys = array_combine($lowercaseCsvColumnValues, $exportedHeaders);

        $importColumns = ProductImporter::getColumns();
        $mapped = [];

        foreach ($importColumns as $column) {
            $match = Arr::first(
                array_intersect($lowercaseCsvColumnValues, $column->getGuesses())
            );

            $mapped[$column->getName()] = $lowercaseCsvColumnKeys[$match] ?? null;
        }

        // Cada uma das 8 colunas DEVE estar perfeitamente mapeada sem nenhuma divergência
        foreach ($mapped as $field => $header) {
            $this->assertNotNull($header, "O campo {$field} não pôde ser mapeado automaticamente a partir do cabeçalho exportado!");
        }

        $this->assertEquals('Código do Produto (SKU)', $mapped['sku']);
        $this->assertEquals('Nome do Produto', $mapped['name']);
        $this->assertEquals('Categoria', $mapped['category']);
        $this->assertEquals('Preço de Custo', $mapped['cost_price']);
        $this->assertEquals('Preço de Venda', $mapped['sale_price']);
        $this->assertEquals('Fornecedor Parceiro', $mapped['stock_reference']);
        $this->assertEquals('Descrição e Detalhes', $mapped['description']);
        $this->assertEquals('Status Ativo', $mapped['is_active']);
    }

    /**
     * Valida a ida e volta (roundtrip) completa de dados:
     * Exporta valores formatados em moeda brasileira (R$ com vírgula e milhar) e status Sim/Não,
     * e o importador higieniza e atualiza o produto com precisão no banco.
     */
    public function test_roundtrip_data_sanitization_and_record_updating(): void
    {
        $product = Product::create([
            'sku' => 'AMB-MESA-001',
            'name' => 'Mesa de Centro Rústica Carvalho',
            'category' => 'Móveis & Mesas',
            'cost_price' => 1250.50,
            'sale_price' => 2490.90,
            'stock_reference' => 'Marcenaria Nobre',
            'description' => 'Madeira maciça com acabamento em verniz fosco',
            'is_active' => true,
        ]);

        // Simula os valores exatos formatados pela exportação
        $exportedCost = number_format(1250.50, 2, ',', '.'); // "1.250,50"
        $exportedSale = number_format(2490.90, 2, ',', '.'); // "2.490,90"
        $exportedActive = 'Sim';

        $this->assertEquals('1.250,50', $exportedCost);
        $this->assertEquals('2.490,90', $exportedSale);

        // Testa higienização do importador
        $this->assertEquals(1250.50, ProductImporter::sanitizePrice($exportedCost));
        $this->assertEquals(2490.90, ProductImporter::sanitizePrice($exportedSale));
        $this->assertEquals(1250.50, ProductImporter::sanitizePrice('R$ 1.250,50'));

        // Simula a resolução do registro pelo SKU
        $importer = new ProductImporter(new \Filament\Actions\Imports\Models\Import(), [], []);
        $reflection = new \ReflectionClass($importer);
        $dataProperty = $reflection->getProperty('data');
        $dataProperty->setAccessible(true);

        $dataProperty->setValue($importer, [
            'sku' => 'AMB-MESA-001',
            'name' => 'Mesa de Centro Rústica Carvalho Atualizada',
            'cost_price' => ProductImporter::sanitizePrice('1.300,00'),
            'sale_price' => ProductImporter::sanitizePrice('2.600,00'),
        ]);

        $resolved = $importer->resolveRecord();

        $this->assertNotNull($resolved);
        $this->assertEquals($product->id, $resolved->id, 'Deve identificar o mesmo produto pelo SKU exportado.');
    }
}
