<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Jobs\ProcessMissingTranslations;
use Appsolutely\AIO\Repositories\TranslationRepository;
use Illuminate\Console\Command;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate
                           {--locale= : Specific locale to process (or none for all)}
                           {--batch= : Number of translations to process per run}
                           {--provider= : Translator provider to use (deepseek or openai)}
                           {--sync : Run the job synchronously without queueing}
                           {--force : Force running the job even if no missing translations are found}
                           {--debug : Enable extra debug output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process missing translations using AI (OpenAI or DeepSeek)';

    /**
     * Execute the console command.
     */
    public function handle(TranslationRepository $repository)
    {
        $locale    = $this->option('locale');
        $batchSize = $this->option('batch') ? (int) $this->option('batch') : 10;
        $debug     = $this->option('debug');

        // Get provider from option or config
        $configProvider = app('config')->get('services.translation.provider', 'deepseek');
        $provider       = $this->option('provider') ?: $configProvider;

        // Validate provider
        if (! in_array($provider, ['deepseek', 'openai'])) {
            $this->error("Invalid provider: $provider. Must be 'deepseek' or 'openai'.");

            return self::FAILURE;
        }

        if ($debug) {
            $this->info('Command options:');
            $this->info('- Locale: ' . ($locale ?: 'all'));
            $this->info("- Batch size: $batchSize");
            $this->info("- Provider: $provider");
            $this->info('- Sync mode: ' . ($this->option('sync') ? 'yes' : 'no'));
        }

        // Check for missing translations first
        $missingCount = count($repository->getMissingTranslations($locale));

        if ($missingCount === 0 && ! $this->option('force')) {
            $this->info('No missing translations found. Use --force to run anyway.');

            return self::SUCCESS;
        }

        if ($debug && $missingCount > 0) {
            $this->info("Found $missingCount missing translations to process.");
        }

        // Check if we should run the job synchronously
        $sync = $this->option('sync');

        if ($sync) {
            $this->info('Running translation job synchronously...');
            $this->info(sprintf(
                'Using provider: %s, Locale: %s, Batch size: %d',
                $provider,
                $locale ?: 'all',
                $batchSize
            ));

            try {
                // Create and run the job directly
                $job = new ProcessMissingTranslations($locale, $batchSize, $provider);
                $job->handle($repository);
                $this->info('Translation job completed!');
            } catch (\Throwable $e) {
                $this->error('Error running translation job: ' . $e->getMessage());
                if ($debug) {
                    $this->error($e->getTraceAsString());
                }

                return self::FAILURE;
            }
        } else {
            $this->info('Dispatching translation job...');
            $this->info(sprintf(
                'Using provider: %s, Locale: %s, Batch size: %d',
                $provider,
                $locale ?: 'all',
                $batchSize
            ));

            try {
                // Dispatch the job to process missing translations
                dispatch(new ProcessMissingTranslations($locale, $batchSize, $provider));
                $this->info('Translation job dispatched successfully!');

                log_info('Translation job dispatched', [
                    'provider'   => $provider,
                    'locale'     => $locale ?: 'all',
                    'batch_size' => $batchSize,
                ]);
            } catch (\Throwable $e) {
                $this->error('Error dispatching translation job: ' . $e->getMessage());
                if ($debug) {
                    $this->error($e->getTraceAsString());
                }

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
