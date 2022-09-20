<?php

namespace JustBetter\MagentoProducts\Contracts;

/** Checks if known products in the database that don't exist in Magento if they exist */
interface ChecksKnownProducts
{
    public function handle(array $skus = []): void;
}
