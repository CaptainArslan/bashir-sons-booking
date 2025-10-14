<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Enums\CityEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
                    return '<span class="fw-bold text-primary">' . e($city->name) . '</span>';
                })
                ->addColumn('status_badge', function ($city) {
                    $statusValue = $city->status instanceof CityEnum ? $city->status->value : $city->status;
                    $statusName = CityEnum::getStatusName($statusValue);
                    $statusColor = CityEnum::getStatusColor($statusValue);
                    return '<span class="badge bg-' . $statusColor . '">' . e($statusName) . '</span>';
                })
                ->addColumn('actions', function ($city) {
                    $actions = '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" 
                                       href="' . route('admin.cities.edit', $city->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit City
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteCity(' . $city->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete City
                                    </a>
                                </li>
                            </ul>
                        </div>';
                    
                    return $actions;
                })
                ->editColumn('created_at', fn($city) => $city->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns(['formatted_name', 'status_badge', 'actions'])
                ->make(true);
        }
    }
}
