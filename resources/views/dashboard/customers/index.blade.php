@extends('layouts.dashboard')

@section('title', 'Customers')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">Customers</h1>
        <p class="text-muted">Manage your customer database</p>
    </div>
    <a href="{{ route('web.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i> Add Customer
    </a>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" placeholder="Search customers...">
            </div>
            <div class="col-md-4">
                <select class="form-select">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="border-0">Customer</th>
                        <th class="border-0">Email</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Subscriptions</th>
                        <th class="border-0">Total Spent</th>
                        <th class="border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <small>JD</small>
                                </div>
                                <span>John Doe</span>
                            </div>
                        </td>
                        <td>john@example.com</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>2</td>
                        <td>$4,250.00</td>
                        <td>
                            <button class="btn btn-sm btn-link">View</button>
                        </td>
                    </tr>
                    <!-- Add more sample rows as needed -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
