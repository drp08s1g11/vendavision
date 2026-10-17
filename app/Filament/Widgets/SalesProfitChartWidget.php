<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class SalesProfitChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Evolução Financeira Diária (Faturamento vs Lucro Real)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = [
        'md' => 8,
        'xl' => 8,
    ];
    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : Carbon::now()->subDays(13);
        $endDate = $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : Carbon::now();
        $channel = $this->filters['channel'] ?? null;

        // Se o intervalo for maior que 31 dias, limita em 31 para manter legibilidade do gráfico
        if ($startDate->diffInDays($endDate) > 31) {
            $startDate = (clone $endDate)->subDays(31);
        }

        $labels = [];
        $revenues = [];
        $costs = [];
        $profits = [];

        $current = clone $startDate;
        while ($current->lte($endDate)) {
            $dayString = $current->toDateString();
            $labels[] = $current->format('d/m');

            $daySales = Sale::whereIn('status', [SaleStatus::Paid, SaleStatus::Shipped])
                ->whereDate('sold_at', $dayString)
                ->when($channel, fn ($query) => $query->where('channel', $channel))
                ->get();

            $revenues[] = (float) $daySales->sum('total_amount');
            $costs[] = (float) $daySales->sum('total_cost');
            $profits[] = (float) $daySales->sum('gross_profit');

            $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento Bruto (R$)',
                    'data' => $revenues,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Lucro Líquido Real (R$)',
                    'data' => $profits,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Custo de Mercadoria & Frete (R$)',
                    'data' => $costs,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [4, 4],
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
