<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountTypeEnum;
use App\Enums\FareStatusEnum;
use App\Enums\RouteStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\City;
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
                ->with(['routeStops.terminal:id,name,code'])
                ->select('id', 'code', 'name', 'base_currency', 'status', 'created_at');

            $dataTable = DataTables::eloquent($routes)
                ->addColumn('formatted_name', function ($route) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($route->name).'</span>
                                <small class="text-muted">Code: '.e($route->code).'</small>
                            </div>';
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
                    // if ($hasViewPermission) {
                    //     $actions .= '<li>
                    //         <a class="dropdown-item"
                    //            href="' . route('admin.routes.stops', $route->id) . '">
                    //             <i class="bx bx-map me-2"></i>Manage Stops
                    //         </a>
                    //     </li>';
                    // }

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
                ->rawColumns(
                    $hasAnyActionPermission
                        ? ['formatted_name', 'total_fare', 'stops_count', 'status_badge', 'actions']
                        : ['formatted_name', 'total_fare', 'stops_count', 'status_badge']
                )
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = RouteStatusEnum::getStatusOptions();
        $cities = City::with(['terminals' => function ($query) {
            $query->where('status', 'active')->orderBy('id');
        }])->where('status', 'active')->orderBy('name')->get();
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        return view('admin.routes.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_city_id' => [
                'required',
                'exists:cities,id',
            ],
            'to_city_id' => [
                'required',
                'exists:cities,id',
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'min:3',
                'unique:routes,code',
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
            'from_city_id.required' => 'From city is required',
            'from_city_id.exists' => 'From city does not exist',
            'to_city_id.required' => 'To city is required',
            'to_city_id.exists' => 'To city does not exist',
            'code.required' => 'Route code is required',
            'code.string' => 'Route code must be a string',
            'code.max' => 'Route code cannot exceed 20 characters',
            'code.min' => 'Route code must be at least 3 characters',
            'code.unique' => 'Route code already exists. Please choose a different code',
            'name.required' => 'Route name is required',
            'name.string' => 'Route name must be a string',
            'name.max' => 'Route name cannot exceed 255 characters',
            'name.min' => 'Route name must be at least 3 characters',
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

            $fromCity = City::findOrFail($validated['from_city_id']);
            $toCity = City::findOrFail($validated['to_city_id']);

            $routeCode = $fromCity->code.'-'.$toCity->code;
            $routeName = $fromCity->name.' → '.$toCity->name;

            // Create the route - only include validated fields that exist in the form
            $routeData = [
                'from_city_id' => $validated['from_city_id'],
                'to_city_id' => $validated['to_city_id'],
                'code' => $routeCode,
                'name' => $routeName,
                'base_currency' => 'PKR',
                'status' => $validated['status'],
                'direction' => 'forward',
                'is_return_of' => null,
            ];
            $route = Route::create($routeData);

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
        $route = Route::with(['routeStops.terminal.city', 'fromCity', 'toCity'])->findOrFail($id);
        $statuses = RouteStatusEnum::getStatusOptions();
        $cities = City::where('status', 'active')->orderBy('name')->get();
        $terminals = Terminal::with('city')->where('status', 'active')->get();

        return view('admin.routes.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        $validated = $request->validate([
            'from_city_id' => [
                'required',
                'exists:cities,id',
            ],
            'to_city_id' => [
                'required',
                'exists:cities,id',
                'different:from_city_id',
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
            'from_city_id.required' => 'From city is required',
            'from_city_id.exists' => 'Selected from city does not exist',
            'to_city_id.required' => 'To city is required',
            'to_city_id.exists' => 'Selected to city does not exist',
            'to_city_id.different' => 'From city and To city must be different',
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

            // Get cities
            $fromCity = City::findOrFail($validated['from_city_id']);
            $toCity = City::findOrFail($validated['to_city_id']);

            // Auto-generate route code and name from city codes
            $routeCode = $fromCity->code.'-'.$toCity->code;
            $routeName = $fromCity->name.' → '.$toCity->name;

            // Update the route
            $routeData = [
                'from_city_id' => $validated['from_city_id'],
                'to_city_id' => $validated['to_city_id'],
                'code' => $routeCode,
                'name' => $routeName,
                'base_currency' => 'PKR',
                'status' => $validated['status'],
                'direction' => 'forward',
                'is_return_of' => null,
            ];
            $route->update($routeData);

            // Handle route stops
            $stops = $validated['stops'];
            $existingStopIds = [];
            $newStops = [];

            foreach ($stops as $key => $stopData) {
                if (is_numeric($key)) {
                    // Existing stop (has database ID)
                    $existingStopIds[] = $key;
                    $route->routeStops()->where('id', $key)->update([
                        'terminal_id' => $stopData['terminal_id'],
                        'sequence' => $stopData['sequence'],
                        'online_booking_allowed' => $stopData['online_booking_allowed'] ?? true,
                    ]);
                } elseif (str_starts_with($key, 'new_')) {
                    // New stop (prefixed with "new_")
                    $newStops[] = $stopData;
                }
            }

            // Delete removed stops
            $route->routeStops()->whereNotIn('id', $existingStopIds)->delete();

            // Create new stops
            foreach ($newStops as $stopData) {
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
