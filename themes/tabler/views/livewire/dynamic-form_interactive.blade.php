@php
    $backgroundOptionsMapping = [];
    foreach ($formFields ?? [] as $fieldConfig) {
        $opts = $fieldConfig['options'] ?? [];
        if (
            ($fieldConfig['type'] ?? null) === \Appsolutely\AIO\Enums\FormFieldType::Hidden &&
            is_array($opts) &&
            !empty($opts) &&
            !array_is_list($opts)
        ) {
            $backgroundOptionsMapping = $opts;
            break;
        }
    }
@endphp
<section class="dynamic-form-interactive" data-asset-base-url="{{ asset_url(null, false) }}"
    data-options-mapping="{{ json_encode($backgroundOptionsMapping) }}" data-trigger-field="vehicle_interest"
    wire:ignore.self>
    <!-- Background Container -->
    <div class="dynamic-form-interactive__background" wire:ignore>
        <div class="dynamic-form-interactive__background-image"></div>
        <div class="dynamic-form-interactive__background-overlay"></div>
    </div>

    <!-- Form Container -->
    <div class="dynamic-form-interactive__container">
        <div class="dynamic-form-interactive__wrapper">
            @if (!$submitted)
                <!-- Form Header -->
                @if ($displayOptions['title'] || $displayOptions['subtitle'] || $displayOptions['description'])
                    <div class="dynamic-form-interactive__header">
                        @if ($displayOptions['title'])
                            <h2 class="dynamic-form-interactive__title">{{ $displayOptions['title'] }}</h2>
                        @endif

                        @if ($displayOptions['subtitle'])
                            <h3 class="dynamic-form-interactive__subtitle">{{ $displayOptions['subtitle'] }}</h3>
                        @endif

                        @if ($displayOptions['description'])
                            <p class="dynamic-form-interactive__description">{{ $displayOptions['description'] }}</p>
                        @endif
                    </div>
                @endif

                <!-- Form Content -->
                <div class="dynamic-form-interactive__content">
                    @include('livewire.dynamic-form-content')
                </div>
            @else
                <!-- Success Message -->
                <div class="dynamic-form-interactive__success" x-data="{ countdown: 5, timer: null }" x-init="timer = setInterval(() => { if (--countdown <= 0) { clearInterval(timer);
                        $wire.resetForm(); } }, 1000)"
                    x-effect="if (countdown <= 0) clearInterval(timer)">
                    <div class="dynamic-form-interactive__success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-success" width="48"
                            height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                            <path d="M9 12l2 2l4 -4" />
                        </svg>
                    </div>

                    @if ($displayOptions['success_title'])
                        <h3 class="dynamic-form-interactive__success-title">{{ $displayOptions['success_title'] }}</h3>
                    @endif

                    <p class="dynamic-form-interactive__success-message">{{ $successMessage }}</p>

                    <p class="dynamic-form-interactive__success-redirect">
                        {{ __t('Redirecting in') }} <span x-text="countdown"></span> {{ __t('seconds') }}...
                    </p>

                    <button wire:click="resetForm" class="dynamic-form-interactive__reset-btn btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        {{ __t('Submit Another Request') }}
                    </button>
                </div>
            @endif

            <!-- Error Flash Message -->
            @if (session()->has('error'))
                <div class="dynamic-form-interactive__error alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v4" />
                        <path
                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                        <path d="M12 16h.01" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</section>
