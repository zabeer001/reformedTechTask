<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->index('quantity', 'stocks_quantity_index');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropIndex('stocks_quantity_index');
        });
    }
};
