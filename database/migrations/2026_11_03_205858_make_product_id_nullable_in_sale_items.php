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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            if (!Schema::hasColumn('sale_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('product_id')->comment('Nome do anúncio ou item no marketplace');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
            if (Schema::hasColumn('sale_items', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }
};
