<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusEnum;
use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLayout;
use App\Models\BusType;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BusController extends Controller
{
    public function index()
    {
        return view('admin.buses.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $buses = Bus::query()
                ->with(['busType', 'busLayout', 'facilities'])
                ->select('id', 'name', 'description', 'bus_type_id', 'bus_layout_id', 'registration_number', 'model', 'color', 'status', 'created_at');

            return DataTables::eloquent($buses)
                ->addColumn('formatted_name', function ($bus) {
                    return '<div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-primary">'.e($bus->name).'</h6>
                                    <small class="text-muted">'.e($bus->registration_number).'</small>
                                </div>
                            </div>';
                })
                ->addColumn('description_preview', function ($bus) {
                    return '<span class="text-muted">'.e(\Str::limit($bus->description, 100)).'</span>';
                })
                ->addColumn('bus_info', function ($bus) {
                    $busInfo = '<div class="d-flex flex-column">';
                    $busInfo .= '<span class="fw-bold">'.e($bus->model).'</span>';
                    $busInfo .= '<small class="text-muted">'.e($bus->color).'</small>';
                    $busInfo .= '</div>';

                    return $busInfo;
                })
                ->addColumn('type_info', function ($bus) {
                    $busType = $bus->busType;
                    if (! $busType) {
                        return '<span class="text-muted">No type</span>';
                    }

                    return '<span class="badge bg-info">'.e($busType->name).'</span>';
                })
                ->addColumn('layout_info', function ($bus) {
                    $busLayout = $bus->busLayout;
                    if (! $busLayout) {
                        return '<span class="text-muted">No layout</span>';
                    }

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold">'.e($busLayout->name).'</span>
                                <small class="text-muted">'.$busLayout->total_seats.' seats</small>
                            </div>';
                })
                ->addColumn('facilities_list', function ($bus) {
                    $facilities = $bus->facilities;
                    if ($facilities->isEmpty()) {
                        return '<span class="badge bg-secondary">No facilities</span>';
                    }

                    return $facilities->take(3)->map(function ($facility) {
                        return '<span class="badge bg-success me-1 mb-1">'.e($facility->name).'</span>';
                    })->implode('').($facilities->count() > 3 ? '<span class="badge bg-light text-dark">+'.($facilities->count() - 3).'</span>' : '');
                })
                ->addColumn('status_badge', function ($bus) {
                    $statusValue = $bus->status instanceof BusEnum ? $bus->status->value : $bus->status;
                    $statusName = BusEnum::getStatusName($statusValue);
                    $statusColor = BusEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                })
                ->addColumn('actions', function ($bus) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if (auth()->user()->can('edit buses')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.buses.edit', $bus->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Bus
                            </a>
                        </li>';
                    }

                    if (auth()->user()->can('delete buses')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteBus('.$bus->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Bus
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($bus) => $bus->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'description_preview', 'bus_info', 'type_info', 'layout_info', 'facilities_list', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $busTypes = BusType::where('status', 'active')->orderBy('name')->get();
        $busLayouts = BusLayout::where('status', 'active')->orderBy('name')->get();
        $facilities = Facility::where('status', 'active')->orderBy('name')->get();
        $statuses = BusEnum::getStatuses();

        return view('admin.buses.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'bus_type_id' => [
                'required',
                'exists:bus_types,id',
            ],
            'bus_layout_id' => [
                'required',
                'exists:bus_layouts,id',
            ],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                'unique:buses,registration_number',
            ],
            'model' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'color' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'facilities' => [
                'nullable',
                'array',
            ],
            'facilities.*' => [
                'exists:facilities,id',
            ],
            'status' => [
                'required',
                'string',
                'in:'.implode(',', BusEnum::getStatuses()),
            ],
        ], [
            'name.required' => 'Bus name is required',
            'name.string' => 'Bus name must be a string',
            'name.max' => 'Bus name must be less than 255 characters',
            'name.regex' => 'Bus name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'bus_type_id.required' => 'Bus type is required',
            'bus_type_id.exists' => 'Selected bus type is invalid',
            'bus_layout_id.required' => 'Bus layout is required',
            'bus_layout_id.exists' => 'Selected bus layout is invalid',
            'registration_number.required' => 'Registration number is required',
            'registration_number.unique' => 'Registration number already exists',
            'model.required' => 'Model is required',
            'model.regex' => 'Model can only contain letters, numbers, spaces, hyphens, and underscores',
            'color.required' => 'Color is required',
            'color.regex' => 'Color can only contain letters and spaces',
            'facilities.array' => 'Facilities must be an array',
            'facilities.*.exists' => 'One or more selected facilities are invalid',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            DB::beginTransaction();

            $bus = Bus::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'bus_type_id' => $validated['bus_type_id'],
                'bus_layout_id' => $validated['bus_layout_id'],
                'registration_number' => strtoupper($validated['registration_number']),
                'model' => $validated['model'],
                'color' => $validated['color'],
                'status' => $validated['status'],
            ]);

            // Attach facilities if provided
            if (! empty($validated['facilities'])) {
                $bus->facilities()->attach($validated['facilities']);
            }

            DB::commit();

            return redirect()->route('admin.buses.index')->with('success', 'Bus created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create bus: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $bus = Bus::with(['facilities'])->findOrFail($id);
        $busTypes = BusType::where('status', 'active')->orderBy('name')->get();
        $busLayouts = BusLayout::where('status', 'active')->orderBy('name')->get();
        $facilities = Facility::where('status', 'active')->orderBy('name')->get();
        $statuses = BusEnum::getStatuses();

        return view('admin.buses.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'bus_type_id' => [
                'required',
                'exists:bus_types,id',
            ],
            'bus_layout_id' => [
                'required',
                'exists:bus_layouts,id',
            ],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                'unique:buses,registration_number,'.$bus->id,
            ],
            'model' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'color' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'facilities' => [
                'nullable',
                'array',
            ],
            'facilities.*' => [
                'exists:facilities,id',
            ],
            'status' => [
                'required',
                'string',
                'in:'.implode(',', BusEnum::getStatuses()),
            ],
        ], [
            'name.required' => 'Bus name is required',
            'name.string' => 'Bus name must be a string',
            'name.max' => 'Bus name must be less than 255 characters',
            'name.regex' => 'Bus name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'bus_type_id.required' => 'Bus type is required',
            'bus_type_id.exists' => 'Selected bus type is invalid',
            'bus_layout_id.required' => 'Bus layout is required',
            'bus_layout_id.exists' => 'Selected bus layout is invalid',
            'registration_number.required' => 'Registration number is required',
            'registration_number.unique' => 'Registration number already exists',
            'model.required' => 'Model is required',
            'model.regex' => 'Model can only contain letters, numbers, spaces, hyphens, and underscores',
            'color.required' => 'Color is required',
            'color.regex' => 'Color can only contain letters and spaces',
            'facilities.array' => 'Facilities must be an array',
            'facilities.*.exists' => 'One or more selected facilities are invalid',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            DB::beginTransaction();

            $bus->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'bus_type_id' => $validated['bus_type_id'],
                'bus_layout_id' => $validated['bus_layout_id'],
                'registration_number' => strtoupper($validated['registration_number']),
                'model' => $validated['model'],
                'color' => $validated['color'],
                'status' => $validated['status'],
            ]);

            // Sync facilities
            $bus->facilities()->sync($validated['facilities'] ?? []);

            DB::commit();

            return redirect()->route('admin.buses.index')->with('success', 'Bus updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update bus: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $bus = Bus::findOrFail($id);

            // Check for associated data
            $tripsCount = $bus->trips()->count();
            $busAssignmentsCount = $bus->busAssignments()->count();

            $associations = [];
            if ($tripsCount > 0) {
                $associations[] = "{$tripsCount} trip".($tripsCount !== 1 ? 's' : '');
            }
            if ($busAssignmentsCount > 0) {
                $associations[] = "{$busAssignmentsCount} bus assignment".($busAssignmentsCount !== 1 ? 's' : '');
            }

            if (! empty($associations)) {
                $associationText = implode(' and ', $associations);
                $message = "Cannot delete bus. It has {$associationText} associated with it. Please remove all associations before deleting.";

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'trips_count' => $tripsCount,
                    'bus_assignments_count' => $busAssignmentsCount,
                ], 400);
            }

            $busName = $bus->name;
            $bus->delete();

            return response()->json([
                'success' => true,
                'message' => "Bus '{$busName}' has been deleted successfully.",
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bus not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the bus. Please try again.',
            ], 500);
        }
    }
}
