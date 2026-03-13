<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Jobs;

use Appsolutely\AIO\Facades\Translation;
use Appsolutely\AIO\Repositories\TranslationRepository;
use Appsolutely\AIO\Services\Translation\TranslatorInterface;
use Appsolutely\AIO\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessMissingTranslations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The locale to process, if specified.
     */
    public ?string $locale = null;

    /**
     * The maximum number of translations to process per job run.
     */
    public int $batchSize = 10;

    /**
     * The provider to use for translation.
     */
    public string $provider = 'deepseek';

    /**
     * Create a new job instance.
     */
    public function __construct(?string $locale = null, int $batchSize = 10, string $provider = 'deepseek')
    {
        $this->locale    = $locale;
        $this->batchSize = $batchSize;
        $this->provider  = $provider;
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationRepository $repository, TranslationService $translationService): void
    {
        log_info('ProcessMissingTranslations job started', [
            'provider'  => $this->provider,
            'locale'    => $this->locale,
            'batchSize' => $this->batchSize,
            'job_id'    => $this->job?->getJobId() ?? uniqid('job_'),
        ]);

        try {
            // Set the provider in the config to ensure the correct translator is used
            app('config')->set('services.translation.provider', $this->provider);

            // Force refresh of the TranslatorInterface binding
            app()->forgetInstance(TranslatorInterface::class);

            // Resolve a new instance with updated config
            $translator = app()->make(TranslatorInterface::class);

            log_info('Translator resolved', [
                'translator_type' => get_class($translator),
            ]);

            // Get missing translations
            $missingTranslations = $repository->getMissingTranslations($this->locale);

            log_info('Missing translations found', [
                'count' => count($missingTranslations),
            ]);

            // Process only a batch to avoid timeouts or API limits
            $translationsToProcess = array_slice($missingTranslations, 0, $this->batchSize);

            if (empty($translationsToProcess)) {
                log_info('No missing translations to process.');

                return;
            }

            log_info('Processing missing translations', [
                'count'    => count($translationsToProcess),
                'locale'   => $this->locale,
                'provider' => $this->provider,
            ]);

            // Get the default locale to use as source
            $sourceLocale = config('app.locale', 'en');

            foreach ($translationsToProcess as $translation) {
                try {
                    // Skip translation if it's already translated or if the target locale is the same as source
                    if (! empty($translation['translated_text']) || $translation['locale'] === $sourceLocale) {
                        continue;
                    }

                    // Translate the text
                    $translatedText = $translator->translate(
                        $translation['original_text'],
                        $translation['locale'],
                        $sourceLocale
                    );

                    // Update the translation in database
                    $isUpdated = $repository->update(
                        $repository->find($translation['id']),
                        [
                            'translated_text' => $translatedText,
                            'translator'      => $this->provider,
                        ]
                    );

                    if ($isUpdated) {
                        // Update the cache with the new translation
                        $translationService->cacheTranslation(
                            $translation['original_text'],
                            $translatedText,
                            $translation['locale']
                        );

                        log_info('Translation updated', [
                            'id'       => $translation['id'],
                            'locale'   => $translation['locale'],
                            'provider' => $this->provider,
                        ]);
                    }
                } catch (\Exception $e) {
                    log_error('Error processing translation', [
                        'id'     => $translation['id'],
                        'error'  => $e->getMessage(),
                        'locale' => $translation['locale'],
                    ]);
                    // Continue with next translation even if one fails
                }
            }

            // If there are more translations to process, dispatch another job
            if (count($missingTranslations) > $this->batchSize) {
                // Create a new job with the same parameters
                $newJob = new self($this->locale, $this->batchSize, $this->provider);
                // Dispatch with a delay to avoid overwhelming the API
                dispatch($newJob)->delay(now()->addMinutes(1));

                log_info('Dispatched follow-up job for remaining translations', [
                    'remaining' => count($missingTranslations) - $this->batchSize,
                ]);
            }
        } catch (Throwable $exception) {
            log_error('Fatal error in ProcessMissingTranslations job', [
                'error' => $exception->getMessage(),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // Throw the exception to trigger Laravel's job failure handling
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        log_error('ProcessMissingTranslations job failed', [
            'provider' => $this->provider,
            'locale'   => $this->locale,
            'error'    => $exception->getMessage(),
            'job_id'   => $this->job?->getJobId() ?? uniqid('failed_job_'),
        ]);
    }
}
