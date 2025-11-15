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
        return view('frontend.auth.login');
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

        // If user is banned, prevent login and show error message
        if ($user && $user->status === \App\Enums\UserStatusEnum::BANNED) {
            // Logout to prevent access
            Auth::logout();

            // Redirect back to login with error message
            return redirect()->route('login')
                ->with('error', 'Your account has been banned. Please contact an administrator to activate your account.');
        }

        // Step 4: Check if the user has 2FA enabled
        if ($user && $user->hasTwoFactorEnabled()) {
            // Logout temporarily until they pass 2FA challenge
            Auth::logout();

            // Store user ID in session for later 2FA verification
            session(['2fa:user_id' => $user->id]);

            return redirect()->route('2fa.challenge');
        }

        // Check for redirect parameter or intended URL
        $redirect = $request->query('redirect');
        if ($redirect && filter_var($redirect, FILTER_VALIDATE_URL)) {
            // Validate that redirect is to same domain
            $redirectHost = parse_url($redirect, PHP_URL_HOST);
            $currentHost = $request->getHost();
            if ($redirectHost === $currentHost) {
                return redirect($redirect);
            }
        }

        // Default fallback - redirect to home or profile based on user role
        if ($user && ($user->isAdmin() || $user->isEmployee())) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
