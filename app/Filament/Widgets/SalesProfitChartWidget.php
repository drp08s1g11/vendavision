<?php

namespace App\Filament\Widgets;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class SalesProfitChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = [
        'md' => 8,
        'xl' => 8,
    ];
    protected static ?string $maxHeight = '320px';

    /**
     * Resolve o intervalo de datas baseado nos filtros do dashboard.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveDateRange(): array
    {
        $startFilter = $this->filters['startDate'] ?? null;
        $endFilter = $this->filters['endDate'] ?? null;

        $startDate = $startFilter
            ? Carbon::parse($startFilter)->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $endDate = $endFilter
            ? Carbon::parse($endFilter)->endOfDay()
            : Carbon::now()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    /**
     * Define a granularidade temporal adaptativa baseada na extensão do período.
     */
    protected function getGranularity(Carbon $startDate, Carbon $endDate): string
    {
        $diffDays = $startDate->diffInDays($endDate);

        if ($diffDays <= 45) {
            return 'day';
        }

        if ($diffDays <= 180) {
            return 'week';
        }

        if ($diffDays <= 1095) {
            return 'month';
        }

        return 'quarter';
    }

    public function getHeading(): string | Htmlable | null
    {
        [$startDate, $endDate] = $this->resolveDateRange();
        $granularity = $this->getGranularity($startDate, $endDate);

        return match ($granularity) {
            'day' => 'Evolução Financeira Diária (Faturamento vs Lucro Real)',
            'week' => 'Evolução Financeira Semanal (Faturamento vs Lucro Real)',
            'month' => 'Evolução Financeira Mensal (Faturamento vs Lucro Real)',
            'quarter' => 'Evolução Financeira Trimestral (Faturamento vs Lucro Real)',
        };
    }

    public function getDescription(): string | Htmlable | null
    {
        [$startDate, $endDate] = $this->resolveDateRange();
        $granularity = $this->getGranularity($startDate, $endDate);

        return match ($granularity) {
            'day' => 'Dados agrupados dia a dia para detalhamento no curto prazo.',
            'week' => 'Dados agrupados semana a semana para visualização fluida e balanceada.',
            'month' => 'Dados consolidados mês a mês para análise consistente de longo prazo.',
            'quarter' => 'Dados consolidados por trimestre para planejamento estratégico plurianual.',
        };
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->resolveDateRange();
        $granularity = $this->getGranularity($startDate, $endDate);
        $channel = $this->filters['channel'] ?? null;

        // Executa uma única query otimizada com índice para todo o período
        $sales = Sale::query()
            ->whereIn('status', [SaleStatus::Paid, SaleStatus::Shipped])
            ->whereBetween('sold_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($channel, fn ($query) => $query->where('channel', $channel))
            ->get(['sold_at', 'total_amount', 'total_cost', 'gross_profit']);

        $ptMonths = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        $labels = [];
        $revenues = [];
        $costs = [];
        $profits = [];

        if ($granularity === 'day') {
            $salesByDay = $sales->groupBy(fn ($sale) => Carbon::parse($sale->sold_at)->format('Y-m-d'));
            $sameYear = $startDate->year === $endDate->year;
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $dayKey = $current->format('Y-m-d');
                $labels[] = $sameYear ? $current->format('d/m') : $current->format('d/m/y');

                $daySales = $salesByDay->get($dayKey);
                $revenues[] = $daySales ? round((float) $daySales->sum('total_amount'), 2) : 0.0;
                $costs[] = $daySales ? round((float) $daySales->sum('total_cost'), 2) : 0.0;
                $profits[] = $daySales ? round((float) $daySales->sum('gross_profit'), 2) : 0.0;

                $current->addDay();
            }
        } elseif ($granularity === 'week') {
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $weekStart = $current->copy();
                $weekEnd = $current->copy()->addDays(6);
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = $endDate->copy();
                }

                $labels[] = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');

                $weekSales = $sales->filter(function ($sale) use ($weekStart, $weekEnd) {
                    $soldDate = Carbon::parse($sale->sold_at)->startOfDay();
                    return $soldDate->gte($weekStart->startOfDay()) && $soldDate->lte($weekEnd->startOfDay());
                });

                $revenues[] = round((float) $weekSales->sum('total_amount'), 2);
                $costs[] = round((float) $weekSales->sum('total_cost'), 2);
                $profits[] = round((float) $weekSales->sum('gross_profit'), 2);

                $current->addDays(7);
            }
        } elseif ($granularity === 'month') {
            $salesByMonth = $sales->groupBy(fn ($sale) => Carbon::parse($sale->sold_at)->format('Y-m'));
            $current = $startDate->copy()->startOfMonth();

            while ($current->lte($endDate)) {
                $monthKey = $current->format('Y-m');
                $labels[] = $ptMonths[(int) $current->format('n')] . '/' . $current->format('y');

                $monthSales = $salesByMonth->get($monthKey);
                $revenues[] = $monthSales ? round((float) $monthSales->sum('total_amount'), 2) : 0.0;
                $costs[] = $monthSales ? round((float) $monthSales->sum('total_cost'), 2) : 0.0;
                $profits[] = $monthSales ? round((float) $monthSales->sum('gross_profit'), 2) : 0.0;

                $current->addMonth()->startOfMonth();
            }
        } else { // 'quarter'
            $current = $startDate->copy()->firstOfQuarter();

            while ($current->lte($endDate)) {
                $quarterEnd = $current->copy()->endOfQuarter();
                if ($quarterEnd->gt($endDate)) {
                    $quarterEnd = $endDate->copy();
                }

                $labels[] = $current->quarter . 'T/' . $current->format('y');

                $quarterSales = $sales->filter(function ($sale) use ($current, $quarterEnd) {
                    $soldDate = Carbon::parse($sale->sold_at)->startOfDay();
                    return $soldDate->gte($current->startOfDay()) && $soldDate->lte($quarterEnd->startOfDay());
                });

                $revenues[] = round((float) $quarterSales->sum('total_amount'), 2);
                $costs[] = round((float) $quarterSales->sum('total_cost'), 2);
                $profits[] = round((float) $quarterSales->sum('gross_profit'), 2);

                $current->addQuarter()->firstOfQuarter();
            }
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
