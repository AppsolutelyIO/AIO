<?php

declare(strict_types=1);

namespace Appsolutely\AIO\DTOs;

use Appsolutely\AIO\Enums\OrderPaymentStatus;

final readonly class PaymentGatewayResult
{
    public function __construct(
        public bool $success,
        public OrderPaymentStatus $status,
        public ?string $vendorReference = null,
        public ?string $redirectUrl = null,
        public array $vendorExtraInfo = [],
        public ?string $errorMessage = null,
    ) {}

    public static function success(
        string $vendorReference,
        ?string $redirectUrl = null,
        array $vendorExtraInfo = [],
    ): self {
        return new self(
            success: true,
            status: OrderPaymentStatus::Paid,
            vendorReference: $vendorReference,
            redirectUrl: $redirectUrl,
            vendorExtraInfo: $vendorExtraInfo,
        );
    }

    public static function pending(
        string $vendorReference,
        ?string $redirectUrl = null,
        array $vendorExtraInfo = [],
    ): self {
        return new self(
            success: true,
            status: OrderPaymentStatus::Pending,
            vendorReference: $vendorReference,
            redirectUrl: $redirectUrl,
            vendorExtraInfo: $vendorExtraInfo,
        );
    }

    public static function redirect(
        string $redirectUrl,
        ?string $vendorReference = null,
        array $vendorExtraInfo = [],
    ): self {
        return new self(
            success: true,
            status: OrderPaymentStatus::Pending,
            vendorReference: $vendorReference,
            redirectUrl: $redirectUrl,
            vendorExtraInfo: $vendorExtraInfo,
        );
    }

    public static function failure(string $errorMessage, array $vendorExtraInfo = []): self
    {
        return new self(
            success: false,
            status: OrderPaymentStatus::Failed,
            errorMessage: $errorMessage,
            vendorExtraInfo: $vendorExtraInfo,
        );
    }
}
