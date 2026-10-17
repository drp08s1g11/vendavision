<?php

namespace Database\Seeders;

use App\Enums\SaleChannel;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;

class AmbrozioDecoraSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catálogo Oficial da Ambrozio Decora
        $p1 = Product::updateOrCreate(['sku' => 'AMB-QD-001'], [
            'name' => 'Quadro Canvas Abstrato Dourado Minimalista 80x120cm',
            'description' => 'Quadro impresso em tela canvas 100% algodão com chassi de madeira reflorestada.',
            'cost_price' => 68.50,
            'sale_price' => 189.90,
            'is_active' => true,
        ]);

        $p2 = Product::updateOrCreate(['sku' => 'AMB-ALM-002'], [
            'name' => 'Kit 4 Almofadas Linho Boho Chic com Enchimento Siliconado',
            'description' => 'Conjunto com 4 capas em linho rústico premium com zíper invisível.',
            'cost_price' => 42.00,
            'sale_price' => 119.90,
            'is_active' => true,
        ]);

        $p3 = Product::updateOrCreate(['sku' => 'AMB-ESP-003'], [
            'name' => 'Espelho Redondo Adnet 60cm com Alça em Couro Legítimo Caramelo',
            'description' => 'Espelho com corte lapidado de alta nitidez e alça em couro genuíno.',
            'cost_price' => 55.00,
            'sale_price' => 149.90,
            'is_active' => true,
        ]);

        $p4 = Product::updateOrCreate(['sku' => 'AMB-VAS-004'], [
            'name' => 'Vaso Cerâmica Artesanal Nó Escultural Terracota Matte 22cm',
            'description' => 'Peça de design nórdico escultural para mesa posta e aparadores.',
            'cost_price' => 32.00,
            'sale_price' => 89.90,
            'is_active' => true,
        ]);

        $p5 = Product::updateOrCreate(['sku' => 'AMB-LUM-005'], [
            'name' => 'Luminária de Mesa Articulada Wood Industrial Vintage E27',
            'description' => 'Base em madeira nobre com cúpula em metal preto fosco.',
            'cost_price' => 64.00,
            'sale_price' => 169.00,
            'is_active' => true,
        ]);

        // 2. Venda 1: Mercado Livre (2x Quadros Canvas)
        $s1 = Sale::updateOrCreate(['order_code' => 'MLB-2024-9841'], [
            'sold_at' => now()->subDays(5)->toDateString(),
            'channel' => SaleChannel::MercadoLivre,
            'status' => SaleStatus::Shipped,
            'marketplace_fee' => 60.77,
            'shipping_cost' => 0.00,
            'total_amount' => 379.80,
            'total_cost' => 137.00,
            'gross_profit' => 182.03,
            'profit_margin' => 47.93,
            'notes' => 'Pedido Full Mercado Livre - Pedido entregue no prazo.',
        ]);

        SaleItem::updateOrCreate(['sale_id' => $s1->id, 'product_id' => $p1->id], [
            'quantity' => 2,
            'unit_price' => 189.90,
            'unit_cost' => 68.50,
            'item_fee' => 0.00,
            'subtotal' => 379.80,
            'cost_subtotal' => 137.00,
        ]);

        // 3. Venda 2: Shopee (Kit Almofadas + Vaso Nórdico)
        $s2 = Sale::updateOrCreate(['order_code' => 'SHP-2024-5512'], [
            'sold_at' => now()->subDays(3)->toDateString(),
            'channel' => SaleChannel::Shopee,
            'status' => SaleStatus::Paid,
            'marketplace_fee' => 33.37,
            'shipping_cost' => 0.00,
            'total_amount' => 209.80,
            'total_cost' => 74.00,
            'gross_profit' => 102.43,
            'profit_margin' => 48.82,
            'notes' => 'Campanha de Frete Grátis Shopee.',
        ]);

        SaleItem::updateOrCreate(['sale_id' => $s2->id, 'product_id' => $p2->id], [
            'quantity' => 1,
            'unit_price' => 119.90,
            'unit_cost' => 42.00,
            'item_fee' => 0.00,
            'subtotal' => 119.90,
            'cost_subtotal' => 42.00,
        ]);

        SaleItem::updateOrCreate(['sale_id' => $s2->id, 'product_id' => $p4->id], [
            'quantity' => 1,
            'unit_price' => 89.90,
            'unit_cost' => 32.00,
            'item_fee' => 0.00,
            'subtotal' => 89.90,
            'cost_subtotal' => 32.00,
        ]);

        // 4. Venda 3: Venda Direta / WhatsApp (Espelho Adnet)
        $s3 = Sale::updateOrCreate(['order_code' => 'DIR-2024-1109'], [
            'sold_at' => now()->subDays(1)->toDateString(),
            'channel' => SaleChannel::Direct,
            'status' => SaleStatus::Paid,
            'marketplace_fee' => 5.25,
            'shipping_cost' => 0.00,
            'total_amount' => 299.80,
            'total_cost' => 110.00,
            'gross_profit' => 184.55,
            'profit_margin' => 61.56,
            'notes' => 'Venda Direta via Instagram / WhatsApp com pagamento no Pix.',
        ]);

        SaleItem::updateOrCreate(['sale_id' => $s3->id, 'product_id' => $p3->id], [
            'quantity' => 2,
            'unit_price' => 149.90,
            'unit_cost' => 55.00,
            'item_fee' => 0.00,
            'subtotal' => 299.80,
            'cost_subtotal' => 110.00,
        ]);
    }
}