<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Bus\Batch;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoProducts\Contracts\DiscoversMagentoProducts;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Events\ProductDataModifiedEvent;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class DiscoverMagentoProducts implements DiscoversMagentoProducts
{
    public function __construct(
        protected Magento $magento,
        protected ProcessesMagentoSkus $skuProcessor,
    ) {}

    public function discover(int $page, Batch $batch): void
    {
        if ($page === 0) {
            MagentoProduct::query()
                ->update(['retrieved' => false]);
        }

        $search = SearchCriteria::make()
            ->paginate($page, config('magento-products.page_size', 50))
            ->get();

        $products = $this->magento->get('products', $search)->throw()->collect('items');

        $hasNextPage = $products->count() == config('magento-products.page_size');

        if ($hasNextPage) {
            $batch->add(new DiscoverMagentoProductsJob($page + 1));
        }

        $skus = $products->pluck('sku');
        $this->skuProcessor->process($skus);

        foreach ($products as $productData) {
            $product = MagentoProduct::query()->firstOrNew(['sku' => $productData['sku']]);

            /** @var non-empty-string $encoded */
            $encoded = json_encode($productData);

            $checksum = md5($encoded);

            if ($product->checksum !== $checksum) {
                event(new ProductDataModifiedEvent($product->sku, $product->data, $productData));
            }

            $product->retrieved = true;
            $product->data = $productData;
            $product->checksum = $checksum;

            $product->save();
        }
    }

    public static function bind(): void
    {
        app()->singleton(DiscoversMagentoProducts::class, static::class);
    }
}
