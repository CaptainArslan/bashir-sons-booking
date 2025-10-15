<?php

namespace App\Http\Controllers\Admin;

use App\Models\BusLayout;
use App\Enums\BusLayoutEnum;
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
                ->select('id', 'name', 'description', 'total_seats', 'status', 'created_at');

            return DataTables::eloquent($busLayouts)
                ->addColumn('formatted_name', function ($busLayout) {
                    return '<span class="fw-bold text-primary">' . e($busLayout->name) . '</span>';
                })
                ->addColumn('description_preview', function ($busLayout) {
                    return '<span class="text-muted">' . e(\Str::limit($busLayout->description, 100)) . '</span>';
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
        return view('admin.bus-layouts.create', compact('statuses'));
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

        BusLayout::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'total_rows' => $validated['total_rows'],
            'total_columns' => $validated['total_columns'],
            'total_seats' => $totalSeats,
            'seat_map' => $validated['seat_map'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.bus-layouts.index')->with('success', 'Bus layout created successfully');
    }

    public function edit($id)
    {
        $busLayout = BusLayout::findOrFail($id);
        $statuses = BusLayoutEnum::getStatuses();
        return view('admin.bus-layouts.edit', compact('busLayout', 'statuses'));
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

        $busLayout->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'total_rows' => $validated['total_rows'],
            'total_columns' => $validated['total_columns'],
            'total_seats' => $totalSeats,
            'seat_map' => $validated['seat_map'] ?? null,
            'status' => $validated['status'],
        ]);

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
}