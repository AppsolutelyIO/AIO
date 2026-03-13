<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Translation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class TranslationRepository extends BaseRepository
{
    public function model(): string
    {
        return Translation::class;
    }

    /**
     * Find translation by original text and locale
     */
    public function findByOriginalText(string $originalText, string $locale): ?Translation
    {
        return $this->model->where('original_text', $originalText)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Find all translations by locale
     */
    public function findByLocale(string $locale): Collection
    {
        return $this->model->where('locale', $locale)->get();
    }

    /**
     * Find translations by type
     */
    public function findByType(string $type, string $locale): Collection
    {
        return $this->model->where('type', $type)
            ->where('locale', $locale)
            ->get();
    }

    /**
     * Update the usage statistics for a translation
     */
    public function incrementUsage(Translation $translation): bool
    {
        return $translation->update([
            'used_count' => $translation->used_count + 1,
            'last_used'  => Carbon::now(),
        ]);
    }

    /**
     * Get all missing translations (where translated_text is null or empty)
     *
     * @param  string|null  $locale  Optional locale to filter by
     * @return array The missing translations
     */
    public function getMissingTranslations(?string $locale = null): array
    {
        $query = $this->model->where(function ($q) {
            $q->whereNull('translated_text')
                ->orWhere('translated_text', '');
        });

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        return $query->get()->toArray();
    }
}
