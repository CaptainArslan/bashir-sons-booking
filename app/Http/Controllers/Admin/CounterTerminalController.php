<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TerminalEnum;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CounterTerminalController extends Controller
{
    public function index()
    {
        return view('admin.counter-terminals.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $terminals = Terminal::query()
                ->with(['city:id,name'])
                ->select('id', 'city_id', 'name', 'code', 'address', 'phone', 'email', 'status', 'created_at');

            return DataTables::eloquent($terminals)
                ->addColumn('formatted_name', function ($terminal) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($terminal->name).'</span>
                                <small class="text-muted">Code: '.e($terminal->code).'</small>
                            </div>';
                })
                ->addColumn('city_name', function ($terminal) {
                    return $terminal->city ? '<span class="badge bg-info">'.e($terminal->city->name).'</span>' : '<span class="text-muted">No City</span>';
                })
                ->addColumn('contact_info', function ($terminal) {
                    $phone = $terminal->phone ? '<div><i class="bx bx-phone me-1"></i>'.e($terminal->phone).'</div>' : '';
                    $email = $terminal->email ? '<div><i class="bx bx-envelope me-1"></i>'.e($terminal->email).'</div>' : '';

                    return $phone.$email;
                })
                ->addColumn('status_badge', function ($terminal) {
                    $statusValue = $terminal->status instanceof TerminalEnum ? $terminal->status->value : $terminal->status;
                    $statusName = TerminalEnum::getStatusName($statusValue);
                    $statusColor = TerminalEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                })
                ->addColumn('actions', function ($terminal) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if (auth()->user()->can('edit terminals')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.counter-terminals.edit', $terminal->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Terminal
                            </a>
                        </li>';
                    }

                    if (auth()->user()->can('delete terminals')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteTerminal('.$terminal->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Terminal
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($terminal) => $terminal->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns(['formatted_name', 'city_name', 'contact_info', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $cities = City::where('status', 'active')->get();
        $statuses = TerminalEnum::getStatuses();

        return view('admin.counter-terminals.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:terminals,code',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20|regex:/^[\d\-\+\(\)\s]+$/',
            'email' => 'nullable|email|max:255',
            'landmark' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'status' => 'required|string|in:'.implode(',', TerminalEnum::getStatuses()),
        ], [
            'city_id.required' => 'City is required',
            'city_id.exists' => 'Selected city is invalid',
            'name.required' => 'Terminal name is required',
            'name.regex' => 'Terminal name can only contain letters, spaces, hyphens, and periods',
            'code.required' => 'Terminal code is required',
            'code.unique' => 'Terminal code already exists',
            'code.regex' => 'Terminal code can only contain uppercase letters and numbers',
            'address.required' => 'Address is required',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Please enter a valid phone number',
            'email.email' => 'Please enter a valid email address',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            DB::beginTransaction();

            Terminal::create($validated);

            DB::commit();

            return redirect()->route('admin.counter-terminals.index')
                ->with('success', 'Terminal created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create terminal: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $terminal = Terminal::findOrFail($id);
        $cities = City::where('status', 'active')->get();
        $statuses = TerminalEnum::getStatuses();

        return view('admin.counter-terminals.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $terminal = Terminal::findOrFail($id);

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:terminals,code,'.$terminal->id,
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20|regex:/^[\d\-\+\(\)\s]+$/',
            'email' => 'nullable|email|max:255',
            'landmark' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'status' => 'required|string|in:'.implode(',', TerminalEnum::getStatuses()),
        ], [
            'city_id.required' => 'City is required',
            'city_id.exists' => 'Selected city is invalid',
            'name.required' => 'Terminal name is required',
            'name.regex' => 'Terminal name can only contain letters, spaces, hyphens, and periods',
            'code.required' => 'Terminal code is required',
            'code.unique' => 'Terminal code already exists',
            'code.regex' => 'Terminal code can only contain uppercase letters and numbers',
            'address.required' => 'Address is required',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Please enter a valid phone number',
            'email.email' => 'Please enter a valid email address',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            DB::beginTransaction();

            $terminal = Terminal::findOrFail($id);
            $terminal->update($validated);

            DB::commit();

            return redirect()->route('admin.counter-terminals.index')
                ->with('success', 'Terminal updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update terminal: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $terminal = Terminal::findOrFail($id);
            $terminal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Terminal deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting terminal: '.$e->getMessage(),
            ], 500);
        }
    }
}
