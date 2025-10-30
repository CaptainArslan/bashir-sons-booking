<?php

namespace App\Http\Controllers\Admin;

use App\Models\Fare;
use App\Models\Route;
use App\Models\Terminal;
use Illuminate\Http\Request;
use App\Enums\RouteStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\DiscountTypeEnum;
use Yajra\DataTables\Facades\DataTables;

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
                ->addColumn('total_fare', function ($route) {
                    // Get all stops for this route
                    $stops = $route->routeStops()->orderBy('sequence')->get();
                    
                    if ($stops->isEmpty()) {
                        return '<span class="badge bg-secondary">No stops</span>';
                    }

                    // Get all fares for this route
                    $fares = Fare::where(function ($query) use ($stops) {
                        $terminalIds = $stops->pluck('terminal_id')->toArray();
                        $query->whereIn('from_terminal_id', $terminalIds)
                              ->whereIn('to_terminal_id', $terminalIds);
                    })->get()->keyBy(function ($fare) {
                        return $fare->from_terminal_id . '-' . $fare->to_terminal_id;
                    });

                    // Generate all possible stop combinations
                    $html = '<div style="max-height: 200px; overflow-y: auto;">';
                    $stopCount = $stops->count();
                    
                    for ($i = 0; $i < $stopCount; $i++) {
                        for ($j = $i + 1; $j < $stopCount; $j++) {
                            $fromStop = $stops[$i];
                            $toStop = $stops[$j];
                            $key = $fromStop->terminal_id . '-' . $toStop->terminal_id;
                            $fare = $fares->get($key);
                            
                            if ($fare) {
                                $html .= '<div class="mb-1"><small>';
                                $html .= '<strong>' . e($fromStop->terminal->code) . '</strong> → <strong>' . e($toStop->terminal->code) . '</strong>: ';
                                $html .= '<span class="badge bg-primary">' . $fare->final_fare . ' ' . $fare->currency . '</span>';
                                $html .= '</small></div>';
                            } else {
                                $html .= '<div class="mb-1"><small>';
                                $html .= '<strong>' . e($fromStop->terminal->code) . '</strong> → <strong>' . e($toStop->terminal->code) . '</strong>: ';
                                $html .= '<span class="badge bg-danger" title="No fare configured">❌ Not Set</span>';
                                $html .= '</small></div>';
                            }
                        }
                    }
                    
                    $html .= '</div>';
                    return $html;
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
                    $statusValue = $route->status instanceof RouteStatusEnum ? $route->status->value : $route->status;
                    return RouteStatusEnum::getStatusBadge($statusValue);
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

                    // Manage fares button
                    if (auth()->user()->can('edit routes')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="' . route('admin.routes.manage-fares', $route->id) . '">
                                <i class="bx bx-money me-2"></i>Manage Fares
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
                ->rawColumns(['formatted_name', 'direction_badge', 'return_route', 'total_fare', 'stops_count', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $routes = Route::where('status', RouteStatusEnum::ACTIVE->value)->get();
        $currencies = ['PKR'];
        $statuses = RouteStatusEnum::getStatusOptions();
        $terminals = Terminal::with('city')->where('status', 'active')->get();

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
                'string',
                'in:' . implode(',', RouteStatusEnum::getStatuses()),
            ],
            'stops' => [
                'required',
                'array',
                'min:2',
            ],
            'stops.*.terminal_id' => [
                'required',
                'exists:terminals,id',
            ],
            'stops.*.sequence' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'stops.*.distance_from_previous' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'stops.*.approx_travel_time' => [
                'nullable',
                'integer',
                'min:0',
                'max:1440',
            ],
            'stops.*.is_pickup_allowed' => [
                'boolean',
            ],
            'stops.*.is_dropoff_allowed' => [
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
            'status.in' => 'Status must be one of: ' . implode(', ', RouteStatusEnum::getStatuses()),
            'stops.required' => 'At least 2 stops are required for a route',
            'stops.min' => 'A route must have at least 2 stops',
            'stops.*.terminal_id.required' => 'Terminal selection is required for each stop',
            'stops.*.terminal_id.exists' => 'Selected terminal does not exist',
            'stops.*.sequence.required' => 'Sequence number is required for each stop',
            'stops.*.sequence.integer' => 'Sequence must be a whole number',
            'stops.*.sequence.min' => 'Sequence must be at least 1',
            'stops.*.sequence.max' => 'Sequence cannot exceed 100',
            'stops.*.distance_from_previous.numeric' => 'Distance must be a valid number',
            'stops.*.distance_from_previous.min' => 'Distance cannot be negative',
            'stops.*.distance_from_previous.max' => 'Distance cannot exceed 10,000 km',
            'stops.*.approx_travel_time.integer' => 'Travel time must be a whole number (minutes)',
            'stops.*.approx_travel_time.min' => 'Travel time cannot be negative',
            'stops.*.approx_travel_time.max' => 'Travel time cannot exceed 24 hours (1440 minutes)',
        ]);

        try {
            DB::beginTransaction();

            // Create the route
            $route = Route::create($validated);

            // Create route stops
            $stops = $validated['stops'];
            foreach ($stops as $stopData) {
                $stopData['is_pickup_allowed'] = isset($stopData['is_pickup_allowed']);
                $stopData['is_dropoff_allowed'] = isset($stopData['is_dropoff_allowed']);
                $route->routeStops()->create($stopData);
            }

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route created successfully with ' . count($stops) . ' stops!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create route: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $route = Route::with(['routeStops.terminal.city'])->findOrFail($id);
        $routes = Route::where('status', RouteStatusEnum::ACTIVE->value)->where('id', '!=', $id)->get();
        $currencies = ['PKR'];
        $statuses = RouteStatusEnum::getStatusOptions();
        $terminals = Terminal::with('city')->where('status', 'active')->get();

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
                'string',
                'in:' . implode(',', RouteStatusEnum::getStatuses()),
            ],
            'stops' => [
                'required',
                'array',
                'min:2',
            ],
            'stops.*.terminal_id' => [
                'required',
                'exists:terminals,id',
            ],
            'stops.*.sequence' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'stops.*.distance_from_previous' => [
                'nullable',
                'numeric',
                'min:0',
                'max:10000',
            ],
            'stops.*.approx_travel_time' => [
                'nullable',
                'integer',
                'min:0',
                'max:1440',
            ],
            'stops.*.is_pickup_allowed' => [
                'boolean',
            ],
            'stops.*.is_dropoff_allowed' => [
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
            'status.in' => 'Status must be one of: ' . implode(', ', RouteStatusEnum::getStatuses()),
            'stops.required' => 'At least 2 stops are required for a route',
            'stops.min' => 'A route must have at least 2 stops',
            'stops.*.terminal_id.required' => 'Terminal selection is required for each stop',
            'stops.*.terminal_id.exists' => 'Selected terminal does not exist',
            'stops.*.sequence.required' => 'Sequence number is required for each stop',
            'stops.*.sequence.integer' => 'Sequence must be a whole number',
            'stops.*.sequence.min' => 'Sequence must be at least 1',
            'stops.*.sequence.max' => 'Sequence cannot exceed 100',
            'stops.*.distance_from_previous.numeric' => 'Distance must be a valid number',
            'stops.*.distance_from_previous.min' => 'Distance cannot be negative',
            'stops.*.distance_from_previous.max' => 'Distance cannot exceed 10,000 km',
            'stops.*.approx_travel_time.integer' => 'Travel time must be a whole number (minutes)',
            'stops.*.approx_travel_time.min' => 'Travel time cannot be negative',
            'stops.*.approx_travel_time.max' => 'Travel time cannot exceed 24 hours (1440 minutes)',
        ]);

        try {
            DB::beginTransaction();

            // Update the route
            $route->update($validated);

            // Handle route stops
            $stops = $validated['stops'];
            $existingStopIds = [];
            $newStops = [];

            foreach ($stops as $key => $stopData) {
                if (is_numeric($key)) {
                    // Existing stop
                    $existingStopIds[] = $key;
                    $stopData['is_pickup_allowed'] = isset($stopData['is_pickup_allowed']);
                    $stopData['is_dropoff_allowed'] = isset($stopData['is_dropoff_allowed']);
                    $route->routeStops()->where('id', $key)->update($stopData);
                } else {
                    // New stop
                    $newStops[] = $stopData;
                }
            }

            // Delete removed stops
            $route->routeStops()->whereNotIn('id', $existingStopIds)->delete();

            // Create new stops
            foreach ($newStops as $stopData) {
                $stopData['is_pickup_allowed'] = isset($stopData['is_pickup_allowed']);
                $stopData['is_dropoff_allowed'] = isset($stopData['is_dropoff_allowed']);
                $route->routeStops()->create($stopData);
            }

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route updated successfully with ' . count($stops) . ' stops!');
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

        return view('admin.routes.stops', compact('route', 'terminals'));
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

            // Handle checkbox values
            $validated['is_pickup_allowed'] = $request->has('is_pickup_allowed');
            $validated['is_dropoff_allowed'] = $request->has('is_dropoff_allowed');

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

            // Check if terminal already exists in this route (excluding current stop)
            if ($route->routeStops()->where('terminal_id', $validated['terminal_id'])->where('id', '!=', $stopId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terminal already exists in this route.'
                ], 400);
            }

            // Handle checkbox values
            $validated['is_pickup_allowed'] = $request->has('is_pickup_allowed');
            $validated['is_dropoff_allowed'] = $request->has('is_dropoff_allowed');

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

    public function getStopData($id, $stopId)
    {
        try {
            $route = Route::findOrFail($id);
            $stop = $route->routeStops()->with('terminal.city')->findOrFail($stopId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $stop->id,
                    'sequence' => $stop->sequence,
                    'distance_from_previous' => $stop->distance_from_previous,
                    'approx_travel_time' => $stop->approx_travel_time,
                    'is_pickup_allowed' => $stop->is_pickup_allowed,
                    'is_dropoff_allowed' => $stop->is_dropoff_allowed,
                    'terminal' => [
                        'id' => $stop->terminal->id,
                        'name' => $stop->terminal->name,
                        'code' => $stop->terminal->code,
                        'city' => $stop->terminal->city->name,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stop data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manageFares($id)
    {
        $route = Route::with(['routeStops.terminal.city'])->findOrFail($id);

        // Get all possible combinations of stops for this route
        $stops = $route->routeStops()->with('terminal.city')->orderBy('sequence')->get();
        $stopCombinations = $this->generateStopCombinations($stops);

        // Get existing fares for this route
        $existingFares = Fare::forRoute($id)->get()->keyBy(function ($fare) {
            return $fare->from_terminal_id . '-' . $fare->to_terminal_id;
        });

        return view('admin.routes.manage-fares', compact('route', 'stops', 'stopCombinations', 'existingFares'));
    }

    public function storeFares(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        $request->validate([
            'fares' => 'required|array',
            'fares.*.from_terminal_id' => 'required|exists:terminals,id',
            'fares.*.to_terminal_id' => 'required|exists:terminals,id',
            'fares.*.base_fare' => 'required|numeric|min:0',
            'fares.*.discount_type' => 'nullable|in:flat,percent',
            'fares.*.discount_value' => 'nullable|numeric|min:0',
            'fares.*.currency' => 'required|string|max:3',
            'fares.*.status' => 'required|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->fares as $fareData) {
                // Calculate final fare
                $baseFare = $fareData['base_fare'];
                $finalFare = $baseFare;

                if (!empty($fareData['discount_type']) && !empty($fareData['discount_value'])) {
                    if ($fareData['discount_type'] === 'flat') {
                        $finalFare = max(0, $baseFare - $fareData['discount_value']);
                    } elseif ($fareData['discount_type'] === 'percent') {
                        $finalFare = max(0, $baseFare - ($baseFare * $fareData['discount_value'] / 100));
                    }
                }

                // Update or create fare
                Fare::updateOrCreate(
                    [
                        'from_terminal_id' => $fareData['from_terminal_id'],
                        'to_terminal_id' => $fareData['to_terminal_id'],
                    ],
                    [
                        'base_fare' => $baseFare,
                        'discount_type' => $fareData['discount_type'] ?? DiscountTypeEnum::FLAT->value,
                        'discount_value' => $fareData['discount_value'] ?? 0,
                        'final_fare' => $finalFare,
                        'currency' => $fareData['currency'],
                        'status' => $fareData['status'],
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.routes.manage-fares', $id)
                ->with('success', 'Fares updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating fares: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function generateStopCombinations($stops)
    {
        $combinations = [];
        $stopCount = $stops->count();

        for ($i = 0; $i < $stopCount; $i++) {
            for ($j = $i + 1; $j < $stopCount; $j++) {
                $fromStop = $stops[$i];
                $toStop = $stops[$j];

                $combinations[] = [
                    'from_terminal_id' => $fromStop->terminal_id,
                    'to_terminal_id' => $toStop->terminal_id,
                    'from_terminal' => $fromStop->terminal,
                    'to_terminal' => $toStop->terminal,
                    'from_sequence' => $fromStop->sequence,
                    'to_sequence' => $toStop->sequence,
                    'distance' => $this->calculateDistanceBetweenStops($stops, $i, $j),
                ];
            }
        }

        return collect($combinations);
    }

    private function calculateDistanceBetweenStops($stops, $fromIndex, $toIndex)
    {
        $distance = 0;
        for ($i = $fromIndex; $i < $toIndex; $i++) {
            $distance += $stops[$i]->distance_from_previous ?? 0;
        }
        return $distance;
    }
}
