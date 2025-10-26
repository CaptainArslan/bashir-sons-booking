<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RouteStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.discounts.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(): JsonResponse
    {
        $discounts = Discount::with(['route', 'creator'])->select('discounts.*');

        return DataTables::of($discounts)
            ->addIndexColumn()
            ->addColumn('route_name', function ($discount) {
                return $discount->route ? $discount->route->name : 'N/A';
            })
            ->addColumn('discount_type_badge', function ($discount) {
                $typeClass = match ($discount->discount_type) {
                    'fixed' => 'success',
                    'percentage' => 'warning',
                    default => 'secondary',
                };
                $typeName = match ($discount->discount_type) {
                    'fixed' => 'Fixed',
                    'percentage' => 'Percentage',
                    default => ucfirst($discount->discount_type),
                };

                return '<span class="badge bg-'.$typeClass.'">'.$typeName.'</span>';
            })
            ->addColumn('formatted_value', function ($discount) {
                return $discount->formatted_value;
            })
            ->addColumn('platforms', function ($discount) {
                $platforms = [];
                if ($discount->is_android) {
                    $platforms[] = '<span class="badge bg-primary me-1">Android</span>';
                }
                if ($discount->is_ios) {
                    $platforms[] = '<span class="badge bg-info me-1">iOS</span>';
                }
                if ($discount->is_web) {
                    $platforms[] = '<span class="badge bg-success me-1">Web</span>';
                }
                if ($discount->is_counter) {
                    $platforms[] = '<span class="badge bg-warning me-1">Counter</span>';
                }

                return implode('', $platforms);
            })
            ->addColumn('status_badge', function ($discount) {
                if ($discount->isExpired()) {
                    return '<span class="badge bg-danger">Expired</span>';
                } elseif ($discount->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                } else {
                    return '<span class="badge bg-secondary">Inactive</span>';
                }
            })
            ->addColumn('validity_period', function ($discount) {
                return $discount->starts_at->format('M d, Y').' - '.$discount->ends_at->format('M d, Y');
            })
            ->addColumn('creator_name', function ($discount) {
                return $discount->creator ? $discount->creator->name : 'N/A';
            })
            ->addColumn('actions', function ($discount) {
                $actions = '<div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </button>
                    <ul class="dropdown-menu">';

                $actions .= '<li><a class="dropdown-item" href="'.route('admin.discounts.show', $discount).'">
                    <i class="bx bx-show me-2"></i>View</a></li>';

                $actions .= '<li><a class="dropdown-item" href="'.route('admin.discounts.edit', $discount).'">
                    <i class="bx bx-edit me-2"></i>Edit</a></li>';

                $actions .= '<li><hr class="dropdown-divider"></li>';

                $actions .= '<li><a class="dropdown-item text-'.($discount->is_active ? 'warning' : 'success').'" href="#" onclick="toggleStatus('.$discount->id.', '.($discount->is_active ? 'false' : 'true').')">
                    <i class="bx bx-'.($discount->is_active ? 'pause' : 'play').' me-2"></i>'.($discount->is_active ? 'Deactivate' : 'Activate').'</a></li>';

                $actions .= '<li><a class="dropdown-item text-danger" href="#" onclick="deleteDiscount('.$discount->id.')">
                    <i class="bx bx-trash me-2"></i>Delete</a></li>';

                $actions .= '</ul></div>';

                return $actions;
            })
            ->rawColumns(['discount_type_badge', 'platforms', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $routes = Route::where('status', RouteStatusEnum::ACTIVE->value)->get();

        return view('admin.discounts.create', compact('routes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->user()?->id;

        Discount::create($data);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Discount created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount): View
    {
        $discount->load(['route', 'creator']);

        return view('admin.discounts.show', compact('discount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount): View
    {
        $routes = Route::where('status', RouteStatusEnum::ACTIVE->value)->get();

        return view('admin.discounts.edit', compact('discount', 'routes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountRequest $request, Discount $discount): RedirectResponse
    {
        $data = $request->validated();

        $discount->update($data);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Discount updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount): JsonResponse
    {
        try {
            $discount->delete();

            return response()->json([
                'success' => true,
                'message' => 'Discount deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting discount: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle discount status.
     */
    public function toggleStatus(Request $request, Discount $discount): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $discount->update(['is_active' => $request->is_active]);

        $status = $request->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Discount {$status} successfully!",
            'is_active' => $discount->is_active,
        ]);
    }
}
