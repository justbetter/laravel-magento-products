<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Contracts;

interface ChecksMagentoExistence
{
    public function exists(string $sku): bool;
}
