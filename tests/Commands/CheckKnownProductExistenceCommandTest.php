<?php

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Commands\CheckKnownProductsExistenceCommand;
use JustBetter\MagentoProducts\Jobs\CheckAllKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckKnownProductExistenceCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(CheckKnownProductsExistenceCommand::class);

        Bus::assertDispatched(CheckAllKnownProductsExistenceJob::class);
    }
}
