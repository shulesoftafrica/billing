@extends('layouts.dashboard')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">Invoices</h1>
        <p class="text-muted">Manage customer invoices</p>
    </div>
    <a href="{{ route('web.invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-file-text me-2"></i> Create Invoice
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Invoices will be displayed here.</p>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
