<?php

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * Prevent banned users from accessing authenticated routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check status for authenticated users
        if (Auth::check()) {
            // Reload user from database to get latest status
            // This ensures we catch status changes even if user is already logged in
            $userId = Auth::id();
            $user = User::find($userId);

            // Check if user is banned
            if ($user && $user->status === UserStatusEnum::BANNED) {
                // Logout the user
                Auth::logout();

                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with error message
                return redirect()->route('login')
                    ->with('error', 'Your account has been banned. Please contact an administrator to activate your account.');
            }
        }

        return $next($request);
    }
}
