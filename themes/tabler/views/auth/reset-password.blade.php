<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Reset Password') }}</h2>

        <x-validation-errors class="mb-3" />

        <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email', $request->email)" required
                    autofocus autocomplete="username" />
            </div>

            <div class="mb-3">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="{{ __('New password') }}" />
            </div>

            <div class="mb-3">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" placeholder="{{ __('Confirm new password') }}" />
            </div>

            <div class="form-footer">
                <x-button class="w-100">
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
