<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Translation;

use DeepSeek\DeepSeekClient;

final readonly class DeepSeekTranslator implements TranslatorInterface
{
    /**
     * DeepSeek client instance.
     */
    protected readonly DeepSeekClient $client;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->client = DeepSeekClient::build(config('services.translation.deepseek.api_key'));
    }

    /**
     * Translate text using DeepSeek's API.
     *
     * @param  string  $text  The text to translate
     * @param  string  $targetLocale  The target locale (e.g., 'fr', 'es', 'de')
     * @param  string  $sourceLocale  The source locale (typically default locale)
     * @return string The translated text
     */
    public function translate(string $text, string $targetLocale, string $sourceLocale): string
    {
        try {
            $prompt = "Translate the following text from {$sourceLocale} to {$targetLocale}. Provide only the translated text without explanations or additional context: \n\n{$text}";

            // Set the model from config or use default
            $model = config('services.translation.deepseek.model', 'deepseek-chat');

            // Set temperature from config or use default
            $temperature = (float) config('services.translation.deepseek.temperature', 0.5);

            $client = $this->client
                ->withModel($model)
                ->setTemperature($temperature)
                ->query('You are a professional translator. Translate the text accurately and naturally, maintaining the original meaning, tone, and format.', 'system')
                ->query($prompt);

            $response = $client->run();

            // Parse the response and extract translated content
            $responseData = json_decode($response, true);

            // Extract content from choices if it exists, otherwise use response as is
            if (isset($responseData['choices'][0]['message']['content'])) {
                return trim($responseData['choices'][0]['message']['content']);
            }

            // Fallback to the original response
            return trim($response);
        } catch (\Exception $e) {
            log_error('DeepSeek translation error: ' . $e->getMessage(), [
                'text'         => $text,
                'targetLocale' => $targetLocale,
                'sourceLocale' => $sourceLocale,
            ]);

            // Return the original text if translation fails
            return $text;
        }
    }
}
