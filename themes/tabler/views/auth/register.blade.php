<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Create new account') }}</h2>

        <x-validation-errors class="mb-3" />

        <form method="POST" action="{{ route('register') }}" autocomplete="off">
            @csrf

            <div class="mb-3">
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus
                    autocomplete="name" placeholder="{{ __('Enter your name') }}" />
            </div>

            <div class="mb-3">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required
                    autocomplete="username" placeholder="{{ __('your@email.com') }}" />
            </div>

            <div class="mb-3">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="{{ __('Your password') }}" />
            </div>

            <div class="mb-3">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" placeholder="{{ __('Confirm your password') }}" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mb-3">
                    <label class="form-check">
                        <x-checkbox name="terms" id="terms" required />
                        <span class="form-check-label">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' =>
                                    '<a target="_blank" href="' .
                                    route('terms.show') .
                                    '">' .
                                    __('Terms of Service') .
                                    '</a>',
                                'privacy_policy' =>
                                    '<a target="_blank" href="' .
                                    route('policy.show') .
                                    '">' .
                                    __('Privacy Policy') .
                                    '</a>',
                            ]) !!}
                        </span>
                    </label>
                </div>
            @endif

            <div class="form-footer">
                <x-button class="w-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                        <path d="M16 19h6" />
                        <path d="M19 16v6" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />
                    </svg>
                    {{ __('Create account') }}
                </x-button>
            </div>
        </form>

        <div class="text-center text-muted mt-3">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" tabindex="-1">{{ __('Sign in') }}</a>
        </div>
    </x-authentication-card>
</x-guest-layout>
