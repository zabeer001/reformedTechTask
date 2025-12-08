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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('slug');
            }

            $table->index('name', 'products_name_index');
            $table->index('category', 'products_category_index');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index('sku', 'stocks_sku_index');
            $table->index('sale_price', 'stocks_sale_price_index');
            $table->index('purchase_price', 'stocks_purchase_price_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_name', 'orders_customer_name_index');
            $table->index('status', 'orders_status_index');
            $table->index('ordered_at', 'orders_ordered_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_name_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_ordered_at_index');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_sku_index');
            $table->dropIndex('stocks_sale_price_index');
            $table->dropIndex('stocks_purchase_price_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_name_index');
            $table->dropIndex('products_category_index');
            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
