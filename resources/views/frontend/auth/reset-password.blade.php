@extends('frontend.layouts.app')

@section('title', 'Reset Password')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-info text-white text-center py-4">
                            <h3 class="mb-0">
                                <i class="bi bi-arrow-repeat me-2"></i>Reset Password
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
                                @csrf

                                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">
                                        <i class="bi bi-envelope me-2"></i>Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $request->email) }}" 
                                           required 
                                           autofocus 
                                           autocomplete="username"
                                           placeholder="Enter your email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">
                                        <i class="bi bi-lock me-2"></i>New Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Enter new password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Must be at least 8 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-bold">
                                        <i class="bi bi-lock-fill me-2"></i>Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Confirm new password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" class="btn btn-info btn-lg text-white">
                                        <i class="bi bi-check-circle me-2"></i>Reset Password
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
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;

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

            if (!password || password.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Password must be at least 8 characters long.'
                });
                return false;
            }

            if (password !== passwordConfirmation) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Password and confirmation password do not match.'
                });
                return false;
            }
        });
    </script>
@endsection
