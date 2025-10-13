<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;


class Rolecontroller extends Controller
{
    public function index()
    {
        return view('admin.roles.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::query()
                ->with(['permissions:id,name'])
                ->select('id', 'name', 'created_at');

            return DataTables::eloquent($roles)
                ->addColumn('formatted_name', function ($role) {
                    return '<span class="fw-bold text-primary">' . e(ucwords(str_replace('_', ' ', $role->name))) . '</span>';
                })
                ->addColumn('permissions_list', function ($role) {
                    if ($role->permissions->isEmpty()) {
                        return '<span class="badge bg-secondary">No permissions</span>';
                    }

                    return $role->permissions->map(function ($p) {
                        return '<span class="badge bg-info me-1 mb-1">' . e(ucfirst($p->name)) . '</span>';
                    })->implode('');
                })
                ->addColumn('permissions_count', function ($role) {
                    $count = $role->permissions->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-warning';
                    return '<span class="badge ' . $badgeClass . '">' . $count . ' permission' . ($count !== 1 ? 's' : '') . '</span>';
                })
                ->addColumn('actions', function ($role) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="' . route('admin.roles.edit', $role->id) . '" 
                               class="btn btn-sm btn-outline-primary" 
                               title="Edit Role">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteRole(' . $role->id . ')" 
                                    title="Delete Role">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>';
                })
                ->editColumn('created_at', fn($r) => $r->created_at->format('d M Y'))
                ->escapeColumns([]) // <– ensures HTML isn’t escaped
                ->rawColumns(['formatted_name', 'permissions_list', 'actions', 'permissions_count'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        return view('admin.roles.store');
    }

    public function edit($id)
    {
        return view('admin.roles.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return view('admin.roles.update', compact('id'));
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role has users assigned
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It has users assigned to it.'
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting role: ' . $e->getMessage()
            ], 500);
        }
    }
}
