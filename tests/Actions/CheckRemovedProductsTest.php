<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoProducts\Actions\CheckRemovedProducts;
use JustBetter\MagentoProducts\Events\ProductDeletedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class CheckRemovedProductsTest extends TestCase
{
    public function test_it_updates_existence_boolean(): void
    {
        Event::fake();

        MagentoProduct::query()->create([
            'sku' => '::sku_1::',
            'exists_in_magento' => true,
            'retrieved' => false,
        ]);

        MagentoProduct::query()->create([
            'sku' => '::sku_2::',
            'exists_in_magento' => false,
            'retrieved' => false,
        ]);

        /** @var CheckRemovedProducts $action */
        $action = app(CheckRemovedProducts::class);
        $action->check();

        /** @var ?MagentoProduct $removedProduct */
        $removedProduct = MagentoProduct::query()->firstWhere('sku', '=', '::sku_1::');

        $this->assertNotNull($removedProduct);
        $this->assertFalse($removedProduct->exists_in_magento);

        Event::assertDispatchedTimes(ProductDeletedInMagentoEvent::class, 1);
    }
}
