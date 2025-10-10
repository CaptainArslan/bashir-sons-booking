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

        if (! $user->two_factor_secret) {
            $google2fa = new Google2FA();

            // Generate new secret key
            $secret = $google2fa->generateSecretKey();

            // Create Google Authenticator QR code URL
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            // Generate inline SVG QR code
            $writer = new Writer(
                new ImageRenderer(
                    new RendererStyle(200),
                    new SvgImageBackEnd()
                )
            );

            $QR_Image = $writer->writeString($qrCodeUrl);

            return view('profile.2fa', compact('secret', 'QR_Image'));
        }

        return view('profile.2fa', ['enabled' => true]);
    }

    public function enable(Request $request)
    {
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if (! $valid) {
            return back()->with('error', 'Invalid 2FA code');
        }

        $user = Auth::user();

        $user->enableTwoFactorAuthentication(
            $request->secret,
            collect(range(1, 8))->map(fn() => bin2hex(random_bytes(4)))->toArray()
        );

        return back()->with('success', 'Two-Factor Authentication enabled!');
    }

    public function disable()
    {
        $user = Auth::user();
        $user->disableTwoFactorAuthentication();

        return back()->with('success', 'Two-Factor Authentication disabled!');
    }

    public function challenge()
    {
        $user = User::find(session('2fa:user_id'));

        if (! $user) {
            return redirect()->route('login')->withErrors(['login' => 'Session expired.']);
        }

        return view('profile.twofactor-challenge', compact('user'));
    }

    public function verifyChallenge(Request $request)
    {
        $user = User::find(session('2fa:user_id'));

        if (! $user) {
            return redirect()->route('login')->withErrors(['login' => 'Session expired.']);
        }

        $google2fa = new Google2FA();
        $secret = Crypt::decryptString($user->two_factor_secret);

        if ($google2fa->verifyKey($secret, $request->code)) {
            session()->forget('2fa:user_id');
            Auth::login($user);
            return redirect()->intended('/');
        }

        return back()->with('error', 'Invalid authentication code.');
    }
}
