<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Support\Collection;
use JustBetter\MagentoProducts\Contracts\ChecksAllKnownProducts;
use JustBetter\MagentoProducts\Jobs\CheckKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class CheckAllKnownProducts implements ChecksAllKnownProducts
{
    public function check(): void
    {
        MagentoProduct::query()
            ->where('exists_in_magento', '=', false)
            ->select(['sku', 'exists_in_magento'])
            ->get()
            ->chunk(CheckKnownProducts::CHUNK_SIZE)
            ->each(fn (Collection $skus) => CheckKnownProductsExistenceJob::dispatch($skus->pluck('sku')->toArray()));
    }

    public static function bind(): void
    {
        app()->singleton(ChecksAllKnownProducts::class, static::class);
    }
}
