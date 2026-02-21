@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="w-100" style="max-width: 450px;">
        <!-- Header -->
        <div class="text-center mb-4">
            <a href="/" class="text-decoration-none">
                <h1 class="h3 fw-bold text-white">Billing Platform</h1>
            </a>
            <h2 class="h4 fw-bold text-white mt-4">Welcome back</h2>
            <p class="text-white-50">Sign in to your account to continue</p>
        </div>

        <!-- Form Card -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Error!</strong> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
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

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="form-label fw-medium mb-0">Password</label>
                            <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                Forgot password?
                            </a>
                        </div>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required
                                placeholder="Enter your password"
                            />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            />
                            <label class="form-check-label" for="remember">
                                Remember me for 30 days
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        Sign In
                    </button>

                    <!-- Divider -->
                    <div class="text-center my-4">
                        <span class="text-muted small">or continue with</span>
                    </div>

                    <!-- Social Login -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-google me-2"></i> Continue with Google
                        </button>
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-github me-2"></i> Continue with GitHub
                        </button>
                    </div>
                </form>

                <!-- Sign Up Link -->
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-decoration-none fw-medium">
                            Sign up for free
                        </a>
                    </p>
                </div>
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

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>
@endpush
@endsection
