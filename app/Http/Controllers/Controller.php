<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function notification($message, $alert_type)
    {
        $notification = [
            'message' => $message,
            'alert-type' => $alert_type,
        ];

        return redirect()->back()->with($notification);
    }

    public function notify(string $message, string $alert_type = 'error', ?string $redirectUrl = null)
    {
        $notification = [
            'message' => $message,
            'alert-type' => $alert_type,
        ];

        return redirect($redirectUrl ?? back())
            ->with($notification);
    }
}
