<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Facades;

use Appsolutely\AIO\Services\TranslationService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string translate(string $text, ?string $locale = null, string $type = 'php', ?string $callStack = null)
 * @method static void clearCache()
 * @method static array getMissingTranslations(string $locale = null)
 *
 * @see \Appsolutely\AIO\Services\TranslationService
 */
class Translation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TranslationService::class;
    }
}
