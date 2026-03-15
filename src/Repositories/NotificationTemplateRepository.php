<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class NotificationTemplateRepository extends BaseRepository
{
    public function model(): string
    {
        return NotificationTemplate::class;
    }

    /**
     * Find template by slug
     */
    public function findBySlug(string $slug): ?NotificationTemplate
    {
        return $this->model->newQuery()->where('slug', $slug)->status()->first();
    }

    /**
     * Get templates by category
     */
    public function getByCategory(string $category): Collection
    {
        return $this->model->newQuery()->where('category', $category)->status()->orderBy('name')->get();
    }

    /**
     * Get all active templates
     */
    public function getActive(): Collection
    {
        return $this->model->newQuery()->status()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create template with unique slug
     */
    public function createWithUniqueSlug(array $data): NotificationTemplate
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        return $this->create($data);
    }

    /**
     * Update template with slug handling
     */
    public function updateWithSlug(int $id, array $data): NotificationTemplate
    {
        $template = $this->find($id);

        if (isset($data['name']) && (empty($data['slug']) || $data['slug'] === $template->slug)) {
            $data['slug'] = Str::slug($data['name']);
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $template->id);
        }

        $this->update($data, $id);

        return $this->find($id);
    }

    /**
     * Duplicate template
     */
    public function duplicate(int $id): NotificationTemplate
    {
        $template = $this->find($id);
        $data     = $template->toArray();

        unset($data['id'], $data['created_at'], $data['updated_at']);

        $data['name']      = $data['name'] . ' (Copy)';
        $data['slug']      = $this->ensureUniqueSlug($data['slug'] . '-copy');
        $data['is_system'] = false;

        return $this->create($data);
    }

    /**
     * Get templates with usage statistics
     */
    public function getWithUsageStats(): Collection
    {
        return $this->model->newQuery()->withCount(['rules as usage_count' => function ($query) {
            $query->where('status', Status::ACTIVE);
        }])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get available categories
     */
    public function getCategories(): array
    {
        return $this->model->newQuery()->distinct('category')->pluck('category')->toArray();
    }

    /**
     * Check if template can be deleted
     */
    public function canDelete(int $id): bool
    {
        $template = $this->find($id);

        return ! $template->is_system && $template->rules()->count() === 0;
    }

    /**
     * Ensure unique slug
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter      = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
