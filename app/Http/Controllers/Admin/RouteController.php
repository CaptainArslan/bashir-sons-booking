<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountTypeEnum;
use App\Enums\FareStatusEnum;
use App\Enums\RouteStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Fare;
use App\Models\Route;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $user = auth()->user();
            $hasEditPermission = $user->can('edit routes');
            $hasDeletePermission = $user->can('delete routes');
            $hasViewPermission = $user->can('view routes');
            $hasAnyActionPermission = $hasEditPermission || $hasDeletePermission || $hasViewPermission;

            $routes = Route::query()
                ->with(['returnRoute:id,name', 'routeStops.terminal:id,name,code'])
                ->select('id', 'code', 'name', 'direction', 'is_return_of', 'base_currency', 'status', 'created_at');

            $dataTable = DataTables::eloquent($routes)
                ->addColumn('formatted_name', function ($route) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($route->name).'</span>
                                <small class="text-muted">Code: '.e($route->code).'</small>
                            </div>';
                })
                ->addColumn('direction_badge', function ($route) {
                    $direction = ucfirst($route->direction);
                    $color = $route->direction === 'forward' ? 'bg-success' : 'bg-warning';

                    return '<span class="badge '.$color.'">'.e($direction).'</span>';
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
                        return $fare->from_terminal_id.'-'.$fare->to_terminal_id;
                    });

                    // Generate all possible stop combinations
                    $html = '<div style="max-height: 200px; overflow-y: auto;">';
                    $stopCount = $stops->count();

                    for ($i = 0; $i < $stopCount; $i++) {
                        for ($j = $i + 1; $j < $stopCount; $j++) {
                            $fromStop = $stops[$i];
                            $toStop = $stops[$j];
                            $key = $fromStop->terminal_id.'-'.$toStop->terminal_id;
                            $fare = $fares->get($key);

                            if ($fare) {
                                $html .= '<div class="mb-1"><small>';
                                $html .= '<strong>'.e($fromStop->terminal->code).'</strong> → <strong>'.e($toStop->terminal->code).'</strong>: ';
                                $html .= '<span class="badge bg-primary">'.$fare->final_fare.' '.$fare->currency.'</span>';
                                $html .= '</small></div>';
                            } else {
                                $html .= '<div class="mb-1"><small>';
                                $html .= '<strong>'.e($fromStop->terminal->code).'</strong> → <strong>'.e($toStop->terminal->code).'</strong>: ';
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
                        return $route->returnRoute ? '<span class="badge bg-secondary">'.e($route->returnRoute->name).'</span>' : '<span class="text-muted">Unknown</span>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('stops_count', function ($route) {
                    $count = $route->routeStops()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$badgeClass.'">'.$count.' stop'.($count !== 1 ? 's' : '').'</span>';
                })
                ->addColumn('status_badge', function ($route) {
                    $statusValue = $route->status instanceof RouteStatusEnum ? $route->status->value : $route->status;

                    return RouteStatusEnum::getStatusBadge($statusValue);
                });

            // Only add actions column if user has at least one action permission
            if ($hasAnyActionPermission) {
                $dataTable->addColumn('actions', function ($route) use ($hasEditPermission, $hasDeletePermission, $hasViewPermission) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if ($hasEditPermission) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.routes.edit', $route->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Route
                            </a>
                        </li>';
                    }

                    // View stops button
                    if ($hasViewPermission) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.routes.stops', $route->id).'">
                                <i class="bx bx-map me-2"></i>Manage Stops
                            </a>
                        </li>';
                    }

                    // Manage fares button
                    if ($hasEditPermission) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.routes.manage-fares', $route->id).'">
                                <i class="bx bx-money me-2"></i>Manage Fares
                            </a>
                        </li>';
                    }

                    // Delete button
                    if ($hasDeletePermission) {
                        $needsDivider = $hasEditPermission || $hasViewPermission;
                        if ($needsDivider) {
                            $actions .= '<li><hr class="dropdown-divider"></li>';
                        }
                        $actions .= '<li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteRoute('.$route->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Route
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                });
            }

            return $dataTable
                ->editColumn('created_at', fn ($route) => $route->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns($hasAnyActionPermission
                    ? ['formatted_name', 'direction_badge', 'return_route', 'total_fare', 'stops_count', 'status_badge', 'actions']
                    : ['formatted_name', 'direction_badge', 'return_route', 'total_fare', 'stops_count', 'status_badge']
                )
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
                'in:'.implode(',', RouteStatusEnum::getStatuses()),
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
            'stops.*.online_booking_allowed' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'code.required' => 'Route code is required',
            'code.string' => 'Route code must be a string',
            'code.max' => 'Route code cannot exceed 20 characters',
            'code.min' => 'Route code must be at least 3 characters',
            'code.unique' => 'Route code already exists. Please choose a different code',
            'name.required' => 'Route name is required',
            'name.string' => 'Route name must be a string',
            'name.max' => 'Route name cannot exceed 255 characters',
            'name.min' => 'Route name must be at least 3 characters',
            'direction.required' => 'Direction is required',
            'direction.string' => 'Direction must be a string',
            'direction.in' => 'Direction must be either forward or return',
            'is_return_of.exists' => 'Selected return route is invalid or does not exist',
            'base_currency.required' => 'Base currency is required',
            'base_currency.string' => 'Base currency must be a string',
            'base_currency.in' => 'Base currency must be PKR',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be one of: '.implode(', ', RouteStatusEnum::getStatuses()),
            'stops.required' => 'At least 2 stops are required for a route',
            'stops.min' => 'A route must have at least 2 stops',
            'stops.*.terminal_id.required' => 'Terminal selection is required for each stop',
            'stops.*.terminal_id.exists' => 'Selected terminal does not exist',
            'stops.*.sequence.required' => 'Sequence number is required for each stop',
            'stops.*.sequence.integer' => 'Sequence must be a whole number',
            'stops.*.sequence.min' => 'Sequence must be at least 1',
            'stops.*.sequence.max' => 'Sequence cannot exceed 100',
            'stops.*.online_booking_allowed.boolean' => 'Online booking allowed must be true or false',
        ]);

        try {
            DB::beginTransaction();

            // Create the route
            $route = Route::create($validated);

            // Create route stops
            $stops = $validated['stops'];
            foreach ($stops as $stopData) {
                // Only keep necessary fields
                $route->routeStops()->create([
                    'terminal_id' => $stopData['terminal_id'],
                    'sequence' => $stopData['sequence'],
                    'online_booking_allowed' => $stopData['online_booking_allowed'] ?? true,
                ]);
            }

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route created successfully with '.count($stops).' stops!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create route: '.$e->getMessage());
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
                'unique:routes,code,'.$route->id,
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
                'in:'.implode(',', RouteStatusEnum::getStatuses()),
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
            'stops.*.online_booking_allowed' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'code.required' => 'Route code is required',
            'code.string' => 'Route code must be a string',
            'code.max' => 'Route code cannot exceed 20 characters',
            'code.min' => 'Route code must be at least 3 characters',
            'code.unique' => 'Route code already exists. Please choose a different code',
            'name.required' => 'Route name is required',
            'name.string' => 'Route name must be a string',
            'name.max' => 'Route name cannot exceed 255 characters',
            'name.min' => 'Route name must be at least 3 characters',
            'direction.required' => 'Direction is required',
            'direction.string' => 'Direction must be a string',
            'direction.in' => 'Direction must be either forward or return',
            'is_return_of.exists' => 'Selected return route is invalid or does not exist',
            'base_currency.required' => 'Base currency is required',
            'base_currency.string' => 'Base currency must be a string',
            'base_currency.in' => 'Base currency must be PKR',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be one of: '.implode(', ', RouteStatusEnum::getStatuses()),
            'stops.required' => 'At least 2 stops are required for a route',
            'stops.min' => 'A route must have at least 2 stops',
            'stops.*.terminal_id.required' => 'Terminal selection is required for each stop',
            'stops.*.terminal_id.exists' => 'Selected terminal does not exist',
            'stops.*.sequence.required' => 'Sequence number is required for each stop',
            'stops.*.sequence.integer' => 'Sequence must be a whole number',
            'stops.*.sequence.min' => 'Sequence must be at least 1',
            'stops.*.sequence.max' => 'Sequence cannot exceed 100',
            'stops.*.online_booking_allowed.boolean' => 'Online booking allowed must be true or false',
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
                    // Only update necessary fields
                    $route->routeStops()->where('id', $key)->update([
                        'terminal_id' => $stopData['terminal_id'],
                        'sequence' => $stopData['sequence'],
                        'online_booking_allowed' => $stopData['online_booking_allowed'] ?? true,
                    ]);
                } else {
                    // New stop
                    $newStops[] = $stopData;
                }
            }

            // Delete removed stops
            $route->routeStops()->whereNotIn('id', $existingStopIds)->delete();

            // Create new stops
            foreach ($newStops as $stopData) {
                // Only keep necessary fields
                $route->routeStops()->create([
                    'terminal_id' => $stopData['terminal_id'],
                    'sequence' => $stopData['sequence'],
                    'online_booking_allowed' => $stopData['online_booking_allowed'] ?? true,
                ]);
            }

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route updated successfully with '.count($stops).' stops!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update route: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorize('delete routes');

            $route = Route::findOrFail($id);

            // Check if route has stops
            // if ($route->routeStops()->count() > 0) {
            //     if (request()->expectsJson()) {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Cannot delete route. It has stops assigned to it.',
            //         ], 400);
            //     }

            //     return redirect()->route('admin.routes.index')
            //         ->with('error', 'Cannot delete route. It has stops assigned to it.');
            // }

            // Check if route has timetables associated
            if ($route->timetables()->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete route. It has timetables assigned to it.',
                    ], 400);
                }

                return redirect()->route('admin.routes.index')
                    ->with('error', 'Cannot delete route. It has timetables assigned to it.');
            }

            $route->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Route deleted successfully.',
                ]);
            }

            return redirect()->route('admin.routes.index')
                ->with('success', 'Route deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting route: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.routes.index')
                ->with('error', 'Error deleting route: '.$e->getMessage());
        }
    }

    public function stops($id)
    {
        $route = Route::with(['routeStops.terminal.city'])->findOrFail($id);
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        // Sort stops by sequence
        $route->routeStops = $route->routeStops->sortBy('sequence')->values();

        return view('admin.routes.stops', compact('route', 'terminals'));
    }

    public function updateStops(Request $request, $id)
    {
        try {
            $route = Route::findOrFail($id);

            $validated = $request->validate([
                'stops' => 'required|array|min:1',
                'stops.*.id' => 'required|exists:route_stops,id',
                'stops.*.terminal_id' => 'required|exists:terminals,id',
                'stops.*.sequence' => 'required|integer|min:1',
                'stops.*.online_booking_allowed' => 'sometimes|boolean',
            ], [
                'stops.required' => 'At least one stop is required',
                'stops.min' => 'At least one stop is required',
                'stops.*.id.required' => 'Stop ID is required',
                'stops.*.id.exists' => 'Invalid stop ID',
                'stops.*.terminal_id.required' => 'Terminal is required',
                'stops.*.terminal_id.exists' => 'Selected terminal does not exist',
                'stops.*.sequence.required' => 'Sequence is required',
                'stops.*.sequence.integer' => 'Sequence must be a whole number',
                'stops.*.sequence.min' => 'Sequence must be at least 1',
                'stops.*.online_booking_allowed.boolean' => 'Online booking allowed must be true or false',
            ]);

            DB::beginTransaction();

            // Check for duplicate terminals in the request
            $terminalIds = array_column($validated['stops'], 'terminal_id');
            if (count($terminalIds) !== count(array_unique($terminalIds))) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate terminals found. Each terminal can only appear once in a route.',
                ], 400);
            }

            // Check if all stop IDs belong to this route
            $stopIds = array_column($validated['stops'], 'id');
            $routeStopIds = $route->routeStops()->pluck('id')->toArray();
            $invalidIds = array_diff($stopIds, $routeStopIds);

            if (! empty($invalidIds)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Some stops do not belong to this route.',
                ], 400);
            }

            // Update each stop
            foreach ($validated['stops'] as $stopData) {
                $stop = $route->routeStops()->findOrFail($stopData['id']);
                $stop->update([
                    'terminal_id' => $stopData['terminal_id'],
                    'sequence' => $stopData['sequence'],
                    'online_booking_allowed' => $stopData['online_booking_allowed'] ?? true,
                ]);
            }

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Route stops updated successfully. Sequences have been automatically reordered.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle unique constraint violations (sequence duplication)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                try {
                    DB::beginTransaction();
                    // Retry with reordering
                    $this->reorderRouteStops($route->id);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Route stops updated successfully. Sequences have been automatically reordered.',
                    ]);
                } catch (\Exception $retryException) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to update stops. Please try again.',
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to update stops. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to update stops. Please try again.',
            ], 500);
        }
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
            'online_booking_allowed' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'terminal_id.required' => 'Terminal is required',
            'terminal_id.exists' => 'Selected terminal is invalid or does not exist',
            'sequence.required' => 'Sequence is required',
            'sequence.integer' => 'Sequence must be a whole number',
            'sequence.min' => 'Sequence must be at least 1',
            'sequence.max' => 'Sequence cannot exceed 100',
            'online_booking_allowed.boolean' => 'Online booking allowed must be true or false',
        ]);

        try {
            DB::beginTransaction();

            // Check if terminal already exists in this route
            if ($route->routeStops()->where('terminal_id', $validated['terminal_id'])->exists()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Terminal already exists in this route.',
                ], 400);
            }

            // Only create with necessary fields
            $route->routeStops()->create([
                'terminal_id' => $validated['terminal_id'],
                'sequence' => $validated['sequence'],
                'online_booking_allowed' => $validated['online_booking_allowed'] ?? true,
            ]);

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop added successfully. Sequences have been automatically reordered.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle unique constraint violations (sequence duplication)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                try {
                    DB::beginTransaction();
                    // Retry with reordering
                    $this->reorderRouteStops($route->id);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Stop added successfully. Sequences have been automatically reordered.',
                    ]);
                } catch (\Exception $retryException) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to add stop. Please try again.',
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to add stop. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to add stop. Please try again.',
            ], 500);
        }
    }

    public function updateStop(Request $request, $id, $stopId)
    {
        try {
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
                'online_booking_allowed' => [
                    'sometimes',
                    'boolean',
                ],
            ], [
                'terminal_id.required' => 'Terminal is required',
                'terminal_id.exists' => 'Selected terminal is invalid or does not exist',
                'sequence.required' => 'Sequence is required',
                'sequence.integer' => 'Sequence must be a whole number',
                'sequence.min' => 'Sequence must be at least 1',
                'sequence.max' => 'Sequence cannot exceed 100',
                'online_booking_allowed.boolean' => 'Online booking allowed must be true or false',
            ]);

            DB::beginTransaction();

            // Check if terminal already exists in this route (excluding current stop)
            $existingStop = $route->routeStops()->where('terminal_id', $validated['terminal_id'])->where('id', '!=', $stopId)->first();
            if ($existingStop) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Terminal already exists in this route at sequence '.$existingStop->sequence.'.',
                ], 400);
            }

            $oldSequence = $stop->sequence;
            $newSequence = $validated['sequence'];

            // Update the stop
            $stop->update([
                'terminal_id' => $validated['terminal_id'],
                'sequence' => $newSequence,
                'online_booking_allowed' => $validated['online_booking_allowed'] ?? true,
            ]);

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop updated successfully and sequences have been reordered.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle unique constraint violations (sequence duplication)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                try {
                    DB::beginTransaction();
                    // Retry with reordering
                    $this->reorderRouteStops($route->id);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Stop updated successfully. Sequences have been automatically reordered.',
                    ]);
                } catch (\Exception $retryException) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to update stop. Please try again.',
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to update stop. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to update stop. Please try again.',
            ], 500);
        }
    }

    /**
     * Reorder route stops to ensure sequential numbering (1, 2, 3, ...)
     * This prevents duplicate sequence errors by ensuring each stop has a unique sequential number.
     * Uses temporary sequence values to avoid unique constraint violations during update.
     */
    private function reorderRouteStops(int $routeId): void
    {
        try {
            $stops = Route::findOrFail($routeId)
                ->routeStops()
                ->orderBy('sequence')
                ->orderBy('id')
                ->get();

            // First, set all sequences to temporary high values to avoid unique constraint violations
            // We use a base of 100000 to ensure no conflicts
            $tempBase = 100000;
            foreach ($stops as $index => $stop) {
                $stop->update(['sequence' => $tempBase + $stop->id]);
            }

            // Now update to sequential numbers (1, 2, 3, ...)
            $sequence = 1;
            foreach ($stops as $stop) {
                $stop->update(['sequence' => $sequence]);
                $sequence++;
            }
        } catch (\Exception $e) {
            // Silently handle errors in reordering - don't throw to avoid showing SQL errors
            // The reordering is a cleanup operation, if it fails, the main operation should still succeed
        }
    }

    public function destroyStop($id, $stopId)
    {
        try {
            DB::beginTransaction();

            $route = Route::findOrFail($id);
            $stop = $route->routeStops()->findOrFail($stopId);

            $stop->delete();

            // Reorder all stops to ensure sequential numbering (1, 2, 3, ...)
            $this->reorderRouteStops($route->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop deleted successfully. Sequences have been automatically reordered.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete stop. Please try again.',
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
                    'online_booking_allowed' => $stop->online_booking_allowed,
                    'terminal' => [
                        'id' => $stop->terminal->id,
                        'name' => $stop->terminal->name,
                        'code' => $stop->terminal->code,
                        'city' => $stop->terminal->city->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stop data: '.$e->getMessage(),
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
            return $fare->from_terminal_id.'-'.$fare->to_terminal_id;
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
            // 'fares.*.status' => 'required|in:active,inactive',`
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->fares as $fareData) {
                // Calculate final fare
                $baseFare = $fareData['base_fare'];
                $finalFare = $baseFare;

                if (! empty($fareData['discount_type']) && ! empty($fareData['discount_value'])) {
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
                        'status' => $fareData['status'] ?? FareStatusEnum::ACTIVE->value,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('admin.routes.manage-fares', $id)
                ->with('success', 'Fares updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error updating fares: '.$e->getMessage())
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
                    'distance' => 0, // Distance not tracked in route_stops
                ];
            }
        }

        return collect($combinations);
    }
}
