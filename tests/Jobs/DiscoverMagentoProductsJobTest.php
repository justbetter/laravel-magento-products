<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoProducts\Contracts\DiscoversMagentoProducts;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class DiscoverMagentoProductsJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(DiscoversMagentoProducts::class, function (MockInterface $mock): void {
            $mock->shouldReceive('discover')->once();
        });

        $job = new DiscoverMagentoProductsJob(0);
        $job->withFakeBatch();

        Bus::dispatch($job);
    }

    public function test_it_stops_when_batch_is_cancelled(): void
    {
        $this->mock(DiscoversMagentoProducts::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('discover');
        });

        $job = new DiscoverMagentoProductsJob(0);
        $job->withFakeBatch();
        $job->batch()?->cancel();

        Bus::dispatch($job);
    }

    public function test_it_has_tags(): void
    {
        $job = new DiscoverMagentoProductsJob(0);

        $this->assertEquals([0], $job->tags());
    }
}
