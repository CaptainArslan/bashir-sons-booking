<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RouteStop;
use App\Models\Route;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class RouteStopController extends Controller
{
    public function index()
    {
        return view('admin.route-stops.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $routeStops = RouteStop::query()
                ->with(['route:id,name,code', 'terminal:id,name,code,city_id', 'terminal.city:id,name'])
                ->select('id', 'route_id', 'terminal_id', 'sequence', 'distance_from_previous', 'approx_travel_time', 'is_pickup_allowed', 'is_dropoff_allowed', 'created_at');

            return DataTables::eloquent($routeStops)
                ->addColumn('route_info', function ($routeStop) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">' . e($routeStop->route->name) . '</span>
                                <small class="text-muted">Code: ' . e($routeStop->route->code) . '</small>
                            </div>';
                })
                ->addColumn('terminal_info', function ($routeStop) {
                    $cityName = $routeStop->terminal->city ? $routeStop->terminal->city->name : 'Unknown';
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold">' . e($routeStop->terminal->name) . '</span>
                                <small class="text-muted">' . e($cityName) . ' (' . e($routeStop->terminal->code) . ')</small>
                            </div>';
                })
                ->addColumn('sequence_badge', function ($routeStop) {
                    return '<span class="badge bg-primary">' . $routeStop->sequence . '</span>';
                })
                ->addColumn('distance_info', function ($routeStop) {
                    $distance = $routeStop->distance_from_previous ? $routeStop->distance_from_previous . ' km' : '-';
                    $time = $routeStop->approx_travel_time ? $routeStop->approx_travel_time . ' min' : '-';
                    return '<div class="d-flex flex-column">
                                <small>' . $distance . '</small>
                                <small class="text-muted">' . $time . '</small>
                            </div>';
                })
                ->addColumn('services', function ($routeStop) {
                    $pickup = $routeStop->is_pickup_allowed ? '<span class="badge bg-success me-1">Pickup</span>' : '';
                    $dropoff = $routeStop->is_dropoff_allowed ? '<span class="badge bg-info">Dropoff</span>' : '';
                    return $pickup . $dropoff;
                })
                ->addColumn('actions', function ($routeStop) {
                    // $actions = '<div class="dropdown">
                    //     <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                    //             type="button" 
                    //             data-bs-toggle="dropdown" 
                    //             aria-expanded="false">
                    //         <i class="bx bx-dots-horizontal-rounded"></i>
                    //     </button>
                    //     <ul class="dropdown-menu">';

                    // // Edit button
                    // if (auth()->user()->can('edit route stops')) {
                    //     $actions .= '<li>
                    //         <a class="dropdown-item" 
                    //            href="' . route('admin.route-stops.edit', $routeStop->id) . '">
                    //             <i class="bx bx-edit me-2"></i>Edit Stop
                    //         </a>
                    //     </li>';
                    // }

                    // // Delete button
                    // if (auth()->user()->can('delete route stops')) {
                    //     $actions .= '<li><hr class="dropdown-divider"></li>
                    //     <li>
                    //         <a class="dropdown-item text-danger" 
                    //            href="javascript:void(0)" 
                    //            onclick="deleteRouteStop(' . $routeStop->id . ')">
                    //             <i class="bx bx-trash me-2"></i>Delete Stop
                    //         </a>
                    //     </li>';
                    // }

                    // $actions .= '</ul></div>';

                    // return $actions;
                    return '';
                })
                ->editColumn('created_at', fn($routeStop) => $routeStop->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['route_info', 'terminal_info', 'sequence_badge', 'distance_info', 'services', 'actions'])
                ->make(true);
        }
    }
}
