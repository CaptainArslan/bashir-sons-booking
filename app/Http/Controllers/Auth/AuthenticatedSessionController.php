<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Step 1: Authenticate user
        $request->authenticate();

        // Step 2: Regenerate session to prevent fixation
        $request->session()->regenerate();

        // Step 3: Check if the user has 2FA enabled
        $user = Auth::user();

        if ($user && $user->hasTwoFactorEnabled()) {
            // Logout temporarily until they pass 2FA challenge
            Auth::logout();

            // Store user ID in session for later 2FA verification
            session(['2fa:user_id' => $user->id]);

            return redirect()->route('2fa.challenge');
        }

        // Step 4: Redirect to correct dashboard based on role
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->hasRole('customer')) {
            return redirect()->intended(route('customer.dashboard'));
        }

        // Default fallback
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
