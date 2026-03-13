<form wire:submit.prevent="submit" method="POST" novalidate>
    @csrf

    <div class="row g-3">
        @foreach ($formFields as $fieldName => $fieldConfig)
            @php
                $colClass = !empty($displayOptions['columns']) && $displayOptions['columns'] == 2 ? 'col-md-6' : 'col-12';
                $errorName = "formData.{$fieldName}";
            @endphp

            @if ($fieldConfig['type'] === \App\Enums\FormFieldType::Hidden)
                <input type="hidden" id="{{ $fieldName }}" name="{{ $fieldName }}"
                    wire:model.defer="formData.{{ $fieldName }}" value="{{ $fieldConfig['default'] ?? '' }}"
                    @if (!empty($fieldConfig['options']) && is_array($fieldConfig['options'])) data-options-mapping="{{ json_encode($fieldConfig['options']) }}" @endif>
            @else
                <div class="{{ $colClass }}">
                    @if (in_array($fieldConfig['type'], [\App\Enums\FormFieldType::Text, \App\Enums\FormFieldType::Email]))
                        <div class="mb-0">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false)
                                    <span class="form-label-description">{{ __('Required') }}</span>
                                @endif
                            </label>
                            <input type="{{ $fieldConfig['type']->value }}" id="{{ $fieldName }}"
                                class="form-control @error($errorName) is-invalid @enderror"
                                wire:model.defer="formData.{{ $fieldName }}"
                                placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                                {{ $fieldConfig['required'] ?? false ? 'required' : '' }}>
                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($fieldConfig['type'] === \App\Enums\FormFieldType::Date)
                        <div class="mb-0">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false)
                                    <span class="form-label-description">{{ __('Required') }}</span>
                                @endif
                            </label>
                            <input type="date" id="{{ $fieldName }}"
                                class="form-control @error($errorName) is-invalid @enderror"
                                wire:model.defer="formData.{{ $fieldName }}"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                {{ $fieldConfig['required'] ?? false ? 'required' : '' }}>
                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($fieldConfig['type'] === \App\Enums\FormFieldType::Select)
                        <div class="mb-0">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false)
                                    <span class="form-label-description">{{ __('Required') }}</span>
                                @endif
                            </label>
                            <select id="{{ $fieldName }}"
                                class="form-select @error($errorName) is-invalid @enderror"
                                wire:model.defer="formData.{{ $fieldName }}"
                                {{ $fieldConfig['required'] ?? false ? 'required' : '' }}>
                                <option value="">{{ $fieldConfig['placeholder'] ?? __('Select an option') }}</option>
                                @foreach ($fieldConfig['options'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($fieldConfig['type'] === \App\Enums\FormFieldType::Textarea)
                        <div class="mb-0">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false)
                                    <span class="form-label-description">{{ __('Required') }}</span>
                                @endif
                            </label>
                            <textarea id="{{ $fieldName }}" class="form-control @error($errorName) is-invalid @enderror"
                                wire:model.defer="formData.{{ $fieldName }}" rows="{{ $fieldConfig['rows'] ?? 3 }}"
                                placeholder="{{ $fieldConfig['placeholder'] ?? '' }}"
                                {{ $fieldConfig['required'] ?? false ? 'required' : '' }}></textarea>
                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    @elseif($fieldConfig['type'] === \App\Enums\FormFieldType::Checkbox)
                        <div class="mb-0">
                            <label class="form-check">
                                <input type="checkbox" id="{{ $fieldName }}"
                                    class="form-check-input @error($errorName) is-invalid @enderror"
                                    wire:model.defer="formData.{{ $fieldName }}" value="1"
                                    {{ $fieldConfig['required'] ?? false ? 'required' : '' }}>
                                <span class="form-check-label">
                                    {{ $fieldConfig['label'] }}
                                    @if ($fieldConfig['required'] ?? false)
                                        <span class="text-danger">*</span>
                                    @endif
                                </span>
                                @error($errorName)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </label>
                        </div>

                    @elseif($fieldConfig['type'] === \App\Enums\FormFieldType::MultipleSelect)
                        <div class="mb-0">
                            <label for="{{ $fieldName }}" class="form-label">
                                {{ $fieldConfig['label'] }}
                                @if ($fieldConfig['required'] ?? false)
                                    <span class="form-label-description">{{ __('Required') }}</span>
                                @endif
                            </label>
                            <select id="{{ $fieldName }}"
                                class="form-select @error($errorName) is-invalid @enderror"
                                wire:model.defer="formData.{{ $fieldName }}" multiple
                                size="{{ min(count($fieldConfig['options'] ?? []), 5) }}"
                                {{ $fieldConfig['required'] ?? false ? 'required' : '' }}>
                                @foreach ($fieldConfig['options'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error($errorName)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">{{ __('Hold Ctrl/Cmd to select multiple options') }}</small>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:target="submit" wire:loading.remove>
                {{ $displayOptions['submit_text'] ?? __('Submit') }}
            </span>
            <span wire:target="submit" wire:loading>
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                {{ __('Processing...') }}
            </span>
        </button>
    </div>
</form>
