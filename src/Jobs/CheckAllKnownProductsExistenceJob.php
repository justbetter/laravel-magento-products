<?php

namespace JustBetter\MagentoProducts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoProducts\Contracts\ChecksAllKnownProducts;

class CheckAllKnownProductsExistenceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('magento-products.queue'));
    }

    public function handle(ChecksAllKnownProducts $products): void
    {
        $products->check();
    }
}
