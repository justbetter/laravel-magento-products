<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Contracts\RetrievesMagentoSkus;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class DiscoverMagentoProductsJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_processes_single_page(): void
    {
        config()->set('magento-products.page_size', 2);

        $skus = collect(['123']);

        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) use ($skus) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn($skus);
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) use ($skus) {
            $mock->shouldReceive('process')->with($skus)->once();
        });

        DiscoverMagentoProductsJob::dispatch(0);
    }

    public function test_it_processes_two_pages(): void
    {
        config()->set('magento-products.page_size', 2);

        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn(collect(['123', '456']));

            $mock->shouldReceive('retrieve')->with(1)->once()
                ->andReturn(collect(['789']));
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->twice();
        });

        DiscoverMagentoProductsJob::dispatch(0);
    }

    public function test_tags(): void
    {
        $job = new DiscoverMagentoProductsJob(0);

        $this->assertEquals([0], $job->tags());
    }
}
