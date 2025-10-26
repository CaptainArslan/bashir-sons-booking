<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\BusType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BusTypeController extends Controller
{
    public function index()
    {
        return view('admin.bus-types.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $busTypes = BusType::query()
                ->select('id', 'name', 'description', 'status', 'created_at');

            return DataTables::eloquent($busTypes)
                ->addColumn('formatted_name', function ($busType) {
                    return '<span class="fw-bold text-primary">'.e($busType->name).'</span>';
                })
                ->addColumn('description_preview', function ($busType) {
                    return '<span class="text-muted">'.e(\Str::limit($busType->description, 100)).'</span>';
                })
                ->addColumn('status_badge', function ($busType) {
                    $statusValue = $busType->status instanceof BusTypeEnum ? $busType->status->value : $busType->status;
                    $statusName = BusTypeEnum::getStatusName($statusValue);
                    $statusColor = BusTypeEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                })
                ->addColumn('buses_count', function ($busType) {
                    $count = $busType->buses()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$badgeClass.'">'.$count.' bus'.($count !== 1 ? 'es' : '').'</span>';
                })
                ->addColumn('actions', function ($busType) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if (auth()->user()->can('edit bus types')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.bus-types.edit', $busType->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Bus Type
                            </a>
                        </li>';
                    }

                    // Delete button
                    if (auth()->user()->can('delete bus types')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteBusType('.$busType->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Bus Type
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($busType) => $busType->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'description_preview', 'status_badge', 'buses_count', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = BusTypeEnum::getStatuses();

        return view('admin.bus-types.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bus_types,name|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:'.implode(',', BusTypeEnum::getStatuses()),
        ], [
            'name.required' => 'Bus type name is required',
            'name.string' => 'Bus type name must be a string',
            'name.max' => 'Bus type name must be less than 255 characters',
            'name.unique' => 'Bus type name already exists',
            'name.regex' => 'Bus type name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        BusType::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.bus-types.index')->with('success', 'Bus type created successfully');
    }

    public function edit($id)
    {
        $busType = BusType::findOrFail($id);
        $statuses = BusTypeEnum::getStatuses();

        return view('admin.bus-types.edit', compact('busType', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $busType = BusType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bus_types,name,'.$busType->id.'|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:'.implode(',', BusTypeEnum::getStatuses()),
        ], [
            'name.required' => 'Bus type name is required',
            'name.string' => 'Bus type name must be a string',
            'name.max' => 'Bus type name must be less than 255 characters',
            'name.unique' => 'Bus type name already exists',
            'name.regex' => 'Bus type name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        $busType->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.bus-types.index')->with('success', 'Bus type updated successfully');
    }

    public function destroy($id)
    {
        try {
            $busType = BusType::findOrFail($id);

            // Check if bus type has buses assigned
            if ($busType->buses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bus type. It has buses assigned to it.',
                ], 400);
            }

            $busType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bus type deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting bus type: '.$e->getMessage(),
            ], 500);
        }
    }
}
