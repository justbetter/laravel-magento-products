<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Contracts\ChecksMagento;
use JustBetter\MagentoProducts\Actions\RetrieveProductData;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

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
            '*/products/123%2B456' => Http::response(['456']),
            '*/some_store/V1/products/789' => Http::response(['789']),
            '*/products/404' => Http::response([], 404),
        ]);
    }

    #[Test]
    public function it_retrieves_existing_product(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('123');

        $this->assertEquals(['test'], $data);
    }

    #[Test]
    public function it_retrieves_new_product(): void
    {
        $data = $this->action->retrieve('123+456');

        $this->assertEquals(['456'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/all/V1/products/123%2B456';
        });
    }

    #[Test]
    public function it_retrieves_product_for_store(): void
    {
        $data = $this->action->retrieve('789', false, 'some_store');

        $this->assertEquals(['789'], $data);

        /** @var MagentoProduct $createdProduct */
        $createdProduct = MagentoProduct::findBySku('789', 'some_store');

        $this->assertEquals('some_store', $createdProduct->store);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/some_store/V1/products/789';
        });
    }

    #[Test]
    public function it_retrieves_missing_product(): void
    {
        $data = $this->action->retrieve('404');

        $this->assertNull($data);
        $this->assertFalse(MagentoProduct::query()->where('sku', '404')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/all/V1/products/404';
        });
    }

    #[Test]
    public function it_rechecks_on_interval(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHours(3)]);

        $data = $this->action->retrieve('123');

        $this->assertEquals(['123'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/all/V1/products/123';
        });
    }

    #[Test]
    public function it_forces_recheck(): void
    {
        MagentoProduct::query()->create(['sku' => '123', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('123', true);

        $this->assertEquals(['123'], $data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/all/V1/products/123';
        });
    }

    #[Test]
    public function it_returns_null_when_check_fails(): void
    {
        MagentoProduct::query()->create(['sku' => '404', 'data' => ['test'], 'last_checked' => now()->subHour()]);

        $data = $this->action->retrieve('404', true);

        $this->assertNull($data);

        Http::assertSent(function (Request $request) {
            return $request->url() == 'magento/rest/all/V1/products/404';
        });
    }

    #[Test]
    public function it_throws_exception_when_magento_is_not_available(): void
    {
        $this->mock(ChecksMagento::class, function (MockInterface $mock): void {
            $mock->shouldReceive('available')->andReturnFalse();
        });

        /** @var RetrieveProductData $action */
        $action = app(RetrieveProductData::class);

        $this->expectException(RuntimeException::class);
        $action->retrieve('456');

        Http::assertNothingSent();
    }
}
