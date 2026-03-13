<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

final class PaymentRepository extends BaseRepository
{
    public function model(): string
    {
        return Payment::class;
    }

    public function getActive(): Collection
    {
        return $this->findWhere(['status' => Status::ACTIVE])
            ->sortBy('sort')
            ->values();
    }

    public function getByProvider(PaymentProvider $provider): Collection
    {
        return $this->findWhere([
            'provider' => $provider,
            'status'   => Status::ACTIVE,
        ])->sortBy('sort')->values();
    }

    public function getByIds(array $ids): Collection
    {
        return $this->findWhereIn('id', $ids);
    }
}
