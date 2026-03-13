<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface TaxServiceInterface
{
    /**
     * Calculate the total tax for an amount based on location.
     */
    public function calculateTax(int $amount, string $country, ?string $region = null): int;
}
