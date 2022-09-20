<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Commands\RetrieveProductDataCommand;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class RetrieveProductDataCommandTest extends TestCase
{
    public function test_it_retrieves_data(): void
    {
        Http::fake([
            '*/products/123' => Http::response(['productdata']),
        ]);

        $this->artisan(RetrieveProductDataCommand::class, ['sku' => '123']);

        /** @var MagentoProduct $product */
        $product = MagentoProduct::first();

        $this->assertTrue($product->exists_in_magento);
        $this->assertEquals(['productdata'], $product->data);
    }
}
