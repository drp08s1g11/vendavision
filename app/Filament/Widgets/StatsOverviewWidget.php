<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $channel = $this->filters['channel'] ?? null;

        $paidSales = Sale::whereIn('status', [SaleStatus::Paid, SaleStatus::Shipped])
            ->when($startDate, fn ($query) => $query->whereDate('sold_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('sold_at', '<=', $endDate))
            ->when($channel, fn ($query) => $query->where('channel', $channel));

        $totalRevenue = (float) (clone $paidSales)->sum('total_amount');
        $totalCost = (float) (clone $paidSales)->sum('total_cost');
        $totalProfit = (float) (clone $paidSales)->sum('gross_profit');
        $totalOrders = (int) (clone $paidSales)->count();
        $overallMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;
        $avgTicket = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        // Histórico real para mini-sparklines
        $revenueSparkline = [];
        $profitSparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $dayRevenue = (float) (clone $paidSales)
                ->whereDate('sold_at', $day)
                ->sum('total_amount');
            $dayProfit = (float) (clone $paidSales)
                ->whereDate('sold_at', $day)
                ->sum('gross_profit');

            $revenueSparkline[] = $dayRevenue;
            $profitSparkline[] = $dayProfit;
        }

        return [
            Stat::make('Faturamento Consolidado', 'R$ ' . number_format($totalRevenue, 2, ',', '.'))
                ->description('Receita bruta no período selecionado')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($revenueSparkline),

            Stat::make('Lucro Líquido Real', 'R$ ' . number_format($totalProfit, 2, ',', '.'))
                ->description('Margem Média: ' . number_format($overallMargin, 1, ',', '.') . '% pós taxas e custos')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->chart($profitSparkline),

            Stat::make('Custos Fornecedor & Operação', 'R$ ' . number_format($totalCost, 2, ',', '.'))
                ->description('Repasses fornecedores dropshipping + fretes + taxas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Volume & Ticket Médio', number_format($totalOrders, 0, ',', '.') . ' pedidos')
                ->description('Ticket Médio: R$ ' . number_format($avgTicket, 2, ',', '.') . ' / pedido')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
        ];
    }
}
