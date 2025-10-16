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

            // Filter by route_id if provided
            if ($request->has('route_id') && $request->route_id) {
                $routeFares->where('route_id', $request->route_id);
            }

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
                ->editColumn('created_at', fn($routeFare) => $routeFare->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['route_info', 'route_path', 'fare_info', 'status_badge'])
                ->make(true);
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

    private function validateDiscountLogic(array $data): array
    {
        if (empty($data['discount_type']) || empty($data['discount_value'])) {
            return ['valid' => true, 'message' => ''];
        }

        if ($data['discount_type'] === 'percent') {
            if ($data['discount_value'] > 100) {
                return ['valid' => false, 'message' => 'Discount percentage cannot exceed 100%'];
            }
        } elseif ($data['discount_type'] === 'flat') {
            if ($data['discount_value'] > $data['base_fare']) {
                return ['valid' => false, 'message' => 'Flat discount amount cannot exceed base fare'];
            }
        }

        return ['valid' => true, 'message' => ''];
    }
}
