<?php

namespace JustBetter\MagentoProducts\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;

class DiscoverMagentoProductsCommand extends Command
{
    protected $signature = 'magento:products:discover';

    protected $description = 'Dispatch job to find products in Magento';

    public function handle(): int
    {
        $this->info('Dispatching...');

        DiscoverMagentoProductsJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}
