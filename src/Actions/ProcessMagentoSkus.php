<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Support\Enumerable;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Events\ProductCreatedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class ProcessMagentoSkus implements ProcessesMagentoSkus
{
    public function process(Enumerable $skus): void
    {
        $knownProductsThatExist = MagentoProduct::query()
            ->whereIn('sku', $skus)
            ->where('exists_in_magento', true)
            ->select(['sku'])
            ->distinct()
            ->get()
            ->pluck('sku');

        // Check if all retrieved skus also exist in Magento
        if ($knownProductsThatExist->count() == config('magento-products.page_size', 0)) {
            return;
        }

        // Get the skus that do not exist in Magento
        $skus = $skus->diff($knownProductsThatExist);

        // Get the products that are discovered but have the exists_in_magento set to false
        $knownProductsThatDontExistQuery = MagentoProduct::query()
            ->whereIn('sku', $skus)
            ->where('exists_in_magento', false);

        $knownProductsThatDontExist = $knownProductsThatDontExistQuery->get();

        $knownProductsThatDontExistQuery->update(['exists_in_magento' => true, 'updated_at' => now()]);

        $missingProducts = $skus->diff($knownProductsThatDontExist->pluck('sku'))
            ->map(fn (string $sku) => ['sku' => $sku, 'exists_in_magento' => true, 'created_at' => now(), 'updated_at' => now()]);

        MagentoProduct::query()
            ->insert($missingProducts->toArray());

        $knownProductsThatDontExist->pluck('sku')->merge($missingProducts->pluck('sku'))
            ->each(fn (string $sku) => event(new ProductCreatedInMagentoEvent($sku)));
    }

    public static function bind(): void
    {
        app()->singleton(ProcessesMagentoSkus::class, static::class);
    }
}
