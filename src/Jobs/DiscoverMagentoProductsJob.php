<?php

namespace JustBetter\MagentoProducts\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Contracts\RetrievesMagentoSkus;

class DiscoverMagentoProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    public int $timeout = 60;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $page = 0)
    {
        $this->onQueue(config('magento-products.queue'));
    }

    public function handle(
        RetrievesMagentoSkus $retrievesMagentoSkus,
        ProcessesMagentoSkus $processesMagentoSkus
    ): void {
        $skus = $retrievesMagentoSkus->retrieve($this->page);

        $hasNextPage = $skus->count() == config('magento-products.page_size', 50);

        if ($hasNextPage) {
            if ($this->batching()) {
                $this->batch()->add(new static($this->page + 1)); /** @phpstan-ignore-line */
            } else {
                static::dispatch($this->page + 1);
            }
        }

        $processesMagentoSkus->process($skus);
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
