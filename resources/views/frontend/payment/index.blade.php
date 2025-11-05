@extends('frontend.layouts.app')

@section('title', 'Complete Payment')

@section('styles')
    <style>
        .payment-method-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
        }

        .payment-method-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .payment-method-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .payment-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .countdown-timer {
            font-size: 2rem;
            font-weight: 700;
            color: #dc3545;
        }
    </style>
@endsection

@section('content')
    <section class="py-5 bg-light" style="min-height: calc(100vh - 300px);">
        <div class="container py-5">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <!-- Booking Info Banner -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2 fw-bold">
                                <i class="bi bi-receipt-cutoff me-2"></i>Complete Payment
                            </h3>
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div>
                                    <i class="bi bi-ticket-perforated me-2"></i>
                                    <strong>Booking #{{ $booking->booking_number }}</strong>
                                </div>
                                <div>
                                    <i class="bi bi-currency-exchange me-2"></i>
                                    <strong>{{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="countdown-timer" id="countdown">
                                <i class="bi bi-clock me-2"></i>
                                <span id="timer">15:00</span>
                            </div>
                            <small class="opacity-75">Time remaining to complete payment</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 mb-4">
                        <div class="card-header bg-white border-0">
                            <h4 class="mb-0 fw-bold">
                                <i class="bi bi-credit-card me-2 text-primary"></i>Select Payment Method
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="paymentForm" action="{{ route('frontend.bookings.payment.process', $booking) }}" method="POST">
                                @csrf
                                
                                <div class="row g-4 mb-4">
                                    <!-- Easypaisa -->
                                    <div class="col-md-6">
                                        <div class="payment-method-card card h-100" data-method="easypaisa">
                                            <div class="card-body text-center p-4">
                                                <div class="payment-icon">📱</div>
                                                <h5 class="fw-bold mb-2">Easypaisa</h5>
                                                <p class="text-muted small mb-3">Pay via Easypaisa mobile wallet or account</p>
                                                <input type="radio" name="payment_method" value="easypaisa" id="easypaisa" class="d-none" required>
                                                <label for="easypaisa" class="btn btn-primary w-100">
                                                    <i class="bi bi-check-circle me-2"></i>Select Easypaisa
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- JazzCash -->
                                    <div class="col-md-6">
                                        <div class="payment-method-card card h-100" data-method="jazzcash">
                                            <div class="card-body text-center p-4">
                                                <div class="payment-icon">💳</div>
                                                <h5 class="fw-bold mb-2">JazzCash</h5>
                                                <p class="text-muted small mb-3">Pay via JazzCash mobile wallet or account</p>
                                                <input type="radio" name="payment_method" value="jazzcash" id="jazzcash" class="d-none" required>
                                                <label for="jazzcash" class="btn btn-primary w-100">
                                                    <i class="bi bi-check-circle me-2"></i>Select JazzCash
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction ID Input -->
                                <div class="card bg-light border-0" id="transactionInput" style="display: none;">
                                    <div class="card-body p-4">
                                        <h5 class="mb-3">
                                            <i class="bi bi-receipt me-2"></i>Enter Transaction Details
                                        </h5>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Transaction ID <span class="text-danger">*</span>
                                                <small class="text-muted d-block">Enter the transaction ID received from your payment</small>
                                            </label>
                                            <input type="text" 
                                                class="form-control form-control-lg" 
                                                name="transaction_id" 
                                                id="transaction_id"
                                                placeholder="e.g., EP123456789 or JC987654321"
                                                value="{{ old('transaction_id') }}"
                                                required>
                                        </div>
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Note:</strong> Please make sure you have completed the payment on your Easypaisa/JazzCash app before entering the transaction ID.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-lg btn-success fw-bold" id="submitPaymentBtn" disabled>
                                        <i class="bi bi-check-circle me-2"></i>Confirm Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-question-circle me-2 text-primary"></i>Payment Instructions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="easypaisa-instructions" class="payment-instructions" style="display: none;">
                                <h6 class="fw-bold mb-3">How to pay via Easypaisa:</h6>
                                <ol class="mb-0">
                                    <li>Open your Easypaisa mobile app or dial *786#</li>
                                    <li>Select "Send Money" or "Mobile Account"</li>
                                    <li>Enter the merchant number: <strong>XXXX-XXXXXXX</strong></li>
                                    <li>Enter amount: <strong>{{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}</strong></li>
                                    <li>Complete the transaction</li>
                                    <li>Copy the transaction ID and paste it above</li>
                                </ol>
                            </div>
                            <div id="jazzcash-instructions" class="payment-instructions" style="display: none;">
                                <h6 class="fw-bold mb-3">How to pay via JazzCash:</h6>
                                <ol class="mb-0">
                                    <li>Open your JazzCash mobile app or dial *786#</li>
                                    <li>Select "Send Money" or "Mobile Account"</li>
                                    <li>Enter the merchant number: <strong>XXXX-XXXXXXX</strong></li>
                                    <li>Enter amount: <strong>{{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}</strong></li>
                                    <li>Complete the transaction</li>
                                    <li>Copy the transaction ID and paste it above</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-lg border-0" style="position: sticky; top: 100px;">
                        <div class="card-header bg-primary text-white border-0">
                            <h4 class="mb-0 fw-bold">
                                <i class="bi bi-receipt-cutoff me-2"></i>Booking Summary
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-muted">Booking Number:</span>
                                    <strong class="text-dark">{{ $booking->booking_number }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-muted">Seats:</span>
                                    <strong class="text-dark">{{ $booking->total_passengers }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-muted">Subtotal:</span>
                                    <strong class="text-dark">{{ $booking->currency }} {{ number_format($booking->total_fare, 2) }}</strong>
                                </div>
                                @if($booking->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-muted">Discount:</span>
                                    <strong class="text-success">-{{ $booking->currency }} {{ number_format($booking->discount_amount, 2) }}</strong>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="text-muted">Tax:</span>
                                    <strong class="text-dark">{{ $booking->currency }} {{ number_format($booking->tax_amount, 2) }}</strong>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <h4 class="mb-0 fw-bold">Total:</h4>
                                    <h4 class="mb-0 fw-bold text-primary">
                                        {{ $booking->currency }} {{ number_format($booking->final_amount, 2) }}
                                    </h4>
                                </div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <small><strong>Important:</strong> Complete payment within 15 minutes or your booking will expire.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const reservedUntil = '{{ $booking->reserved_until?->format('Y-m-d H:i:s') }}';
            let countdownInterval;

            // Initialize countdown timer
            if (reservedUntil) {
                startCountdown(reservedUntil);
            }

            // Restore old payment method selection if validation failed
            @if(old('payment_method'))
                const oldMethod = '{{ old('payment_method') }}';
                $(`#${oldMethod}`).prop('checked', true);
                $(`.payment-method-card[data-method="${oldMethod}"]`).addClass('selected');
                $('#transactionInput').show();
                $('#submitPaymentBtn').prop('disabled', false);
                $('.payment-instructions').hide();
                $(`#${oldMethod}-instructions`).show();
            @endif

            // Payment method selection
            $('.payment-method-card').on('click', function() {
                $('.payment-method-card').removeClass('selected');
                $(this).addClass('selected');
                
                const method = $(this).data('method');
                $(`#${method}`).prop('checked', true);
                
                // Show transaction input
                $('#transactionInput').slideDown();
                $('#submitPaymentBtn').prop('disabled', false);
                
                // Show instructions
                $('.payment-instructions').hide();
                $(`#${method}-instructions`).show();
            });

            // Manual radio button selection
            $('input[name="payment_method"]').on('change', function() {
                const method = $(this).val();
                $('.payment-method-card').removeClass('selected');
                $(`.payment-method-card[data-method="${method}"]`).addClass('selected');
                
                $('#transactionInput').slideDown();
                $('#submitPaymentBtn').prop('disabled', false);
                
                $('.payment-instructions').hide();
                $(`#${method}-instructions`).show();
            });

            // Form submission
            $('#paymentForm').on('submit', function(e) {
                const transactionId = $('#transaction_id').val().trim();
                if (!transactionId) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Transaction ID Required',
                        text: 'Please enter the transaction ID from your payment.',
                        confirmButtonColor: '#ffc107'
                    });
                    return false;
                }

                $('#submitPaymentBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Processing...');
            });

            function startCountdown(reservedUntil) {
                const endTime = new Date(reservedUntil).getTime();
                
                countdownInterval = setInterval(function() {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    
                    if (distance < 0) {
                        clearInterval(countdownInterval);
                        $('#timer').text('00:00');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Booking Expired',
                            text: 'Your booking has expired. Please create a new booking.',
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            window.location.href = '{{ route('home') }}';
                        });
                        return;
                    }
                    
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    $('#timer').text(
                        String(minutes).padStart(2, '0') + ':' + 
                        String(seconds).padStart(2, '0')
                    );
                    
                    // Already styled with text-danger in CSS
                }, 1000);
            }
        });
    </script>
@endsection

