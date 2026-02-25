@extends('layouts.dashboard')

@section('title', 'Create Invoice')

@section('content')
<div class="mb-4">
    <h1 class="h2 fw-bold">Create Invoice</h1>
    <p class="text-muted">Generate a new invoice</p>
</div>

<div class="card">
    <div class="card-body">
        <form>
            <div class="mb-3">
                <label class="form-label">Customer</label>
                <select class="form-select" required>
                    <option value="">Select a customer...</option>
                    <option>John Doe</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Invoice</button>
                <a href="{{ route('web.invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
