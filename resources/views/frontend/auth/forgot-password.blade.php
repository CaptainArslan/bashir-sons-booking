@extends('frontend.layouts.app')

@section('title', 'Forgot Password')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-warning text-dark text-center py-4">
                            <h3 class="mb-0">
                                <i class="bi bi-key me-2"></i>Forgot Password
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">
                                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                            </p>

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">
                                        <i class="bi bi-envelope me-2"></i>Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus
                                           placeholder="Enter your email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" class="btn btn-warning btn-lg text-dark">
                                        <i class="bi bi-send me-2"></i>Email Password Reset Link
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-light text-center py-3">
                            <p class="mb-0">
                                <a href="{{ route('login') }}" class="text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();

            if (!email) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter your email address.'
                });
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a valid email address.'
                });
                return false;
            }
        });
    </script>
@endsection
