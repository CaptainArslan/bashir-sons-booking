<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FrequencyTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRouteTimetableRequest;
use App\Http\Requests\UpdateRouteTimetableRequest;
use App\Models\Route;
use App\Models\RouteTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RouteTimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RouteTimetable::with(['route', 'stops.routeStop.terminal']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $timetables = $query->orderBy('departure_time')->paginate(15);

        $routes = Route::where('status', 'active')->get();

        return view('admin.route-timetables.index', compact('timetables', 'routes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $routeId = $request->route_id;
        $route = null;

        if ($routeId) {
            $route = Route::with('routeStops.terminal')->findOrFail($routeId);
        }

        $routes = Route::where('status', 'active')->get();
        $frequencyTypes = FrequencyTypeEnum::cases();

        return view('admin.route-timetables.create', compact('routes', 'route', 'frequencyTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRouteTimetableRequest $request)
    {

        DB::beginTransaction();
        try {
            $timetable = RouteTimetable::create([
                'route_id' => $request->route_id,
                'trip_code' => $request->trip_code,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'frequency' => $request->frequency,
                'operating_days' => $request->frequency === FrequencyTypeEnum::CUSTOM->value ? $request->operating_days : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.route-timetables.show', $timetable)
                ->with('success', 'Route timetable created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create route timetable: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RouteTimetable $routeTimetable)
    {
        $routeTimetable->load([
            'route.routeStops.terminal',
            'stops.routeStop.terminal'
        ]);

        return view('admin.route-timetables.show', compact('routeTimetable'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RouteTimetable $routeTimetable)
    {
        $routeTimetable->load('route.routeStops.terminal');
        $routes = Route::where('status', 'active')->get();
        $frequencyTypes = FrequencyTypeEnum::cases();

        return view('admin.route-timetables.edit', compact('routeTimetable', 'routes', 'frequencyTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRouteTimetableRequest $request, RouteTimetable $routeTimetable)
    {

        DB::beginTransaction();
        try {
            $routeTimetable->update([
                'route_id' => $request->route_id,
                'trip_code' => $request->trip_code,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'frequency' => $request->frequency,
                'operating_days' => $request->frequency === FrequencyTypeEnum::CUSTOM->value ? $request->operating_days : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.route-timetables.show', $routeTimetable)
                ->with('success', 'Route timetable updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update route timetable: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RouteTimetable $routeTimetable)
    {
        DB::beginTransaction();
        try {
            $routeTimetable->delete();
            DB::commit();

            return redirect()
                ->route('admin.route-timetables.index')
                ->with('success', 'Route timetable deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete route timetable: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the timetable.
     */
    public function toggleStatus(RouteTimetable $routeTimetable)
    {
        $routeTimetable->update(['is_active' => !$routeTimetable->is_active]);

        $status = $routeTimetable->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Route timetable {$status} successfully.");
    }

    /**
     * Get data for DataTable
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $timetables = RouteTimetable::with(['route'])
                ->select('id', 'trip_code', 'route_id', 'departure_time', 'arrival_time', 'frequency', 'operating_days', 'is_active', 'created_at');

            return DataTables::eloquent($timetables)
                ->addColumn('formatted_trip_code', function ($timetable) {
                    return '<strong class="text-primary">' . e($timetable->trip_code) . '</strong>';
                })
                ->addColumn('route_info', function ($timetable) {
                    $route = $timetable->route;
                    if (!$route) {
                        return '<span class="text-muted">No route</span>';
                    }
                    return '<div><strong>' . e($route->name) . '</strong><br><small class="text-muted">' . e($route->code) . '</small></div>';
                })
                ->addColumn('formatted_departure_time', function ($timetable) {
                    return '<span class="badge bg-info">' . e($timetable->departure_time) . '</span>';
                })
                ->addColumn('formatted_arrival_time', function ($timetable) {
                    if ($timetable->arrival_time) {
                        return '<span class="badge bg-success">' . e($timetable->arrival_time) . '</span>';
                    }
                    return '<span class="text-muted">Not set</span>';
                })
                ->addColumn('formatted_frequency', function ($timetable) {
                    $frequency = $timetable->frequency;
                    $colorClass = '';
                    $name = '';
                    
                    try {
                        $name = $timetable->frequency->getName();
                        switch ($frequency) {
                            case 'daily':
                                $colorClass = 'bg-primary';
                                break;
                            case 'weekdays':
                                $colorClass = 'bg-info';
                                break;
                            case 'weekends':
                                $colorClass = 'bg-warning';
                                break;
                            case 'custom':
                                $colorClass = 'bg-secondary';
                                break;
                            default:
                                $colorClass = 'bg-light text-dark';
                        }
                    } catch (\Exception $e) {
                        $name = ucfirst($frequency);
                        $colorClass = 'bg-light text-dark';
                    }
                    
                    return '<span class="badge ' . $colorClass . '">' . e($name) . '</span>';
                })
                ->addColumn('operating_days_list', function ($timetable) {
                    if ($timetable->frequency === 'custom' && $timetable->operating_days && is_array($timetable->operating_days)) {
                        $daysHtml = '';
                        foreach ($timetable->operating_days as $day) {
                            $daysHtml .= '<span class="badge bg-secondary me-1 mb-1">' . e(ucfirst($day)) . '</span>';
                        }
                        return $daysHtml;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('status_badge', function ($timetable) {
                    if ($timetable->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('formatted_created_at', function ($timetable) {
                    return $timetable->created_at->format('M d, Y');
                })
                ->addColumn('actions', function ($timetable) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';
                    
                    // View action
                    $actions .= '<li>
                        <a class="dropdown-item" href="' . route('admin.route-timetables.show', $timetable) . '">
                            <i class="bx bx-show me-2"></i>View Details
                        </a>
                    </li>';
                    
                    // Edit action
                    $actions .= '<li>
                        <a class="dropdown-item" href="' . route('admin.route-timetables.edit', $timetable) . '">
                            <i class="bx bx-edit me-2"></i>Edit Timetable
                        </a>
                    </li>';
                    
                    // Divider
                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    
                    // Toggle status action
                    $actions .= '<li>
                        <form method="POST" action="' . route('admin.route-timetables.toggle-status', $timetable) . '" class="d-inline">
                            ' . csrf_field() . '
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" class="dropdown-item text-' . ($timetable->is_active ? 'warning' : 'success') . '">
                                <i class="bx bx-' . ($timetable->is_active ? 'pause' : 'play') . ' me-2"></i>
                                ' . ($timetable->is_active ? 'Deactivate' : 'Activate') . '
                            </button>
                        </form>
                    </li>';
                    
                    // Divider
                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    
                    // Delete action
                    $actions .= '<li>
                        <form method="POST" action="' . route('admin.route-timetables.destroy', $timetable) . '" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this timetable?\')">
                            ' . csrf_field() . '
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bx bx-trash me-2"></i>Delete
                            </button>
                        </form>
                    </li>';
                    
                    $actions .= '</ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['formatted_trip_code', 'route_info', 'formatted_departure_time', 'formatted_arrival_time', 'formatted_frequency', 'operating_days_list', 'status_badge', 'actions'])
                ->make(true);
        }
    }
}
