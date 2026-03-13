<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\NotificationSender;
use Illuminate\Database\Eloquent\Collection;

final class NotificationSenderRepository extends BaseRepository
{
    public function model(): string
    {
        return NotificationSender::class;
    }

    /**
     * Get default sender for a category
     */
    public function getDefaultForCategory(string $category): ?NotificationSender
    {
        return $this->model
            ->where('category', $category)
            ->where('is_default', true)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Get active senders by category
     */
    public function getActiveByCategory(string $category): Collection
    {
        return $this->model
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find sender by slug
     */
    public function findBySlug(string $slug): ?NotificationSender
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get all active senders
     */
    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get senders by type
     */
    public function getByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
