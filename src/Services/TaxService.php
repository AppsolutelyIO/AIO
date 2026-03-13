<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\TaxRate;
use Appsolutely\AIO\Repositories\TaxRateRepository;
use Appsolutely\AIO\Services\Contracts\TaxServiceInterface;

final readonly class TaxService implements TaxServiceInterface
{
    public function __construct(
        protected TaxRateRepository $taxRateRepository,
    ) {}

    public function calculateTax(int $amount, string $country, ?string $region = null): int
    {
        $rates = TaxRate::query()
            ->where('is_active', true)
            ->where('country', $country)
            ->where(function ($query) use ($region) {
                $query->whereNull('region')
                    ->orWhere('region', $region);
            })
            ->orderBy('priority')
            ->get();

        $totalTax      = 0;
        $taxableAmount = $amount;

        foreach ($rates as $rate) {
            $tax = $rate->calculateTax($taxableAmount);
            $totalTax += $tax;

            if ($rate->is_compound) {
                $taxableAmount += $tax;
            }
        }

        return $totalTax;
    }
}
