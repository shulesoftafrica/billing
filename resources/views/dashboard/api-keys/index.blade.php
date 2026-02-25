@extends('layouts.dashboard')

@section('title', 'API Keys')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">API Keys</h1>
        <p class="text-muted">Manage your API authentication keys</p>
    </div>
    <button class="btn btn-primary">
        <i class="bi bi-key me-2"></i> Create API Key
    </button>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Your API keys will be displayed here.</p>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
