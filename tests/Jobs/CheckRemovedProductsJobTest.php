<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ChecksRemovedProducts;
use JustBetter\MagentoProducts\Jobs\CheckRemovedProductsJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CheckRemovedProductsJobTest extends TestCase
{
    #[Test]
    public function it_calls_contract(): void
    {
        $this->mock(ChecksRemovedProducts::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')->once();
        });

        CheckRemovedProductsJob::dispatch();
    }
}
