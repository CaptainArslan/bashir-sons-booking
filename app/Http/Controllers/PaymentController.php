<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function show(Request $request, Booking $booking): View
    {
        // Verify booking belongs to authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Check if booking is expired
        if ($booking->status === 'expired' || ($booking->reserved_until && now()->gt($booking->reserved_until))) {
            $booking->update(['status' => 'expired']);

            return view('frontend.payment.expired', compact('booking'));
        }

        // Check if already paid
        if ($booking->payment_status === 'paid' && $booking->status === 'confirmed') {
            return redirect()->route('frontend.bookings.success', $booking)->with('success', 'Booking already confirmed!');
        }

        return view('frontend.payment.index', compact('booking'));
    }

    public function process(Request $request, Booking $booking): RedirectResponse
    {
        // Verify booking belongs to authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Check if booking is expired
        if ($booking->status === 'expired' || ($booking->reserved_until && now()->gt($booking->reserved_until))) {
            $booking->update(['status' => 'expired']);

            return redirect()->route('frontend.bookings.payment', $booking)
                ->with('error', 'Booking has expired. Please create a new booking.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:easypaisa,jazzcash',
            'transaction_id' => 'required|string|max:100',
        ]);

        try {
            // Process payment (mock implementation - integrate with actual payment gateway)
            $paymentMethod = $validated['payment_method'];
            $transactionId = $validated['transaction_id'];

            // Verify payment with gateway (placeholder - implement actual gateway integration)
            $paymentVerified = $this->verifyPayment($paymentMethod, $transactionId, $booking->final_amount);

            if ($paymentVerified) {
                // Confirm payment
                $this->bookingService->confirmPayment($booking, $paymentMethod, $booking->final_amount);

                // Update booking with transaction ID
                $booking->update([
                    'online_transaction_id' => $transactionId,
                ]);

                return redirect()->route('frontend.bookings.success', $booking)
                    ->with('success', 'Payment successful! Your booking has been confirmed.');
            } else {
                return redirect()->route('frontend.bookings.payment', $booking)
                    ->with('error', 'Payment verification failed. Please try again or contact support.')
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Payment processing error: '.$e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('frontend.bookings.payment', $booking)
                ->with('error', 'An error occurred while processing your payment. Please try again.')
                ->withInput();
        }
    }

    public function success(Booking $booking): View
    {
        // Verify booking belongs to authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $booking->load(['trip.route', 'trip.bus', 'fromStop.terminal', 'toStop.terminal', 'seats', 'passengers']);

        return view('frontend.payment.success', compact('booking'));
    }

    /**
     * Verify payment with gateway (placeholder - implement actual gateway integration)
     */
    private function verifyPayment(string $gateway, string $transactionId, float $amount): bool
    {
        // TODO: Implement actual payment gateway verification
        // For now, accept any transaction ID (for testing)
        // In production, verify with Easypaisa/JazzCash APIs

        Log::info('Payment verification requested', [
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        // Mock verification - accept if transaction ID is provided
        return ! empty($transactionId);
    }
}
