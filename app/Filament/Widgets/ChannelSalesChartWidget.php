<?php

namespace App\Filament\Widgets;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ChannelSalesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Distribuição por Canal de Venda';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = [
        'md' => 4,
        'xl' => 4,
    ];
    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $channelFilter = $this->filters['channel'] ?? null;

        $sales = Sale::whereIn('status', [SaleStatus::Paid, SaleStatus::Shipped])
            ->when($startDate, fn ($query) => $query->whereDate('sold_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('sold_at', '<=', $endDate))
            ->when($channelFilter, fn ($query) => $query->where('channel', $channelFilter))
            ->get();

        $channelDefinitions = [
            SaleChannel::Shopee->value => ['label' => 'Shopee', 'color' => '#f97316', 'provider' => 'shopee'],
            SaleChannel::MercadoLivre->value => ['label' => 'Mercado Livre', 'color' => '#fbbf24', 'provider' => 'mercadolivre'],
            SaleChannel::Amazon->value => ['label' => 'Amazon', 'color' => '#38bdf8', 'provider' => 'amazon'],
            SaleChannel::Direct->value => ['label' => 'Venda Direta', 'color' => '#10b981', 'provider' => null],
        ];

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($channelDefinitions as $key => $info) {
            $total = (float) $sales->filter(fn ($s) => $s->channel === SaleChannel::tryFrom($key) || $s->channel === $key || (is_object($s->channel) && $s->channel->value === $key))->sum('total_amount');
            $isActive = $info['provider'] ? \App\Models\ApiIntegration::isProviderActive($info['provider']) : ($total > 0);

            // Oculta plataformas não instaladas que não possuem vendas registradas
            if ($total > 0 || $isActive) {
                $labels[] = $info['label'];
                $data[] = $total;
                $backgroundColors[] = $info['color'];
            }
        }

        if (array_sum($data) === 0.0) {
            $labels = ['Sem dados no período'];
            $data = [1];
            $backgroundColors = ['#e5e7eb'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
