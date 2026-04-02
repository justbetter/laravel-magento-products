<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Tests\Listeners;

use JustBetter\MagentoProducts\Events\ProductDiscoveredEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class RegisterProductListenerTest extends TestCase
{
    #[Test]
    public function it_registers_products_on_discover_event(): void
    {
        event(new ProductDiscoveredEvent('123'));

        $this->assertInstanceOf(MagentoProduct::class, MagentoProduct::query()->where('sku', '123')->first());
    }
}
