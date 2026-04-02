<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ProductDataModifiedEvent
{
    use Dispatchable;

    public function __construct(public string $sku, public ?array $oldData, public array $newData) {}
}
