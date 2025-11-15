<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FareStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Fare;
use App\Models\Route;
use App\Models\Terminal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FareController extends Controller
{
    public function index()
    {
        $routes = Route::with(['fromCity', 'toCity'])
            ->orderBy('name')
            ->get();

        return view('admin.fares.index', compact('routes'));
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $fares = Fare::query()
                ->with([
                    'fromTerminal.city',
                    'toTerminal.city',
                ])
                ->select('id', 'from_terminal_id', 'to_terminal_id', 'base_fare', 'final_fare', 'currency', 'status', 'created_at');

            // Filter by route if provided
            if ($request->has('route_id') && $request->route_id) {
                $route = Route::with('routeStops')->find($request->route_id);
                if ($route) {
                    // Get all terminal IDs for this route
                    $terminalIds = $route->routeStops->pluck('terminal_id')->toArray();

                    // Filter fares where both from_terminal_id and to_terminal_id are in the route's terminals
                    $fares->whereIn('from_terminal_id', $terminalIds)
                        ->whereIn('to_terminal_id', $terminalIds);
                }
            }

            return DataTables::eloquent($fares)
                ->addColumn('route_path', function ($fare) {
                    $fromTerminal = $fare->fromTerminal?->name ?? 'Unknown Terminal';
                    $toTerminal = $fare->toTerminal?->name ?? 'Unknown Terminal';
                    $fromCity = $fare->fromTerminal?->city?->name ?? 'Unknown City';
                    $toCity = $fare->toTerminal?->city?->name ?? 'Unknown City';

                    return '
                    <div class="d-flex flex-column">
                        <span class="fw-bold">'.e($fromTerminal).' → '.e($toTerminal).'</span>
                        <small class="text-muted">'.e($fromCity).' → '.e($toCity).'</small>
                    </div>
                ';
                })

                ->addColumn('fare_info', function ($fare) {
                    $finalFare = $fare->currency.' '.number_format($fare->final_fare, 0);

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-success">'.$finalFare.'</span>
                            </div>';
                })
                ->addColumn('status_badge', function ($fare) {
                    $statusValue = $fare->status instanceof FareStatusEnum ? $fare->status->value : $fare->status;

                    return FareStatusEnum::getStatusBadge($statusValue);
                })
                ->addColumn('actions', function ($fare) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if (auth()->user()->can('edit fares')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.fares.edit', $fare->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Fare
                            </a>
                        </li>';
                    }

                    // Delete button
                    if (auth()->user()->can('delete fares')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteFare('.$fare->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Fare
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($fare) => $fare->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['route_path', 'fare_info', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $terminals = Terminal::with('city')->where('status', 'active')->get();
        $currencies = ['PKR' => 'PKR', 'USD' => 'USD', 'EUR' => 'EUR'];

        return view('admin.fares.create', compact('terminals', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_terminal_id' => [
                'required',
                'exists:terminals,id',
            ],
            'to_terminal_id' => [
                'required',
                'exists:terminals,id',
                'different:from_terminal_id',
            ],
            'base_fare' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
            'currency' => [
                'required',
                'string',
                'in:PKR,USD,EUR',
            ],
        ], [
            'from_terminal_id.required' => 'From terminal is required',
            'from_terminal_id.exists' => 'Selected from terminal does not exist',
            'to_terminal_id.required' => 'To terminal is required',
            'to_terminal_id.exists' => 'Selected to terminal does not exist',
            'to_terminal_id.different' => 'To terminal must be different from from terminal',
            'base_fare.required' => 'Base fare is required',
            'base_fare.integer' => 'Base fare must be a whole number',
            'base_fare.min' => 'Base fare must be at least 1',
            'base_fare.max' => 'Base fare cannot exceed 100,000',
            'currency.required' => 'Currency is required',
            'currency.in' => 'Currency must be PKR, USD, or EUR',
        ]);

        try {
            DB::beginTransaction();

            // Check if fare already exists for this terminal pair
            $existingFare = Fare::where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id'])
                ->first();

            if ($existingFare) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A fare already exists for this terminal pair.');
            }

            // Base fare is the final fare (no discounts)
            $finalFare = $validated['base_fare'];

            // Set fare data
            $fareData = [
                'from_terminal_id' => $validated['from_terminal_id'],
                'to_terminal_id' => $validated['to_terminal_id'],
                'base_fare' => $validated['base_fare'],
                'final_fare' => $finalFare,
                'currency' => $validated['currency'],
                'discount_type' => null,
                'discount_value' => 0,
                'status' => FareStatusEnum::ACTIVE->value,
            ];

            Fare::create($fareData);

            DB::commit();

            return redirect()->route('admin.fares.index')
                ->with('success', 'Fare created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create fare: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $fare = Fare::with(['fromTerminal.city', 'toTerminal.city'])->findOrFail($id);
        $terminals = Terminal::with('city')->where('status', 'active')->get();
        $currencies = ['PKR' => 'PKR', 'USD' => 'USD', 'EUR' => 'EUR'];

        return view('admin.fares.edit', compact('fare', 'terminals', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $fare = Fare::findOrFail($id);

        $validated = $request->validate([
            'from_terminal_id' => [
                'required',
                'exists:terminals,id',
            ],
            'to_terminal_id' => [
                'required',
                'exists:terminals,id',
                'different:from_terminal_id',
            ],
            'base_fare' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
            'currency' => [
                'required',
                'string',
                'in:PKR,USD,EUR',
            ],
        ], [
            'from_terminal_id.required' => 'From terminal is required',
            'from_terminal_id.exists' => 'Selected from terminal does not exist',
            'to_terminal_id.required' => 'To terminal is required',
            'to_terminal_id.exists' => 'Selected to terminal does not exist',
            'to_terminal_id.different' => 'To terminal must be different from from terminal',
            'base_fare.required' => 'Base fare is required',
            'base_fare.integer' => 'Base fare must be a whole number',
            'base_fare.min' => 'Base fare must be at least 1',
            'base_fare.max' => 'Base fare cannot exceed 100,000',
            'currency.required' => 'Currency is required',
            'currency.in' => 'Currency must be PKR, USD, or EUR',
        ]);

        try {
            DB::beginTransaction();

            // Check if fare already exists for this terminal pair (excluding current fare)
            $existingFare = Fare::where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingFare) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A fare already exists for this terminal pair.');
            }

            // Base fare is the final fare (no discounts)
            $finalFare = $validated['base_fare'];

            // Update fare data
            $fare->update([
                'from_terminal_id' => $validated['from_terminal_id'],
                'to_terminal_id' => $validated['to_terminal_id'],
                'base_fare' => $validated['base_fare'],
                'final_fare' => $finalFare,
                'currency' => $validated['currency'],
                'discount_type' => null,
                'discount_value' => 0,
                'status' => FareStatusEnum::ACTIVE->value,
            ]);

            DB::commit();

            return redirect()->route('admin.fares.index')
                ->with('success', 'Fare updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update fare: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorize('delete fares');

            $fare = Fare::findOrFail($id);
            $fare->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fare deleted successfully.',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete fares.',
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fare not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fare: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if fare exists for terminal pair (AJAX)
     */
    public function checkFare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id|different:from_terminal_id',
            'exclude_fare_id' => 'nullable|exists:fares,id',
        ]);

        try {
            $fare = Fare::where('from_terminal_id', $validated['from_terminal_id'])
                ->where('to_terminal_id', $validated['to_terminal_id']);

            // Exclude current fare when editing
            if (! empty($validated['exclude_fare_id'])) {
                $fare->where('id', '!=', $validated['exclude_fare_id']);
            }

            $fare = $fare->first();

            if ($fare) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'fare' => [
                        'id' => $fare->id,
                        'base_fare' => (int) $fare->base_fare,
                        'currency' => $fare->currency,
                        'final_fare' => (int) $fare->final_fare,
                    ],
                    'message' => 'A fare already exists for this terminal pair.',
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'No fare exists for this terminal pair. You can create a new one.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Error checking fare: '.$e->getMessage(),
            ], 500);
        }
    }
}
