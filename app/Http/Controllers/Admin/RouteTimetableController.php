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
}
