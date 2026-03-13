<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{ photoName: null, photoPreview: null }" class="col-12">
                <input type="file" id="photo" class="d-none" wire:model.live="photo" x-ref="photo"
                    x-on:change="
                        photoName = $refs.photo.files[0].name;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            photoPreview = e.target.result;
                        };
                        reader.readAsDataURL($refs.photo.files[0]);
                    " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <span class="avatar avatar-xl"
                        style="background-image: url('{{ $this->user->profile_photo_url }}')"></span>
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="avatar avatar-xl"
                        x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
                </div>

                <div class="mt-2">
                    <x-secondary-button type="button" x-on:click.prevent="$refs.photo.click()">
                        {{ __('Select A New Photo') }}
                    </x-secondary-button>

                    @if ($this->user->profile_photo_path)
                        <x-secondary-button type="button" class="ms-2" wire:click="deleteProfilePhoto">
                            {{ __('Remove Photo') }}
                        </x-secondary-button>
                    @endif
                </div>

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-12 col-sm-6">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-1" />
        </div>

        <!-- Email -->
        <div class="col-12 col-sm-6">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-1" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
                    !$this->user->hasVerifiedEmail())
                <div class="mt-2">
                    <span class="text-muted small">{{ __('Your email address is unverified.') }}</span>
                    <button type="button" class="btn btn-link btn-sm p-0"
                        wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if ($this->verificationLinkSent)
                        <p class="mt-1 small text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
