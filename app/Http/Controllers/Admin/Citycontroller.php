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
            $cities = City::query()
                ->select('id', 'name', 'status', 'created_at');

            return DataTables::eloquent($cities)
                ->addColumn('formatted_name', function ($city) {
                    return '<span class="fw-bold text-primary">'.e($city->name).'</span>';
                })
                ->addColumn('status_badge', function ($city) {
                    $statusValue = $city->status instanceof CityEnum ? $city->status->value : $city->status;
                    $statusName = CityEnum::getStatusName($statusValue);
                    $statusColor = CityEnum::getStatusColor($statusValue);

                    return '<span class="badge bg-'.$statusColor.'">'.e($statusName).'</span>';
                })
                ->addColumn('actions', function ($city) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if (auth()->user()->can('edit cities')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.cities.edit', $city->id).'">
                                <i class="bx bx-edit me-2"></i>Edit City
                            </a>
                        </li>';
                    }

                    if (auth()->user()->can('delete cities')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteCity('.$city->id.')">
                                <i class="bx bx-trash me-2"></i>Delete City
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($city) => $city->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns(['formatted_name', 'status_badge', 'actions'])
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
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:'.implode(',', CityEnum::getStatuses()),
        ], [
            'name.required' => 'City name is required',
            'name.string' => 'City name must be a string',
            'name.max' => 'City name must be less than 255 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        City::create([
            'name' => $validated['name'],
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
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:'.implode(',', CityEnum::getStatuses()),
        ], [
            'name.required' => 'City name is required',
            'name.string' => 'City name must be a string',
            'name.max' => 'City name must be less than 255 characters',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        $city = City::findOrFail($id);
        $city->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully');
    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully');
    }
}
