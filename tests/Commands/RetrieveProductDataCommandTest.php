<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Commands\RetrieveProductDataCommand;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveProductDataCommandTest extends TestCase
{
    #[Test]
    public function it_retrieves_data(): void
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
