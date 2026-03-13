<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div class="py-4">
        @livewire('teams.update-team-name-form', ['team' => $team])

        @livewire('teams.team-member-manager', ['team' => $team])

        @if (Gate::check('delete', $team) && !$team->personal_team)
            <x-section-border />

            <div class="mt-4">
                @livewire('teams.delete-team-form', ['team' => $team])
            </div>
        @endif
    </div>
</x-app-layout>
