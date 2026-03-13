<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Translation;

interface TranslatorInterface
{
    /**
     * Translate text from one language to another.
     *
     * @param  string  $text  The text to translate
     * @param  string  $targetLocale  The target locale (e.g., 'fr', 'es', 'de')
     * @param  string  $sourceLocale  The source locale (typically default locale)
     * @return string The translated text
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale): string;
}
