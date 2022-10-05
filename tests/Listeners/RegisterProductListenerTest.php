<?php

namespace JustBetter\MagentoProducts\Tests\Listeners;

use JustBetter\MagentoProducts\Events\ProductDiscoveredEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class RegisterProductListenerTest extends TestCase
{
    public function test_it_registers_products_on_discover_event(): void
    {
        event(new ProductDiscoveredEvent('123'));

        $this->assertNotNull(MagentoProduct::query()->where('sku', '123')->first());
    }
}
