<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\ShopeeReportImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildProductsFromShopee extends Command
{
    protected $signature = 'products:rebuild-from-shopee {path?}';
    protected $description = 'Deduplica e recria o catálogo de produtos e vendas 100% baseado no relatório real da Shopee';

    public function handle(ShopeeReportImportService $importService)
    {
        $path = $this->argument('path') ?: '/var/www/html/storage/Pasta1.csv';
        if (!file_exists($path)) {
            $this->error("Arquivo não encontrado em: {$path}");
            return 1;
        }

        $this->info("Iniciando reconstrução do catálogo a partir de: {$path}");

        // 1. Lê e agrupa por SKU único
        $content = file_get_contents($path);
        $cleanContent = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\r|\n/', $cleanContent);

        $delimiter = ';';
        if (substr_count(substr($cleanContent, 0, 2000), ',') > substr_count(substr($cleanContent, 0, 2000), ';')) {
            $delimiter = ',';
        }

        // Encontra cabeçalho
        $headerMap = [];
        $headerRow = 0;
        foreach ($lines as $idx => $line) {
            $cols = str_getcsv($line, $delimiter);
            $lower = mb_strtolower($line);
            if (str_contains($lower, 'id do pedido') && (str_contains($lower, 'sku') || str_contains($lower, 'nome do produto'))) {
                $headerRow = $idx;
                foreach ($cols as $cIdx => $cName) {
                    $headerMap[trim(mb_strtolower($cName))] = $cIdx;
                }
                break;
            }
        }

        $parseNum = function ($val): float {
            if (!$val || $val === '-') return 0.0;
            $val = preg_replace('/[^\d,.-]/', '', trim((string)$val));
            if (str_contains($val, '.') && str_contains($val, ',')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } elseif (str_contains($val, ',')) {
                $val = str_replace(',', '.', $val);
            }
            return (float)$val;
        };

        $getCol = function ($row, $keys) use ($headerMap) {
            foreach ($keys as $k) {
                $kLower = mb_strtolower($k);
                if (isset($headerMap[$kLower]) && isset($row[$headerMap[$kLower]])) {
                    return trim($row[$headerMap[$kLower]]);
                }
            }
            return null;
        };

        $skuData = [];

        for ($i = $headerRow + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            $row = str_getcsv($line, $delimiter);
            if (count($row) < 5) continue;

            $ver = $getCol($row, ['ver', 'tipo']);
            $sku = $getCol($row, ['sku', 'código sku']);
            $name = $getCol($row, ['nome do produto', 'produto']);
            $price = $parseNum($getCol($row, ['preço do produto']));
            $net = $parseNum($getCol($row, ['quantia total lançada (r$)']));

            if ($ver === 'Sku' || (filled($name) && $name !== '-')) {
                if (!$sku || $sku === '-') continue;

                if (!isset($skuData[$sku])) {
                    $skuData[$sku] = [
                        'sku' => $sku,
                        'names' => [],
                        'prices' => [],
                        'net_amounts' => [],
                        'sales_count' => 0,
                    ];
                }

                if (filled($name) && $name !== '-') {
                    $skuData[$sku]['names'][] = $name;
                }
                if ($price > 0) {
                    $skuData[$sku]['prices'][] = $price;
                }
                if ($net > 0) {
                    $skuData[$sku]['net_amounts'][] = $net;
                }
                $skuData[$sku]['sales_count']++;
            }
        }

        $this->info("Total de SKUs únicos identificados na planilha: " . count($skuData));

        // Custos base de referência para produtos de decoração
        $knownCosts = [
            '58200310538' => 12.78, // Capa Galão 20L
            '58200311853' => 58.94, // Namoradeira Opala
            '23494381997' => 42.57, // Poltrona Ferradura
            '23194376153' => 50.39, // Poltrona Opala
            '22798884140' => 39.76, // Canoa Vegas
            '23099346266' => 59.92, // Poltrona Nina
            '58200311484' => 65.20, // Ferradura Estampada
            '23094366092' => 51.56, // Sofá Cama 3 Lugares
            '22098953683' => 8.15,  // Cadeira Escritório
            '22899136287' => 57.00, // 4 Capas Jantar
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SaleItem::truncate();
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $created = 0;
        foreach ($skuData as $sku => $d) {
            // Nome limpo (pega o mais longo sem variações de cor)
            $rawName = !empty($d['names']) ? max($d['names']) : "Produto {$sku}";
            $cleanName = trim(explode(' - ', $rawName)[0]);

            // Categoria inteligente
            $cat = 'Capas';
            $nLower = mb_strtolower($cleanName);
            if (str_contains($nLower, 'galão')) {
                $cat = 'Capas de Galão';
            } elseif (str_contains($nLower, 'sofá') || str_contains($nLower, 'namoradeira')) {
                $cat = 'Capas de Sofá';
            } elseif (str_contains($nLower, 'poltrona')) {
                $cat = 'Capas de Poltrona';
            } elseif (str_contains($nLower, 'cadeira')) {
                $cat = 'Capas de Cadeira';
            }

            // Preço de venda base unitário
            $basePrice = !empty($d['prices']) ? min($d['prices']) : 59.90;
            $costPrice = $knownCosts[$sku] ?? round($basePrice * 0.50, 2);

            Product::create([
                'sku' => $sku,
                'name' => $cleanName,
                'category' => $cat,
                'description' => "Produto importado do catálogo oficial da Shopee (SKU {$sku})",
                'cost_price' => $costPrice,
                'sale_price' => $basePrice,
                'stock_reference' => 'Ambrozio Decora',
                'is_active' => true,
            ]);

            $this->line("Criado produto [SKU: {$sku}] {$cleanName} (Venda: R$ {$basePrice} | Custo: R$ {$costPrice})");
            $created++;
        }

        $this->info("Recriados {$created} produtos limpos no catálogo!");

        // Agora re-processa todas as vendas da planilha para vincular exatamente a esses produtos
        $this->info("Re-vinculando histórico completo de vendas...");
        $res = $importService->importFromCsvContent($content);

        $this->info("Resultado da importação: " . $res['message']);
        return 0;
    }
}
