@extends('layouts.dashboard')

@section('title', 'Settings')

@section('content')
<!-- Page Header -->
<div class="mb-4">
    <h1 class="h2 fw-bold">Settings</h1>
    <p class="text-muted">Manage your account and company preferences.</p>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
            Profile
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button">
            Company
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
            Security
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button">
            Notifications
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="settingsTabContent">
    <!-- Profile Tab -->
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Profile Information</h5>
                
                <form method="POST" action="{{ route('settings.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Avatar -->
                    <div class="mb-4">
                        <label class="form-label">Profile Photo</label>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px;">
                                <span class="fs-3 fw-bold">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm">Change Photo</button>
                                <p class="text-muted small mb-0 mt-1">JPG, PNG or GIF. Max size 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->first_name ?? 'John') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', auth()->user()->last_name ?? 'Doe') }}" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', auth()->user()->email ?? 'john@example.com') }}" required>
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Change Password</h5>
                
                <form method="POST" action="{{ route('settings.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Company Tab -->
    <div class="tab-pane fade" id="company" role="tabpanel">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Company Information</h5>
                
                <form method="POST" action="{{ route('settings.company.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', 'Acme Inc') }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="industry" class="form-label">Industry</label>
                            <select class="form-select" id="industry" name="industry">
                                <option value="">Select industry...</option>
                                <option value="technology">Technology</option>
                                <option value="ecommerce">E-commerce</option>
                                <option value="saas">SaaS</option>
                                <option value="finance">Finance</option>
                                <option value="healthcare">Healthcare</option>
                                <option value="education">Education</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="company_size" class="form-label">Company Size</label>
                            <select class="form-select" id="company_size" name="company_size">
                                <option value="">Select size...</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="500+">500+ employees</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" placeholder="https://">
                    </div>

                    <div class="mb-3">
                        <label for="support_email" class="form-label">Support Email</label>
                        <input type="email" class="form-control" id="support_email" name="support_email">
                    </div>

                    <div class="mb-3">
                        <label for="tax_id" class="form-label">Tax ID / VAT Number</label>
                        <input type="text" class="form-control" id="tax_id" name="tax_id">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Billing Address -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Billing Address</h5>
                
                <form method="POST" action="{{ route('settings.address.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1">
                    </div>

                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        <div class="col-md-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select" id="country" name="country">
                            <option value="">Select country...</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                            <option value="CA">Canada</option>
                            <option value="AU">Australia</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Address</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Tab -->
    <div class="tab-pane fade" id="security" role="tabpanel">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Two-Factor Authentication</h5>
                        <p class="text-muted mb-0">Add an extra layer of security to your account</p>
                    </div>
                    <button class="btn btn-success">Enable 2FA</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Active Sessions</h5>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-laptop me-2"></i>
                            <strong>Chrome on Windows</strong>
                            <p class="text-muted mb-0 small">Current session • San Francisco, CA</p>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Tab -->
    <div class="tab-pane fade" id="notifications" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Email Notifications</h5>
                
                <form method="POST" action="{{ route('settings.notifications.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="payment_received" name="payment_received" checked>
                        <label class="form-check-label" for="payment_received">
                            <strong>Payment Received</strong>
                            <p class="text-muted small mb-0">Get notified when a payment is successful</p>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="subscription_created" name="subscription_created" checked>
                        <label class="form-check-label" for="subscription_created">
                            <strong>New Subscription</strong>
                            <p class="text-muted small mb-0">Get notified when a new subscription is created</p>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="invoice_paid" name="invoice_paid" checked>
                        <label class="form-check-label" for="invoice_paid">
                            <strong>Invoice Paid</strong>
                            <p class="text-muted small mb-0">Get notified when an invoice is paid</p>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="weekly_summary" name="weekly_summary">
                        <label class="form-check-label" for="weekly_summary">
                            <strong>Weekly Summary</strong>
                            <p class="text-muted small mb-0">Receive a weekly summary of your activity</p>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
