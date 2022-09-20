<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_products', function (Blueprint $table): void {
            $table->dropColumn('exists');
        });

        Schema::table('magento_products', function (Blueprint $table): void {
            $table->boolean('exists_in_magento')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('magento_products', function (Blueprint $table): void {
            $table->dropColumn('exists_in_magento');
            $table->boolean('exists')->default(false);
        });
    }
};
