@extends('layouts.dashboard')

@section('title', 'Create Subscription')

@section('content')
<div class="mb-4">
    <h1 class="h2 fw-bold">Create Subscription</h1>
    <p class="text-muted">Set up a new subscription for a customer</p>
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
                <label class="form-label">Plan</label>
                <select class="form-select" required>
                    <option value="">Select a plan...</option>
                    <option>Basic Plan - $9.99/month</option>
                    <option>Pro Plan - $29.99/month</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Subscription</button>
                <a href="{{ route('web.subscriptions.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
