<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_products', function (Blueprint $table): void {
            $table->dropUnique('magento_products_sku_unique');
            $table->string('store')->nullable()->after('sku');

            $table->unique(['sku', 'store']);
        });
    }

    public function down(): void
    {
        Schema::dropColumns('magento_products', ['store']);
    }
};
