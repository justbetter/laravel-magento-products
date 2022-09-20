<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Commands\CheckKnownProductsExistenceCommand;
use JustBetter\MagentoProducts\Jobs\CheckKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Tests\TestCase;

class CheckKnownProductExistenceCommandTest extends TestCase
{
    public function test_it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(CheckKnownProductsExistenceCommand::class);

        Bus::assertDispatched(CheckKnownProductsExistenceJob::class);
    }
}
