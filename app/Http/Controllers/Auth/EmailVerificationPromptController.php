<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            if ($user->isAdmin() || $user->isEmployee()) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }
            return redirect()->intended(route('home', absolute: false));
        }
        return view('frontend.auth.verify-email');
    }
}
