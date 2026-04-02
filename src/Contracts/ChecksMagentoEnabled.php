<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Contracts;

interface ChecksMagentoEnabled
{
    public function enabled(string $sku, bool $force = false, ?string $store = null): bool;
}
