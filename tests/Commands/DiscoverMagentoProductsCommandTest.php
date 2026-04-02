<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Tests\Commands;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use JustBetter\MagentoProducts\Commands\DiscoverMagentoProductsCommand;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DiscoverMagentoProductsCommandTest extends TestCase
{
    #[Test]
    public function it_dispatches_job(): void
    {
        Bus::fake();

        $this->artisan(DiscoverMagentoProductsCommand::class);

        Bus::assertBatched(fn (PendingBatchFake $batch): bool => $batch->jobs->count() === 1 && $batch->jobs->first()::class === DiscoverMagentoProductsJob::class);
    }
}
