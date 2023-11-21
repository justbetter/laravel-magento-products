<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Bus\Batch;
use JustBetter\MagentoProducts\Contracts\DiscoversMagentoProducts;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Contracts\RetrievesMagentoSkus;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class DiscoverMagentoProducts implements DiscoversMagentoProducts
{
    public function __construct(
        protected RetrievesMagentoSkus $magentoSkus,
        protected ProcessesMagentoSkus $processor
    ) {
    }

    public function discover(int $page, Batch $batch): void
    {
        if ($page === 0) {
            MagentoProduct::query()
                ->update(['retrieved' => false]);
        }

        $skus = $this->magentoSkus->retrieve($page);

        $hasNextPage = $skus->count() == config('magento-products.page_size');

        if ($hasNextPage) {
            $batch->add(new DiscoverMagentoProductsJob($page + 1));
        }

        MagentoProduct::query()
            ->whereIn('sku', $skus)
            ->update(['retrieved' => true]);

        $this->processor->process($skus);
    }

    public static function bind(): void
    {
        app()->singleton(DiscoversMagentoProducts::class, static::class);
    }
}
