<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Mail\EnquiryFormSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function home(): View
    {
        return view('frontend.home');
    }

    public function services(): View
    {
        return view('frontend.services');
    }

    public function bookings(): View
    {
        return view('frontend.bookings');
    }

    public function aboutUs(): View
    {
        return view('frontend.about');
    }

    public function contact(): View
    {
        return view('frontend.contact');
    }

    public function submitEnquiry(Request $request)
    {
        // Step 1: Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'service' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Step 2: Save to database
        $enquiry = Enquiry::create($validated);

        // Step 3: Send email notification to admin
        try {
            Mail::to(config('mail.from.address')) // Or replace with your admin email
                ->send(new EnquiryFormSubmitted($enquiry));
        } catch (\Exception $e) {
            // You can log error if email fails
            Log::error('Enquiry form email failed: ' . $e->getMessage());
        }

        // Step 4: Redirect with success
        return redirect()
            ->route('contact')
            ->with('success', 'Thank you! Your message has been sent successfully.');
    }

    public function booking(): View
    {
        return view('frontend.booking');
    }
}
