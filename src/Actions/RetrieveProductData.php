<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\RetrievesProductData;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class RetrieveProductData implements RetrievesProductData
{
    public function __construct(protected Magento $magento)
    {
    }

    public function retrieve(string $sku, bool $force = false, string $store = null): ?array
    {
        $product = MagentoProduct::findBySku($sku, $store);

        if ($product === null) {
            $magentoProductResponse = $this->getMagentoProduct($sku, $store);

            $magentoProductResponse->throwIf($magentoProductResponse->serverError());

            $product = MagentoProduct::query()
                ->create([
                    'sku' => $sku,
                    'last_checked' => now(),
                    'exists_in_magento' => $magentoProductResponse->successful(),
                    'data' => $magentoProductResponse->successful() ? $magentoProductResponse->json() : null,
                    'store' => $store,
                ]);
        }

        $lastChecked = $product->last_checked;

        if ($force ||
            $product->data === null ||
            $lastChecked === null ||
            now()->diffInHours($lastChecked) > config('magento-products.check_interval', 24)
        ) {
            $response = $this->getMagentoProduct($sku, $store);

            if (! $response->successful()) {
                return null;
            }

            $product->data = $response->json();
            $product->last_checked = now();
            $product->exists_in_magento = true;
            $product->save();
        }

        return $product->data;
    }

    protected function getMagentoProduct(string $sku, string $store = null): Response
    {
        return $this->magento
            ->store($store)
            ->get('products/'.urlencode($sku));
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesProductData::class, static::class);
    }
}
