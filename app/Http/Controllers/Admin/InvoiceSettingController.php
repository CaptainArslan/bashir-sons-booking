<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceSettingController extends Controller
{
    public function index(): View
    {
        $invoiceSettings = InvoiceSetting::orderBy('is_default', 'desc')
            ->orderBy('size')
            ->orderBy('template_name')
            ->get();

        return view('admin.invoice-settings.index', compact('invoiceSettings'));
    }

    public function create(): View
    {
        $sizes = ['A4', '58mm', '80mm'];
        $numberingTypes = ['sequential', 'random', 'custom'];
        $fileTypes = ['pdf', 'html'];

        return view('admin.invoice-settings.create', compact('sizes', 'numberingTypes', 'fileTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:191',
            'invoice_name' => 'nullable|string|max:191',
            'invoice_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'file_type' => 'nullable|string|in:pdf,html',
            'prefix' => 'nullable|string|max:191',
            'number_of_digit' => 'nullable|string|max:191',
            'numbering_type' => 'nullable|string|in:sequential,random,custom',
            'start_number' => 'nullable|integer|min:1',
            'last_invoice_number' => 'nullable|integer|min:0',
            'header_text' => 'nullable|string',
            'header_title' => 'nullable|string|max:191',
            'footer_text' => 'nullable|string',
            'footer_title' => 'nullable|string|max:191',
            'preview_invoice' => 'nullable|string|max:191',
            'size' => 'required|string|in:A4,58mm,80mm',
            'primary_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'logo_height' => 'nullable|string|max:191',
            'logo_width' => 'nullable|string|max:191',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'invoice_date_format' => 'required|string|max:191',
            'show_column' => 'nullable|array',
            'extra' => 'nullable|array',
        ], [
            'template_name.required' => 'Template name is required',
            'size.required' => 'Size is required',
            'size.in' => 'Size must be A4, 58mm, or 80mm',
            'invoice_date_format.required' => 'Invoice date format is required',
            'primary_color.regex' => 'Primary color must be a valid hex color (e.g., #FF0000)',
            'secondary_color.regex' => 'Secondary color must be a valid hex color (e.g., #FF0000)',
            'text_color.regex' => 'Text color must be a valid hex color (e.g., #FF0000)',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            if ($request->hasFile('invoice_logo')) {
                $validated['invoice_logo'] = $request->file('invoice_logo')->store('invoice-settings/logos', 'public');
            }

            if ($request->hasFile('company_logo')) {
                $validated['company_logo'] = $request->file('company_logo')->store('invoice-settings/logos', 'public');
            }

            // If this is set as default, unset other defaults for the same size
            if ($request->boolean('is_default')) {
                InvoiceSetting::where('size', $validated['size'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $validated['created_by'] = auth()->id();
            $validated['status'] = $request->has('status') ? true : false;
            $validated['is_default'] = $request->has('is_default') ? true : false;

            InvoiceSetting::create($validated);

            DB::commit();

            return redirect()->route('admin.invoice-settings.index')
                ->with('success', 'Invoice setting created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create invoice setting: '.$e->getMessage());
        }
    }

    public function show(InvoiceSetting $invoiceSetting): View
    {
        return view('admin.invoice-settings.show', compact('invoiceSetting'));
    }

    public function edit(InvoiceSetting $invoiceSetting): View
    {
        $sizes = ['A4', '58mm', '80mm'];
        $numberingTypes = ['sequential', 'random', 'custom'];
        $fileTypes = ['pdf', 'html'];

        return view('admin.invoice-settings.edit', compact('invoiceSetting', 'sizes', 'numberingTypes', 'fileTypes'));
    }

    public function update(Request $request, InvoiceSetting $invoiceSetting): RedirectResponse
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:191',
            'invoice_name' => 'nullable|string|max:191',
            'invoice_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'file_type' => 'nullable|string|in:pdf,html',
            'prefix' => 'nullable|string|max:191',
            'number_of_digit' => 'nullable|string|max:191',
            'numbering_type' => 'nullable|string|in:sequential,random,custom',
            'start_number' => 'nullable|integer|min:1',
            'last_invoice_number' => 'nullable|integer|min:0',
            'header_text' => 'nullable|string',
            'header_title' => 'nullable|string|max:191',
            'footer_text' => 'nullable|string',
            'footer_title' => 'nullable|string|max:191',
            'preview_invoice' => 'nullable|string|max:191',
            'size' => 'required|string|in:A4,58mm,80mm',
            'primary_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|string|max:191|regex:/^#[0-9A-Fa-f]{6}$/',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'logo_height' => 'nullable|string|max:191',
            'logo_width' => 'nullable|string|max:191',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'invoice_date_format' => 'required|string|max:191',
            'show_column' => 'nullable|array',
            'extra' => 'nullable|array',
        ], [
            'template_name.required' => 'Template name is required',
            'size.required' => 'Size is required',
            'size.in' => 'Size must be A4, 58mm, or 80mm',
            'invoice_date_format.required' => 'Invoice date format is required',
            'primary_color.regex' => 'Primary color must be a valid hex color (e.g., #FF0000)',
            'secondary_color.regex' => 'Secondary color must be a valid hex color (e.g., #FF0000)',
            'text_color.regex' => 'Text color must be a valid hex color (e.g., #FF0000)',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            if ($request->hasFile('invoice_logo')) {
                // Delete old logo if exists
                if ($invoiceSetting->invoice_logo) {
                    Storage::disk('public')->delete($invoiceSetting->invoice_logo);
                }
                $validated['invoice_logo'] = $request->file('invoice_logo')->store('invoice-settings/logos', 'public');
            }

            if ($request->hasFile('company_logo')) {
                // Delete old logo if exists
                if ($invoiceSetting->company_logo) {
                    Storage::disk('public')->delete($invoiceSetting->company_logo);
                }
                $validated['company_logo'] = $request->file('company_logo')->store('invoice-settings/logos', 'public');
            }

            // If this is set as default, unset other defaults for the same size
            if ($request->boolean('is_default')) {
                InvoiceSetting::where('size', $validated['size'])
                    ->where('id', '!=', $invoiceSetting->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $validated['updated_by'] = auth()->id();
            $validated['status'] = $request->has('status') ? true : false;
            $validated['is_default'] = $request->has('is_default') ? true : false;

            $invoiceSetting->update($validated);

            DB::commit();

            return redirect()->route('admin.invoice-settings.index')
                ->with('success', 'Invoice setting updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update invoice setting: '.$e->getMessage());
        }
    }

    public function destroy(InvoiceSetting $invoiceSetting): RedirectResponse
    {
        try {
            // Delete associated files
            if ($invoiceSetting->invoice_logo) {
                Storage::disk('public')->delete($invoiceSetting->invoice_logo);
            }
            if ($invoiceSetting->company_logo) {
                Storage::disk('public')->delete($invoiceSetting->company_logo);
            }

            $invoiceSetting->delete();

            return redirect()->route('admin.invoice-settings.index')
                ->with('success', 'Invoice setting deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete invoice setting: '.$e->getMessage());
        }
    }

    public function setDefault(InvoiceSetting $invoiceSetting): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Unset other defaults for the same size
            InvoiceSetting::where('size', $invoiceSetting->size)
                ->where('id', '!=', $invoiceSetting->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $invoiceSetting->update([
                'is_default' => true,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.invoice-settings.index')
                ->with('success', 'Default invoice setting updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to set default invoice setting: '.$e->getMessage());
        }
    }
}
