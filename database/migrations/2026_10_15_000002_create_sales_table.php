<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 64)->unique();          // ID do pedido no marketplace
            $table->date('sold_at')->index();
            $table->string('channel', 32)->index();              // enum SaleChannel
            $table->string('status', 32)->default('pending')->index(); // enum SaleStatus

            // Custos a nível de pedido
            $table->decimal('marketplace_fee', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);

            // Totais armazenados (recalculados via Sale::recalculateTotals())
            $table->decimal('total_amount', 12, 2)->default(0);  // faturamento
            $table->decimal('total_cost', 12, 2)->default(0);    // custo produtos + taxas + frete
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->decimal('profit_margin', 6, 2)->default(0);  // %

            $table->text('notes')->nullable();
            // LGPD: nenhum dado pessoal do cliente final (nome, CPF, endereço) é persistido.
            $table->timestamps();

            $table->index(['status', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
