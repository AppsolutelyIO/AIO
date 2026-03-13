<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PullFormEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'form_slug'     => ['required', 'string', 'max:255'],
            'from_date'     => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'       => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'entry_id_from' => ['nullable', 'integer', 'min:1'],
            'entry_id_to'   => ['nullable', 'integer', 'min:1'],
            'page'          => ['nullable', 'integer', 'min:1'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
