<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RouteFare;
use App\Models\Route;
use App\Models\RouteStop;
use App\Enums\RouteFareStatusEnum;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class RouteFareController extends Controller
{
    public function index()
    {
        return view('admin.route-fares.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $routeFares = RouteFare::query()
                ->with([
                    'route:id,name,code',
                    'fromStop.terminal.city',
                    'toStop.terminal.city'
                ])
                ->select('id', 'route_id', 'from_stop_id', 'to_stop_id', 'base_fare', 'discount_type', 'discount_value', 'final_fare', 'status', 'created_at');

            return DataTables::eloquent($routeFares)
                ->addColumn('route_info', function ($routeFare) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">' . e($routeFare->route->name) . '</span>
                                <small class="text-muted">Code: ' . e($routeFare->route->code) . '</small>
                            </div>';
                })
                ->addColumn('route_path', function ($routeFare) {
                    $fromCity = $routeFare->fromStop?->terminal?->city?->name ?? 'Unknown';
                    $toCity = $routeFare->toStop?->terminal?->city?->name ?? 'Unknown';
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold">' . e($fromCity) . ' → ' . e($toCity) . '</span>
                                <small class="text-muted">' . e($routeFare->fromStop?->terminal?->name ?? 'N/A') . ' → ' . e($routeFare->toStop?->terminal?->name ?? 'N/A') . '</small>
                            </div>';
                })
                ->addColumn('fare_info', function ($routeFare) {
                    $baseFare = 'PKR ' . number_format($routeFare->base_fare, 2);
                    $finalFare = 'PKR ' . number_format($routeFare->final_fare, 2);

                    $discountHtml = '';
                    if ($routeFare->discount_type && $routeFare->discount_value) {
                        $discount = $routeFare->discount_type === 'percent'
                            ? $routeFare->discount_value . '%'
                            : 'PKR ' . number_format($routeFare->discount_value, 2);
                        $discountHtml = '<small class="text-success">Discount: ' . $discount . '</small>';
                    }

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-success">' . $finalFare . '</span>
                                <small class="text-muted">Base: ' . $baseFare . '</small>
                                ' . $discountHtml . '
                            </div>';
                })
                ->addColumn('status_badge', function ($routeFare) {
                    $statusValue = $routeFare->status instanceof RouteFareStatusEnum ? $routeFare->status->value : $routeFare->status;
                    return RouteFareStatusEnum::getStatusBadge($statusValue);
                })
                ->addColumn('actions', function ($routeFare) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if (auth()->user()->can('edit route fares')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="' . route('admin.route-fares.edit', $routeFare->id) . '">
                                <i class="bx bx-edit me-2"></i>Edit Fare
                            </a>
                        </li>';
                    }

                    // Delete button
                    if (auth()->user()->can('delete route fares')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteRouteFare(' . $routeFare->id . ')">
                                <i class="bx bx-trash me-2"></i>Delete Fare
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($routeFare) => $routeFare->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['route_info', 'route_path', 'fare_info', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $routes = Route::where('status', RouteFareStatusEnum::ACTIVE->value)->get();
        $routeStops = RouteStop::with(['route', 'terminal.city'])
            ->whereHas('route', function ($query) {
                $query->where('status', RouteFareStatusEnum::ACTIVE->value);
            })
            ->get();
        $statuses = RouteFareStatusEnum::getStatusOptions();

        return view('admin.route-fares.create', compact('routes', 'routeStops', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => [
                'required',
                'exists:routes,id',
            ],
            'from_stop_id' => [
                'required',
                'exists:route_stops,id',
            ],
            'to_stop_id' => [
                'required',
                'exists:route_stops,id',
                'different:from_stop_id',
            ],
            'base_fare' => [
                'required',
                'numeric',
                'min:1',
                'max:100000',
            ],
            'discount_type' => [
                'nullable',
                'string',
                'in:flat,percent',
            ],
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'status' => [
                'required',
                'string',
                'in:' . implode(',', RouteFareStatusEnum::getStatuses()),
            ],
        ], [
            'route_id.required' => 'Route is required',
            'route_id.exists' => 'Selected route does not exist',
            'from_stop_id.required' => 'From stop is required',
            'from_stop_id.exists' => 'Selected from stop does not exist',
            'to_stop_id.required' => 'To stop is required',
            'to_stop_id.exists' => 'Selected to stop does not exist',
            'to_stop_id.different' => 'To stop must be different from from stop',
            'base_fare.required' => 'Base fare is required',
            'base_fare.numeric' => 'Base fare must be a valid number',
            'base_fare.min' => 'Base fare must be at least PKR 1',
            'base_fare.max' => 'Base fare cannot exceed PKR 100,000',
            'discount_type.in' => 'Discount type must be either flat or percent',
            'discount_value.numeric' => 'Discount value must be a valid number',
            'discount_value.min' => 'Discount value cannot be negative',
            'discount_value.max' => 'Discount value cannot exceed 100',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be one of: ' . implode(', ', RouteFareStatusEnum::getStatuses()),
        ]);

        try {
            DB::beginTransaction();

            // Check if stops belong to the selected route
            $fromStop = RouteStop::where('id', $validated['from_stop_id'])
                ->where('route_id', $validated['route_id'])
                ->first();
            $toStop = RouteStop::where('id', $validated['to_stop_id'])
                ->where('route_id', $validated['route_id'])
                ->first();

            if (!$fromStop || !$toStop) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected stops must belong to the selected route.');
            }

            // Check if fare already exists for this route and stops
            if (RouteFare::where('route_id', $validated['route_id'])
                ->where('from_stop_id', $validated['from_stop_id'])
                ->where('to_stop_id', $validated['to_stop_id'])
                ->exists()
            ) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Fare already exists for this route and stops combination.');
            }

            // Calculate final fare
            $finalFare = $this->calculateFinalFare(
                $validated['base_fare'],
                $validated['discount_type'],
                $validated['discount_value']
            );

            $validated['final_fare'] = $finalFare;

            RouteFare::create($validated);

            DB::commit();

            return redirect()->route('admin.route-fares.index')
                ->with('success', 'Route fare created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create route fare: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $routeFare = RouteFare::with(['route', 'fromStop.terminal.city', 'toStop.terminal.city'])
            ->findOrFail($id);
        $routes = Route::where('status', RouteFareStatusEnum::ACTIVE->value)->get();
        $routeStops = RouteStop::with(['route', 'terminal.city'])
            ->whereHas('route', function ($query) {
                $query->where('status', RouteFareStatusEnum::ACTIVE->value);
            })
            ->get();
        $statuses = RouteFareStatusEnum::getStatusOptions();

        return view('admin.route-fares.edit', compact('routeFare', 'routes', 'routeStops', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $routeFare = RouteFare::findOrFail($id);

        $validated = $request->validate([
            'route_id' => [
                'required',
                'exists:routes,id',
            ],
            'from_stop_id' => [
                'required',
                'exists:route_stops,id',
            ],
            'to_stop_id' => [
                'required',
                'exists:route_stops,id',
                'different:from_stop_id',
            ],
            'base_fare' => [
                'required',
                'numeric',
                'min:1',
                'max:100000',
            ],
            'discount_type' => [
                'nullable',
                'string',
                'in:flat,percent',
            ],
            'discount_value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'status' => [
                'required',
                'string',
                'in:' . implode(',', RouteFareStatusEnum::getStatuses()),
            ],
        ], [
            'route_id.required' => 'Route is required',
            'route_id.exists' => 'Selected route does not exist',
            'from_stop_id.required' => 'From stop is required',
            'from_stop_id.exists' => 'Selected from stop does not exist',
            'to_stop_id.required' => 'To stop is required',
            'to_stop_id.exists' => 'Selected to stop does not exist',
            'to_stop_id.different' => 'To stop must be different from from stop',
            'base_fare.required' => 'Base fare is required',
            'base_fare.numeric' => 'Base fare must be a valid number',
            'base_fare.min' => 'Base fare must be at least PKR 1',
            'base_fare.max' => 'Base fare cannot exceed PKR 100,000',
            'discount_type.in' => 'Discount type must be either flat or percent',
            'discount_value.numeric' => 'Discount value must be a valid number',
            'discount_value.min' => 'Discount value cannot be negative',
            'discount_value.max' => 'Discount value cannot exceed 100',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be one of: ' . implode(', ', RouteFareStatusEnum::getStatuses()),
        ]);

        try {
            DB::beginTransaction();

            // Check if stops belong to the selected route
            $fromStop = RouteStop::where('id', $validated['from_stop_id'])
                ->where('route_id', $validated['route_id'])
                ->first();
            $toStop = RouteStop::where('id', $validated['to_stop_id'])
                ->where('route_id', $validated['route_id'])
                ->first();

            if (!$fromStop || !$toStop) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected stops must belong to the selected route.');
            }

            // Check if fare already exists for this route and stops (excluding current record)
            if (RouteFare::where('route_id', $validated['route_id'])
                ->where('from_stop_id', $validated['from_stop_id'])
                ->where('to_stop_id', $validated['to_stop_id'])
                ->where('id', '!=', $id)
                ->exists()
            ) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Fare already exists for this route and stops combination.');
            }

            // Calculate final fare
            $finalFare = $this->calculateFinalFare(
                $validated['base_fare'],
                $validated['discount_type'],
                $validated['discount_value']
            );

            $validated['final_fare'] = $finalFare;

            $routeFare->update($validated);

            DB::commit();

            return redirect()->route('admin.route-fares.index')
                ->with('success', 'Route fare updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update route fare: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $routeFare = RouteFare::findOrFail($id);
            $routeFare->delete();

            return response()->json([
                'success' => true,
                'message' => 'Route fare deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting route fare: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateFinalFare(float $baseFare, ?string $discountType, ?float $discountValue): float
    {
        if (!$discountType || !$discountValue) {
            return $baseFare;
        }

        return match ($discountType) {
            'flat' => max(0, $baseFare - $discountValue),
            'percent' => max(0, $baseFare - ($baseFare * $discountValue / 100)),
            default => $baseFare,
        };
    }

    public function manage()
    {
        $routes = Route::where('status', RouteFareStatusEnum::ACTIVE->value)->get();
        return view('admin.route-fares.manage', compact('routes'));
    }

    public function getRouteStops(Request $request, $routeId)
    {
        $routeStops = RouteStop::with('terminal.city')
            ->where('route_id', $routeId)
            ->orderBy('sequence')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $routeStops->map(function ($stop) {
                return [
                    'id' => $stop->id,
                    'text' => $stop->terminal->name . ' - ' . $stop->terminal->city->name . ' (' . $stop->terminal->code . ')',
                    'sequence' => $stop->sequence,
                ];
            })
        ]);
    }

    public function bulkSave(Request $request)
    {
        $validated = $request->validate([
            'fares' => 'required|array',
            'fares.*.route_id' => 'required|exists:routes,id',
            'fares.*.from_stop_id' => 'required|exists:route_stops,id',
            'fares.*.to_stop_id' => 'required|exists:route_stops,id|different:fares.*.from_stop_id',
            'fares.*.base_fare' => 'required|numeric|min:1|max:100000',
            'fares.*.discount_type' => 'nullable|string|in:flat,percent',
            'fares.*.discount_value' => 'nullable|numeric|min:0|max:100',
            'fares.*.status' => 'required|string|in:' . implode(',', RouteFareStatusEnum::getStatuses()),
        ], [
            'fares.required' => 'Fare data is required',
            'fares.array' => 'Fare data must be an array',
            'fares.*.route_id.required' => 'Route ID is required for each fare',
            'fares.*.route_id.exists' => 'Selected route does not exist',
            'fares.*.from_stop_id.required' => 'From stop is required for each fare',
            'fares.*.from_stop_id.exists' => 'Selected from stop does not exist',
            'fares.*.to_stop_id.required' => 'To stop is required for each fare',
            'fares.*.to_stop_id.exists' => 'Selected to stop does not exist',
            'fares.*.to_stop_id.different' => 'To stop must be different from from stop',
            'fares.*.base_fare.required' => 'Base fare is required for each fare',
            'fares.*.base_fare.numeric' => 'Base fare must be a valid number',
            'fares.*.base_fare.min' => 'Base fare must be at least PKR 1',
            'fares.*.base_fare.max' => 'Base fare cannot exceed PKR 100,000',
            'fares.*.discount_type.in' => 'Discount type must be either flat or percent',
            'fares.*.discount_value.numeric' => 'Discount value must be a valid number',
            'fares.*.discount_value.min' => 'Discount value cannot be negative',
            'fares.*.discount_value.max' => 'Discount value cannot exceed 100',
            'fares.*.status.required' => 'Status is required for each fare',
            'fares.*.status.in' => 'Status must be one of: ' . implode(', ', RouteFareStatusEnum::getStatuses()),
        ]);

        try {
            DB::beginTransaction();

            $savedCount = 0;
            $updatedCount = 0;
            $errors = [];

            foreach ($validated['fares'] as $fareData) {
                try {
                    // Calculate final fare
                    $finalFare = $this->calculateFinalFare(
                        $fareData['base_fare'],
                        $fareData['discount_type'],
                        $fareData['discount_value']
                    );

                    $fareData['final_fare'] = $finalFare;

                    // Check if fare already exists
                    $existingFare = RouteFare::where('route_id', $fareData['route_id'])
                        ->where('from_stop_id', $fareData['from_stop_id'])
                        ->where('to_stop_id', $fareData['to_stop_id'])
                        ->first();

                    if ($existingFare) {
                        // Update existing fare
                        $existingFare->update($fareData);
                        $updatedCount++;
                    } else {
                        // Create new fare
                        RouteFare::create($fareData);
                        $savedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing fare from stop {$fareData['from_stop_id']} to stop {$fareData['to_stop_id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully processed fares. ";
            if ($savedCount > 0) {
                $message .= "Created {$savedCount} new fares. ";
            }
            if ($updatedCount > 0) {
                $message .= "Updated {$updatedCount} existing fares. ";
            }
            if (!empty($errors)) {
                $message .= "Errors: " . implode('; ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fares: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRouteFares(Request $request, $routeId)
    {
        $routeFares = RouteFare::with(['fromStop.terminal.city', 'toStop.terminal.city'])
            ->where('route_id', $routeId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $routeFares->map(function ($fare) {
                return [
                    'id' => $fare->id,
                    'from_stop_id' => $fare->from_stop_id,
                    'to_stop_id' => $fare->to_stop_id,
                    'base_fare' => $fare->base_fare,
                    'discount_type' => $fare->discount_type,
                    'discount_value' => $fare->discount_value,
                    'final_fare' => $fare->final_fare,
                    'status' => $fare->status,
                    'from_stop_name' => $fare->fromStop->terminal->name,
                    'to_stop_name' => $fare->toStop->terminal->name,
                ];
            })
        ]);
    }
}
