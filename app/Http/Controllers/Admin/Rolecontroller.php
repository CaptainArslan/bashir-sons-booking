<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;


class Rolecontroller extends Controller
{
    public $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = [
            [
                'title' => 'Roles',
                'url' => route('admin.roles.index')
            ]
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
                    // Define default roles that should not be deletable
                    $defaultRoles = User::DEFAULT_ROLES;
                    $isDefaultRole = in_array($role->name, $defaultRoles);

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
                                       href="' . route('admin.roles.edit', $role->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit Role
                                    </a>
                                </li>';

                    // Only show delete option for non-default roles
                    if (!$isDefaultRole) {
                        $actions .= '
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteRole(' . $role->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Role
                                    </a>
                                </li>';
                    }

                    $actions .= '
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($r) => $r->created_at->format('d M Y'))
                ->escapeColumns([]) // <– ensures HTML isn’t escaped
                ->rawColumns(['formatted_name', 'permissions_list', 'actions', 'permissions_count'])
                ->make(true);
        }
    }

    public function create()
    {
        $permissions = Permission::all();

        return view('admin.roles.create', get_defined_vars());
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
                ->with('success', 'Role "' . $role->name . '" created successfully with ' . count($validated['permissions']) . ' permission(s)!');
        } catch (\Exception $e) {
            DB::rollBack();
            // ✅ 5. Handle any errors during creation
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();

        return view('admin.roles.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();

        return view('admin.roles.update', get_defined_vars());
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
