<?php

namespace JustBetter\MagentoProducts\Actions;

use JustBetter\MagentoProducts\Contracts\ChecksRemovedProducts;
use JustBetter\MagentoProducts\Events\ProductDeletedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class CheckRemovedProducts implements ChecksRemovedProducts
{
    public function check(): void
    {
        $query = MagentoProduct::query()
            ->where('exists_in_magento', '=', true)
            ->where('retrieved', '=', false);

        $skus = $query->select(['sku'])->get();

        $query->update([
            'exists_in_magento' => false,
        ]);

        $skus->each(fn (MagentoProduct $product) => ProductDeletedInMagentoEvent::dispatch($product->sku));
    }

    public static function bind(): void
    {
        app()->singleton(ChecksRemovedProducts::class, static::class);
    }
}
