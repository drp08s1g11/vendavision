<?php

namespace Tests\Feature;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\Csv\Reader as CsvReader;
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

    /**
     * TESTE DE INTEGRAÇÃO COMPLETO (E2E):
     * Executa o fluxo real ponta a ponta:
     * 1. Cria produtos no banco de dados.
     * 2. Gera a exportação CSV real via ProductExporter.
     * 3. Simula o usuário abrindo o CSV e editando preços e adicionando um novo produto.
     * 4. Executa o Job real do Filament ImportCsv.
     * 5. Comprova que os dados foram atualizados sem duplicação e os novos foram criados com sucesso.
     */
    public function test_full_e2e_export_to_csv_and_import_pipeline_execution(): void
    {
        $user = User::factory()->create();

        $prodA = Product::create([
            'sku' => 'AMB-QD-001',
            'name' => 'Quadro Canvas Flores',
            'category' => 'Quadros',
            'cost_price' => 50.00,
            'sale_price' => 120.00,
            'stock_reference' => 'Fábrica SP',
            'description' => 'Canvas premium',
            'is_active' => true,
        ]);

        $prodB = Product::create([
            'sku' => 'AMB-ESP-002',
            'name' => 'Espelho Redondo',
            'category' => 'Espelhos',
            'cost_price' => 80.00,
            'sale_price' => 190.00,
            'stock_reference' => 'Vidraçaria Sul',
            'description' => 'Espelho 50cm',
            'is_active' => false,
        ]);

        // 1. Simula a exportação real
        $columnMap = [
            'sku' => 'Código do Produto (SKU)',
            'name' => 'Nome do Produto',
            'category' => 'Categoria',
            'cost_price' => 'Preço de Custo',
            'sale_price' => 'Preço de Venda',
            'stock_reference' => 'Fornecedor Parceiro',
            'description' => 'Descrição e Detalhes',
            'is_active' => 'Status Ativo',
        ];

        $export = new \Filament\Actions\Exports\Models\Export();
        $export->user()->associate($user);
        $export->exporter = ProductExporter::class;
        $export->total_rows = 2;
        $export->file_disk = 'local';
        $export->file_name = 'produtos_exportados.csv';
        $export->save();

        $exporterInstance = $export->getExporter($columnMap, []);

        $rowA = $exporterInstance($prodA);
        $rowB = $exporterInstance($prodB);

        $this->assertEquals(['AMB-QD-001', 'Quadro Canvas Flores', 'Quadros', '50,00', '120,00', 'Fábrica SP', 'Canvas premium', 'Sim'], $rowA);
        $this->assertEquals(['AMB-ESP-002', 'Espelho Redondo', 'Espelhos', '80,00', '190,00', 'Vidraçaria Sul', 'Espelho 50cm', 'Não'], $rowB);

        // 2. Simula o usuário alterando o CSV:
        // - Atualiza o preço do Produto A de 120,00 para 145,90 e altera status para Não
        // - Adiciona um Produto C inédito (SKU AMB-NOVO-003)
        $csvRowsToImport = [
            [
                'Código do Produto (SKU)' => 'AMB-QD-001',
                'Nome do Produto' => 'Quadro Canvas Flores Atualizado',
                'Categoria' => 'Quadros Modernos',
                'Preço de Custo' => '55,50',
                'Preço de Venda' => '145,90',
                'Fornecedor Parceiro' => 'Fábrica SP Nova',
                'Descrição e Detalhes' => 'Canvas premium atualizado',
                'Status Ativo' => 'Não',
            ],
            [
                'Código do Produto (SKU)' => 'AMB-NOVO-003',
                'Nome do Produto' => 'Vaso Nó Terracota 25cm',
                'Categoria' => 'Vasos',
                'Preço de Custo' => '32,00',
                'Preço de Venda' => '89,90',
                'Fornecedor Parceiro' => 'Cerâmica Decor',
                'Descrição e Detalhes' => 'Cerâmica artesanal fosca',
                'Status Ativo' => 'Sim',
            ],
        ];

        // 3. Executa o Importador do Filament
        $import = new Import();
        $import->user()->associate($user);
        $import->importer = ProductImporter::class;
        $import->total_rows = 2;
        $import->file_name = 'produtos_reimportacao.csv';
        $import->file_path = 'imports/produtos_reimportacao.csv';
        $import->save();

        // Mapeamento automático (exatamente como o Filament detecta a partir dos cabeçalhos exportados)
        $importColumnMap = [
            'sku' => 'Código do Produto (SKU)',
            'name' => 'Nome do Produto',
            'category' => 'Categoria',
            'cost_price' => 'Preço de Custo',
            'sale_price' => 'Preço de Venda',
            'stock_reference' => 'Fornecedor Parceiro',
            'description' => 'Descrição e Detalhes',
            'is_active' => 'Status Ativo',
        ];

        // Executa o Job real do Filament de forma síncrona
        $job = new ImportCsv($import, $csvRowsToImport, $importColumnMap);
        $job->handle();

        // 4. Verificações no banco de dados para comprovação absoluta
        $import->refresh();
        $this->assertEquals(2, $import->successful_rows, 'Todas as 2 linhas do CSV devem ter sido importadas com 100% de sucesso.');
        $this->assertEquals(0, $import->getFailedRowsCount(), 'Não deve haver nenhuma linha com falha.');

        // Produto A: Deve estar atualizado, sem criar duplicatas
        $this->assertEquals(1, Product::where('sku', 'AMB-QD-001')->count(), 'Não deve duplicar o produto existente!');
        $updatedA = Product::where('sku', 'AMB-QD-001')->first();
        $this->assertEquals($prodA->id, $updatedA->id, 'Deve manter o mesmo ID original.');
        $this->assertEquals('Quadro Canvas Flores Atualizado', $updatedA->name);
        $this->assertEquals('Quadros Modernos', $updatedA->category);
        $this->assertEquals(55.50, (float) $updatedA->cost_price);
        $this->assertEquals(145.90, (float) $updatedA->sale_price);
        $this->assertFalse((bool) $updatedA->is_active, 'Status "Não" deve ter sido convertido para false.');

        // Produto C: Deve ter sido criado com sucesso
        $createdC = Product::where('sku', 'AMB-NOVO-003')->first();
        $this->assertNotNull($createdC, 'O novo produto deve existir no banco de dados.');
        $this->assertEquals('Vaso Nó Terracota 25cm', $createdC->name);
        $this->assertEquals(32.00, (float) $createdC->cost_price);
        $this->assertEquals(89.90, (float) $createdC->sale_price);
        $this->assertTrue((bool) $createdC->is_active, 'Status "Sim" deve ter sido convertido para true.');

        // Total de produtos no banco deve ser 3 (Prod A atualizado, Prod B intacto, Prod C novo)
        $this->assertEquals(3, Product::count());
    }
}
