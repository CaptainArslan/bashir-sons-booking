<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'advance_booking_days' => 'required|integer|min:1|max:365',
        ], [
            'advance_booking_enable.required' => 'Advance booking status is required.',
            'advance_booking_days.required' => 'Number of days is required.',
            'advance_booking_days.integer' => 'Number of days must be a valid number.',
            'advance_booking_days.min' => 'Number of days must be at least 1.',
            'advance_booking_days.max' => 'Number of days cannot exceed 365.',
        ]);

        $settings = GeneralSetting::first();

        $updateData = [
            'advance_booking_enable' => $request->advance_booking_enable,
            'advance_booking_days' => $request->advance_booking_days ?? 7,
        ];

        if (! $settings) {
            $settings = GeneralSetting::create($updateData);
        } else {
            $settings->update($updateData);
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

        if (! $settings) {
            $settings = GeneralSetting::create([
                'advance_booking_enable' => $request->enabled,
                'advance_booking_days' => 7,
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
            'enabled' => $settings->advance_booking_enable,
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
                'advance_booking_days' => $settings?->advance_booking_days ?? 7,
            ],
        ]);
    }
}
