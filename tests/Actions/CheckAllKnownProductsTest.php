<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Actions\CheckAllKnownProducts;
use JustBetter\MagentoProducts\Actions\CheckKnownProducts;
use JustBetter\MagentoProducts\Jobs\CheckKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class CheckAllKnownProductsTest extends TestCase
{
    public function test_it_dispatches_jobs(): void
    {
        Bus::fake();

        foreach (range(1, CheckKnownProducts::CHUNK_SIZE * 2) as $number) {
            MagentoProduct::query()->create(['sku' => $number]);
        }

        MagentoProduct::query()->create(['sku' => '::sku::', 'exists_in_magento' => true]);

        /** @var CheckAllKnownProducts $action */
        $action = app(CheckAllKnownProducts::class);

        $action->check();

        Bus::assertDispatchedTimes(CheckKnownProductsExistenceJob::class, 2);
    }
}
