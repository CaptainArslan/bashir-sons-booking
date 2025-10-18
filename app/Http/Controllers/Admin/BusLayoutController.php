<?php

namespace App\Http\Controllers\Admin;

use App\Models\BusLayout;
use Illuminate\Support\Str;
use App\Enums\BusLayoutEnum;
use App\Enums\SeatTypeEnum;
use App\Enums\GenderEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BusLayoutController extends Controller
{
    public function index()
    {
        return view('admin.bus-layouts.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $busLayouts = BusLayout::query()
                ->select('id', 'name', 'description', 'total_rows', 'total_columns', 'total_seats', 'status', 'created_at');

            return DataTables::eloquent($busLayouts)
                ->addColumn('formatted_name', function ($busLayout) {
                    return '<span class="fw-bold text-primary">' . e($busLayout->name) . '</span>';
                })
                ->addColumn('description_preview', function ($busLayout) {
                    return '<span class="text-muted">' . e(Str::limit($busLayout->description, 100)) . '</span>';
                })
                ->addColumn('seats_info', function ($busLayout) {
                    $seatsInfo = '<div class="d-flex flex-column">';
                    $seatsInfo .= '<span class="fw-bold">' . $busLayout->total_seats . ' seats</span>';
                    $seatsInfo .= '<small class="text-muted">' . $busLayout->total_rows . ' rows × ' . $busLayout->total_columns . ' columns</small>';
                    $seatsInfo .= '</div>';
                    return $seatsInfo;
                })
                ->addColumn('status_badge', function ($busLayout) {
                    $statusValue = $busLayout->status instanceof BusLayoutEnum ? $busLayout->status->value : $busLayout->status;
                    $statusName = BusLayoutEnum::getStatusName($statusValue);
                    $statusColor = BusLayoutEnum::getStatusColor($statusValue);
                    return '<span class="badge bg-' . $statusColor . '">' . e($statusName) . '</span>';
                })
                ->addColumn('buses_count', function ($busLayout) {
                    $count = $busLayout->buses()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . $count . ' bus' . ($count !== 1 ? 'es' : '') . '</span>';
                })
                ->addColumn('actions', function ($busLayout) {
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
                                       href="' . route('admin.bus-layouts.edit', $busLayout->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit Bus Layout
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteBusLayout(' . $busLayout->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Bus Layout
                                    </a>
                                </li>
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($busLayout) => $busLayout->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'description_preview', 'seats_info', 'status_badge', 'buses_count', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = BusLayoutEnum::getStatuses();
        $seatTypes = SeatTypeEnum::getSeatTypes();
        $genders = GenderEnum::getGenders();
        return view('admin.bus-layouts.create', compact('statuses', 'seatTypes', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bus_layouts,name|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'total_rows' => 'required|integer|min:1|max:50',
            'total_columns' => 'required|integer|min:1|max:10',
            'seat_map' => 'nullable|array',
            'status' => 'required|string|in:' . implode(',', BusLayoutEnum::getStatuses()),
        ], [
            'name.required' => 'Bus layout name is required',
            'name.string' => 'Bus layout name must be a string',
            'name.max' => 'Bus layout name must be less than 255 characters',
            'name.unique' => 'Bus layout name already exists',
            'name.regex' => 'Bus layout name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'total_rows.required' => 'Total rows is required',
            'total_rows.integer' => 'Total rows must be a number',
            'total_rows.min' => 'Total rows must be at least 1',
            'total_rows.max' => 'Total rows cannot exceed 50',
            'total_columns.required' => 'Total columns is required',
            'total_columns.integer' => 'Total columns must be a number',
            'total_columns.min' => 'Total columns must be at least 1',
            'total_columns.max' => 'Total columns cannot exceed 10',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        // Calculate total seats
        $totalSeats = $validated['total_rows'] * $validated['total_columns'];

        // Create bus layout instance to generate seat map
        $busLayout = new BusLayout([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'total_rows' => $validated['total_rows'],
            'total_columns' => $validated['total_columns'],
            'total_seats' => $totalSeats,
            'status' => $validated['status'],
        ]);

        // Generate default seat map if not provided
        $seatMap = $validated['seat_map'] ?? $busLayout->generateDefaultSeatMap();

        // Validate seat map if provided
        if ($seatMap) {
            $busLayout->seat_map = $seatMap;
            $errors = $busLayout->validateSeatMap();
            if (!empty($errors)) {
                return back()->withErrors(['seat_map' => implode(', ', $errors)])->withInput();
            }
        }

        $busLayout->save();

        return redirect()->route('admin.bus-layouts.index')->with('success', 'Bus layout created successfully');
    }

    public function edit($id)
    {
        $busLayout = BusLayout::findOrFail($id);
        $statuses = BusLayoutEnum::getStatuses();
        $seatTypes = SeatTypeEnum::getSeatTypes();
        $genders = GenderEnum::getGenders();
        return view('admin.bus-layouts.edit', compact('busLayout', 'statuses', 'seatTypes', 'genders'));
    }

    public function update(Request $request, $id)
    {
        $busLayout = BusLayout::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bus_layouts,name,' . $busLayout->id . '|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'description' => 'nullable|string|max:1000',
            'total_rows' => 'required|integer|min:1|max:50',
            'total_columns' => 'required|integer|min:1|max:10',
            'seat_map' => 'nullable|array',
            'status' => 'required|string|in:' . implode(',', BusLayoutEnum::getStatuses()),
        ], [
            'name.required' => 'Bus layout name is required',
            'name.string' => 'Bus layout name must be a string',
            'name.max' => 'Bus layout name must be less than 255 characters',
            'name.unique' => 'Bus layout name already exists',
            'name.regex' => 'Bus layout name can only contain letters, numbers, spaces, hyphens, and underscores',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must be less than 1000 characters',
            'total_rows.required' => 'Total rows is required',
            'total_rows.integer' => 'Total rows must be a number',
            'total_rows.min' => 'Total rows must be at least 1',
            'total_rows.max' => 'Total rows cannot exceed 50',
            'total_columns.required' => 'Total columns is required',
            'total_columns.integer' => 'Total columns must be a number',
            'total_columns.min' => 'Total columns must be at least 1',
            'total_columns.max' => 'Total columns cannot exceed 10',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        // Calculate total seats
        $totalSeats = $validated['total_rows'] * $validated['total_columns'];

        // Update bus layout properties
        $busLayout->name = $validated['name'];
        $busLayout->description = $validated['description'];
        $busLayout->total_rows = $validated['total_rows'];
        $busLayout->total_columns = $validated['total_columns'];
        $busLayout->total_seats = $totalSeats;
        $busLayout->status = $validated['status'];

        // Handle seat map
        if (isset($validated['seat_map'])) {
            $busLayout->seat_map = $validated['seat_map'];
            
            // Validate seat map
            $errors = $busLayout->validateSeatMap();
            if (!empty($errors)) {
                return back()->withErrors(['seat_map' => implode(', ', $errors)])->withInput();
            }
        } else {
            // Generate new seat map if dimensions changed
            if ($busLayout->isDirty(['total_rows', 'total_columns'])) {
                $busLayout->seat_map = $busLayout->generateDefaultSeatMap();
            }
        }

        $busLayout->save();

        return redirect()->route('admin.bus-layouts.index')->with('success', 'Bus layout updated successfully');
    }

    public function destroy($id)
    {
        try {
            $busLayout = BusLayout::findOrFail($id);

            // Check if bus layout has buses assigned
            if ($busLayout->buses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bus layout. It has buses assigned to it.'
                ], 400);
            }

            $busLayout->delete();
            return response()->json([
                'success' => true,
                'message' => 'Bus layout deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting bus layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate seat map based on rows and columns
     */
    public function generateSeatMap(Request $request)
    {
        $request->validate([
            'rows' => 'required|integer|min:1|max:50',
            'columns' => 'required|integer|min:1|max:10',
        ]);

        $busLayout = new BusLayout([
            'total_rows' => $request->rows,
            'total_columns' => $request->columns,
        ]);

        $seatMap = $busLayout->generateDefaultSeatMap();

        return response()->json([
            'success' => true,
            'seat_map' => $seatMap,
            'total_seats' => $request->rows * $request->columns,
        ]);
    }

    /**
     * Update individual seat properties
     */
    public function updateSeat(Request $request, $id)
    {
        $busLayout = BusLayout::findOrFail($id);
        
        $validated = $request->validate([
            'seat_number' => 'required|integer|min:1',
            'seat_type' => 'required|string|in:' . implode(',', SeatTypeEnum::getSeatTypes()),
            'gender' => 'nullable|string|in:' . implode(',', GenderEnum::getGenders()),
            'is_reserved_for_female' => 'boolean',
            'is_available' => 'boolean',
        ]);

        $success = $busLayout->updateSeat($validated['seat_number'], [
            'type' => $validated['seat_type'],
            'gender' => $validated['gender'],
            'is_reserved_for_female' => $validated['is_reserved_for_female'],
            'is_available' => $validated['is_available'],
        ]);

        if ($success) {
            $busLayout->save();
            return response()->json([
                'success' => true,
                'message' => 'Seat updated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Seat not found'
        ], 404);
    }
}
