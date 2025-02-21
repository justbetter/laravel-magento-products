<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\CheckMagentoEnabled;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckMagentoEnabledTest extends TestCase
{
    protected CheckMagentoEnabled $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CheckMagentoEnabled::class);
    }

    #[Test]
    public function it_checks_unknown_products(): void
    {
        Http::fake([
            '*/products/1' => Http::response([
                'status' => 1,
            ]),
            '*/products/2' => Http::response([
                'status' => 0,
            ]),
        ]);

        $this->assertTrue($this->action->enabled('1'));
        $this->assertFalse($this->action->enabled('2'));
    }

    #[Test]
    public function it_checks_missing_product(): void
    {
        Http::fake([
            '*/products/3' => Http::response([], 404),
        ]);

        $this->assertFalse($this->action->enabled('3'));
    }
}
