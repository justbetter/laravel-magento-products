<?php

namespace JustBetter\MagentoProducts\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoProducts\Jobs\CheckKnownProductsExistenceJob;

class CheckKnownProductsExistenceCommand extends Command
{
    protected $signature = 'magento:products:check-known-magento-existence';

    protected $description = 'Dispatch job to check if products exist in Magento';

    public function handle(): int
    {
        $this->info('Dispatching...');

        CheckKnownProductsExistenceJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}
