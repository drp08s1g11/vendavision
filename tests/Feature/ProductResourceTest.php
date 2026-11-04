<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_sort_products_by_estimated_margin_and_unit_profit(): void
    {
        $user = User::factory()->create();

        $p1 = Product::create([
            'name' => 'Produto A',
            'sku' => 'SKU-A',
            'cost_price' => 50.00,
            'sale_price' => 100.00, // lucro 50, margem 50%
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'name' => 'Produto B',
            'sku' => 'SKU-B',
            'cost_price' => 80.00,
            'sale_price' => 100.00, // lucro 20, margem 20%
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ListProducts::class)
            ->sortTable('estimated_margin', 'asc')
            ->assertCanSeeTableRecords([$p2, $p1], inOrder: true)
            ->sortTable('estimated_margin', 'desc')
            ->assertCanSeeTableRecords([$p1, $p2], inOrder: true)
            ->sortTable('unit_profit', 'asc')
            ->assertCanSeeTableRecords([$p2, $p1], inOrder: true)
            ->sortTable('unit_profit', 'desc')
            ->assertCanSeeTableRecords([$p1, $p2], inOrder: true);
    }
}
