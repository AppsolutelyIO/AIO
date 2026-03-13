@props(['team', 'component' => 'dropdown-link'])

<form method="POST" action="{{ route('current-team.update') }}" x-data>
    @method('PUT')
    @csrf

    <input type="hidden" name="team_id" value="{{ $team->reference }}">

    <x-dynamic-component :component="$component" href="#" x-on:click.prevent="$root.submit();">
        <div class="d-flex align-items-center">
            @if (Auth::user()->isCurrentTeam($team))
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-success me-2" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                    <path d="M9 12l2 2l4 -4" />
                </svg>
            @endif
            <span class="text-truncate">{{ $team->name }}</span>
        </div>
    </x-dynamic-component>
</form>
