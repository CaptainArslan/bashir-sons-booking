<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $settings = GeneralSetting::first();
        return view('admin.general-settings.index', compact('settings'));
    }

    public function create()
    {
        return view('admin.general-settings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_&.]+$/',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'alternate_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'state' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'country' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'postal_code' => 'nullable|string|max:20|regex:/^[A-Z0-9\s\-]+$/',
            'website_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico,webp|max:1024',
            'tagline' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'business_hours' => 'nullable|string|max:500',
        ], [
            'company_name.required' => 'Company name is required',
            'company_name.regex' => 'Company name can only contain letters, numbers, spaces, hyphens, underscores, ampersands, and periods',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number format is invalid',
            'alternate_phone.regex' => 'Alternate phone number format is invalid',
            'address.required' => 'Address is required',
            'city.required' => 'City is required',
            'city.regex' => 'City can only contain letters and spaces',
            'state.required' => 'State is required',
            'state.regex' => 'State can only contain letters and spaces',
            'country.required' => 'Country is required',
            'country.regex' => 'Country can only contain letters and spaces',
            'postal_code.regex' => 'Postal code format is invalid',
            'website_url.url' => 'Website URL must be a valid URL',
            'logo.image' => 'Logo must be an image',
            'logo.mimes' => 'Logo must be jpeg, png, jpg, gif, or webp',
            'logo.max' => 'Logo size must be less than 2MB',
            'favicon.image' => 'Favicon must be an image',
            'favicon.mimes' => 'Favicon must be jpeg, png, jpg, gif, ico, or webp',
            'favicon.max' => 'Favicon size must be less than 1MB',
            'facebook_url.url' => 'Facebook URL must be a valid URL',
            'instagram_url.url' => 'Instagram URL must be a valid URL',
            'twitter_url.url' => 'Twitter URL must be a valid URL',
            'linkedin_url.url' => 'LinkedIn URL must be a valid URL',
            'youtube_url.url' => 'YouTube URL must be a valid URL',
            'support_email.email' => 'Support email must be a valid email address',
            'support_phone.regex' => 'Support phone number format is invalid',
        ]);

        try {
            // Handle file uploads
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('settings', 'public');
            }

            if ($request->hasFile('favicon')) {
                $validated['favicon'] = $request->file('favicon')->store('settings', 'public');
            }

            GeneralSetting::create($validated);

            return redirect()->route('admin.general-settings.index')->with('success', 'General settings created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create settings: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $settings = GeneralSetting::findOrFail($id);
        return view('admin.general-settings.edit', compact('settings'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_&.]+$/',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'alternate_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'state' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'country' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'postal_code' => 'nullable|string|max:20|regex:/^[A-Z0-9\s\-]+$/',
            'website_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico,webp|max:1024',
            'tagline' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'business_hours' => 'nullable|string|max:500',
        ], [
            'company_name.required' => 'Company name is required',
            'company_name.regex' => 'Company name can only contain letters, numbers, spaces, hyphens, underscores, ampersands, and periods',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number format is invalid',
            'alternate_phone.regex' => 'Alternate phone number format is invalid',
            'address.required' => 'Address is required',
            'city.required' => 'City is required',
            'city.regex' => 'City can only contain letters and spaces',
            'state.required' => 'State is required',
            'state.regex' => 'State can only contain letters and spaces',
            'country.required' => 'Country is required',
            'country.regex' => 'Country can only contain letters and spaces',
            'postal_code.regex' => 'Postal code format is invalid',
            'website_url.url' => 'Website URL must be a valid URL',
            'logo.image' => 'Logo must be an image',
            'logo.mimes' => 'Logo must be jpeg, png, jpg, gif, or webp',
            'logo.max' => 'Logo size must be less than 2MB',
            'favicon.image' => 'Favicon must be an image',
            'favicon.mimes' => 'Favicon must be jpeg, png, jpg, gif, ico, or webp',
            'favicon.max' => 'Favicon size must be less than 1MB',
            'facebook_url.url' => 'Facebook URL must be a valid URL',
            'instagram_url.url' => 'Instagram URL must be a valid URL',
            'twitter_url.url' => 'Twitter URL must be a valid URL',
            'linkedin_url.url' => 'LinkedIn URL must be a valid URL',
            'youtube_url.url' => 'YouTube URL must be a valid URL',
            'support_email.email' => 'Support email must be a valid email address',
            'support_phone.regex' => 'Support phone number format is invalid',
        ]);

        try {
            $settings = GeneralSetting::findOrFail($id);

            // Handle file uploads
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                    Storage::disk('public')->delete($settings->logo);
                }
                $validated['logo'] = $request->file('logo')->store('settings', 'public');
            } else {
                unset($validated['logo']);
            }

            if ($request->hasFile('favicon')) {
                // Delete old favicon if exists
                if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                    Storage::disk('public')->delete($settings->favicon);
                }
                $validated['favicon'] = $request->file('favicon')->store('settings', 'public');
            } else {
                unset($validated['favicon']);
            }

            $settings->update($validated);

            return redirect()->route('admin.general-settings.index')->with('success', 'General settings updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $settings = GeneralSetting::findOrFail($id);
            
            // Delete associated files
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }
            if ($settings->favicon && Storage::disk('public')->exists($settings->favicon)) {
                Storage::disk('public')->delete($settings->favicon);
            }

            $settings->delete();
            return response()->json([
                'success' => true,
                'message' => 'General settings deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting settings: ' . $e->getMessage()
            ], 500);
        }
    }
}