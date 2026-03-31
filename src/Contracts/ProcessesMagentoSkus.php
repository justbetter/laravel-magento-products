<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Contracts;

use Illuminate\Support\Enumerable;

interface ProcessesMagentoSkus
{
    public function process(Enumerable $skus): void;
}
