<?php

declare(strict_types=1);

namespace JustBetter\MagentoProducts\Contracts;

interface ChecksAllKnownProducts
{
    public function check(): void;
}
