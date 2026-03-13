<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Login to your account') }}</h2>

        <x-validation-errors class="mb-3" />

        @session('status')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf

            <div class="mb-3">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus
                    autocomplete="username" placeholder="{{ __('your@email.com') }}" />
            </div>

            <div class="mb-3">
                <x-label for="password" class="d-flex justify-content-between">
                    {{ __('Password') }}
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </x-label>
                <div class="input-group input-group-flat">
                    <x-input id="password" type="password" name="password" required
                        autocomplete="current-password" placeholder="{{ __('Your password') }}" />
                </div>
            </div>

            <div class="mb-3">
                <label class="form-check">
                    <x-checkbox name="remember" id="remember_me" />
                    <span class="form-check-label">{{ __('Remember me on this device') }}</span>
                </label>
            </div>

            <div class="form-footer">
                <x-button class="w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                        <path d="M20 12h-13l3 -3m0 6l-3 -3" />
                    </svg>
                    {{ __('Sign in') }}
                </x-button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-muted mt-3">
                {{ __("Don't have an account yet?") }}
                <a href="{{ route('register') }}" tabindex="-1">{{ __('Sign up') }}</a>
            </div>
        @endif
    </x-authentication-card>
</x-guest-layout>
