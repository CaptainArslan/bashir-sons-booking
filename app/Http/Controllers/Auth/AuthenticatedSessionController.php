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

        // Step 3: Check user status
        $user = Auth::user();

        // If user is banned, redirect to activation page (only on login, not while active)
        if ($user && $user->status === \App\Enums\UserStatusEnum::BANNED) {
            // Store user ID in session for activation
            session(['banned_user_id' => $user->id]);

            // Logout to prevent access
            Auth::logout();

            // Redirect to activation page
            return redirect()->route('user.activate');
        }

        // Step 4: Check if the user has 2FA enabled
        if ($user && $user->hasTwoFactorEnabled()) {
            // Logout temporarily until they pass 2FA challenge
            Auth::logout();

            // Store user ID in session for later 2FA verification
            session(['2fa:user_id' => $user->id]);

            return redirect()->route('2fa.challenge');
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
