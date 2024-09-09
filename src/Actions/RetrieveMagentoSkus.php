<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Support\Enumerable;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoClient\Query\SearchCriteria;
use JustBetter\MagentoProducts\Contracts\RetrievesMagentoSkus;

class RetrieveMagentoSkus implements RetrievesMagentoSkus
{
    public function __construct(
        protected Magento $magento
    ) {}

    public function retrieve(int $page): Enumerable
    {
        $search = SearchCriteria::make()
            ->select(['items[sku]'])
            ->paginate($page, config('magento-products.page_size', 50))
            ->get();

        return $this->magento->get('products', $search)->throw()->collect('items')->pluck('sku');
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesMagentoSkus::class, static::class);
    }
}
