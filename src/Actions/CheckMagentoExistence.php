<?php

namespace JustBetter\MagentoProducts\Actions;

use Illuminate\Http\Client\Response;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoProducts\Models\MagentoProduct;

class CheckMagentoExistence implements ChecksMagentoExistence
{
    public function __construct(
        protected Magento $magento,
        protected RetrieveMagentoSkus $retrieveMagentoSkus
    ) {
    }

    public function exists(string $sku): bool
    {
        $magentoProduct = MagentoProduct::findBySku($sku);

        if ($magentoProduct === null) {
            $response = $this->getMagentoProduct($sku);

            $response->throwIf(!in_array($response->status, [200, 404]));

            $magentoProduct = MagentoProduct::query()->create([
                'sku' => $sku,
                'exists_in_magento' => $response->ok(),
                'last_checked' => now(),
            ]);

            return $magentoProduct->exists_in_magento;
        }

        return $magentoProduct->exists_in_magento;
    }

    protected function getMagentoProduct(string $sku): Response
    {
        return $this->magento->get("products/$sku", ['fields' => 'sku']);
    }

    public static function bind(): void
    {
        app()->singleton(ChecksMagentoExistence::class, static::class);
    }
}
