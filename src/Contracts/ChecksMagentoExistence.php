<?php

namespace JustBetter\MagentoProducts\Contracts;

interface ChecksMagentoExistence
{
    public function exists(string $sku): bool;
}
