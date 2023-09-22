<?php

namespace JustBetter\MagentoProducts\Actions;

use JustBetter\MagentoProducts\Contracts\ChecksMagentoEnabled;
use JustBetter\MagentoProducts\Contracts\RetrievesProductData;

class CheckMagentoEnabled implements ChecksMagentoEnabled
{
    public function __construct(
        protected RetrievesProductData $productData
    ) {
    }

    public function enabled(string $sku, bool $force = false, string $store = null): bool
    {
        $data = $this->productData->retrieve($sku, $force, $store);

        if ($data === null) {
            return false;
        }

        return $data['status'] === 1;
    }

    public static function bind(): void
    {
        app()->singleton(ChecksMagentoEnabled::class, static::class);
    }
}
