<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Support\Facades\Event;
use JustBetter\MagentoProducts\Actions\ProcessMagentoSkus;
use JustBetter\MagentoProducts\Events\ProductCreatedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class ProcessMagentoSkusTest extends TestCase
{
    protected ProcessMagentoSkus $action;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->action = app(ProcessMagentoSkus::class);
    }

    public function test_it_returns_when_page_size_equals(): void
    {
        config()->set('magento-products.page_size', 1);

        MagentoProduct::query()->create(['sku' => '123', 'exists_in_magento' => true]);
        $skus = collect(['123']);

        $this->action->process($skus);

        Event::assertNotDispatched(ProductCreatedInMagentoEvent::class);
    }

    public function test_it_sets_exists_to_true(): void
    {
        MagentoProduct::query()->create(['sku' => '456', 'exists_in_magento' => false]);
        $skus = collect(['456']);

        $this->action->process($skus);

        $this->assertTrue(MagentoProduct::query()->where('sku', '456')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Event::assertDispatched(ProductCreatedInMagentoEvent::class);
    }

    public function test_it_adds_missing_products(): void
    {
        $skus = collect(['789']);

        $this->action->process($skus);

        $this->assertTrue(MagentoProduct::query()->where('sku', '789')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Event::assertDispatched(ProductCreatedInMagentoEvent::class);
    }
}
