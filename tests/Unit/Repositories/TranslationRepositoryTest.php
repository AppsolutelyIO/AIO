<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\Translation;
use Appsolutely\AIO\Repositories\TranslationRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TranslationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TranslationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TranslationRepository::class);
    }

    private function createTranslation(array $attrs = []): Translation
    {
        return Translation::create(array_merge([
            'locale'          => 'en',
            'type'            => 'php',
            'original_text'   => 'Hello World ' . uniqid(),
            'translated_text' => 'Hello World',
        ], $attrs));
    }

    // --- findByOriginalText ---

    public function test_find_by_original_text_returns_matching_translation(): void
    {
        $this->createTranslation(['locale' => 'zh', 'original_text' => 'Goodbye']);

        $result = $this->repository->findByOriginalText('Goodbye', 'zh');

        $this->assertInstanceOf(Translation::class, $result);
        $this->assertEquals('Goodbye', $result->original_text);
        $this->assertEquals('zh', $result->locale);
    }

    public function test_find_by_original_text_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByOriginalText('Nonexistent', 'en');

        $this->assertNull($result);
    }

    public function test_find_by_original_text_is_locale_specific(): void
    {
        $this->createTranslation(['locale' => 'fr', 'original_text' => 'Test']);

        $result = $this->repository->findByOriginalText('Test', 'de');

        $this->assertNull($result);
    }

    // --- findByLocale ---

    public function test_find_by_locale_returns_all_translations_for_locale(): void
    {
        $this->createTranslation(['locale' => 'ja']);
        $this->createTranslation(['locale' => 'ja']);
        $this->createTranslation(['locale' => 'ko']);

        $result = $this->repository->findByLocale('ja');

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals('ja', $t->locale));
    }

    // --- findByType ---

    public function test_find_by_type_returns_translations_of_given_type_and_locale(): void
    {
        $this->createTranslation(['locale' => 'en', 'type' => 'php']);
        $this->createTranslation(['locale' => 'en', 'type' => 'blade']);
        $this->createTranslation(['locale' => 'en', 'type' => 'php']);

        $result = $this->repository->findByType('php', 'en');

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals('php', $t->type->value));
    }

    // --- incrementUsage ---

    public function test_increment_usage_increases_used_count(): void
    {
        $translation = $this->createTranslation(['used_count' => 5]);

        $this->repository->incrementUsage($translation);

        $this->assertEquals(6, $translation->fresh()->used_count);
    }

    public function test_increment_usage_updates_last_used(): void
    {
        $translation = $this->createTranslation();

        $before = now()->subSecond();
        $this->repository->incrementUsage($translation);

        $this->assertTrue($translation->fresh()->last_used->gte($before));
    }

    // --- getMissingTranslations ---

    public function test_get_missing_translations_returns_null_translated_text(): void
    {
        $this->createTranslation(['translated_text' => null]);
        $this->createTranslation(['translated_text' => 'translated']);

        $result = $this->repository->getMissingTranslations();

        $this->assertCount(1, $result);
    }

    public function test_get_missing_translations_returns_empty_translated_text(): void
    {
        $this->createTranslation(['translated_text' => '']);
        $this->createTranslation(['translated_text' => 'translated']);

        $result = $this->repository->getMissingTranslations();

        $this->assertCount(1, $result);
    }

    public function test_get_missing_translations_with_locale_includes_locale_records(): void
    {
        $this->createTranslation(['locale' => 'zh', 'translated_text' => null]);

        $result = $this->repository->getMissingTranslations('zh');

        $locales = array_column($result, 'locale');
        $this->assertContains('zh', $locales);
    }

    public function test_get_missing_translations_returns_array(): void
    {
        $result = $this->repository->getMissingTranslations();

        $this->assertIsArray($result);
    }
}
