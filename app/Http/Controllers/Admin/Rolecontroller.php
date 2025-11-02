<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class Rolecontroller extends Controller
{
    public $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = [
            [
                'title' => 'Roles',
                'url' => route('admin.roles.index'),
            ],
        ];
    }

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
                    return '<span class="fw-bold text-primary">'.e(ucwords(str_replace('_', ' ', $role->name))).'</span>';
                })
                ->addColumn('permissions_list', function ($role) {
                    if ($role->permissions->isEmpty()) {
                        return '<span class="badge bg-secondary">No permissions</span>';
                    }

                    return $role->permissions->map(function ($p) {
                        return '<span class="badge bg-info me-1 mb-1">'.e(ucfirst($p->name)).'</span>';
                    })->implode('');
                })
                ->addColumn('permissions_count', function ($role) {
                    $count = $role->permissions->count();
                    $badgeClass = $count > 0 ? 'bg-success' : 'bg-warning';

                    return '<span class="badge '.$badgeClass.'">'.$count.' permission'.($count !== 1 ? 's' : '').'</span>';
                })
                ->addColumn('actions', function ($role) {
                    // Only super_admin role is not editable/deletable
                    $isSuperAdmin = $role->name === 'super_admin';

                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    if (auth()->user()->can('edit roles')) {
                        $actions .= '<li>
                            <a class="dropdown-item" 
                               href="'.route('admin.roles.edit', $role->id).'">
                                <i class="bx bx-edit me-2"></i>Edit Role
                            </a>
                        </li>';
                    }

                    // Only show delete option for non-super_admin roles
                    if (! $isSuperAdmin && auth()->user()->can('delete roles')) {
                        $actions .= '<li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" 
                               href="javascript:void(0)" 
                               onclick="deleteRole('.$role->id.')">
                                <i class="bx bx-trash me-2"></i>Delete Role
                            </a>
                        </li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->editColumn('created_at', fn ($r) => $r->created_at->format('d M Y'))
                ->escapeColumns([]) // <– ensures HTML isn’t escaped
                ->rawColumns(['formatted_name', 'permissions_list', 'actions', 'permissions_count'])
                ->make(true);
        }
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $permissionsByModule = $permissions->groupBy('module');

        return view('admin.roles.create', compact('permissions', 'permissionsByModule'));
    }

    public function store(Request $request)
    {
        // ✅ 1. Validate incoming data
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,id'],
        ], [
            'name.required' => 'Role name is required.',
            'name.unique' => 'A role with this name already exists.',
            'permissions.required' => 'Please select at least one permission.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ]);

        try {
            DB::beginTransaction();
            // ✅ 2. Create the new role
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            // ✅ 3. Sync permissions to the role using IDs
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
            DB::commit();

            // ✅ 4. Return success response
            return redirect()->route('admin.roles.index')
                ->with('success', 'Role "'.$role->name.'" created successfully with '.count($validated['permissions']).' permission(s)!');
        } catch (\Exception $e) {
            DB::rollBack();

            // ✅ 5. Handle any errors during creation
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $permissionsByModule = $permissions->groupBy('module');

        // Only super_admin role is not editable
        $isDefaultRole = $role->name === 'super_admin';

        return view('admin.roles.edit', compact('role', 'permissions', 'permissionsByModule', 'isDefaultRole'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,id'],
        ], [
            'name.required' => 'Role name is required.',
            'name.unique' => 'A role with this name already exists.',
            'permissions.required' => 'Please select at least one permission.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ]);

        // Only super_admin role is not editable
        abort_if($role->name === 'super_admin', 403, 'Cannot edit super_admin role.');

        try {
            DB::beginTransaction();
            $role->update([
                'name' => $validated['name'],
            ]);

            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role "'.$role->name.'" updated successfully with '.count($validated['permissions']).' permission(s)!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update role: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevent deletion of super_admin role
            if ($role->name === 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete super_admin role.',
                ], 403);
            }

            // Check if role has users assigned
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It has users assigned to it.',
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting role: '.$e->getMessage(),
            ], 500);
        }
    }
}
