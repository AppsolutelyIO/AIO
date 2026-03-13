<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Translation;
use Appsolutely\AIO\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TranslationService::class);
        // Set a non-default locale for tests that need translation
        config(['app.locale' => 'en']);
    }

    private function createTranslation(array $attrs = []): Translation
    {
        return Translation::create(array_merge([
            'locale'          => 'zh',
            'type'            => 'php',
            'original_text'   => 'Hello',
            'translated_text' => '你好',
        ], $attrs));
    }

    // --- translate ---

    public function test_translate_returns_original_for_default_locale(): void
    {
        // Default locale is 'en', so translating 'en' text returns as-is
        $result = $this->service->translate('Hello World', 'en');

        $this->assertEquals('Hello World', $result);
    }

    public function test_translate_returns_empty_string_for_empty_input(): void
    {
        $result = $this->service->translate('', 'zh');

        $this->assertEquals('', $result);
    }

    public function test_translate_returns_translated_text_from_database(): void
    {
        $this->createTranslation(['original_text' => 'Goodbye', 'translated_text' => '再见', 'locale' => 'zh']);

        $result = $this->service->translate('Goodbye', 'zh');

        $this->assertEquals('再见', $result);
    }

    public function test_translate_returns_original_when_no_translation_found(): void
    {
        $result = $this->service->translate('No translation available', 'zh');

        $this->assertEquals('No translation available', $result);
    }

    public function test_translate_creates_translation_record_when_not_found(): void
    {
        $this->service->translate('Brand new text', 'zh');

        $this->assertDatabaseHas('translations', [
            'original_text' => 'Brand new text',
            'locale'        => 'zh',
        ]);
    }

    public function test_translate_increments_usage_when_found(): void
    {
        $translation = $this->createTranslation([
            'original_text' => 'Click here',
            'locale'        => 'zh',
        ]);
        $originalCount = $translation->used_count;

        $this->service->translate('Click here', 'zh');

        $this->assertEquals($originalCount + 1, $translation->fresh()->used_count);
    }

    // --- getMissingTranslations ---

    public function test_get_missing_translations_returns_array(): void
    {
        $result = $this->service->getMissingTranslations();

        $this->assertIsArray($result);
    }

    public function test_get_missing_translations_includes_null_translated(): void
    {
        $this->createTranslation(['translated_text' => null]);

        $result = $this->service->getMissingTranslations();

        $this->assertNotEmpty($result);
    }

    // --- clearCache ---

    public function test_clear_cache_forgets_root_cache_key(): void
    {
        // The service caches translations under a root key
        $cacheKey = appsolutely() . '.translations';

        // Put something under the root key
        app(\Illuminate\Contracts\Cache\Repository::class)->put($cacheKey, 'cached-data', 3600);
        $this->assertNotNull(app(\Illuminate\Contracts\Cache\Repository::class)->get($cacheKey));

        $this->service->clearCache();

        $this->assertNull(app(\Illuminate\Contracts\Cache\Repository::class)->get($cacheKey));
    }

    // --- updateTranslation ---

    public function test_update_translation_updates_translated_text(): void
    {
        $translation = $this->createTranslation([
            'original_text'   => 'Update me',
            'translated_text' => '旧翻译',
            'locale'          => 'zh',
        ]);

        $result = $this->service->updateTranslation($translation->id, '新翻译', 'Manual');

        $this->assertTrue($result);
        $this->assertEquals('新翻译', $translation->fresh()->translated_text);
        $this->assertEquals('Manual', $translation->fresh()->translator->value);
    }

    public function test_update_translation_returns_false_for_nonexistent_id(): void
    {
        $result = $this->service->updateTranslation(99999, 'text', 'Manual');

        $this->assertFalse($result);
    }
}
