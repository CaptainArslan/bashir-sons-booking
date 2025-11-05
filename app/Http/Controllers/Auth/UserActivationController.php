<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserActivationController extends Controller
{
    /**
     * Display the user activation form (for banned users to self-activate)
     */
    public function show(Request $request): View|RedirectResponse
    {
        $userId = session('banned_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->with('error', 'Invalid activation request. Please login first.');
        }

        $user = User::find($userId);

        if (! $user || $user->status !== UserStatusEnum::BANNED) {
            session()->forget('banned_user_id');

            return redirect()->route('login')
                ->with('error', 'Invalid activation request.');
        }

        return view('auth.activate-user', compact('user'));
    }

    /**
     * Handle user self-activation (only works on login, not while active)
     */
    public function activate(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $userId = session('banned_user_id');

        if (! $userId) {
            return redirect()->route('login')
                ->with('error', 'Invalid activation request. Please login first.');
        }

        $user = User::find($userId);

        if (! $user || $user->status !== UserStatusEnum::BANNED) {
            session()->forget('banned_user_id');

            return redirect()->route('login')
                ->with('error', 'Invalid activation request.');
        }

        // Verify credentials
        if ($user->email !== $request->email || ! Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid credentials. Please enter your correct email and password.');
        }

        // Activate the user
        $user->update([
            'status' => UserStatusEnum::ACTIVE->value,
        ]);

        // Clear the banned user session
        session()->forget('banned_user_id');

        // Log in the user
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Check for 2FA
        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();
            session(['2fa:user_id' => $user->id]);

            return redirect()->route('2fa.challenge');
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Your account has been activated successfully!');
    }
}
