@extends('layouts.dashboard')

@section('title', 'Subscriptions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">Subscriptions</h1>
        <p class="text-muted">Manage customer subscriptions</p>
    </div>
    <a href="{{ route('web.subscriptions.create') }}" class="btn btn-primary">
        <i class="bi bi-arrow-repeat me-2"></i> Create Subscription
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Subscriptions will be displayed here.</p>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
