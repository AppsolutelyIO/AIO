<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Create Team') }}
        </h2>
    </x-slot>

    <div class="py-4">
        @livewire('teams.create-team-form')
    </div>
</x-app-layout>
