<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Contracts;

use Illuminate\Support\Enumerable;

/**
 * @deprecated
 */
interface RetrievesMagentoSkus
{
    public function retrieve(int $page): Enumerable;
}
