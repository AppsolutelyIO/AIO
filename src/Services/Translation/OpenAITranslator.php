<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Translation;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

final readonly class OpenAITranslator implements TranslatorInterface
{
    /**
     * Translate text using OpenAI's API.
     *
     * @param  string  $text  The text to translate
     * @param  string  $targetLocale  The target locale (e.g., 'fr', 'es', 'de')
     * @param  string  $sourceLocale  The source locale (typically default locale)
     * @return string The translated text
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale): string
    {
        try {
            // Log the configuration for debugging
            log_debug('OpenAI translation configuration', [
                'api_key_exists' => ! empty(config('services.translation.openai.api_key')),
                'model'          => config('services.translation.openai.model', 'gpt-4-turbo'),
            ]);

            $prompt = "Translate the following text from {$sourceLocale} to {$targetLocale}. Provide only the translated text without explanations or additional context: \n\n{$text}";

            $response = OpenAI::chat()->create([
                'model'    => config('services.translation.openai.model', 'gpt-4-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional translator. Translate the text accurately and naturally, maintaining the original meaning, tone, and format.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => (float) config('services.translation.openai.temperature', 0.5),
                'max_tokens'  => (int) config('services.translation.openai.max_tokens', 1000),
            ]);

            return trim($response->choices[0]->message->content);
        } catch (\Exception $e) {
            log_error('OpenAI translation error: ' . $e->getMessage(), [
                'text'           => $text,
                'targetLocale'   => $targetLocale,
                'sourceLocale'   => $sourceLocale,
                'api_key_exists' => ! empty(config('services.translation.openai.api_key')),
            ]);

            // Return the original text if translation fails
            return $text;
        }
    }
}
