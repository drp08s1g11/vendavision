<?php

namespace Tests\Feature;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Filament\Widgets\SalesProfitChartWidget;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesProfitChartWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_adapts_to_daily_weekly_and_monthly_ranges(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::create([
            'order_code' => 'ORD-2025',
            'sold_at' => '2025-06-15',
            'channel' => SaleChannel::Shopee,
            'status' => SaleStatus::Paid,
            'total_amount' => 500.00,
            'total_cost' => 100.00,
            'gross_profit' => 400.00,
            'profit_margin' => 80.00,
        ]);

        Sale::create([
            'order_code' => 'ORD-2026',
            'sold_at' => '2026-10-15',
            'channel' => SaleChannel::Shopee,
            'status' => SaleStatus::Paid,
            'total_amount' => 1200.00,
            'total_cost' => 300.00,
            'gross_profit' => 900.00,
            'profit_margin' => 75.00,
        ]);

        // 1. Long range (2025-01-01 a 2026-12-31): deve agrupar por mês
        $monthlyWidget = Livewire::test(SalesProfitChartWidget::class, [
            'filters' => [
                'startDate' => '2025-01-01',
                'endDate' => '2026-12-31',
            ],
        ]);

        $this->assertStringContainsString('Mensal', $monthlyWidget->instance()->getHeading());
        $getDataMethod = new \ReflectionMethod(SalesProfitChartWidget::class, 'getData');
        $getDataMethod->setAccessible(true);
        $data = $getDataMethod->invoke($monthlyWidget->instance());
        $this->assertContains('Jun/25', $data['labels']);
        $this->assertContains('Out/26', $data['labels']);
        // Faturamento total do dataset deve contemplar os dois anos
        $totalMonthlyRevenue = array_sum($data['datasets'][0]['data']);
        $this->assertEquals(1700.00, $totalMonthlyRevenue);

        // 2. Medium range (90 dias): deve agrupar por semana
        $weeklyWidget = Livewire::test(SalesProfitChartWidget::class, [
            'filters' => [
                'startDate' => '2026-08-01',
                'endDate' => '2026-10-31',
            ],
        ]);

        $this->assertStringContainsString('Semanal', $weeklyWidget->instance()->getHeading());
        $weeklyData = $getDataMethod->invoke($weeklyWidget->instance());
        $this->assertNotEmpty($weeklyData['labels']);
        $this->assertEquals(1200.00, array_sum($weeklyData['datasets'][0]['data']));

        // 3. Short range (15 dias): deve agrupar por dia
        $dailyWidget = Livewire::test(SalesProfitChartWidget::class, [
            'filters' => [
                'startDate' => '2026-10-10',
                'endDate' => '2026-10-25',
            ],
        ]);

        $this->assertStringContainsString('Diária', $dailyWidget->instance()->getHeading());
        $dailyData = $getDataMethod->invoke($dailyWidget->instance());
        $this->assertContains('15/10', $dailyData['labels']);
        $this->assertEquals(1200.00, array_sum($dailyData['datasets'][0]['data']));
    }

    public function test_channel_distribution_widget_is_hidden_when_single_platform_is_used(): void
    {
        // Apenas Shopee ativa e todas as vendas da Shopee
        \App\Models\ApiIntegration::create([
            'name' => 'Shopee Oficial',
            'provider' => 'shopee',
            'is_active' => true,
        ]);

        Sale::create([
            'order_code' => 'ORD-SHOPEE',
            'sold_at' => '2026-10-15',
            'channel' => SaleChannel::Shopee,
            'status' => SaleStatus::Paid,
            'total_amount' => 500.00,
        ]);

        // Deve estar oculto pois só há 1 plataforma em uso
        $this->assertFalse(\App\Filament\Widgets\ChannelSalesChartWidget::canView());

        // O widget financeiro deve ocupar 100% da tela (largura total)
        $chartWidget = new SalesProfitChartWidget();
        $this->assertEquals('full', $chartWidget->getColumnSpan());

        // Ativa um segundo marketplace
        \App\Models\ApiIntegration::create([
            'name' => 'Mercado Livre Loja',
            'provider' => 'mercadolivre',
            'is_active' => true,
        ]);

        // Agora que há mais de uma plataforma, o gráfico deve ser exibido
        $this->assertTrue(\App\Filament\Widgets\ChannelSalesChartWidget::canView());
    }
}
