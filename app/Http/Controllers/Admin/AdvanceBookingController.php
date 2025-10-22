<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdvanceBookingController extends Controller
{
    /**
     * Display the advance booking settings page.
     */
    public function index(): View
    {
        $settings = GeneralSetting::first();
        
        return view('admin.advance-booking.index', compact('settings'));
    }

    /**
     * Update the advance booking settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'advance_booking_enable' => 'required|boolean',
        ]);

        $settings = GeneralSetting::first();
        
        if (!$settings) {
            $settings = GeneralSetting::create([
                'advance_booking_enable' => $request->advance_booking_enable,
            ]);
        } else {
            $settings->update([
                'advance_booking_enable' => $request->advance_booking_enable,
            ]);
        }

        return redirect()->route('admin.advance-booking.index')
            ->with('success', 'Advance booking settings updated successfully.');
    }

    /**
     * Toggle advance booking status via AJAX.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $settings = GeneralSetting::first();
        
        if (!$settings) {
            $settings = GeneralSetting::create([
                'advance_booking_enable' => $request->enabled,
            ]);
        } else {
            $settings->update([
                'advance_booking_enable' => $request->enabled,
            ]);
        }

        $status = $request->enabled ? 'enabled' : 'disabled';
        
        return response()->json([
            'success' => true,
            'message' => "Advance booking {$status} successfully!",
            'enabled' => $settings->advance_booking_enable
        ]);
    }

    /**
     * Get advance booking settings via AJAX.
     */
    public function getSettings(): JsonResponse
    {
        $settings = GeneralSetting::first();
        
        return response()->json([
            'success' => true,
            'settings' => [
                'advance_booking_enable' => $settings?->advance_booking_enable ?? false,
            ]
        ]);
    }
}
