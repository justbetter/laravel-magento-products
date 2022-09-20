<?php

namespace JustBetter\MagentoProducts\Contracts;

use Illuminate\Support\Enumerable;

interface RetrievesMagentoSkus
{
    public function retrieve(int $page): Enumerable;
}
