<?php

namespace Tests\Feature;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa criação de produto e cálculo automático de atributos.
     */
    public function test_product_creation_and_attributes(): void
    {
        $product = Product::create([
            'name' => 'Quadro Abstrato Dourado 60x90',
            'sku' => 'QD-ABS-01',
            'cost_price' => 45.00,
            'sale_price' => 120.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('products', ['sku' => 'QD-ABS-01']);
        $this->assertEquals(75.00, $product->unit_profit);
        $this->assertEquals(62.50, $product->estimated_margin);
    }

    /**
     * Testa o recálculo e persistência dos totais de uma venda com múltiplos itens.
     */
    public function test_sale_persistence_and_recalculation(): void
    {
        $p1 = Product::create([
            'name' => 'Vaso Cerâmica Nórdico',
            'sku' => 'VS-CER-01',
            'cost_price' => 30.00,
            'sale_price' => 79.90,
        ]);

        $p2 = Product::create([
            'name' => 'Luminária Industrial Mesa',
            'sku' => 'LUM-IND-01',
            'cost_price' => 50.00,
            'sale_price' => 149.90,
        ]);

        $sale = Sale::create([
            'order_code' => 'AD-TEST-001',
            'sold_at' => now(),
            'channel' => SaleChannel::MercadoLivre,
            'status' => SaleStatus::Paid,
            'marketplace_fee' => 36.77,
            'shipping_cost' => 18.00,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $p1->id,
            'quantity' => 1,
            'unit_price' => 79.90,
            'unit_cost' => 30.00,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'unit_price' => 149.90,
            'unit_cost' => 50.00,
        ]);

        $sale->recalculateTotals();

        $this->assertEquals(229.80, (float) $sale->total_amount);
        // Custo = (30 + 50) + 36.77 + 18.00 = 134.77
        $this->assertEquals(134.77, (float) $sale->total_cost);
        // Lucro = 229.80 - 134.77 = 95.03
        $this->assertEquals(95.03, (float) $sale->gross_profit);
    }
}
