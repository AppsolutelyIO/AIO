<x-form-section submit="createTeam">
    <x-slot name="title">
        {{ __('Team Details') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Create a new team to collaborate with others on projects.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-12">
            <x-label value="{{ __('Team Owner') }}" />

            <div class="d-flex align-items-center mt-2">
                <span class="avatar avatar-md"
                    style="background-image: url('{{ $this->user->profile_photo_url }}')"></span>
                <div class="ms-3">
                    <div class="fw-semibold">{{ $this->user->name }}</div>
                    <div class="text-muted small">{{ $this->user->email }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <x-label for="name" value="{{ __('Team Name') }}" />
            <x-input id="name" type="text" wire:model="state.name" autofocus />
            <x-input-error for="name" class="mt-1" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button>
            {{ __('Create') }}
        </x-button>
    </x-slot>
</x-form-section>
