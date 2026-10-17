<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('sku')->unique()->comment('Código de referência único / SKU');
        $table->string('category')->nullable()->comment('Categoria do produto');
        $table->text('description')->nullable();
        
        // Custos e Preços
        $table->decimal('cost_price', 10, 2)->comment('Custo do fornecedor dropshipping');
        $table->decimal('sale_price', 10, 2)->comment('Preço de venda praticado');
        
        // Metadados
        $table->string('stock_reference')->nullable()->comment('Nome/contato do fornecedor parceiro');
        $table->boolean('status')->default(true)->comment('Ativo ou Inativo');
        
        $table->timestamps();
        
        // Índices de busca
        $table->index('sku');
        $table->index('category');
        $table->index('status');
    });
}

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
