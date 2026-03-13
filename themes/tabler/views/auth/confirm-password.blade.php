<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Confirm Password') }}</h2>

        <p class="text-muted mb-4">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>

        <x-validation-errors class="mb-3" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-3">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password"
                    autofocus placeholder="{{ __('Your password') }}" />
            </div>

            <div class="form-footer">
                <x-button class="w-100">
                    {{ __('Confirm') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
