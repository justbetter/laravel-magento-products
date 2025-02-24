<?php

namespace JustBetter\MagentoProducts\Tests\Actions;

use Illuminate\Support\Facades\Http;
use JustBetter\MagentoProducts\Actions\RetrieveMagentoSkus;
use JustBetter\MagentoProducts\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveMagentoSkusTest extends TestCase
{
    #[Test]
    public function it_retrieves_skus(): void
    {
        Http::fake([
            '*' => Http::response([
                'items' => [
                    [
                        'sku' => '123',
                    ],
                    [
                        'sku' => '456',
                    ],
                ],
            ]),
        ]);

        /** @var RetrieveMagentoSkus $action */
        $action = app(RetrieveMagentoSkus::class);

        $this->assertEquals(collect(['123', '456']), $action->retrieve(1));
    }
}
