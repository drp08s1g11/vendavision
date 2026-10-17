<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Sale;
use PHPUnit\Framework\TestCase;

class SaleCalculationTest extends TestCase
{
    /**
     * Testa o cálculo de margem estimada no produto.
     */
    public function test_product_margin_calculation(): void
    {
        $cost = 50.00;
        $price = 100.00;
        $margin = Product::calculateMargin($price, $cost);

        $this->assertEquals(50.00, $margin);

        // Preço zero não deve gerar divisão por zero
        $this->assertEquals(0.00, Product::calculateMargin(0, 10));
    }

    /**
     * Testa o cálculo consolidado de totais, custo, lucro e margem de uma venda.
     */
    public function test_sale_compute_totals_calculation(): void
    {
        $items = [
            [
                'quantity' => 2,
                'unit_price' => 120.00, // R$ 240
                'unit_cost' => 60.00,   // R$ 120
                'item_fee' => 5.00,     // R$ 5
            ],
            [
                'quantity' => 1,
                'unit_price' => 80.00,  // R$ 80
                'unit_cost' => 40.00,   // R$ 40
                'item_fee' => 0.00,
            ],
        ];

        $marketplaceFee = 32.00;
        $shipping = 20.00;

        $result = Sale::computeTotals($items, $marketplaceFee, $shipping);

        // Faturamento total: (2 * 120) + (1 * 80) = 320.00
        $this->assertEquals(320.00, $result['total_amount']);

        // Custo total: (2*60 + 5) + (1*40) + 32 + 20 = 125 + 40 + 52 = 217.00
        $this->assertEquals(217.00, $result['total_cost']);

        // Lucro Bruto: 320 - 217 = 103.00
        $this->assertEquals(103.00, $result['gross_profit']);

        // Margem Real (%): (103 / 320) * 100 = 32.19%
        $this->assertEquals(32.19, $result['profit_margin']);
    }
}
