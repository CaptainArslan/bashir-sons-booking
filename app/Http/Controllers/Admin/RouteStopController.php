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
                ->select('id', 'route_id', 'terminal_id', 'sequence', 'created_at');

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
                ->editColumn('created_at', fn($routeStop) => $routeStop->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['route_info', 'terminal_info', 'sequence_badge'])
                ->make(true);
        }
    }
}
