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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->decimal('sale_price', 12, 2);
            $table->decimal('purchase_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->timestamp('last_update_at')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'sku'], 'stocks_product_id_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
