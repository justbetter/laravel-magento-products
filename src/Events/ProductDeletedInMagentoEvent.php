<?php

namespace JustBetter\MagentoProducts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ProductDeletedInMagentoEvent
{
    use Dispatchable;

    public function __construct(public string $sku) {}
}
