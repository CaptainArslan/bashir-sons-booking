@extends('frontend.layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-primary text-white text-center py-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Profile
                                </a>
                                <h3 class="mb-0">
                                    <i class="bi bi-shield-check me-2"></i>Two-Factor Authentication
                                </h3>
                                <div style="width: 100px;"></div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (isset($enabled) && $enabled)
                                {{-- 2FA Already Enabled --}}
                                <div class="text-center mb-4">
                                    <div class="mb-4">
                                        <i class="bi bi-shield-check display-1 text-success"></i>
                                    </div>
                                    <h4 class="text-success mb-3">Two-Factor Authentication is Enabled</h4>
                                    <p class="text-muted mb-4">
                                        Your account is protected with an additional security layer. When you log in, you'll be asked to enter a code from your authenticator app.
                                    </p>
                                    <form method="POST" action="{{ route('2fa.disable') }}" class="d-inline" id="disable2faForm">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to disable Two-Factor Authentication? This will make your account less secure.')">
                                            <i class="bi bi-x-circle me-2"></i>Disable Two-Factor Authentication
                                        </button>
                                    </form>
                                </div>
                            @else
                                {{-- Enable 2FA Setup --}}
                                <div class="text-center mb-4">
                                    <h4 class="mb-3">Set Up Two-Factor Authentication</h4>
                                    <p class="text-muted">
                                        Scan the QR code below with your authenticator app (like Google Authenticator, Authy, or Microsoft Authenticator) and then enter the 6-digit code to enable 2FA.
                                    </p>
                                </div>

                                {{-- QR Code --}}
                                <div class="text-center mb-4">
                                    <div class="card bg-white border p-4 d-inline-block">
                                        {!! $QR_Image !!}
                                    </div>
                                </div>

                                {{-- Secret Key --}}
                                <div class="alert alert-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-key me-2" style="font-size: 1.5rem;"></i>
                                        <div class="flex-grow-1">
                                            <strong>Secret Key:</strong>
                                            <p class="mb-0 mt-1">
                                                <code class="bg-light p-2 rounded d-inline-block">{{ $secret }}</code>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copySecret()">
                                                    <i class="bi bi-clipboard me-1"></i>Copy
                                                </button>
                                            </p>
                                            <small class="text-muted">Use this key if you can't scan the QR code</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Instructions --}}
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">
                                            <i class="bi bi-info-circle me-2"></i>Instructions:
                                        </h6>
                                        <ol class="text-start mb-0">
                                            <li>Install an authenticator app on your phone (Google Authenticator, Authy, Microsoft Authenticator)</li>
                                            <li>Open the app and tap "Add account" or the "+" button</li>
                                            <li>Scan the QR code above or enter the secret key manually</li>
                                            <li>Enter the 6-digit code from your app below to verify and enable 2FA</li>
                                        </ol>
                                    </div>
                                </div>

                                {{-- Verification Form --}}
                                <form method="POST" action="{{ route('2fa.enable') }}" id="enable2faForm">
                                    @csrf
                                    <input type="hidden" name="secret" value="{{ $secret }}">

                                    <div class="mb-4">
                                        <label for="code" class="form-label fw-bold">
                                            <i class="bi bi-keyboard me-2"></i>Enter 6-Digit Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                               id="code" 
                                               name="code" 
                                               required 
                                               autofocus 
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               placeholder="000000"
                                               style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Enter the 6-digit code from your authenticator app</small>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-shield-check me-2"></i>Enable Two-Factor Authentication
                                        </button>
                                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // Auto-format code input
        document.getElementById('code')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });

        function copySecret() {
            const secret = '{{ $secret ?? "" }}';
            navigator.clipboard.writeText(secret).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Secret key copied to clipboard',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        document.getElementById('enable2faForm')?.addEventListener('submit', function(e) {
            const code = document.getElementById('code').value.trim();

            if (!code || code.length !== 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a valid 6-digit code from your authenticator app.'
                });
                return false;
            }

            if (!/^\d{6}$/.test(code)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Code must contain exactly 6 digits.'
                });
                return false;
            }
        });
    </script>
@endsection
