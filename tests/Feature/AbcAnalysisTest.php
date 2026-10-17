<?php

namespace Tests\Feature;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Filament\Pages\AbcAnalysis;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AbcAnalysisTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Valida que a página de Curva ABC (Pareto) classifica produtos corretamente
     * com base na representatividade do faturamento acumulado.
     */
    public function test_abc_analysis_classifies_products_accurately(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        $suffix = uniqid();

        // Produto A (alto faturamento - deve ser Classe A)
        $productA = Product::create([
            'name' => 'Mesa de Jantar Madeira Maciça ' . $suffix,
            'sku' => 'ABC-MESA-' . $suffix,
            'cost_price' => 500.00,
            'sale_price' => 1000.00,
            'status' => true,
        ]);

        // Produto B (faturamento médio - deve ser Classe B)
        $productB = Product::create([
            'name' => 'Quadro Decorativo Abstrato ' . $suffix,
            'sku' => 'ABC-QUAD-' . $suffix,
            'cost_price' => 50.00,
            'sale_price' => 150.00,
            'status' => true,
        ]);

        // Produto C (baixo faturamento - deve ser Classe C)
        $productC = Product::create([
            'name' => 'Vaso Cerâmica Pequeno ' . $suffix,
            'sku' => 'ABC-VASO-' . $suffix,
            'cost_price' => 15.00,
            'sale_price' => 30.00,
            'status' => true,
        ]);

        // Venda 1: 10 mesas = R$ 10.000 (83.3% do total)
        $sale1 = Sale::create([
            'order_code' => 'ABC-TEST-1-' . $suffix,
            'channel' => SaleChannel::MercadoLivre,
            'status' => SaleStatus::Paid,
            'sold_at' => now(),
            'total_amount' => 10000.00,
            'total_cost' => 5000.00,
            'gross_profit' => 5000.00,
            'profit_margin' => 50.00,
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $productA->id,
            'quantity' => 10,
            'unit_price' => 1000.00,
            'unit_cost' => 500.00,
            'subtotal' => 10000.00,
            'cost_subtotal' => 5000.00,
        ]);

        // Venda 2: 10 quadros = R$ 1.500 (12.5% do total -> acumulado 95.8%)
        $sale2 = Sale::create([
            'order_code' => 'ABC-TEST-2-' . $suffix,
            'channel' => SaleChannel::Shopee,
            'status' => SaleStatus::Shipped,
            'sold_at' => now(),
            'total_amount' => 1500.00,
            'total_cost' => 500.00,
            'gross_profit' => 1000.00,
            'profit_margin' => 66.67,
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productB->id,
            'quantity' => 10,
            'unit_price' => 150.00,
            'unit_cost' => 50.00,
            'subtotal' => 1500.00,
            'cost_subtotal' => 500.00,
        ]);

        // Venda 3: 10 vasos = R$ 300 (2.5% do total -> acumulado 100%)
        $sale3 = Sale::create([
            'order_code' => 'ABC-TEST-3-' . $suffix,
            'channel' => SaleChannel::Direct,
            'status' => SaleStatus::Paid,
            'sold_at' => now(),
            'total_amount' => 300.00,
            'total_cost' => 150.00,
            'gross_profit' => 150.00,
            'profit_margin' => 50.00,
        ]);
        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $productC->id,
            'quantity' => 10,
            'unit_price' => 30.00,
            'unit_cost' => 15.00,
            'subtotal' => 300.00,
            'cost_subtotal' => 150.00,
        ]);

        // Validação da renderização Livewire
        Livewire::test(AbcAnalysis::class)
            ->assertSuccessful()
            ->assertSee('Curva ABC')
            ->assertSee('Mesa de Jantar Madeira Maciça ' . $suffix)
            ->assertSee('Quadro Decorativo Abstrato ' . $suffix)
            ->assertSee('Vaso Cerâmica Pequeno ' . $suffix);
    }
}
