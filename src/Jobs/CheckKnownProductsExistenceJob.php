<?php

namespace JustBetter\MagentoProducts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoClient\Jobs\Middleware\AvailableMiddleware;
use JustBetter\MagentoProducts\Contracts\ChecksKnownProducts;

class CheckKnownProductsExistenceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;

    public function __construct(public array $skus = [])
    {
        $this->onQueue(config('magento-products.queue'));
    }

    public function handle(ChecksKnownProducts $checksKnownProducts): void
    {
        $checksKnownProducts->handle($this->skus);
    }

    public function middleware(): array
    {
        return [
            new AvailableMiddleware,
        ];
    }
}
