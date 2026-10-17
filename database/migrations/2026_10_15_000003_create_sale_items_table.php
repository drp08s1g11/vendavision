<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);

            // Snapshot do momento da venda (histórico exato do dropshipping)
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_cost', 12, 2);

            // Calculados por item
            $table->decimal('subtotal', 12, 2)->default(0);      // quantity * unit_price
            $table->decimal('cost_subtotal', 12, 2)->default(0); // quantity * unit_cost
            $table->decimal('item_fee', 12, 2)->default(0);      // taxa/comissão específica do item

            $table->timestamps();
            $table->index(['product_id', 'sale_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
