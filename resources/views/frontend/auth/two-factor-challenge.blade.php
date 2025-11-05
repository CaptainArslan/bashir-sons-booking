@extends('frontend.layouts.app')

@section('title', 'Two-Factor Authentication Challenge')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-warning text-dark text-center py-4">
                            <h3 class="mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Two-Factor Authentication
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-shield-lock display-4 text-warning mb-3"></i>
                                <h5 class="mb-2">Authentication Required</h5>
                                <p class="text-muted mb-0">
                                    Please enter the 6-digit authentication code from your authenticator app to complete login.
                                </p>
                            </div>

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($user)
                                <div class="alert alert-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong>Logging in as:</strong> {{ $user->name }}
                                            <br>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('2fa.verify') }}" id="challengeForm">
                                @csrf

                                <div class="mb-4">
                                    <label for="code" class="form-label fw-bold">
                                        <i class="bi bi-keyboard me-2"></i>Authentication Code <span class="text-danger">*</span>
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

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" class="btn btn-warning btn-lg text-dark">
                                        <i class="bi bi-check-circle me-2"></i>Verify Code
                                    </button>
                                </div>

                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none text-muted">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                                    </a>
                                </div>
                            </form>
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

        document.getElementById('challengeForm')?.addEventListener('submit', function(e) {
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
