<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Bus\Batch;
use JustBetter\MagentoProducts\Actions\DiscoverMagentoProducts;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Contracts\RetrievesMagentoSkus;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class DiscoverMagentoProductsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('magento-products.page_size', 2);
    }

    public function test_it_processes_single_page(): void
    {
        $skus = collect(['123']);

        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) use ($skus) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn($skus);
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) use ($skus) {
            $mock->shouldReceive('process')->with($skus)->once();
        });

        $job = new DiscoverMagentoProductsJob();
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);
    }

    public function test_it_dispatches_next_job(): void
    {
        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn(collect(['123', '456']));
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob();
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?DiscoverMagentoProductsJob $addedJob */
        $addedJob = $job->batch()->added[0] ?? null;

        $this->assertNotNull($addedJob);
        $this->assertEquals(1, $addedJob->page);
    }

    public function test_it_sets_retrieved_false(): void
    {
        MagentoProduct::query()->create([
            'sku' => '123',
            'exists_in_magento' => true,
            'retrieved' => true,
        ]);

        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn(collect());
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob();
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?MagentoProduct $product */
        $product = MagentoProduct::query()->first();

        $this->assertNotNull($product);
        $this->assertFalse($product->retrieved);
    }

    public function test_it_sets_retrieved_true(): void
    {
        MagentoProduct::query()->create([
            'sku' => '123',
            'exists_in_magento' => true,
            'retrieved' => true,
        ]);

        $this->mock(RetrievesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('retrieve')->with(0)->once()
                ->andReturn(collect(['123']));
        });

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock) {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob();
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?MagentoProduct $product */
        $product = MagentoProduct::query()->first();

        $this->assertNotNull($product);
        $this->assertTrue($product->retrieved);
    }
}
