<div>
    <!-- Generate API Token -->
    <x-form-section submit="createApiToken">
        <x-slot name="title">
            {{ __('Create API Token') }}
        </x-slot>

        <x-slot name="description">
            {{ __('API tokens allow third-party services to authenticate with our application on your behalf.') }}
        </x-slot>

        <x-slot name="form">
            <div class="col-12 col-sm-6">
                <x-label for="name" value="{{ __('Token Name') }}" />
                <x-input id="name" type="text" wire:model="createApiTokenForm.name" autofocus />
                <x-input-error for="name" class="mt-1" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasPermissions())
                <div class="col-12">
                    <x-label for="permissions" value="{{ __('Permissions') }}" />

                    <div class="row g-2 mt-1">
                        @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                            <div class="col-6 col-md-4">
                                <label class="form-check">
                                    <x-checkbox wire:model="createApiTokenForm.permissions" :value="$permission" />
                                    <span class="form-check-label">{{ $permission }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3" on="created">
                {{ __('Created.') }}
            </x-action-message>

            <x-button>
                {{ __('Create') }}
            </x-button>
        </x-slot>
    </x-form-section>

    @if ($this->user->tokens->isNotEmpty())
        <x-section-border />

        <div class="mt-4">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Manage API Tokens') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('You may delete any of your existing tokens if they are no longer needed.') }}
                </x-slot>

                <x-slot name="content">
                    @foreach ($this->user->tokens->sortBy('name') as $token)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-break">{{ $token->name }}</div>

                            <div class="d-flex align-items-center gap-2 ms-2">
                                @if ($token->last_used_at)
                                    <span class="text-muted small">
                                        {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                    </span>
                                @endif

                                @if (Laravel\Jetstream\Jetstream::hasPermissions())
                                    <button class="btn btn-ghost-secondary btn-sm"
                                        wire:click="manageApiTokenPermissions({{ $token->id }})">
                                        {{ __('Permissions') }}
                                    </button>
                                @endif

                                <button class="btn btn-ghost-danger btn-sm"
                                    wire:click="confirmApiTokenDeletion({{ $token->id }})">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Token Value Modal -->
    <x-dialog-modal wire:model.live="displayingToken">
        <x-slot name="title">
            {{ __('API Token') }}
        </x-slot>

        <x-slot name="content">
            <p>{{ __('Please copy your new API token. For your security, it won\'t be shown again.') }}</p>

            <x-input x-ref="plaintextToken" type="text" readonly :value="$plainTextToken"
                class="mt-2 font-monospace bg-light" autofocus autocomplete="off" autocorrect="off"
                autocapitalize="off" spellcheck="false"
                @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)" />
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('displayingToken', false)" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    <!-- API Token Permissions Modal -->
    <x-dialog-modal wire:model.live="managingApiTokenPermissions">
        <x-slot name="title">
            {{ __('API Token Permissions') }}
        </x-slot>

        <x-slot name="content">
            <div class="row g-2">
                @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                    <div class="col-6 col-md-4">
                        <label class="form-check">
                            <x-checkbox wire:model="updateApiTokenForm.permissions" :value="$permission" />
                            <span class="form-check-label">{{ $permission }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('managingApiTokenPermissions', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-2" wire:click="updateApiToken" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Delete Token Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingApiTokenDeletion">
        <x-slot name="title">
            {{ __('Delete API Token') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you would like to delete this API token?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingApiTokenDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="deleteApiToken" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
