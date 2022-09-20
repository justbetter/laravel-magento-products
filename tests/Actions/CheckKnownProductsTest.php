<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\CheckKnownProducts;
use JustBetter\MagentoProducts\Events\ProductCreatedInMagentoEvent;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class CheckKnownProductsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        Http::fake([
            '*/all/V1/products*' => Http::response([
                'items' => [
                    [
                        'sku' => '123',
                    ],
                ],
            ]),
        ]);

        MagentoProduct::query()->create(['sku' => '123']);
        MagentoProduct::query()->create(['sku' => '456']);
        MagentoProduct::query()->create(['sku' => '789', 'exists_in_magento' => true]);
    }

    public function test_it_sets_in_magento(): void
    {
        /** @var CheckKnownProducts $action */
        $action = app(CheckKnownProducts::class);

        $action->handle();

        $this->assertTrue(MagentoProduct::query()->where('sku', '123')->first()->exists_in_magento); /** @phpstan-ignore-line */
        $this->assertFalse(MagentoProduct::query()->where('sku', '456')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            $expectedSearchCriteria = [
                'fields' => 'items[sku]',
                'searchCriteria[filter_groups][0][filters][0][field]' => 'sku',
                'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'in',
                'searchCriteria[filter_groups][0][filters][0][value]' => '123,456',
                'searchCriteria[pageSize]' => 100,
                'searchCriteria[currentPage]' => 1,
            ];

            return $expectedSearchCriteria == $request->data();
        });
    }

    public function test_it_checks_limited_skus(): void
    {
        /** @var CheckKnownProducts $action */
        $action = app(CheckKnownProducts::class);

        $action->handle(['123']);

        Http::assertSent(function (Request $request) {
            $expectedSearchCriteria = [
                'fields' => 'items[sku]',
                'searchCriteria[filter_groups][0][filters][0][field]' => 'sku',
                'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'in',
                'searchCriteria[filter_groups][0][filters][0][value]' => '123',
                'searchCriteria[pageSize]' => 100,
                'searchCriteria[currentPage]' => 1,
            ];

            return $expectedSearchCriteria == $request->data();
        });
    }

    public function test_it_dispatches_events(): void
    {
        /** @var CheckKnownProducts $action */
        $action = app(CheckKnownProducts::class);

        $action->handle();

        Event::assertDispatched(ProductCreatedInMagentoEvent::class, function (ProductCreatedInMagentoEvent $event) {
            return $event->sku === '123';
        });
    }
}
