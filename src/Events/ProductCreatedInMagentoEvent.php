<?php

namespace JustBetter\MagentoProducts\Events;

use Illuminate\Foundation\Bus\Dispatchable;

class ProductCreatedInMagentoEvent
{
    use Dispatchable;

    public function __construct(public string $sku)
    {
    }
}
