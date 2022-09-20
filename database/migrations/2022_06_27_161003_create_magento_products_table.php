<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magento_products', function (Blueprint $table): void {
            $table->id();

            $table->string('sku')->unique();
            $table->boolean('exists_in_magento')->default(false);
            $table->dateTime('last_checked')->nullable()->after('exists');
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magento_products');
    }
};
