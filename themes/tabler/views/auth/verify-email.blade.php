<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h2 class="h3 text-center mb-3">{{ __('Verify Email') }}</h2>

        <p class="text-muted mb-4">
            {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success" role="alert">
                {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button type="submit">
                    {{ __('Resend Verification Email') }}
                </x-button>
            </form>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('profile.show') }}" class="btn btn-ghost-secondary btn-sm">
                    {{ __('Edit Profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost-danger btn-sm">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
