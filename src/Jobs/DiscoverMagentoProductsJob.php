<?php

namespace JustBetter\MagentoProducts\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoProducts\Contracts\DiscoversMagentoProducts;

class DiscoverMagentoProductsJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 60;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $page = 0)
    {
        $this->onQueue(config('magento-products.queue'));
    }

    public function handle(DiscoversMagentoProducts $contract): void
    {
        if ($this->batch() === null || $this->batch()->cancelled()) {
            return;
        }

        $contract->discover($this->page, $this->batch());
    }

    public function tags(): array
    {
        return [
            $this->page,
        ];
    }

    public function uniqueId(): int
    {
        return $this->page;
    }
}
