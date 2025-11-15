@extends('frontend.layouts.app')

@section('title', 'Confirm Password')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-warning text-dark text-center py-4">
                            <h3 class="mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Confirm Password
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <p class="text-muted mb-4">
                                This is a secure area of the application. Please confirm your password before continuing.
                            </p>

                            <form method="POST" action="{{ route('password.confirm') }}" id="confirmPasswordForm">
                                @csrf

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">
                                        <i class="bi bi-lock me-2"></i>Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           autofocus
                                           autocomplete="current-password"
                                           placeholder="Enter your password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" class="btn btn-warning btn-lg text-dark">
                                        <i class="bi bi-check-circle me-2"></i>Confirm
                                    </button>
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
        document.getElementById('confirmPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;

            if (!password) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Password is required.'
                });
                return false;
            }
        });
    </script>
@endsection
