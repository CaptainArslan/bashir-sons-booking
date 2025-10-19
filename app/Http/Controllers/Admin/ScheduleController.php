<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FrequencyTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['route']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        $routes = Route::where('status', 'active')->get();

        return view('admin.schedules.index', compact('routes'));
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

        return view('admin.schedules.create', compact('routes', 'route', 'frequencyTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        DB::beginTransaction();
        try {
            $schedule = Schedule::create([
                'route_id' => $request->route_id,
                'code' => $request->code,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'frequency' => $request->frequency,
                'operating_days' => $request->frequency === FrequencyTypeEnum::CUSTOM->value ? $request->operating_days : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.schedules.show', $schedule)
                ->with('success', 'Schedule created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load([
            'route.routeStops.terminal',
            'stops.routeStop.terminal'
        ]);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load('route.routeStops.terminal');
        $routes = Route::where('status', 'active')->get();
        $frequencyTypes = FrequencyTypeEnum::cases();

        return view('admin.schedules.edit', compact('schedule', 'routes', 'frequencyTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        DB::beginTransaction();
        try {
            $schedule->update([
                'route_id' => $request->route_id,
                'code' => $request->code,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'frequency' => $request->frequency,
                'operating_days' => $request->frequency === FrequencyTypeEnum::CUSTOM->value ? $request->operating_days : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.schedules.show', $schedule)
                ->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        DB::beginTransaction();
        try {
            $schedule->delete();
            DB::commit();

            return redirect()
                ->route('admin.schedules.index')
                ->with('success', 'Schedule deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the schedule.
     */
    public function toggleStatus(Schedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        $status = $schedule->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Schedule {$status} successfully.");
    }

    /**
     * Get data for DataTable
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $schedules = Schedule::with(['route'])
                ->select('id', 'code', 'route_id', 'frequency', 'operating_days', 'is_active', 'created_at');

            return DataTables::eloquent($schedules)
                ->addColumn('formatted_code', function ($schedule) {
                    return '<strong class="text-primary">' . e($schedule->code) . '</strong>';
                })
                ->addColumn('route_info', function ($schedule) {
                    $route = $schedule->route;
                    if (!$route) {
                        return '<span class="text-muted">No route</span>';
                    }
                    return '<div><strong>' . e($route->name) . '</strong><br><small class="text-muted">' . e($route->code) . '</small></div>';
                })
                ->addColumn('formatted_frequency', function ($schedule) {
                    $frequency = $schedule->frequency;
                    $colorClass = '';
                    $name = '';
                    
                    try {
                        $name = $schedule->frequency->getName();
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
                ->addColumn('operating_days_list', function ($schedule) {
                    if ($schedule->frequency === 'custom' && $schedule->operating_days && is_array($schedule->operating_days)) {
                        $daysHtml = '';
                        foreach ($schedule->operating_days as $day) {
                            $daysHtml .= '<span class="badge bg-secondary me-1 mb-1">' . e(ucfirst($day)) . '</span>';
                        }
                        return $daysHtml;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('status_badge', function ($schedule) {
                    if ($schedule->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('formatted_created_at', function ($schedule) {
                    return $schedule->created_at->format('M d, Y');
                })
                ->addColumn('actions', function ($schedule) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';
                    
                    // View action
                    $actions .= '<li>
                        <a class="dropdown-item" href="' . route('admin.schedules.show', $schedule) . '">
                            <i class="bx bx-show me-2"></i>View Details
                        </a>
                    </li>';
                    
                    // Edit action
                    $actions .= '<li>
                        <a class="dropdown-item" href="' . route('admin.schedules.edit', $schedule) . '">
                            <i class="bx bx-edit me-2"></i>Edit Schedule
                        </a>
                    </li>';
                    
                    // Divider
                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    
                    // Toggle status action
                    $actions .= '<li>
                        <form method="POST" action="' . route('admin.schedules.toggle-status', $schedule) . '" class="d-inline">
                            ' . csrf_field() . '
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" class="dropdown-item text-' . ($schedule->is_active ? 'warning' : 'success') . '">
                                <i class="bx bx-' . ($schedule->is_active ? 'pause' : 'play') . ' me-2"></i>
                                ' . ($schedule->is_active ? 'Deactivate' : 'Activate') . '
                            </button>
                        </form>
                    </li>';
                    
                    // Divider
                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    
                    // Delete action
                    $actions .= '<li>
                        <form method="POST" action="' . route('admin.schedules.destroy', $schedule) . '" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this schedule?\')">
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
                ->rawColumns(['formatted_code', 'route_info', 'formatted_frequency', 'operating_days_list', 'status_badge', 'actions'])
                ->make(true);
        }
    }
}
