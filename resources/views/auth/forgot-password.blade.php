@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="w-100" style="max-width: 450px;">
        <!-- Header -->
        <div class="text-center mb-4">
            <a href="/" class="text-decoration-none">
                <h1 class="h3 fw-bold text-white">Billing Platform</h1>
            </a>
            <h2 class="h4 fw-bold text-white mt-4">Reset your password</h2>
            <p class="text-white-50">Enter your email to receive a password reset link</p>
        </div>

        <!-- Form Card -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <input
                            type="email"
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="john@example.com"
                        />
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        Send Reset Link
                    </button>

                    <!-- Back to Login -->
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="text-center mt-4">
            <div class="d-flex justify-content-center gap-3 text-white-50 small">
                <a href="/terms" class="text-white-50 text-decoration-none">Terms</a>
                <span>•</span>
                <a href="/privacy" class="text-white-50 text-decoration-none">Privacy</a>
                <span>•</span>
                <a href="/contact" class="text-white-50 text-decoration-none">Contact</a>
            </div>
        </div>
    </div>
</div>
@endsection
