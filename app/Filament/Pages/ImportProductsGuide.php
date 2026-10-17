<?php

namespace App\Filament\Pages;

use App\Filament\Imports\ProductImporter;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportProductsGuide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Importar Planilha';

    protected static ?string $title = 'Central de Importação de Produtos & Guia de Planilha';

    protected static ?string $navigationGroup = 'Gestão Comercial';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.import-products-guide';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadSample')
                ->label('Baixar Planilha Modelo (.CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('downloadSampleCsv'),

            ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Fazer Upload da Planilha')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary'),
        ];
    }

    /**
     * Gera e envia para download um arquivo CSV modelo formatado com cabeçalho 100% em português,
     * termos descritivos de fácil compreensão e BOM UTF-8 para abertura perfeita no Microsoft Excel.
     */
    public function downloadSampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modelo_importacao_produtos_ambrozio.csv"',
        ];

        // Cabeçalho 100% em português claro e descritivo
        $columns = [
            'codigo_produto_sku',
            'nome_do_produto',
            'categoria',
            'preco_de_custo_fornecedor',
            'preco_de_venda',
            'fornecedor_parceiro',
            'descricao_detalhada',
            'produto_ativo',
        ];

        $sampleRows = [
            [
                'AMB-QD-010',
                'Quadro Canvas Abstrato Dourado Minimalista 60x90cm',
                'Quadros e Telas',
                '58.00',
                '169.90',
                'Arte Quadros SP',
                'Tela canvas 100% algodão com chassi de madeira reflorestada 3cm',
                '1',
            ],
            [
                'AMB-ALM-011',
                'Kit 2 Almofadas Linho Boho Chic 45x45cm com Zíper',
                'Têxtil & Almofadas',
                '34.50',
                '89.90',
                'Confecções Têxtil MG',
                'Capas em linho rústico premium com zíper invisível e toque macio',
                '1',
            ],
            [
                'AMB-ESP-012',
                'Espelho Redondo Adnet 50cm Alça Couro Caramelo',
                'Espelhos & Paredes',
                '48.00',
                '139.90',
                'Vidraçaria & Molduras',
                'Vidro cristal lapidado de alta nitidez com suporte reforçado',
                '1',
            ],
            [
                'AMB-VAS-013',
                'Vaso Cerâmica Nó Escultural Terracota Matte 20cm',
                'Cerâmicas & Vasos',
                '26.00',
                '79.90',
                'Ateliê Cerâmica PR',
                'Peça de design nórdico contemporâneo acabamento fosco',
                '1',
            ],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            // Byte Order Mark (BOM) UTF-8 para o Excel reconhecer acentuação e caracteres no Windows
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns, ';');
            foreach ($sampleRows as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
