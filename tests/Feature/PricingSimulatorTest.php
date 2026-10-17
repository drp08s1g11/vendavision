<?php

namespace Tests\Feature;

use App\Filament\Pages\PricingSimulator;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class PricingSimulatorTest extends TestCase
{
    /**
     * Valida que o simulador de precificação carrega com sucesso e calcula
     * o preço de venda sugerido e o lucro líquido conforme a fórmula de dropshipping.
     */
    public function test_pricing_simulator_page_renders_and_calculates_markup(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        // Teste de renderização direta do componente Livewire da página
        Livewire::test(PricingSimulator::class)
            ->assertSuccessful()
            ->fillForm([
                'cost_price' => 100.00,
                'shipping_cost' => 20.00,
                'fixed_fee' => 0.00,
                'commission_rate' => 15.00,
                'tax_rate' => 5.00,
                'target_margin' => 20.00,
            ])
            // Preço = 120 / (1 - (0.15 + 0.05 + 0.20)) = 120 / 0.60 = R$ 200,00
            // Lucro = 200 * 20% = R$ 40,00
            ->assertSet('calculationResults.suggested_price', 200.00)
            ->assertSet('calculationResults.net_profit', 40.00)
            ->assertSet('calculationResults.markup', 2.00)
            ->assertSee('R$ 200,00')
            ->assertSee('R$ 40,00');
    }
}
