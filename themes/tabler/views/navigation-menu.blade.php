<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        {{-- Mobile toggle --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <x-application-mark style="height: 2rem;" />
        </a>

        {{-- Right side nav items --}}
        @if (Auth::user())
            <div class="navbar-nav flex-row order-md-last">
                {{-- Teams Dropdown --}}
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="nav-item dropdown me-3 d-none d-md-flex">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                            aria-label="Team switcher">
                            <span class="avatar avatar-sm bg-blue-lt">
                                {{ strtoupper(substr(Auth::user()->currentTeam->name ?? 'T', 0, 1)) }}
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div class="small fw-semibold">{{ Auth::user()->currentTeam->name ?? __('No Team') }}</div>
                                <div class="mt-1 small text-muted">{{ __('Current Team') }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <div class="dropdown-header">{{ __('Manage Team') }}</div>

                            @if (Auth::user()->currentTeam)
                                <a class="dropdown-item"
                                    href="{{ route('teams.show', Auth::user()->currentTeam->reference) }}">
                                    {{ __('Team Settings') }}
                                </a>
                            @endif

                            @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                <a class="dropdown-item" href="{{ route('teams.create') }}">
                                    {{ __('Create New Team') }}
                                </a>
                            @endcan

                            @if (Auth::user()->allTeams()->count() > 1)
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-header">{{ __('Switch Teams') }}</div>

                                @foreach (Auth::user()->allTeams() as $team)
                                    <x-switchable-team :team="$team" />
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                {{-- User Dropdown --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                        aria-label="Open user menu">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <span class="avatar avatar-sm"
                                style="background-image: url('{{ Auth::user()->profile_photo_url }}')"></span>
                        @else
                            <span class="avatar avatar-sm bg-primary-lt">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <div class="d-none d-xl-block ps-2">
                            <div class="small fw-semibold">{{ Auth::user()->name }}</div>
                            <div class="mt-1 small text-muted">{{ Auth::user()->email }}</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <div class="dropdown-header">{{ __('Manage Account') }}</div>

                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24"
                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            </svg>
                            {{ __('Profile') }}
                        </a>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-3.602 -3.602a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" />
                                    <path d="M4.172 19.828a2.877 2.877 0 0 0 4.069 0l2.643 -2.643a2.877 2.877 0 0 0 0 -4.069l-3.602 -3.602a2.877 2.877 0 0 0 -4.069 0l-2.643 2.643a2.877 2.877 0 0 0 0 4.069l3.602 3.602z" />
                                </svg>
                                {{ __('API Tokens') }}
                            </a>
                        @endif

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                @click.prevent="$root.submit();">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                    <path d="M9 12h12l-3 -3" />
                                    <path d="M18 15l3 -3" />
                                </svg>
                                {{ __('Log Out') }}
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main navigation --}}
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ __('Dashboard') }}</span>
                        </x-nav-link>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Responsive menu (mobile) --}}
        @if (Auth::user())
            <div class="d-md-none">
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-top pt-3 mt-3">
                        <div class="px-3 small text-muted mb-2">{{ __('Manage Team') }}</div>
                        @if (Auth::user()->currentTeam)
                            <x-responsive-nav-link
                                href="{{ route('teams.show', Auth::user()->currentTeam->reference) }}"
                                :active="request()->routeIs('teams.show')">
                                {{ __('Team Settings') }}
                            </x-responsive-nav-link>
                        @endif

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-responsive-nav-link href="{{ route('teams.create') }}"
                                :active="request()->routeIs('teams.create')">
                                {{ __('Create New Team') }}
                            </x-responsive-nav-link>
                        @endcan

                        @if (Auth::user()->allTeams()->count() > 1)
                            <div class="px-3 small text-muted mb-2 mt-3">{{ __('Switch Teams') }}</div>
                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" component="responsive-nav-link" />
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</header>
