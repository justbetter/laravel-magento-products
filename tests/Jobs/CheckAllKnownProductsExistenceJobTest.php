<?php

namespace JustBetter\MagentoProducts\Tests\Jobs;

use JustBetter\MagentoProducts\Contracts\ChecksAllKnownProducts;
use JustBetter\MagentoProducts\Jobs\CheckAllKnownProductsExistenceJob;
use JustBetter\MagentoProducts\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CheckAllKnownProductsExistenceJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ChecksAllKnownProducts::class, function (MockInterface $mock) {
            $mock->shouldReceive('check')->once();
        });

        CheckAllKnownProductsExistenceJob::dispatch();
    }
}
