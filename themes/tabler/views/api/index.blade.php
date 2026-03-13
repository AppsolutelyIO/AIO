<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('API Tokens') }}
        </h2>
    </x-slot>

    <div class="py-4">
        @livewire('api.api-token-manager')
    </div>
</x-app-layout>
