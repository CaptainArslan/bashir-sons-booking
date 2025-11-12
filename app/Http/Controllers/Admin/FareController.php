<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountTypeEnum;
use App\Enums\FareStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Fare;
use App\Models\Terminal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FareController extends Controller
{
    public function index()
    {
        return view('admin.fares.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $fares = Fare::query()
                ->with([
                    'fromTerminal.city',
                    'toTerminal.city',
                ])
                ->select('id', 'from_terminal_id', 'to_terminal_id', 'base_fare', 'discount_type', 'discount_value', 'final_fare', 'currency', 'status', 'created_at');

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
                    $baseFare = $fare->currency.' '.number_format($fare->base_fare, 0);
                    $finalFare = $fare->currency.' '.number_format($fare->final_fare, 0);

                    $discountHtml = '';
                    if ($fare->discount_type && $fare->discount_value > 0) {
                        $discount = $fare->discount_type === DiscountTypeEnum::PERCENT->value
                            ? $fare->discount_value.'%'
                            : $fare->currency.' '.number_format($fare->discount_value, 0);
                        $discountHtml = '<small class="text-success">Discount: '.$discount.'</small>';
                    }

                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-success">'.$finalFare.'</span>
                                <small class="text-muted">Base: '.$baseFare.'</small>
                                '.$discountHtml.'
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
        $discountTypes = ['flat' => 'Flat Amount', 'percent' => 'Percentage'];
        $currencies = ['PKR' => 'PKR', 'USD' => 'USD', 'EUR' => 'EUR'];

        return view('admin.fares.create', compact('terminals', 'discountTypes', 'currencies'));
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
            'discount_type' => [
                'nullable',
                'string',
                'in:'.implode(',', ['flat', 'percent']),
            ],
            'discount_value' => [
                'nullable',
                'integer',
                'min:0',
                'required_if:discount_type,flat,percent',
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
            'discount_type.in' => 'Discount type must be either flat or percent',
            'discount_value.integer' => 'Discount value must be a whole number',
            'discount_value.min' => 'Discount value cannot be negative',
            'discount_value.required_if' => 'Discount value is required when discount type is selected',
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

            // Calculate final fare
            $finalFare = $this->calculateFinalFare(
                $validated['base_fare'],
                $validated['discount_type'] ?? null,
                $validated['discount_value'] ?? null
            );

            // Always set status as active
            $validated['final_fare'] = $finalFare;
            $validated['status'] = FareStatusEnum::ACTIVE->value;

            Fare::create($validated);

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
        $discountTypes = ['flat' => 'Flat Amount', 'percent' => 'Percentage'];
        $currencies = ['PKR' => 'PKR', 'USD' => 'USD', 'EUR' => 'EUR'];

        return view('admin.fares.edit', compact('fare', 'terminals', 'discountTypes', 'currencies'));
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
            'discount_type' => [
                'nullable',
                'string',
                'in:'.implode(',', ['flat', 'percent']),
            ],
            'discount_value' => [
                'nullable',
                'integer',
                'min:0',
                'required_if:discount_type,flat,percent',
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
            'discount_type.in' => 'Discount type must be either flat or percent',
            'discount_value.integer' => 'Discount value must be a whole number',
            'discount_value.min' => 'Discount value cannot be negative',
            'discount_value.required_if' => 'Discount value is required when discount type is selected',
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

            // Calculate final fare
            $finalFare = $this->calculateFinalFare(
                $validated['base_fare'],
                $validated['discount_type'] ?? null,
                $validated['discount_value'] ?? null
            );

            // Always keep status as active (users cannot change it)
            $validated['final_fare'] = $finalFare;
            $validated['status'] = FareStatusEnum::ACTIVE->value;

            $fare->update($validated);

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
                        'discount_type' => $fare->discount_type?->value,
                        'discount_value' => (int) $fare->discount_value,
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

    private function calculateFinalFare(int $baseFare, ?string $discountType, ?int $discountValue): int
    {
        if (! $discountType || ! $discountValue || $discountValue <= 0) {
            return $baseFare;
        }

        return match ($discountType) {
            DiscountTypeEnum::FLAT->value => max(0, $baseFare - $discountValue),
            DiscountTypeEnum::PERCENT->value => max(0, (int) round($baseFare - ($baseFare * $discountValue / 100))),
            default => $baseFare,
        };
    }
}
