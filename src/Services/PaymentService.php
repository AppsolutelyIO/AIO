<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Repositories\OrderPaymentRepository;
use Appsolutely\AIO\Repositories\PaymentRepository;
use Appsolutely\AIO\Services\Contracts\PaymentServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected OrderPaymentRepository $orderPaymentRepository,
    ) {}

    public function getPayments(): Collection
    {
        return $this->paymentRepository->all();
    }

    public function getActivePayments(): Collection
    {
        return $this->paymentRepository->getActive();
    }

    public function getPaymentsByProvider(PaymentProvider $provider): Collection
    {
        return $this->paymentRepository->getByProvider($provider);
    }

    public function getAvailablePaymentsForProduct(Product $product): Collection
    {
        $paymentMethodIds = $product->payment_methods ?? [];

        if (! empty($paymentMethodIds)) {
            $payments = $this->paymentRepository->getByIds($paymentMethodIds)
                ->where('status', Status::ACTIVE)
                ->sortBy('sort')
                ->values();

            if ($payments->isNotEmpty()) {
                return $payments;
            }
        }

        return $this->paymentRepository->getActive();
    }

    public function findPayment(int $id): ?Payment
    {
        return $this->paymentRepository->findWhere(['id' => $id])->first();
    }

    public function createPayment(array $data): Payment
    {
        return $this->paymentRepository->create($data);
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        return $this->paymentRepository->update($data, $payment->id);
    }
}
