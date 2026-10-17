<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('api_integrations', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Ex: Mercado Livre, Shopee, Amazon, Magalu
        $table->string('provider'); // Ex: mercadolivre, shopee, amazon, custom
        $table->string('client_id')->nullable(); // Partner ID / App ID
        $table->text('client_secret')->nullable(); // Criptografado
        $table->text('access_token')->nullable(); // Criptografado
        $table->text('refresh_token')->nullable(); // Criptografado
        $table->string('seller_id')->nullable(); // Shop ID / Seller ID
        $table->string('environment')->default('production'); // sandbox ou production
        $table->json('extra_fields')->nullable(); // Configurações extras flexíveis
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_synced_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('api_integrations');
    }
};
