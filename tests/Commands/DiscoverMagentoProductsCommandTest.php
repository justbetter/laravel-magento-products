<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use JustBetter\MagentoProducts\Commands\DiscoverMagentoProductsCommand;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;

class DiscoverMagentoProductsCommandTest extends TestCase
{
    public function test_it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(DiscoverMagentoProductsCommand::class);

        Bus::assertBatched(function (PendingBatchFake $batch) {
            return $batch->jobs->count() === 1 && get_class($batch->jobs->first()) === DiscoverMagentoProductsJob::class;
        });
    }
}
