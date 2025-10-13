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
                ->withCount('permissions')
                ->with('permissions:id,name')
                ->select('id', 'name', 'created_at');
            
            return DataTables::eloquent($roles)
                ->addColumn('permissions_list', function ($role) {
                    if ($role->permissions->isEmpty()) {
                        return '<span class="badge bg-secondary">No permissions</span>';
                    }
                    
                    $permissionBadges = $role->permissions->map(function ($permission) {
                        return '<span class="badge bg-info me-1 mb-1">' . 
                               e($permission->name) . 
                               '</span>';
                    })->implode('');
                    
                    return $permissionBadges;
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
                        </div>
                    ';
                })
                ->editColumn('created_at', function ($role) {
                    return $role->created_at->format('d M Y');
                })
                ->editColumn('permissions_count', function ($role) {
                    return '<span class="badge bg-primary">' . $role->permissions_count . '</span>';
                })
                ->rawColumns(['permissions_list', 'actions', 'permissions_count'])
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
