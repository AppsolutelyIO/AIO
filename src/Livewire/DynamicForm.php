<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Services\Contracts\DynamicFormServiceInterface;
use Appsolutely\AIO\Services\Contracts\TurnstileServiceInterface;
use Appsolutely\AIO\Services\PageSlugAliasService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DynamicForm extends GeneralBlock
{
    /**
     * @var array<string, mixed>
     */
    public array $formData = [];

    public array $formFields = [];

    public bool $submitted = false;

    public string $successMessage = '';

    public ?Form $form = null;

    /**
     * Honeypot field — must remain empty (bots auto-fill it).
     */
    public string $website = '';

    /**
     * Cloudflare Turnstile response token (set by frontend widget).
     */
    public string $turnstileToken = '';

    /**
     * Timestamp when form was mounted (server-side, tamper-proof).
     */
    protected float $mountedAt = 0;

    protected ?DynamicFormServiceInterface $formService = null;

    protected ?TurnstileServiceInterface $turnstileService = null;

    protected ?PageSlugAliasService $pageSlugAliasService = null;

    protected array $defaultQueryOptions = [
        'form_slug' => 'test-drive',
    ];

    protected function initializeComponent(Container $container): void
    {
        $this->formService           = $container->make(DynamicFormServiceInterface::class);
        $this->turnstileService      = $container->make(TurnstileServiceInterface::class);
        $this->pageSlugAliasService  = $container->make(PageSlugAliasService::class);
        $this->mountedAt             = microtime(true);

        $formSlug = $this->queryOptions['form_slug'] ?? '';

        try {
            $this->form = $this->formService->getFormBySlug($formSlug);

            if (! $this->form) {
                \Log::warning("Form not found for slug: {$formSlug}.");
                abort(404, "Form not found for slug: \"{$formSlug}\"");
            }

            $this->formFields = $this->formService->getFields($this->form);
            $this->initializeFormDataFromQuery();

            // When visiting via alias URL (e.g. thank-you-for-submitting), show success state
            $pageAlias = $this->page['page_alias'] ?? null;
            if ($pageAlias !== null && $pageAlias !== '') {
                $this->submitted      = true;
                $this->successMessage = $this->displayOptions['success_message'] ?? '';
            } else {
                // Form page (submitted=false): add this alias to cache on demand
                if ($this->hasForceRedirect()) {
                    $pageSlug = $this->page['slug'] ?? '';
                    if ($pageSlug !== '') {
                        $this->pageSlugAliasService->addAlias(
                            (string) $this->displayOptions['redirect_url'],
                            $pageSlug
                        );
                    }
                }
            }
        } catch (NotFoundHttpException $e) {
            // Re-throw 404 exceptions so they're properly handled
            throw $e;
        } catch (\Exception $e) {
            \Log::error("Error loading form with slug {$formSlug}: " . $e->getMessage());
            $this->form       = null;
            $this->formFields = [];
        }
    }

    /**
     * Initialize form data from URL query parameters
     * Matches query parameter values to form field options
     * First tries exact match, then tries matching with hyphens replaced by spaces
     */
    protected function initializeFormDataFromQuery(): void
    {
        $queryParams = request()->query();

        foreach ($queryParams as $paramName => $paramValue) {
            // Skip if parameter doesn't match any form field
            if (! isset($this->formFields[$paramName])) {
                continue;
            }

            // Skip if value is not a string
            if (! is_string($paramValue)) {
                continue;
            }

            $fieldConfig = $this->formFields[$paramName];

            // Only process select and multiselect fields
            $fieldType = $fieldConfig['type'] ?? null;
            if (! $fieldType instanceof FormFieldType || ! $fieldType->supportsOptions()) {
                continue;
            }

            $options = $fieldConfig['options'] ?? [];
            if (empty($options)) {
                continue;
            }

            // Find matching option value
            $matchedValue = $this->findMatchingOption($paramValue, $options);

            if ($matchedValue !== null) {
                $this->formData[$paramName] = $matchedValue;
            }
        }
    }

    /**
     * Find matching option value from query parameter
     * Tries exact match, hyphen/space variations, and case-insensitive matching
     *
     * @param  string  $queryValue  The value from URL query parameter
     * @param  array<string>  $options  Available form field options
     * @return string|null The matched option value or null if no match found
     */
    protected function findMatchingOption(string $queryValue, array $options): ?string
    {
        $normalize = static fn (string $v): string => strtolower(trim($v));

        // First, try exact match
        if (in_array($queryValue, $options, true)) {
            return $queryValue;
        }

        // Try hyphen/space variations (case-sensitive)
        $queryValueWithSpaces = str_replace('-', ' ', $queryValue);
        if (in_array($queryValueWithSpaces, $options, true)) {
            return $queryValueWithSpaces;
        }

        $queryValueWithHyphens = str_replace(' ', '-', $queryValue);
        if (in_array($queryValueWithHyphens, $options, true)) {
            return $queryValueWithHyphens;
        }

        // Case-insensitive match (e.g. "aion-v" matches "AION V")
        $queryNormalized        = $normalize($queryValue);
        $queryNormalizedSpaces  = $normalize(str_replace('-', ' ', $queryValue));
        $queryNormalizedHyphens = $normalize(str_replace(' ', '-', $queryValue));

        foreach ($options as $option) {
            $optNormalized = $normalize($option);
            if ($optNormalized    === $queryNormalized
                || $optNormalized === $queryNormalizedSpaces
                || $optNormalized === $queryNormalizedHyphens) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Submit the form
     */
    public function submit(): void
    {
        // Layer 1: Honeypot check — reject if hidden field is filled or form submitted too fast
        if ($this->isHoneypotTriggered()) {
            $this->fakeSuccess();

            return;
        }

        // Layer 2: Cloudflare Turnstile verification
        if (! $this->verifyTurnstile()) {
            throw ValidationException::withMessages([
                'turnstile' => __('Human verification failed. Please try again.'),
            ]);
        }

        // Apply rate limiting to prevent spam (5 submissions per minute per IP)
        $key = 'form-submission:' . client_ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'form' => "Too many submission attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        try {
            $request = request();
            $request->merge([
                'ip_address' => client_ip($request),
                'user_agent' => $request->userAgent(),
                'referer'    => $request->header('referer'),
            ]);
            // Ensure formService is resolved
            if (! $this->formService) {
                $this->formService = app(DynamicFormServiceInterface::class);
            }
            $this->formService->submitForm($this->form->slug, $this->formData, $request);

            // Hit rate limiter on successful submission (60 second decay)
            RateLimiter::hit($key, 60);

            // Set success state
            $this->submitted      = true;
            $this->successMessage = $this->displayOptions['success_message'];

            // Redirect if configured: full URL takes precedence, else alias path
            $redirectUrl = $this->displayOptions['redirect_after_submit'] ?? '';
            if ($redirectUrl !== '') {
                $this->redirect($redirectUrl);
            } elseif ($this->hasForceRedirect()) {
                $path = trim((string) $this->displayOptions['redirect_url']);
                if ($path !== '') {
                    session()->flash('submitting', true);
                    $this->redirect(normalize_slug($path));
                }
            }
        } catch (ValidationException $e) {
            // Re-throw validation exception to show errors
            throw $e;
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            \Log::error('Form submission error: ' . $e->getMessage(), [
                'form_data' => $this->formData,
                'exception' => $e,
            ]);

            session()->flash('error', 'There was an error processing your request. Please try again.');
        }
    }

    private function hasForceRedirect(): bool
    {
        return ($this->displayOptions['redirect'] ?? '') === 'force'
            && ! empty($this->displayOptions['redirect_url']);
    }

    /**
     * Check if honeypot was triggered (hidden field filled or submission too fast).
     */
    protected function isHoneypotTriggered(): bool
    {
        if (! config('forms.captcha.honeypot.enabled', true)) {
            return false;
        }

        // Hidden field was filled — definitely a bot
        if ($this->website !== '') {
            \Log::info('Honeypot triggered: hidden field filled', ['ip' => client_ip()]);

            return true;
        }

        // Submitted faster than a human could fill the form
        $minTime = (float) config('forms.captcha.honeypot.min_time', 3);
        $elapsed = microtime(true) - $this->mountedAt;
        if ($this->mountedAt > 0 && $elapsed < $minTime) {
            \Log::info('Honeypot triggered: submitted too fast', [
                'ip'      => client_ip(),
                'elapsed' => round($elapsed, 2),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Verify Turnstile token with Cloudflare API.
     */
    protected function verifyTurnstile(): bool
    {
        if (! $this->turnstileService) {
            $this->turnstileService = app(TurnstileServiceInterface::class);
        }

        if (! $this->turnstileService->isEnabled()) {
            return true;
        }

        return $this->turnstileService->verify($this->turnstileToken, client_ip());
    }

    /**
     * Show fake success to fool bots — no actual submission.
     */
    protected function fakeSuccess(): void
    {
        $this->submitted      = true;
        $this->successMessage = $this->displayOptions['success_message'] ?? '';
    }

    /**
     * Reset the form
     */
    public function resetForm(): void
    {
        // When on thank-you alias URL, redirect to form page so form and background work correctly
        $pageAlias = $this->page['page_alias'] ?? null;
        if ($pageAlias !== null && $pageAlias !== '') {
            $slug = $this->page['slug'] ?? '';
            if ($slug !== '') {
                $this->redirect(normalize_slug($slug));

                return;
            }
        }

        $this->submitted      = false;
        $this->successMessage = '';
        $this->formData       = [];
        $this->turnstileToken = '';
        $this->resetValidation();
        $this->initializeComponent(app());
    }
}
