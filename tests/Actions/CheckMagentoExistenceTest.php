<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\MagentoClient\Contracts\ChecksMagento;
use JustBetter\MagentoProducts\Actions\CheckMagentoExistence;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

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
            '*products/123%2B456?fields=sku' => Http::response(['data']),
        ])->preventStrayRequests();
    }

    #[Test]
    public function existing_product(): void
    {
        MagentoProduct::query()->create([
            'sku' => '123', 'exists_in_magento' => true, 'last_checked' => now()->subHour(),
        ]);

        $this->assertTrue($this->action->exists('123'));
    }

    #[Test]
    public function urlencode(): void
    {
        $this->assertTrue($this->action->exists('123+456'));
    }

    #[Test]
    public function new_existing_product(): void
    {
        $this->assertTrue($this->action->exists('123'));
        $this->assertTrue(MagentoProduct::query()->where('sku', '123')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() === 'magento/rest/all/V1/products/123?fields=sku';
        });
    }

    #[Test]
    public function new_non_existing_product(): void
    {
        $this->assertFalse($this->action->exists('456'));
        $this->assertFalse(MagentoProduct::query()->where('sku', '456')->first()->exists_in_magento); /** @phpstan-ignore-line */
        Http::assertSent(function (Request $request) {
            return $request->url() === 'magento/rest/all/V1/products/456?fields=sku';
        });
    }

    #[Test]
    public function existing_last_checked(): void
    {
        MagentoProduct::query()->create([
            'sku' => '123', 'exists_in_magento' => true, 'last_checked' => now()->subHours(3),
        ]);

        $this->assertTrue($this->action->exists('123'));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_throws_exception_when_magento_is_not_available(): void
    {
        $this->mock(ChecksMagento::class, function (MockInterface $mock): void {
            $mock->shouldReceive('available')->andReturnFalse();
        });

        /** @var CheckMagentoExistence $action */
        $action = app(CheckMagentoExistence::class);

        $this->expectException(RuntimeException::class);
        $action->exists('456');

        Http::assertNothingSent();
    }
}
