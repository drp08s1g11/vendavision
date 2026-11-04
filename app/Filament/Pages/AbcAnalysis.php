<?php

namespace App\Filament\Pages;

use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\SaleItem;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AbcAnalysis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Curva ABC (Pareto)';

    protected static ?string $title = 'Análise de Curva ABC de Produtos (Princípio de Pareto)';

    protected static ?string $navigationGroup = 'Inteligência Comercial';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.abc-analysis';

    /**
     * Calcula a Curva ABC de faturamento dos produtos cadastrados
     */
    public function getAbcDataProperty(): array
    {
        // Agrupa vendas faturadas por produto
        $productStats = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereIn('sales.status', [SaleStatus::Paid->value, SaleStatus::Shipped->value])
            ->whereNull('products.deleted_at')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.subtotal - sale_items.item_fee) as total_profit'),
            ])
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category')
            ->orderByDesc('total_revenue')
            ->get();

        $totalRevenueAll = (float) $productStats->sum('total_revenue');

        $accumulated = 0.0;
        $classifiedProducts = [];
        $classA = ['count' => 0, 'revenue' => 0.0, 'profit' => 0.0];
        $classB = ['count' => 0, 'revenue' => 0.0, 'profit' => 0.0];
        $classC = ['count' => 0, 'revenue' => 0.0, 'profit' => 0.0];

        $rank = 1;
        foreach ($productStats as $item) {
            $revenue = (float) $item->total_revenue;
            $profit = (float) $item->total_profit;
            $percentage = $totalRevenueAll > 0 ? ($revenue / $totalRevenueAll) * 100 : 0;
            $accumulated += $percentage;

            // Classificação clássica de Pareto (80% / 15% / 5%)
            if ($accumulated <= 80.0 || $rank === 1) {
                $class = 'A';
                $classA['count']++;
                $classA['revenue'] += $revenue;
                $classA['profit'] += $profit;
            } elseif ($accumulated <= 95.0) {
                $class = 'B';
                $classB['count']++;
                $classB['revenue'] += $revenue;
                $classB['profit'] += $profit;
            } else {
                $class = 'C';
                $classC['count']++;
                $classC['revenue'] += $revenue;
                $classC['profit'] += $profit;
            }

            $classifiedProducts[] = [
                'rank' => $rank++,
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $item->category ?? 'Geral',
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => $revenue,
                'total_profit' => $profit,
                'margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                'percentage' => $percentage,
                'accumulated' => min(100.0, $accumulated),
                'class' => $class,
            ];
        }

        return [
            'total_revenue' => $totalRevenueAll,
            'total_products_sold' => count($classifiedProducts),
            'total_catalog_count' => Product::count(),
            'class_a' => $classA,
            'class_b' => $classB,
            'class_c' => $classC,
            'products' => $classifiedProducts,
        ];
    }
}
