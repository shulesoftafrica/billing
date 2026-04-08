<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Organization – Safari API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2563EB; --navy: #0A1628; }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; display: flex; }

        .auth-left {
            width: 390px;
            flex-shrink: 0;
            background: var(--navy);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.5rem;
        }
        .auth-left .brand { font-size: 1.2rem; font-weight: 700; color: #fff; text-decoration: none; }
        .auth-left .brand i { color: #60a5fa; }
        .auth-left .tagline { font-size: 1.55rem; line-height: 1.35; font-weight: 700; margin-top: 2.8rem; }
        .auth-left .tagline span { color: #60a5fa; }
        .auth-left .sub { margin-top: .8rem; font-size: .86rem; color: rgba(255,255,255,.55); line-height: 1.6; }
        .auth-left .feature-list { list-style: none; padding: 0; margin: 1.5rem 0 0; }
        .auth-left .feature-list li {
            font-size: .82rem;
            color: rgba(255,255,255,.62);
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            margin-bottom: .55rem;
        }
        .auth-left .feature-list i { color: #34d399; margin-top: .1rem; }
        .auth-left .copyright { font-size: .73rem; color: rgba(255,255,255,.3); }

        .auth-right {
            flex: 1;
            background: #f8fafc;
            padding: 2rem 1.25rem;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .auth-card {
            width: 100%;
            max-width: 760px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
            padding: 1.9rem 2rem;
        }

        .auth-card h4 { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .35rem; }
        .auth-card p.lead { font-size: .86rem; color: #64748b; margin-bottom: 1.2rem; }

        .section-divider {
            font-size: .72rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin: 1.25rem 0 .75rem;
        }
        .form-label { font-size: .83rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
        .form-control, .form-select {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .84rem;
            border-color: #e2e8f0;
            border-radius: 8px;
            padding: .55rem .85rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .form-text { font-size: .74rem; color: #94a3b8; }

        .document-row {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 10px;
            padding: .9rem 1rem;
            margin-bottom: .65rem;
        }
        .document-row .doc-label { font-size: .84rem; font-weight: 600; color: #1e293b; margin-bottom: .2rem; }
        .document-row .doc-desc { font-size: .76rem; color: #64748b; margin-bottom: .5rem; }
        .required-star { color: #ef4444; }

        .notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: 8px;
            padding: .7rem .9rem;
            font-size: .78rem;
            margin-bottom: .9rem;
        }

        .btn-primary-full {
            width: 100%;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            padding: .66rem 1rem;
            font-size: .9rem;
            font-weight: 600;
            transition: background .15s;
        }
        .btn-primary-full:hover { background: #1d4ed8; }

        @media (max-width: 991.98px) {
            .auth-left { display: none; }
            .auth-right { padding: 1rem; }
            .auth-card { padding: 1.25rem; }
        }
    </style>
</head>
<body>

<div class="auth-left">
    <div>
        <a href="{{ route('landing') }}" class="brand">
            <i class="bi bi-lightning-charge-fill"></i> Safari API
        </a>
        <div class="tagline">Register your<br><span>organization</span><br>in minutes.</div>
        <div class="sub">Create your organization profile and submit required documents for verification before activating live payment integrations.</div>

        <ul class="feature-list">
            <li><i class="bi bi-check-circle-fill"></i> Multi-currency support</li>
            <li><i class="bi bi-check-circle-fill"></i> Developer-ready API stack</li>
            <li><i class="bi bi-check-circle-fill"></i> Built-in webhooks & logs</li>
            <li><i class="bi bi-check-circle-fill"></i> Secure KYC document workflow</li>
        </ul>
    </div>
    <div class="copyright">© {{ date('Y') }} ShuleSoft Ltd.</div>
</div>

<div class="auth-right">
    <div class="auth-card">
        <h4>Organization Registration</h4>
        <p class="lead">Complete all required fields and upload the four mandatory PDF documents.</p>

        @if(session('error'))
            <div class="mb-3 d-flex align-items-center gap-2 p-2 rounded" style="background:#fef2f2;color:#991b1b;font-size:.82rem;border:1px solid #fecaca;">
                <i class="bi bi-exclamation-circle-fill shrink-0"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-3">
                @foreach($errors->all() as $error)
                    <div class="d-flex align-items-center gap-2 p-2 mb-1 rounded" style="background:#fef2f2;color:#991b1b;font-size:.82rem;border:1px solid #fecaca;">
                        <i class="bi bi-exclamation-circle-fill shrink-0"></i> {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('organizations.register.store') }}" method="POST" enctype="multipart/form-data" id="registrationForm">
            @csrf

            <div class="section-divider">Basic Information</div>
            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Organization Name <span class="required-star">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" placeholder="Acme Corporation" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="required-star">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" placeholder="info@acme.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone <span class="required-star">*</span></label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                           id="phone" name="phone" value="{{ old('phone') }}" placeholder="+255 700 000 000" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="country_id" class="form-label">Country <span class="required-star">*</span></label>
                    <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id" required>
                        <option value="">Select country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="section-divider">Business Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="tin_number" class="form-label">TIN Number</label>
                    <input type="text" class="form-control @error('tin_number') is-invalid @enderror"
                           id="tin_number" name="tin_number" value="{{ old('tin_number') }}" placeholder="123-456-789">
                    @error('tin_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="registration_number" class="form-label">Registration Number</label>
                    <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                           id="registration_number" name="registration_number" value="{{ old('registration_number') }}" placeholder="BRN-2026-001234">
                    @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="section-divider">Required Documents</div>
            <div class="notice">
                <i class="bi bi-exclamation-triangle me-1"></i>
                All four documents are mandatory. Only PDF files are accepted (maximum 10MB each).
            </div>

            @if($errors->any())
            <div class="d-flex align-items-start gap-2 p-2 mb-3 rounded" style="background:#fffbeb;color:#92400e;font-size:.82rem;border:1px solid #fcd34d;">
                <i class="bi bi-arrow-repeat flex-shrink-0 mt-1"></i>
                <span>Your information above has been kept. Please <strong>re-select your PDF documents</strong> below — browsers do not retain uploaded files after a page reload.</span>
            </div>
            @endif

            <div class="document-row">
                <div class="doc-label">1. TIN Certificate <span class="required-star">*</span></div>
                <div class="doc-desc">Tax Identification Number certificate issued by TRA.</div>
                <input type="hidden" name="document_names[]" value="TIN Certificate">
                <input type="file" class="form-control @error('document_files.0') is-invalid @enderror" name="document_files[]" accept="application/pdf,.pdf" required>
                @error('document_files.0')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    @if($errors->any())<div class="form-text text-warning-emphasis"><i class="bi bi-arrow-repeat me-1"></i>Please re-select this file.</div>@endif
                @enderror
            </div>

            <div class="document-row">
                <div class="doc-label">2. Business License <span class="required-star">*</span></div>
                <div class="doc-desc">Valid business license issued by the relevant authority.</div>
                <input type="hidden" name="document_names[]" value="Business License">
                <input type="file" class="form-control @error('document_files.1') is-invalid @enderror" name="document_files[]" accept="application/pdf,.pdf" required>
                @error('document_files.1')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    @if($errors->any())<div class="form-text text-warning-emphasis"><i class="bi bi-arrow-repeat me-1"></i>Please re-select this file.</div>@endif
                @enderror
            </div>

            <div class="document-row">
                <div class="doc-label">3. Certificate of Incorporation / Registration <span class="required-star">*</span></div>
                <div class="doc-desc">Certificate issued by BRELA.</div>
                <input type="hidden" name="document_names[]" value="Certificate of Incorporation (BRELA)">
                <input type="file" class="form-control @error('document_files.2') is-invalid @enderror" name="document_files[]" accept="application/pdf,.pdf" required>
                @error('document_files.2')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    @if($errors->any())<div class="form-text text-warning-emphasis"><i class="bi bi-arrow-repeat me-1"></i>Please re-select this file.</div>@endif
                @enderror
            </div>

            <div class="document-row">
                <div class="doc-label">4. Lease Agreement <span class="required-star">*</span></div>
                <div class="doc-desc">Agreement proving physical operating location.</div>
                <input type="hidden" name="document_names[]" value="Lease Agreement">
                <input type="file" class="form-control @error('document_files.3') is-invalid @enderror" name="document_files[]" accept="application/pdf,.pdf" required>
                @error('document_files.3')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    @if($errors->any())<div class="form-text text-warning-emphasis"><i class="bi bi-arrow-repeat me-1"></i>Please re-select this file.</div>@endif
                @enderror
            </div>

            @error('document_files')
                <div class="text-danger mt-2" style="font-size:0.82rem;">{{ $message }}</div>
            @enderror

            <div class="section-divider">Account Type</div>
            <div class="mb-3">
                <label for="account_type" class="form-label">I am registering as <span class="required-star">*</span></label>
                <select class="form-select @error('account_type') is-invalid @enderror" id="account_type" name="account_type" required>
                    <option value="" disabled {{ old('account_type') ? '' : 'selected' }}>Select account type</option>
                    <option value="organization" {{ old('account_type') == 'organization' ? 'selected' : '' }}>Organization — Integrate payments for my own organization</option>
                    <option value="developer" {{ old('account_type') == 'developer' ? 'selected' : '' }}>Developer — Build and integrate for other organizations</option>
                </select>
                <div class="form-text" id="accountTypeHint"></div>
                @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn-primary-full mt-2">
                <i class="bi bi-building-add me-1"></i> Register Organization
            </button>
        </form>

        <div class="mt-3 text-center" style="font-size:.82rem;color:#64748b;">
            Already have a developer account?
            <a href="{{ route('dashboard.login') }}" style="color:var(--primary);font-weight:500;">Sign in →</a>
        </div>
        <div class="mt-2 text-center" style="font-size:.8rem;color:#94a3b8;">
            <a href="{{ route('landing') }}" style="color:#64748b;text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i> Back to Landing
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ─── Account type hint ───────────────────────────────────────────────────
    const accountType = document.getElementById('account_type');
    const hint = document.getElementById('accountTypeHint');
    const hints = {
        organization: 'You will integrate payment gateways directly for your organization.',
        developer: 'You will get dashboard access to manage integrations for multiple organizations.'
    };

    function updateHint() {
        hint.textContent = hints[accountType.value] || '';
    }

    accountType.addEventListener('change', updateHint);
    updateHint();
</script>
</body>
</html>
