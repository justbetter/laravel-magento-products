<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Bus\Batch;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\DiscoverMagentoProducts;
use JustBetter\MagentoProducts\Contracts\ProcessesMagentoSkus;
use JustBetter\MagentoProducts\Events\ProductDataModifiedEvent;
use JustBetter\MagentoProducts\Jobs\DiscoverMagentoProductsJob;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class DiscoverMagentoProductsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('magento-products.page_size', 2);
    }

    #[Test]
    public function it_processes_single_page(): void
    {
        Bus::fake();
        Event::fake([ProductDataModifiedEvent::class]);
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=2&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [
                    ['sku' => '123'],
                ],
            ]),
        ])->preventingStrayRequests();

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->withArgs(fn (Enumerable $skus): bool => $skus->toArray() === ['123'])->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        Event::assertDispatched(ProductDataModifiedEvent::class, fn (ProductDataModifiedEvent $event): bool => $event->oldData === null && $event->newData === ['sku' => '123']);
        Bus::assertNothingBatched();
    }

    #[Test]
    public function it_dispatches_modified_event_with_old_data(): void
    {
        Bus::fake();
        Event::fake([ProductDataModifiedEvent::class]);
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=2&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [
                    ['sku' => '123'],
                ],
            ]),
        ])->preventingStrayRequests();

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->withArgs(fn (Enumerable $skus): bool => $skus->toArray() === ['123'])->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        MagentoProduct::query()->create([
            'sku' => '123',
            'checksum' => 'old',
            'data' => ['sku' => '123', 'old' => 'data'],
        ]);

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        Event::assertDispatched(ProductDataModifiedEvent::class, fn (ProductDataModifiedEvent $event): bool => $event->oldData === ['sku' => '123', 'old' => 'data'] && $event->newData === ['sku' => '123']);
        Bus::assertNothingBatched();
    }

    #[Test]
    public function it_does_not_dispatch_event_when_checksum_has_not_changed(): void
    {
        Bus::fake();
        Event::fake([ProductDataModifiedEvent::class]);
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=2&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [
                    ['sku' => '123'],
                ],
            ]),
        ])->preventingStrayRequests();

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->withArgs(fn (Enumerable $skus): bool => $skus->toArray() === ['123'])->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        MagentoProduct::query()->create([
            'sku' => '123',
            'checksum' => 'ffd4c4101da9cd00e59cab0b0874f192',
            'data' => ['sku' => '123', 'old' => 'data'],
        ]);

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        Event::assertNotDispatched(ProductDataModifiedEvent::class);
        Bus::assertNothingBatched();
    }

    #[Test]
    public function it_dispatches_next_job(): void
    {
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=1&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [
                    ['sku' => '123'],
                ],
            ]),
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=1&searchCriteria%5BcurrentPage%5D=1' => Http::response([
                'items' => [],
            ]),
        ])->preventingStrayRequests();
        config()->set('magento-products.page_size', 1);

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?DiscoverMagentoProductsJob $addedJob */
        $addedJob = $job->batch()->added[0] ?? null;

        $this->assertInstanceOf(DiscoverMagentoProductsJob::class, $addedJob);
        $this->assertSame(1, $addedJob->page);
    }

    #[Test]
    public function it_sets_retrieved_false(): void
    {
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=2&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [],
            ]),
        ])->preventingStrayRequests();

        MagentoProduct::query()->create([
            'sku' => '123',
            'exists_in_magento' => true,
            'retrieved' => true,
        ]);

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?MagentoProduct $product */
        $product = MagentoProduct::query()->first();

        $this->assertInstanceOf(MagentoProduct::class, $product);
        $this->assertFalse($product->retrieved);
    }

    #[Test]
    public function it_sets_retrieved_true(): void
    {
        Http::fake([
            'magento/rest/all/V1/products?searchCriteria%5BpageSize%5D=2&searchCriteria%5BcurrentPage%5D=0' => Http::response([
                'items' => [
                    ['sku' => '123'],
                ],
            ]),
        ])->preventingStrayRequests();

        MagentoProduct::query()->create([
            'sku' => '123',
            'exists_in_magento' => true,
            'retrieved' => true,
        ]);

        $this->mock(ProcessesMagentoSkus::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        $job = new DiscoverMagentoProductsJob;
        $job->withFakeBatch();

        /** @var Batch $batch */
        $batch = $job->batch();

        /** @var DiscoverMagentoProducts $action */
        $action = app(DiscoverMagentoProducts::class);
        $action->discover(0, $batch);

        /** @var ?MagentoProduct $product */
        $product = MagentoProduct::query()->first();

        $this->assertInstanceOf(MagentoProduct::class, $product);
        $this->assertTrue($product->retrieved);
    }
}
