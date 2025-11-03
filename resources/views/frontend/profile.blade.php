@extends('frontend.layouts.app')

@section('title', 'Profile')

@section('content')
    <section class="py-5" style="background: #f8f9fa; min-height: calc(100vh - 300px);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Profile Information Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="bi bi-person-circle me-2"></i>Profile Information
                                </h4>
                                <a href="{{ route('profile.bookings') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-ticket-perforated me-2"></i>My Bookings
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if (session('status') === 'profile-updated')
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Profile updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="post" action="{{ route('profile.update') }}" id="profileForm">
                                @csrf
                                @method('patch')

                                <div class="row">
                                    <!-- Basic Information -->
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $user->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                            <div class="mt-2">
                                                <p class="text-warning mb-2 small">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>Your email address is unverified.
                                                </p>
                                                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        Resend verification email
                                                    </button>
                                                </form>

                                                @if (session('status') === 'verification-link-sent')
                                                    <p class="text-success mt-2 small">
                                                        <i class="bi bi-check-circle me-2"></i>A new verification link has been sent to your email address.
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Additional Profile Information -->
                                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Additional Information</h5>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                                        <input type="text" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $user->profile?->phone) }}" 
                                               placeholder="0317-7777777">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Format: XXXX-XXXXXXX</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="cnic" class="form-label fw-bold">CNIC</label>
                                        <input type="text" 
                                               class="form-control @error('cnic') is-invalid @enderror" 
                                               id="cnic" 
                                               name="cnic" 
                                               value="{{ old('cnic', $user->profile?->cnic) }}" 
                                               placeholder="34101-1111111-1">
                                        @error('cnic')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Format: XXXXX-XXXXXXX-X</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label fw-bold">Gender</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" 
                                                id="gender" 
                                                name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', $user->profile?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $user->profile?->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                        <input type="date" 
                                               class="form-control @error('date_of_birth') is-invalid @enderror" 
                                               id="date_of_birth" 
                                               name="date_of_birth" 
                                               value="{{ old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d')) }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label fw-bold">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Enter your address">{{ old('address', $user->profile?->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Update Password -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-info text-white py-3">
                            <h4 class="mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Update Password
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">
                                Ensure your account is using a long, random password to stay secure.
                            </p>

                            @if (session('status') === 'password-updated')
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Password updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="post" action="{{ route('password.update') }}" id="passwordForm">
                                @csrf
                                @method('put')

                                <div class="mb-3">
                                    <label for="update_password_current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                           id="update_password_current_password" 
                                           name="current_password" 
                                           autocomplete="current-password">
                                    @error('current_password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="update_password_password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                           id="update_password_password" 
                                           name="password" 
                                           autocomplete="new-password">
                                    @error('password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="update_password_password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                           id="update_password_password_confirmation" 
                                           name="password_confirmation" 
                                           autocomplete="new-password">
                                    @error('password_confirmation', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-warning text-dark py-3">
                            <h4 class="mb-0">
                                <i class="bi bi-shield-check me-2"></i>Two-Factor Authentication
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">
                                Add an additional layer of security to your account by enabling two-factor authentication.
                            </p>

                            @if ($twoFactorEnabled)
                                <div class="alert alert-success d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-check-circle me-2"></i>
                                        <strong>Two-Factor Authentication is enabled</strong>
                                        <p class="mb-0 mt-1">Your account is protected with an additional security layer.</p>
                                    </div>
                                    <form method="POST" action="{{ route('2fa.disable') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                            Disable 2FA
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Two-Factor Authentication is disabled</strong>
                                        <p class="mb-0 mt-1">Enable 2FA to add an extra layer of security to your account.</p>
                                    </div>
                                    <a href="{{ route('2fa.show') }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-shield-check me-1"></i>Setup 2FA
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Delete Account -->
                    <div class="card shadow-sm border-0 border-danger">
                        <div class="card-header bg-danger text-white py-3">
                            <h4 class="mb-0">
                                <i class="bi bi-trash me-2"></i>Delete Account
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">
                                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
                            </p>

                            <button type="button" 
                                    class="btn btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteAccountModal">
                                <i class="bi bi-trash me-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAccountModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p class="mb-3">
                            <strong>Are you sure you want to delete your account?</strong>
                        </p>
                        <p class="text-muted mb-3">
                            Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Initialize input masks
        $(document).ready(function() {
            // Phone mask
            $('#phone').inputmask('9999-9999999', {
                placeholder: '_',
                clearMaskOnLostFocus: false,
                showMaskOnHover: true,
                showMaskOnFocus: true
            });

            // CNIC mask
            $('#cnic').inputmask('99999-9999999-9', {
                placeholder: '_',
                clearMaskOnLostFocus: false,
                showMaskOnHover: true,
                showMaskOnFocus: true
            });
        });

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const cnic = document.getElementById('cnic').value.trim();

            if (!name || name.length < 2) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Name must be at least 2 characters long.'
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

            if (cnic && !/^\d{5}-\d{7}-\d{1}$/.test(cnic.replace(/[^\d-]/g, ''))) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'CNIC must be in format: XXXXX-XXXXXXX-X'
                });
                return false;
            }
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('update_password_current_password').value;
            const newPassword = document.getElementById('update_password_password').value;
            const confirmPassword = document.getElementById('update_password_password_confirmation').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'All password fields are required.'
                });
                return false;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'New password must be at least 8 characters long.'
                });
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'New password and confirmation password do not match.'
                });
                return false;
            }
        });
    </script>
@endsection