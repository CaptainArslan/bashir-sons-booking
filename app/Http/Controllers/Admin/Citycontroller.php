<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CityEnum;
use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class Citycontroller extends Controller
{
    public function index()
    {
        return view('admin.cities.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $hasEditPermission = $user->can('edit cities');
            $hasDeletePermission = $user->can('delete cities');
            $hasAnyActionPermission = $hasEditPermission || $hasDeletePermission;

            $cities = City::query()
                ->select('id', 'name', 'code', 'status', 'created_at');

            $dataTable = DataTables::eloquent($cities)
                ->addColumn('formatted_name', function ($city) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">'.e($city->name).'</span>
                                <small class="text-muted">Code: '.e($city->code ?? 'N/A').'</small>
                            </div>';
                })
                ->addColumn('status_badge', function ($city) {
                    $statusValue = $city->status instanceof CityEnum ? $city->status->value : $city->status;
                    $statusName = CityEnum::getStatusName($statusValue);
                    $statusColor = CityEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                });

            // Only add actions column if user has at least one action permission
            if ($hasAnyActionPermission) {
                $dataTable->addColumn('actions', function ($city) use ($hasEditPermission, $hasDeletePermission) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if ($hasEditPermission) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.cities.edit', $city->id).'">
                                <i class="bx bx-edit me-2"></i>Edit City
                            </a>
                        </li>';
                    }

                    if ($hasDeletePermission) {
                        if ($hasEditPermission) {
                            $actions .= '<li><hr class="dropdown-divider"></li>';
                        }
                        $actions .= '<li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteCity('.$city->id.')">
                                <i class="bx bx-trash me-2"></i>Delete City
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                });
            }

            return $dataTable
                ->editColumn('created_at', fn ($city) => $city->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns($hasAnyActionPermission
                    ? ['formatted_name', 'code', 'status_badge', 'actions']
                    : ['formatted_name', 'code', 'status_badge']
                )
                ->make(true);
        }
    }

    public function create()
    {
        $statuses = CityEnum::getStatuses();

        return view('admin.cities.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'code' => 'nullable|string|max:10|unique:cities,code|regex:/^[A-Z0-9]+$/',
            'status' => 'required|string|in:'.implode(',', CityEnum::getStatuses()),
        ], [
            'name.required' => 'City name is required',
            'name.string' => 'City name must be a string',
            'name.max' => 'City name must be less than 255 characters',
            'name.unique' => 'City name must be unique',
            'code.string' => 'City code must be a string',
            'code.max' => 'City code must be less than 10 characters',
            'code.unique' => 'City code must be unique',
            'code.regex' => 'City code must contain only uppercase letters and numbers',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        // Auto-generate code if not provided
        $code = $validated['code'] ?? null;
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']), 0, 3));

            // Ensure uniqueness
            $baseCode = $code;
            $counter = 1;
            while (City::where('code', $code)->exists()) {
                $code = $baseCode.$counter;
                $counter++;
            }
        }

        City::create([
            'name' => $validated['name'],
            'code' => $code,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully');
    }

    public function edit($id)
    {
        $city = City::findOrFail($id);
        $statuses = CityEnum::getStatuses();

        return view('admin.cities.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,'.$id,
            'code' => 'nullable|string|max:10|unique:cities,code,'.$id.'|regex:/^[A-Z0-9]+$/',
            'status' => 'required|string|in:'.implode(',', CityEnum::getStatuses()),
        ], [
            'name.required' => 'City name is required',
            'name.string' => 'City name must be a string',
            'name.max' => 'City name must be less than 255 characters',
            'code.string' => 'City code must be a string',
            'code.max' => 'City code must be less than 10 characters',
            'code.unique' => 'City code must be unique',
            'code.regex' => 'City code must contain only uppercase letters and numbers',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        $city = City::findOrFail($id);

        // Auto-generate code if not provided
        $code = $validated['code'] ?? null;
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']), 0, 3));

            // Ensure uniqueness (exclude current city)
            $baseCode = $code;
            $counter = 1;
            while (City::where('code', $code)->where('id', '!=', $id)->exists()) {
                $code = $baseCode.$counter;
                $counter++;
            }
        }

        $city->update([
            'name' => $validated['name'],
            'code' => $code,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully');
    }

    public function destroy($id)
    {
        try {
            $city = City::findOrFail($id);

            if ($city->terminals->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'City has associated terminals. Please delete the terminals first.',
                    ], 400);
                }

                return redirect()->route('admin.cities.index')->with('error', 'City has associated terminals. Please delete the terminals first.');
            }

            $city->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'City deleted successfully.',
                ]);
            }

            return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting city: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.cities.index')->with('error', 'Error deleting city: '.$e->getMessage());
        }
    }
}
