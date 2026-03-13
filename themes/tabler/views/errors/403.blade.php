@extends('layouts.public')

@section('content')
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="empty">
                <div class="empty-header">403</div>
                <p class="empty-title">{{ __('Access Denied') }}</p>
                <p class="empty-subtitle text-muted">
                    {{ __('Sorry, you do not have permission to access this page.') }}
                </p>
                <div class="empty-action">
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                        {{ __('Take me home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
