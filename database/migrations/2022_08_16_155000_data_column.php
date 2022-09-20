<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_products', function (Blueprint $table): void {
            $table->json('data')->nullable()->after('last_checked');
        });
    }

    public function down(): void
    {
        Schema::table('magento_products', function (Blueprint $table): void {
            $table->dropColumn('data');
        });
    }
};
