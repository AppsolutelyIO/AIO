<section class="dynamic-form py-5">
    <div class="container-xl">
        @if (!$submitted)
            @if (($displayOptions['title'] ?? null) || ($displayOptions['subtitle'] ?? null) || ($displayOptions['description'] ?? null))
                <div class="text-center mb-5">
                    @if ($displayOptions['title'] ?? null)
                        <h2 class="h1 mb-2">{{ $displayOptions['title'] }}</h2>
                    @endif
                    @if ($displayOptions['subtitle'] ?? null)
                        <h3 class="h4 text-muted mb-3">{{ $displayOptions['subtitle'] }}</h3>
                    @endif
                    @if ($displayOptions['description'] ?? null)
                        <p class="text-muted lead">{{ $displayOptions['description'] }}</p>
                    @endif
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            @include('livewire.dynamic-form-content')
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="empty">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-success" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    <path d="M9 12l2 2l4 -4" />
                                </svg>
                            </div>

                            @if ($displayOptions['success_title'] ?? null)
                                <p class="empty-title">{{ $displayOptions['success_title'] }}</p>
                            @endif

                            <p class="empty-subtitle text-muted">{{ $successMessage }}</p>

                            <div class="empty-action">
                                <button wire:click="resetForm" class="btn btn-primary">
                                    {{ __('Submit Another Request') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="row justify-content-center mt-4">
                <div class="col-lg-8 col-xl-6">
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
