<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\CheckMagentoExistence;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class CheckMagentoExistenceTest extends TestCase
{
    protected CheckMagentoExistence $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CheckMagentoExistence::class);

        config()->set('magento-products.check_interval', 2);

        Http::fake([
            '*products/123?fields=sku' => Http::response(['data']),
            '*products/456?fields=sku' => Http::response([], 404),
        ]);
    }

    public function test_existing_product(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'exists_in_magento' => true, 'last_checked' => now()->subHour()]);

        $this->assertTrue($this->action->exists('123'));
    }

    public function test_new_existing_product(): void
    {
        $this->assertTrue($this->action->exists('123'));
        $this->assertTrue(MagentoProduct::query()->where('sku', '123')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() === 'rest/all/V1/products/123?fields=sku';
        });
    }

    public function test_new_non_existing_product(): void
    {
        $this->assertFalse($this->action->exists('456'));
        $this->assertFalse(MagentoProduct::query()->where('sku', '456')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() === 'rest/all/V1/products/456?fields=sku';
        });
    }

    public function test_existing_last_checked(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'exists_in_magento' => true, 'last_checked' => now()->subHours(3)]);

        $this->assertTrue($this->action->exists('123'));

        Http::assertNothingSent();
    }
}
