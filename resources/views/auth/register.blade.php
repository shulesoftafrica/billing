@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="w-100" style="max-width: 500px;">
        <!-- Header -->
        <div class="text-center mb-4">
            <a href="/" class="text-decoration-none">
                <h1 class="h3 fw-bold text-white">Billing Platform</h1>
            </a>
            <h2 class="h4 fw-bold text-white mt-4">Create your account</h2>
            <p class="text-white-50">Start accepting payments in minutes</p>
        </div>

        <!-- Form Card -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Error!</strong> Please fix the errors below.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Full Name</label>
                        <input
                            type="text"
                            class="form-control form-control-lg @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            placeholder="John Doe"
                        />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
                            placeholder="john@example.com"
                        />
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Organization Name -->
                    <div class="mb-3">
                        <label for="organization" class="form-label fw-medium">Organization Name</label>
                        <input
                            type="text"
                            class="form-control form-control-lg @error('organization') is-invalid @enderror"
                            id="organization"
                            name="organization"
                            value="{{ old('organization') }}"
                            placeholder="Acme Inc"
                        />
                        @error('organization')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required
                                placeholder="At least 8 characters"
                            />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                <i class="bi bi-eye" id="toggleIconPassword"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Must be at least 8 characters</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <input
                                type="password"
                                class="form-control form-control-lg"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                placeholder="Confirm your password"
                            />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                <i class="bi bi-eye" id="toggleIconConfirm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                class="form-check-input @error('terms') is-invalid @enderror"
                                type="checkbox"
                                name="terms"
                                id="terms"
                                required
                            />
                            <label class="form-check-label small" for="terms">
                                I agree to the <a href="/terms" target="_blank">Terms of Service</a> 
                                and <a href="/privacy" target="_blank">Privacy Policy</a>
                            </label>
                            @error('terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        Create Account
                    </button>

                    <!-- Divider -->
                    <div class="text-center my-4">
                        <span class="text-muted small">or sign up with</span>
                    </div>

                    <!-- Social Sign Up -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-google me-2"></i> Continue with Google
                        </button>
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-github me-2"></i> Continue with GitHub
                        </button>
                    </div>
                </form>

                <!-- Sign In Link -->
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-decoration-none fw-medium">
                            Sign in
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
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById('toggleIcon' + (fieldId === 'password' ? 'Password' : 'Confirm'));
    
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
