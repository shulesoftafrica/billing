@extends('layouts.app')

@section('title', 'Register Organization')

@push('styles')
<style>
    .page-header {
        background: #0f1e36;
        padding: 1.5rem 1.75rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(79,141,255,0.12);
        border-radius: var(--radius);
    }
    [data-theme="dark"] .page-header {
        background: #010409;
        border-color: var(--border);
    }
    .page-header h1 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.3px;
        margin: 0;
    }
    .page-header p {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.94rem;
        margin: 6px 0 0;
    }
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text);
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
        border-bottom: 2px solid var(--accent-soft);
        letter-spacing: 0.3px;
    }
    .document-row {
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.625rem;
        position: relative;
        transition: border-color 0.2s ease;
    }
    .document-row:hover { border-color: var(--accent); }
    .document-row .btn-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1rem;
        line-height: 1;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-soft);
        cursor: pointer;
        transition: all 0.15s;
    }
    .document-row .btn-remove:hover {
        background: var(--danger);
        border-color: var(--danger);
        color: #fff;
    }
    .empty-docs {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        color: var(--text-soft);
        font-size: 0.88rem;
    }
    .required-star { color: var(--danger); }

    /* Select2 theme overrides */
    .select2-container--bootstrap-5 .select2-selection {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.9rem;
        border-color: var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
        min-height: 42px;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
        padding: 2px 8px;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background: var(--accent-soft);
        border: 1px solid var(--accent);
        border-radius: 6px;
        color: var(--accent);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--accent);
        border-right-color: var(--accent);
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: var(--border);
        border-radius: 10px;
        background: var(--surface);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.88rem;
    }
    .select2-container--bootstrap-5 .select2-results__option {
        color: var(--text);
    }
    .select2-container--bootstrap-5 .select2-results__option--selected {
        background: var(--accent-soft);
        color: var(--accent);
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background: var(--accent);
        color: #fff;
    }
    .alert {
        border-radius: 12px;
        font-size: 0.92rem;
        border: 1px solid;
    }
    .alert-danger {
        background: rgba(239,68,68,0.08);
        border-color: rgba(239,68,68,0.25);
        color: #b91c1c;
    }

    .btn-reset {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-soft);
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.5rem 1.25rem;
        transition: all 0.15s;
    }
    .btn-reset:hover {
        border-color: #b0c2dc;
        color: var(--text);
        background: var(--surface-soft);
    }
    [data-theme="dark"] .alert-danger {
        background: rgba(248, 81, 73, 0.12);
        border-color: rgba(248, 81, 73, 0.3);
        color: #ffa198;
    }
    [data-theme="dark"] .btn-reset {
        background: var(--surface);
        border-color: var(--border);
        color: var(--text-soft);
    }
    [data-theme="dark"] .btn-reset:hover {
        border-color: var(--accent);
        color: var(--text);
        background: var(--surface-soft);
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <div class="page-header mt-4">
                <h1>Register Organization</h1>
                <p>Complete the form below to get started on the billing platform.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('organizations.register.store') }}" method="POST" enctype="multipart/form-data" id="registrationForm">
                @csrf

                {{-- Basic Information --}}
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h6 class="section-title">Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Organization Name <span class="required-star">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Acme Corporation" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="required-star">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="info@acme.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="required-star">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="+255 700 000 000" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="country_id" class="form-label">Country <span class="required-star">*</span></label>
                                <select class="form-select @error('country_id') is-invalid @enderror"
                                        id="country_id" name="country_id" required>
                                    <option value="">Select country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency <span class="required-star">*</span></label>
                                <select class="form-select @error('currency') is-invalid @enderror"
                                        id="currency" name="currency[]" multiple required>
                                    @php
                                        $oldCurrencies = old('currency', []);
                                    @endphp
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->code }}" {{ in_array($currency->code, $oldCurrencies) ? 'selected' : '' }}>{{ $currency->code }} — {{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text">Select one or more currencies.</small>
                                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Details --}}
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h6 class="section-title">Business Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tin_number" class="form-label">TIN Number</label>
                                <input type="text" class="form-control @error('tin_number') is-invalid @enderror"
                                       id="tin_number" name="tin_number" value="{{ old('tin_number') }}"
                                       placeholder="123-456-789">
                                @error('tin_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                       id="registration_number" name="registration_number"
                                       value="{{ old('registration_number') }}"
                                       placeholder="BRN-2026-001234">
                                @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0 border-0 pb-0">Documents</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addDocumentBtn">+ Add Document</button>
                        </div>
                        <p class="form-text mb-3">
                            Upload supporting documents such as business license or certificates. Only <strong>PDF</strong> files, max 10 MB each. <strong class="required-star">At least one document is required.</strong>
                        </p>

                        <div id="documentsContainer"></div>

                        <div id="noDocumentsMsg" class="empty-docs">
                            <p class="mb-1" style="font-weight:500;">No documents added</p>
                            <small>Click "Add Document" to attach at least one PDF file.</small>
                        </div>

                        @error('document_files')
                            <div class="text-danger mt-2" style="font-size:0.85rem;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <button type="reset" class="btn-reset">Reset Form</button>
                    <button type="submit" class="btn btn-primary px-4 py-2">Register Organization</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('documentsContainer');
        const addBtn = document.getElementById('addDocumentBtn');
        const noDocsMsg = document.getElementById('noDocumentsMsg');

        function toggleMsg() {
            noDocsMsg.style.display = container.querySelectorAll('.document-row').length ? 'none' : 'block';
        }

        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.classList.add('document-row');
            row.innerHTML = `
                <button type="button" class="btn-remove" title="Remove" onclick="this.parentElement.remove(); document.getElementById('noDocumentsMsg').style.display = document.querySelectorAll('.document-row').length ? 'none' : 'block';">&times;</button>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Document Name <span class="required-star">*</span></label>
                        <input type="text" class="form-control" name="document_names[]" placeholder="Business License" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">PDF File <span class="required-star">*</span></label>
                        <input type="file" class="form-control" name="document_files[]" accept="application/pdf,.pdf" required>
                    </div>
                </div>
            `;
            container.appendChild(row);
            toggleMsg();
        });

        toggleMsg();

        // Initialize Select2 on currency field
        $('#currency').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select currencies',
            allowClear: true,
            width: '100%'
        });

        // Client-side check: at least one document required
        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            if (container.querySelectorAll('.document-row').length === 0) {
                e.preventDefault();
                alert('Please add and upload at least one document before submitting.');
                addBtn.focus();
            }
        });
    });
</script>
@endpush
