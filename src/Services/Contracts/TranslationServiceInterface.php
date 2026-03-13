<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface TranslationServiceInterface
{
    /**
     * Translate a string
     *
     * @param  string  $text  The original text to translate
     * @param  string|null  $locale  The target locale
     * @param  string  $type  The source type (php, blade, db)
     * @param  string|null  $callStack  The call stack information
     * @return string The translated text or original if not found
     */
    public function translate(string $text, ?string $locale = null, string $type = 'php', ?string $callStack = null): string;

    /**
     * Cache a translation
     */
    public function cacheTranslation(string $original, string $translated, string $locale): void;

    /**
     * Clear the translation cache
     */
    public function clearCache(): void;

    /**
     * Get all missing translations
     */
    public function getMissingTranslations(?string $locale = null): array;

    /**
     * Update a translation with a new translated text and translator
     *
     * @param  int  $id  The translation ID
     * @param  string  $translatedText  The new translated text
     * @param  string  $translator  The translator source (Google, DeepSeek, OpenAI, Manual)
     * @return bool True if successful
     */
    public function updateTranslation(int $id, string $translatedText, string $translator): bool;
}
