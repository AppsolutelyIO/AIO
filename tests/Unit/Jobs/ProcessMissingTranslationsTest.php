<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Jobs;

use Appsolutely\AIO\Jobs\ProcessMissingTranslations;
use Illuminate\Contracts\Queue\ShouldQueue;
use Appsolutely\AIO\Tests\TestCase;

final class ProcessMissingTranslationsTest extends TestCase
{
    // --- job configuration ---

    public function test_job_implements_should_queue(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    public function test_job_has_correct_tries_value(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertEquals(3, $job->tries);
    }

    public function test_job_has_backoff_configured(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertGreaterThan(0, $job->backoff);
    }

    public function test_job_accepts_locale_parameter(): void
    {
        $job = new ProcessMissingTranslations('zh');

        $this->assertEquals('zh', $job->locale);
    }

    public function test_job_accepts_batch_size_parameter(): void
    {
        $job = new ProcessMissingTranslations(null, 25);

        $this->assertEquals(25, $job->batchSize);
    }

    public function test_job_accepts_provider_parameter(): void
    {
        $job = new ProcessMissingTranslations(null, 10, 'openai');

        $this->assertEquals('openai', $job->provider);
    }

    public function test_job_defaults_provider_to_deepseek(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertEquals('deepseek', $job->provider);
    }

    public function test_job_defaults_batch_size_to_ten(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertEquals(10, $job->batchSize);
    }

    // --- failed ---

    public function test_failed_logs_error_without_throwing(): void
    {
        $job = new ProcessMissingTranslations('zh', 10, 'deepseek');

        // log_error() calls Log::log('error', ...) under the hood
        \Illuminate\Support\Facades\Log::shouldReceive('log')
            ->once()
            ->withArgs(function (string $level, string $message, ?array $context = []) {
                return $level === 'error'
                    && str_contains($message, 'ProcessMissingTranslations')
                    && ($context['provider'] ?? '') === 'deepseek'
                    && ($context['locale'] ?? '')   === 'zh'
                    && str_contains($context['error'] ?? '', 'Connection timeout');
            });

        $job->failed(new \Exception('Connection timeout'));

        // Mockery verifies the Log expectation; count it as a PHPUnit assertion
        $this->addToAssertionCount(1);
    }

    public function test_job_defaults_locale_to_null(): void
    {
        $job = new ProcessMissingTranslations();

        $this->assertNull($job->locale);
    }
}
