<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\PaymentMethod;
use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use ScopeReference;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'title',
        'display',
        'vendor',
        'provider',
        'payment_method',
        'handler',
        'device',
        'currency',
        'supported_currencies',
        'merchant_id',
        'merchant_key',
        'merchant_secret',
        'setting',
        'is_test_mode',
        'webhook_url',
        'fee_percentage',
        'fee_fixed',
        'min_amount',
        'max_amount',
        'instruction',
        'remark',
        'sort',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'provider'             => PaymentProvider::class,
            'payment_method'       => PaymentMethod::class,
            'supported_currencies' => 'array',
            'setting'              => 'array',
            'is_test_mode'         => 'boolean',
            'fee_percentage'       => 'decimal:2',
            'fee_fixed'            => 'integer',
            'min_amount'           => 'integer',
            'max_amount'           => 'integer',
            'sort'                 => 'integer',
            'status'               => Status::class,
        ];
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    /**
     * Check if the payment amount is within the configured limits.
     */
    public function isAmountAllowed(int $amount): bool
    {
        if ($this->min_amount > 0 && $amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount > 0 && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the fee for a given amount.
     *
     * @return int Fee amount in the smallest currency unit
     */
    public function calculateFee(int $amount): int
    {
        $percentageFee = (int) round($amount * $this->fee_percentage / 100);

        return $percentageFee + $this->fee_fixed;
    }
}
