<?php

namespace JustBetter\MagentoProducts\Listeners;

use JustBetter\MagentoProducts\Events\ProductDiscoveredEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class RegisterProduct
{
    public function handle(ProductDiscoveredEvent $event): void
    {
        MagentoProduct::query()
            ->updateOrCreate([
                'sku' => $event->sku,
                'exists_in_magento' => $event->exists,
            ]);
    }
}
