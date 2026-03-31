@extends('layouts.app')

@section('title', 'Registration Successful')

@push('styles')
<style>
    .success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(49, 196, 141, 0.15);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 mt-5">
            <div class="card">
                <div class="card-body text-center py-5 px-4">
                    <div class="success-icon mb-4">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#31c48d" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 style="font-weight:700; font-size:1.2rem; margin-bottom:0.5rem;">Registration Successful</h4>
                    <p style="color: var(--text-soft); font-size: 0.88rem;" class="mb-3">
                        @if(session('organization'))
                            <strong style="color: var(--text);">{{ session('organization')->name }}</strong> has been registered and is now active.
                        @else
                            Your organization has been registered successfully.
                        @endif
                    </p>
                    <p style="color: var(--text-soft); font-size: 0.8rem;" class="mb-4">
                        You can now integrate payment gateways and use the billing API.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('organizations.register') }}" class="btn btn-outline-primary">Register Another</a>
                        <a href="{{ route('api.docs') }}" class="btn btn-primary">API Documentation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
