<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\FormEntry;
use Illuminate\Http\Request;

interface DynamicFormSubmissionServiceInterface
{
    /**
     * Submit form entry
     */
    public function submitForm(string $slug, array $data, ?Request $request = null): FormEntry;
}
