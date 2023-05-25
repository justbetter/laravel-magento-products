<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ChecksAllKnownProducts;
use JustBetter\MagentoProducts\Jobs\CheckAllKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class CheckAllKnownProductsExistenceJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(ChecksAllKnownProducts::class, function (MockInterface $mock) {
            $mock->shouldReceive('check')->once();
        });

        CheckAllKnownProductsExistenceJob::dispatch();
    }
}
