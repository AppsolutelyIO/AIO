<div>
    @if (Gate::check('addTeamMember', $team))
        <x-section-border />

        <div class="mt-4">
            <x-form-section submit="addTeamMember">
                <x-slot name="title">
                    {{ __('Add Team Member') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Add a new team member to your team, allowing them to collaborate with you.') }}
                </x-slot>

                <x-slot name="form">
                    <div class="col-12">
                        <p class="text-muted small">
                            {{ __('Please provide the email address of the person you would like to add to this team.') }}
                        </p>
                    </div>

                    <div class="col-12 col-sm-6">
                        <x-label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" type="email" wire:model="addTeamMemberForm.email" />
                        <x-input-error for="email" class="mt-1" />
                    </div>

                    @if (count($this->roles) > 0)
                        <div class="col-12">
                            <x-label for="role" value="{{ __('Role') }}" />
                            <x-input-error for="role" class="mt-1" />

                            <div class="list-group mt-2">
                                @foreach ($this->roles as $index => $role)
                                    <button type="button"
                                        class="list-group-item list-group-item-action {{ isset($addTeamMemberForm['role']) && $addTeamMemberForm['role'] === $role->key ? 'active' : '' }}"
                                        wire:click="$set('addTeamMemberForm.role', '{{ $role->key }}')">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong class="small">{{ $role->name }}</strong>
                                                <div class="text-muted small mt-1">{{ $role->description }}</div>
                                            </div>
                                            @if (isset($addTeamMemberForm['role']) && $addTeamMemberForm['role'] === $role->key)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-sm text-success ms-auto" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M5 12l5 5l10 -10" />
                                                </svg>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Added.') }}
                    </x-action-message>

                    <x-button>
                        {{ __('Add') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    @endif

    @if ($team->teamInvitations->isNotEmpty() && Gate::check('addTeamMember', $team))
        <x-section-border />

        <div class="mt-4">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Pending Team Invitations') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.') }}
                </x-slot>

                <x-slot name="content">
                    @foreach ($team->teamInvitations as $invitation)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-muted">{{ $invitation->email }}</span>

                            @if (Gate::check('removeTeamMember', $team))
                                <button class="btn btn-ghost-danger btn-sm"
                                    wire:click="cancelTeamInvitation({{ $invitation->id }})">
                                    {{ __('Cancel') }}
                                </button>
                            @endif
                        </div>
                    @endforeach
                </x-slot>
            </x-action-section>
        </div>
    @endif

    @if ($team->users->isNotEmpty())
        <x-section-border />

        <div class="mt-4">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Team Members') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('All of the people that are part of this team.') }}
                </x-slot>

                <x-slot name="content">
                    @foreach ($team->users->sortBy('name') as $user)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm me-3"
                                    style="background-image: url('{{ $user->profile_photo_url }}')"></span>
                                <span>{{ $user->name }}</span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                @if (Gate::check('updateTeamMember', $team) && Laravel\Jetstream\Jetstream::hasRoles())
                                    <button class="btn btn-ghost-secondary btn-sm"
                                        wire:click="manageRole('{{ $user->id }}')">
                                        {{ Laravel\Jetstream\Jetstream::findRole($user->membership->role)->name }}
                                    </button>
                                @elseif (Laravel\Jetstream\Jetstream::hasRoles())
                                    <span class="badge bg-secondary-lt">
                                        {{ Laravel\Jetstream\Jetstream::findRole($user->membership->role)->name }}
                                    </span>
                                @endif

                                @if ($this->user->id === $user->id)
                                    <button class="btn btn-ghost-danger btn-sm"
                                        wire:click="$toggle('confirmingLeavingTeam')">
                                        {{ __('Leave') }}
                                    </button>
                                @elseif (Gate::check('removeTeamMember', $team))
                                    <button class="btn btn-ghost-danger btn-sm"
                                        wire:click="confirmTeamMemberRemoval('{{ $user->id }}')">
                                        {{ __('Remove') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-dialog-modal wire:model.live="currentlyManagingRole">
        <x-slot name="title">
            {{ __('Manage Role') }}
        </x-slot>

        <x-slot name="content">
            <div class="list-group">
                @foreach ($this->roles as $index => $role)
                    <button type="button"
                        class="list-group-item list-group-item-action {{ $currentRole === $role->key ? 'active' : '' }}"
                        wire:click="$set('currentRole', '{{ $role->key }}')">
                        <div class="d-flex align-items-center">
                            <div>
                                <strong class="small">{{ $role->name }}</strong>
                                <div class="text-muted small mt-1">{{ $role->description }}</div>
                            </div>
                            @if ($currentRole === $role->key)
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-success ms-auto"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l5 5l10 -10" />
                                </svg>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="stopManagingRole" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-2" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Leave Team Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingLeavingTeam">
        <x-slot name="title">
            {{ __('Leave Team') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to leave this team?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingLeavingTeam')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Remove Team Member Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingTeamMemberRemoval">
        <x-slot name="title">
            {{ __('Remove Team Member') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to remove this person from the team?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingTeamMemberRemoval')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
