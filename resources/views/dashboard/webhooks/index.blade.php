@extends('layouts.dashboard')

@section('title', 'Webhooks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">Webhooks</h1>
        <p class="text-muted">Configure webhook endpoints</p>
    </div>
    <button class="btn btn-primary">
        <i class="bi bi-globe me-2"></i> Add Webhook
    </button>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Your webhooks will be displayed here.</p>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
