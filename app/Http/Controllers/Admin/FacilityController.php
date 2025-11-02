<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FacilityEnum;
use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
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
            $user = auth()->user();
            $hasEditPermission = $user->can('edit facilities');
            $hasDeletePermission = $user->can('delete facilities');
            $hasAnyActionPermission = $hasEditPermission || $hasDeletePermission;

            $facilities = Facility::query()
                ->select('id', 'name', 'description', 'icon', 'status', 'created_at');

            $dataTable = DataTables::eloquent($facilities)
                ->addColumn('formatted_name', function ($facility) {
                    return '<div class="d-flex align-items-center">
                                <i class="'.e($facility->icon).' me-2 text-primary"></i>
                                <span class="fw-bold text-primary">'.e($facility->name).'</span>
                            </div>';
                })
                ->addColumn('description_preview', function ($facility) {
                    return '<span class="text-muted">'.e(\Str::limit($facility->description, 100)).'</span>';
                })
                ->addColumn('status_badge', function ($facility) {
                    $statusValue = $facility->status instanceof FacilityEnum ? $facility->status->value : $facility->status;
                    $statusName = FacilityEnum::getStatusName($statusValue);
                    $statusColor = FacilityEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                })
                ->addColumn('buses_count', function ($facility) {
                    $count = $facility->buses()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$badgeClass.'">'.$count.' bus'.($count !== 1 ? 'es' : '').'</span>';
                });

            // Only add actions column if user has at least one action permission
            if ($hasAnyActionPermission) {
                $dataTable->addColumn('actions', function ($facility) use ($hasEditPermission, $hasDeletePermission) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if ($hasEditPermission) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.facilities.edit', $facility->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Facility
                            </a>
                        </li>';
                    }

                    if ($hasDeletePermission) {
                        if ($hasEditPermission) {
                            $actions .= '<li><hr class="dropdown-divider"></li>';
                        }
                        $actions .= '<li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteFacility('.$facility->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Facility
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                });
            }

            return $dataTable
                ->editColumn('created_at', fn ($facility) => $facility->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns($hasAnyActionPermission
                    ? ['formatted_name', 'description_preview', 'status_badge', 'buses_count', 'actions']
                    : ['formatted_name', 'description_preview', 'status_badge', 'buses_count']
                )
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = FacilityEnum::getStatuses();

        return view('admin.facilities.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'icon' => 'required|string|max:255|regex:/^bx\s+bx-[a-zA-Z0-9\-]+$/',
            'status' => 'required|string|in:'.implode(',', FacilityEnum::getStatuses()),
        ], [
            'name.required' => 'Facility name is required',
            'name.string' => 'Facility name must be a string',
            'name.max' => 'Facility name must be less than 255 characters',
            'name.unique' => 'Facility name already exists',
            'name.regex' => 'Facility name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'icon.required' => 'Icon is required',
            'icon.string' => 'Icon must be a string',
            'icon.max' => 'Icon must be less than 255 characters',
            'icon.regex' => 'Icon must be a valid Boxicons class (e.g., bx bx-wifi)',
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

        return view('admin.facilities.edit', compact('facility', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:facilities,name,'.$facility->id.'|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'icon' => 'required|string|max:255|regex:/^bx\s+bx-[a-zA-Z0-9\-]+$/',
            'status' => 'required|string|in:'.implode(',', FacilityEnum::getStatuses()),
        ], [
            'name.required' => 'Facility name is required',
            'name.string' => 'Facility name must be a string',
            'name.max' => 'Facility name must be less than 255 characters',
            'name.unique' => 'Facility name already exists',
            'name.regex' => 'Facility name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'icon.required' => 'Icon is required',
            'icon.string' => 'Icon must be a string',
            'icon.max' => 'Icon must be less than 255 characters',
            'icon.regex' => 'Icon must be a valid Boxicons class (e.g., bx bx-wifi)',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

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
        try {
            $this->authorize('delete facilities');

            $facility = Facility::findOrFail($id);

            // Check if facility has buses assigned
            if ($facility->buses()->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete facility. It has buses assigned to it.',
                    ], 400);
                }

                return redirect()->route('admin.facilities.index')
                    ->with('error', 'Cannot delete facility. It has buses assigned to it.');
            }

            $facility->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Facility deleted successfully.',
                ]);
            }

            return redirect()->route('admin.facilities.index')
                ->with('success', 'Facility deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting facility: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.facilities.index')
                ->with('error', 'Error deleting facility: '.$e->getMessage());
        }
    }
}
