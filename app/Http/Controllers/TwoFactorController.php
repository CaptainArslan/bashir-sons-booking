<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;


class TwoFactorController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // If 2FA already enabled
        if ($user->two_factor_secret) {
            return view('frontend.auth.two-factor', ['enabled' => true]);
        }

        // Try to use a pending secret from session
        $secret = session('2fa_secret');

        if (! $secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();

            // Store temporary secret in session until user verifies
            session(['2fa_secret' => $secret]);
        }

        // Generate QR code
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );

        $QR_Image = $writer->writeString($qrCodeUrl);

        return view('frontend.auth.two-factor', compact('secret', 'QR_Image'));
    }


    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'secret' => ['required', 'string'],
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if (! $valid) {
            return back()->with('error', 'Invalid authentication code. Please check your authenticator app and try again.');
        }

        $user = Auth::user();

        $user->enableTwoFactorAuthentication(
            $request->secret,
            collect(range(1, 8))->map(fn() => bin2hex(random_bytes(4)))->toArray()
        );

        // Clear the temporary secret from session
        session()->forget('2fa_secret');

        return redirect()->route('2fa.show')->with('success', 'Two-Factor Authentication enabled successfully! Your account is now more secure.');
    }

    public function disable()
    {
        $user = Auth::user();
        $user->disableTwoFactorAuthentication();

        return redirect()->route('2fa.show')->with('success', 'Two-Factor Authentication disabled successfully.');
    }

    public function challenge()
    {
        $user = User::find(session('2fa:user_id'));

        if (! $user) {
            return redirect()->route('login')->withErrors(['login' => 'Session expired. Please log in again.']);
        }

        return view('frontend.auth.two-factor-challenge', compact('user'));
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = User::find(session('2fa:user_id'));

        if (! $user) {
            return redirect()->route('login')->withErrors(['login' => 'Session expired. Please log in again.']);
        }

        $google2fa = new Google2FA();
        $secret = Crypt::decryptString($user->two_factor_secret);

        if ($google2fa->verifyKey($secret, $request->code)) {
            session()->forget('2fa:user_id');
            Auth::login($user);

            // Redirect based on user role
            if ($user->isAdmin() || $user->isEmployee()) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }
            return redirect()->intended(route('home', absolute: false));
        }

        return back()->with('error', 'Invalid authentication code. Please check your authenticator app and try again.');
    }
}
