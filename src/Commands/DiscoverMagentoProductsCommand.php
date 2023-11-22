<?php

namespace JustBetter\MagentoProducts\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Jobs\CheckRemovedProductsJob;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;

class DiscoverMagentoProductsCommand extends Command
{
    protected $signature = 'magento:products:discover';

    protected $description = 'Dispatch job to find products in Magento';

    public function handle(): int
    {
        Bus::batch([new DiscoverMagentoProductsJob])
            ->name('Discover Magento Products')
            ->then(fn () => CheckRemovedProductsJob::dispatch())
            ->onQueue(config('magento-products.queue'))
            ->dispatch();

        return static::SUCCESS;
    }
}
