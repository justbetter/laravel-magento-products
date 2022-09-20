<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Commands\DiscoverMagentoProductsCommand;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;

class DiscoverMagentoProductsCommandTest extends TestCase
{
    public function test_it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(DiscoverMagentoProductsCommand::class);

        Bus::assertDispatched(DiscoverMagentoProductsJob::class);
    }
}
