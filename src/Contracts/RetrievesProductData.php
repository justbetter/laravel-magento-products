<?php

namespace JustBetter\MagentoProducts\Contracts;

interface RetrievesProductData
{
    public function retrieve(string $sku, bool $force = false, ?string $store = null): ?array;
}
