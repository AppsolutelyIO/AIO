@extends('layouts.public')

@section('content')
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="empty">
                <div class="empty-header">503</div>
                <p class="empty-title">{{ __('Service Unavailable') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ __('We are performing maintenance. Please check back soon.') }}
                </p>
            </div>
        </div>
    </div>
@endsection
