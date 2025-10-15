<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\User;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function index()
    {
        return view('admin.routes.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $routes = Route::query()
                ->with(['returnRoute:id,name', 'routeStops.terminal:id,name,code'])
                ->select('id', 'code', 'name', 'direction', 'is_return_of', 'base_currency', 'status', 'created_at');

            return DataTables::eloquent($routes)
                ->addColumn('formatted_name', function ($route) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">' . e($route->name) . '</span>
                                <small class="text-muted">Code: ' . e($route->code) . '</small>
                            </div>';
                })
                ->addColumn('direction_badge', function ($route) {
                    $direction = ucfirst($route->direction);
                    $color = $route->direction === 'forward' ? 'bg-success' : 'bg-warning';
                    return '<span class="badge ' . $color . '">' . e($direction) . '</span>';
                })
                ->addColumn('return_route', function ($route) {
                    if ($route->is_return_of) {
                        return $route->returnRoute ? '<span class="badge bg-secondary">' . e($route->returnRoute->name) . '</span>' : '<span class="text-muted">Unknown</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('stops_count', function ($route) {
                    $count = $route->routeStops()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . $count . ' stop' . ($count !== 1 ? 's' : '') . '</span>';
                })
                ->addColumn('status_badge', function ($route) {
                    $statusValue = $route->status;
                    $statusName = $statusValue ? 'Active' : 'Inactive';
                    $statusColor = $statusValue ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $statusColor . '">' . e($statusName) . '</span>';
                })
                ->addColumn('actions', function ($route) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if (auth()->user()->can('edit routes')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="' . route('admin.routes.edit', $route->id) . '">
                                <i class="bx bx-edit me-2"></i>Edit Route
                            </a>
                        </li>';
                    }

                    // View stops button
                    if (auth()->user()->can('view routes')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="' . route('admin.routes.stops', $route->id) . '">
                                <i class="bx bx-map me-2"></i>Manage Stops
                            </a>
                        </li>';
                    }

                    // Delete button
                    if (auth()->user()->can('delete routes')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteRoute(' . $route->id . ')">
                                <i class="bx bx-trash me-2"></i>Delete Route
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($route) => $route->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'direction_badge', 'return_route', 'stops_count', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $routes = Route::where('status', 'active')->get();
        $currencies = ['PKR'];

        return view('admin.routes.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'min:3',
                'unique:routes,code',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'direction' => [
                'required',
                'string',
                'in:forward,return',
            ],
            'is_return_of' => [
                'nullable',
                'exists:routes,id',
            ],
            'base_currency' => [
                'required',
                'string',
                'in:PKR',
            ],
            'status' => [
                'required',
                'boolean',
            ],
        ], [
            'code.required' => 'Route code is required',
            'code.string' => 'Route code must be a string',
            'code.max' => 'Route code cannot exceed 20 characters',
            'code.min' => 'Route code must be at least 3 characters',
            'code.unique' => 'Route code already exists. Please choose a different code',
            'code.regex' => 'Route code can only contain uppercase letters, numbers, and hyphens',
            'name.required' => 'Route name is required',
            'name.string' => 'Route name must be a string',
            'name.max' => 'Route name cannot exceed 255 characters',
            'name.min' => 'Route name must be at least 3 characters',
            'name.regex' => 'Route name can only contain letters, spaces, hyphens, and periods',
            'direction.required' => 'Direction is required',
            'direction.string' => 'Direction must be a string',
            'direction.in' => 'Direction must be either forward or return',
            'is_return_of.exists' => 'Selected return route is invalid or does not exist',
            'base_currency.required' => 'Base currency is required',
            'base_currency.string' => 'Base currency must be a string',
            'base_currency.in' => 'Base currency must be PKR',
            'status.required' => 'Status is required',
            'status.boolean' => 'Status must be true (Active) or false (Inactive)',
        ]);

        try {
            DB::beginTransaction();

            Route::create($validated);

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create route: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $route = Route::findOrFail($id);
        $routes = Route::where('status', 'active')->where('id', '!=', $id)->get();
        $currencies = ['PKR'];

        return view('admin.routes.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'min:3',
                'unique:routes,code,' . $route->id,
                'regex:/^[A-Z0-9\-]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'regex:/^[a-zA-Z\s\-\.]+$/',
            ],
            'direction' => [
                'required',
                'string',
                'in:forward,return',
            ],
            'is_return_of' => [
                'nullable',
                'exists:routes,id',
            ],
            'base_currency' => [
                'required',
                'string',
                'in:PKR',
            ],
            'status' => [
                'required',
                'boolean',
            ],
        ], [
            'code.required' => 'Route code is required',
            'code.string' => 'Route code must be a string',
            'code.max' => 'Route code cannot exceed 20 characters',
            'code.min' => 'Route code must be at least 3 characters',
            'code.unique' => 'Route code already exists. Please choose a different code',
            'code.regex' => 'Route code can only contain uppercase letters, numbers, and hyphens',
            'name.required' => 'Route name is required',
            'name.string' => 'Route name must be a string',
            'name.max' => 'Route name cannot exceed 255 characters',
            'name.min' => 'Route name must be at least 3 characters',
            'name.regex' => 'Route name can only contain letters, spaces, hyphens, and periods',
            'direction.required' => 'Direction is required',
            'direction.string' => 'Direction must be a string',
            'direction.in' => 'Direction must be either forward or return',
            'is_return_of.exists' => 'Selected return route is invalid or does not exist',
            'base_currency.required' => 'Base currency is required',
            'base_currency.string' => 'Base currency must be a string',
            'base_currency.in' => 'Base currency must be PKR',
            'status.required' => 'Status is required',
            'status.boolean' => 'Status must be true (Active) or false (Inactive)',
        ]);

        try {
            DB::beginTransaction();

            $route->update($validated);

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update route: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $route = Route::findOrFail($id);

            // Check if route has stops
            if ($route->routeStops()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete route. It has stops assigned to it.'
                ], 400);
            }

            $route->delete();
            return response()->json([
                'success' => true,
                'message' => 'Route deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting route: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stops($id)
    {
        $route = Route::with(['routeStops.terminal.city'])->findOrFail($id);
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        return view('admin.routes.stops', get_defined_vars());
    }

    public function storeStop(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        $validated = $request->validate([
            'terminal_id' => [
                'required',
                'exists:terminals,id',
            ],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'distance_from_previous' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'approx_travel_time' => [
                'nullable',
                'integer',
                'min:0',
                'max:1440',
            ],
            'is_pickup_allowed' => [
                'boolean',
            ],
            'is_dropoff_allowed' => [
                'boolean',
            ],
        ], [
            'terminal_id.required' => 'Terminal is required',
            'terminal_id.exists' => 'Selected terminal is invalid or does not exist',
            'sequence.required' => 'Sequence is required',
            'sequence.integer' => 'Sequence must be a whole number',
            'sequence.min' => 'Sequence must be at least 1',
            'sequence.max' => 'Sequence cannot exceed 100',
            'distance_from_previous.numeric' => 'Distance must be a valid number',
            'distance_from_previous.min' => 'Distance cannot be negative',
            'distance_from_previous.max' => 'Distance cannot exceed 10,000 km',
            'approx_travel_time.integer' => 'Travel time must be a whole number (minutes)',
            'approx_travel_time.min' => 'Travel time cannot be negative',
            'approx_travel_time.max' => 'Travel time cannot exceed 24 hours (1440 minutes)',
        ]);

        try {
            DB::beginTransaction();

            // Check if terminal already exists in this route
            if ($route->routeStops()->where('terminal_id', $validated['terminal_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terminal already exists in this route.'
                ], 400);
            }

            $route->routeStops()->create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop added successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stop: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStop(Request $request, $id, $stopId)
    {
        $route = Route::findOrFail($id);
        $stop = $route->routeStops()->findOrFail($stopId);

        $validated = $request->validate([
            'sequence' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'distance_from_previous' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'approx_travel_time' => [
                'nullable',
                'integer',
                'min:0',
                'max:1440',
            ],
            'is_pickup_allowed' => [
                'boolean',
            ],
            'is_dropoff_allowed' => [
                'boolean',
            ],
        ], [
            'sequence.required' => 'Sequence is required',
            'sequence.integer' => 'Sequence must be a whole number',
            'sequence.min' => 'Sequence must be at least 1',
            'sequence.max' => 'Sequence cannot exceed 100',
            'distance_from_previous.numeric' => 'Distance must be a valid number',
            'distance_from_previous.min' => 'Distance cannot be negative',
            'distance_from_previous.max' => 'Distance cannot exceed 10,000 km',
            'approx_travel_time.integer' => 'Travel time must be a whole number (minutes)',
            'approx_travel_time.min' => 'Travel time cannot be negative',
            'approx_travel_time.max' => 'Travel time cannot exceed 24 hours (1440 minutes)',
        ]);

        try {
            DB::beginTransaction();

            $stop->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stop: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyStop($id, $stopId)
    {
        try {
            $route = Route::findOrFail($id);
            $stop = $route->routeStops()->findOrFail($stopId);

            $stop->delete();

            return response()->json([
                'success' => true,
                'message' => 'Stop deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting stop: ' . $e->getMessage()
            ], 500);
        }
    }
}
