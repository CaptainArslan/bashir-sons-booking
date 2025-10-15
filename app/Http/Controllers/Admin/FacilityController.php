<?php

namespace App\Http\Controllers\Admin;

use App\Models\Facility;
use App\Enums\FacilityEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class FacilityController extends Controller
{
    public function index()
    {
        return view('admin.facilities.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $facilities = Facility::query()
                ->select('id', 'name', 'description', 'icon', 'status', 'created_at');

            return DataTables::eloquent($facilities)
                ->addColumn('formatted_name', function ($facility) {
                    return '<div class="d-flex align-items-center">
                                <i class="' . e($facility->icon) . ' me-2 text-primary"></i>
                                <span class="fw-bold text-primary">' . e($facility->name) . '</span>
                            </div>';
                })
                ->addColumn('description_preview', function ($facility) {
                    return '<span class="text-muted">' . e(\Str::limit($facility->description, 100)) . '</span>';
                })
                ->addColumn('status_badge', function ($facility) {
                    $statusValue = $facility->status instanceof FacilityEnum ? $facility->status->value : $facility->status;
                    $statusName = FacilityEnum::getStatusName($statusValue);
                    $statusColor = FacilityEnum::getStatusColor($statusValue);
                    return '<span class="badge bg-' . $statusColor . '">' . e($statusName) . '</span>';
                })
                ->addColumn('buses_count', function ($facility) {
                    $count = $facility->buses()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . $count . ' bus' . ($count !== 1 ? 'es' : '') . '</span>';
                })
                ->addColumn('actions', function ($facility) {
                    $actions = '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" 
                                       href="' . route('admin.facilities.edit', $facility->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit Facility
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteFacility(' . $facility->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Facility
                                    </a>
                                </li>
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($facility) => $facility->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'description_preview', 'status_badge', 'buses_count', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = FacilityEnum::getStatuses();
        return view('admin.facilities.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'required|string|max:255',
            'status' => 'required|string|in:' . implode(',', FacilityEnum::getStatuses()),
        ], [
            'name.required' => 'Facility name is required',
            'name.string' => 'Facility name must be a string',
            'name.max' => 'Facility name must be less than 255 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'icon.required' => 'Icon is required',
            'icon.string' => 'Icon must be a string',
            'icon.max' => 'Icon must be less than 255 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        Facility::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully');
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        $statuses = FacilityEnum::getStatuses();
        return view('admin.facilities.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'required|string|max:255',
            'status' => 'required|string|in:' . implode(',', FacilityEnum::getStatuses()),
        ], [
            'name.required' => 'Facility name is required',
            'name.string' => 'Facility name must be a string',
            'name.max' => 'Facility name must be less than 255 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'icon.required' => 'Icon is required',
            'icon.string' => 'Icon must be a string',
            'icon.max' => 'Icon must be less than 255 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        $facility = Facility::findOrFail($id);
        $facility->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.facilities.index')->with('success', 'Facility updated successfully');
    }

    public function destroy($id)
    {
        $facility = Facility::findOrFail($id);
        
        // Check if facility has buses assigned
        if ($facility->buses()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete facility. It has buses assigned to it.'
            ], 400);
        }

        $facility->delete();
        return response()->json([
            'success' => true,
            'message' => 'Facility deleted successfully.'
        ]);
    }
}
