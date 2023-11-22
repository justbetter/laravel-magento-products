<?php

namespace JustBetter\MagentoProducts\Contracts;

use Illuminate\Bus\Batch;

interface DiscoversMagentoProducts
{
    public function discover(int $page, Batch $batch): void;
}
