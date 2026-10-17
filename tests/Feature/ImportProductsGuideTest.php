<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportProductsGuide;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class ImportProductsGuideTest extends TestCase
{
    /**
     * Valida que a página de guia de importação carrega com o dicionário de dados em português
     * e permite baixar a planilha modelo CSV pré-formatada com termos simples e descritivos.
     */
    public function test_import_products_guide_page_renders_and_downloads_sample(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        // Renderização Livewire da página com cabeçalhos em português
        Livewire::test(ImportProductsGuide::class)
            ->assertSuccessful()
            ->assertSee('codigo_produto_sku')
            ->assertSee('nome_do_produto')
            ->assertSee('preco_de_custo_fornecedor')
            ->assertSee('preco_de_venda')
            ->assertSee('Baixar Planilha Modelo Oficial');

        // Teste de download da planilha modelo CSV
        $page = new ImportProductsGuide();
        $response = $page->downloadSampleCsv();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('modelo_importacao_produtos_ambrozio.csv', $response->headers->get('Content-Disposition'));
    }
}
