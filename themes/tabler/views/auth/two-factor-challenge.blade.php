<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Two-Factor Authentication') }}</h2>

        <div x-data="{ recovery: false }">
            <p class="text-muted mb-4" x-show="! recovery">
                {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
            </p>

            <p class="text-muted mb-4" x-cloak x-show="recovery">
                {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
            </p>

            <x-validation-errors class="mb-3" />

            <form method="POST" action="{{ route('two-factor.login') }}">
                @csrf

                <div class="mb-3" x-show="! recovery">
                    <x-label for="code" value="{{ __('Code') }}" />
                    <x-input id="code" type="text" inputmode="numeric" name="code" autofocus
                        x-ref="code" autocomplete="one-time-code" placeholder="{{ __('Authentication code') }}" />
                </div>

                <div class="mb-3" x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Recovery Code') }}" />
                    <x-input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code"
                        autocomplete="one-time-code" placeholder="{{ __('Recovery code') }}" />
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <button type="button" class="btn btn-ghost-secondary btn-sm" x-show="! recovery"
                        x-on:click="
                            recovery = true;
                            $nextTick(() => { $refs.recovery_code.focus() })
                        ">
                        {{ __('Use a recovery code') }}
                    </button>

                    <button type="button" class="btn btn-ghost-secondary btn-sm" x-cloak x-show="recovery"
                        x-on:click="
                            recovery = false;
                            $nextTick(() => { $refs.code.focus() })
                        ">
                        {{ __('Use an authentication code') }}
                    </button>
                </div>

                <div class="form-footer">
                    <x-button class="w-100">
                        {{ __('Log in') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
