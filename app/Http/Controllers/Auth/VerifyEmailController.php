<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            if ($user->isAdmin() || $user->isEmployee()) {
                return redirect()->intended(route('admin.dashboard', absolute: false).'?verified=1');
            }
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ($user->isAdmin() || $user->isEmployee()) {
            return redirect()->intended(route('admin.dashboard', absolute: false).'?verified=1');
        }
        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
