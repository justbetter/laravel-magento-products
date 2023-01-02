<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\RetrieveProductData;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;

class RetrieveProductDataTest extends TestCase
{
    protected RetrieveProductData $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RetrieveProductData::class);

        config()->set('magento-products.check_interval', 2);

        Http::fake([
            '*/products/123' => Http::response(['123']),
            '*/products/456' => Http::response(['456']),
            '*/products/404' => Http::response([], 404),

        ]);
    }

    public function test_it_retrieves_existing_product(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('123');

        $this->assertEquals(['test'], $data);
    }

    public function test_it_retrieves_new_product(): void
    {
        $data = $this->action->retrieve('456');

        $this->assertEquals(['456'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'rest/all/V1/products/456';
        });
    }

    public function test_it_retrieves_missing_product(): void
    {
        $data = $this->action->retrieve('404');

        $this->assertNull($data);
        $this->assertFalse(MagentoProduct::query()->where('sku', '404')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() == 'rest/all/V1/products/404';
        });
    }

    public function test_it_rechecks_on_interval(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHours(3)]);

        $data = $this->action->retrieve('123');

        $this->assertEquals(['123'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'rest/all/V1/products/123';
        });
    }

    public function test_it_forces_recheck(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('123', true);

        $this->assertEquals(['123'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'rest/all/V1/products/123';
        });
    }

    public function test_it_returns_null_when_check_fails(): void
    {
        MagentoProduct::query()->create(['sku' => '404', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('404', true);

        $this->assertNull($data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'rest/all/V1/products/404';
        });
    }
}
