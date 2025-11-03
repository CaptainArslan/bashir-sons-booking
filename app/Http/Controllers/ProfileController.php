<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('profile');
        
        return view('frontend.profile', [
            'user' => $user,
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update user basic info
        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create profile
        $profileData = $request->only(['phone', 'cnic', 'gender', 'date_of_birth', 'address']);
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('message', 'Profile updated successfully!')->with('alert-type', 'success');
    }

    /**
     * Display the user's bookings.
     */
    public function bookings(Request $request): View
    {
        $user = $request->user();
        $user->load('profile');
        
        $userCnic = $user->profile?->cnic;
        
        // Get bookings where user is the creator OR any passenger matches user's CNIC
        $bookings = \App\Models\Booking::with([
            'trip.route',
            'trip.bus',
            'fromStop.terminal',
            'toStop.terminal',
            'seats',
            'passengers'
        ])
        ->where(function ($query) use ($user, $userCnic) {
            // Bookings created by this user
            $query->where('user_id', $user->id);
            
            // OR bookings where any passenger matches user's CNIC
            if ($userCnic) {
                $query->orWhereHas('passengers', function ($q) use ($userCnic) {
                    $q->where('cnic', $userCnic);
                });
            }
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('frontend.my-bookings', [
            'bookings' => $bookings,
            'userCnic' => $userCnic,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
