<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Database\Eloquent\Builder;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoProducts\Contracts\ChecksKnownProducts;
use JustBetter\MagentoProducts\Events\ProductCreatedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class CheckKnownProducts implements ChecksKnownProducts
{
    public const CHUNK_SIZE = 100;

    public function __construct(protected Magento $magento)
    {
    }

    public function handle(array $skus = []): void
    {
        $productChunks = MagentoProduct::query()
            ->where('exists_in_magento', false)
            ->when(count($skus), fn (Builder $query): Builder => $query->whereIn('sku', $skus)) /** @phpstan-ignore-line */
            ->get()
            ->chunk(static::CHUNK_SIZE);

        foreach ($productChunks as $chunk) {
            $search = SearchCriteria::make()
                ->select(['items[sku]'])
                ->whereIn('sku', $chunk->pluck('sku')->toArray())
                ->paginate(1, static::CHUNK_SIZE)
                ->get();

            $response = $this->magento->get('products', $search);

            $skusThatExist = $response->json('items.*.sku', []);

            MagentoProduct::query()
                ->whereIn('sku', $skusThatExist)
                ->update(['exists_in_magento' => true]);

            foreach ($skusThatExist as $sku) {
                event(new ProductCreatedInMagentoEvent($sku));
            }
        }
    }

    public static function bind(): void
    {
        app()->singleton(ChecksKnownProducts::class, static::class);
    }
}
