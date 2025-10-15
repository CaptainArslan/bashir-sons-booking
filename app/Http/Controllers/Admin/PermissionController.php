<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query()
                ->select('id', 'name', 'created_at');

            return DataTables::eloquent($permissions)
                ->addColumn('formatted_name', function ($permission) {
                    return '<span class="fw-bold text-primary">' . e(ucwords(str_replace('_', ' ', $permission->name))) . '</span>';
                })
                ->addColumn('roles_count', function ($permission) {
                    $count = $permission->roles()->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . $count . ' role' . ($count !== 1 ? 's' : '') . '</span>';
                })
                ->addColumn('actions', function ($permission) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    // Edit button
                    if (auth()->user()->can('edit permissions')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="' . route('admin.permissions.edit', $permission->id) . '">
                                <i class="bx bx-edit me-2"></i>Edit Permission
                            </a>
                        </li>';
                    }

                    // Delete button
                    if (auth()->user()->can('delete permissions')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deletePermission(' . $permission->id . ')">
                                <i class="bx bx-trash me-2"></i>Delete Permission
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($permission) => $permission->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_name', 'roles_count', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name|regex:/^[a-z_]+$/',
        ], [
            'name.required' => 'Permission name is required',
            'name.string' => 'Permission name must be a string',
            'name.max' => 'Permission name must be less than 255 characters',
            'name.unique' => 'Permission name already exists',
            'name.regex' => 'Permission name can only contain lowercase letters and underscores',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $roles = Role::all();
        return view('admin.permissions.edit', compact('permission', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id . '|regex:/^[a-z_]+$/',
        ], [
            'name.required' => 'Permission name is required',
            'name.string' => 'Permission name must be a string',
            'name.max' => 'Permission name must be less than 255 characters',
            'name.unique' => 'Permission name already exists',
            'name.regex' => 'Permission name can only contain lowercase letters and underscores',
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully');
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            
            // Check if permission has roles assigned
            if ($permission->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission. It has roles assigned to it.'
                ], 400);
            }

            $permission->delete();
            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting permission: ' . $e->getMessage()
            ], 500);
        }
    }
}