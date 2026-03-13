<x-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('Team Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s name and owner information.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-12">
            <x-label value="{{ __('Team Owner') }}" />

            <div class="d-flex align-items-center mt-2">
                <span class="avatar avatar-md"
                    style="background-image: url('{{ $team->owner->profile_photo_url }}')"></span>
                <div class="ms-3">
                    <div class="fw-semibold">{{ $team->owner->name }}</div>
                    <div class="text-muted small">{{ $team->owner->email }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <x-label for="name" value="{{ __('Team Name') }}" />
            <x-input id="name" type="text" wire:model="state.name" :disabled="!Gate::check('update', $team)" />
            <x-input-error for="name" class="mt-1" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    @endif
</x-form-section>
