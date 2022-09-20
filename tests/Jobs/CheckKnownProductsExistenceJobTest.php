<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ChecksKnownProducts;
use JustBetter\MagentoProducts\Jobs\CheckKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class CheckKnownProductsExistenceJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(ChecksKnownProducts::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')->with(['123'])->once();
        });

        CheckKnownProductsExistenceJob::dispatch(['123']);
    }
}
