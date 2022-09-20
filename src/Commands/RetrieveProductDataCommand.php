<?php

namespace JustBetter\MagentoProducts\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoProducts\Contracts\RetrievesProductData;

class RetrieveProductDataCommand extends Command
{
    protected $signature = 'magento:products:retrieve-data {sku}';

    protected $description = 'Retrieve data for a product';

    public function handle(RetrievesProductData $retrievesProductData): int
    {
        $data = $retrievesProductData->retrieve($this->argument('sku')); /** @phpstan-ignore-line */
        $this->info(json_encode($data)); /** @phpstan-ignore-line */

        return static::SUCCESS;
    }
}
