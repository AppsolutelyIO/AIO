<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FulfillDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'token'            => ['required', 'string', 'size:64'],
            'delivery_payload' => ['required', 'string', 'max:10000'],
            'channel'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
