<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ChecksRemovedProducts;
use JustBetter\MagentoProducts\Jobs\CheckRemovedProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;

class CheckRemovedProductsJobTest extends TestCase
{
    public function test_it_calls_contract(): void
    {
        $this->mock(ChecksRemovedProducts::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')->once();
        });

        CheckRemovedProductsJob::dispatch();
    }
}
