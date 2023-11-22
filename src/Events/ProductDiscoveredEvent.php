<?php

namespace JustBetter\MagentoProducts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ProductDiscoveredEvent
{
    use Dispatchable;

    public function __construct(public string $sku, public bool $exists = false)
    {
    }
}
