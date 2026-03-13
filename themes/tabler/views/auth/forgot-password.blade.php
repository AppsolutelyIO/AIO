<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Forgot password') }}</h2>

        <p class="text-muted mb-4">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>

        @session('status')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-3" />

        <form method="POST" action="{{ route('password.email') }}" autocomplete="off">
            @csrf

            <div class="mb-3">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus
                    autocomplete="username" placeholder="{{ __('your@email.com') }}" />
            </div>

            <div class="form-footer">
                <x-button class="w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                        <path d="M3 7l9 6l9 -6" />
                    </svg>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>

        <div class="text-center text-muted mt-3">
            {{ __('Forget it,') }}
            <a href="{{ route('login') }}">{{ __('send me back') }}</a>
            {{ __('to the sign in screen.') }}
        </div>
    </x-authentication-card>
</x-guest-layout>
