<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query()
                ->with(['roles:id,name'])
                ->select('id', 'name', 'email', 'created_at');

            // Filter by user type if specified
            if ($request->has('type') && $request->type !== 'all') {
                $users->whereHas('roles', function ($query) use ($request) {
                    $query->where('name', $request->type);
                });
            }

            return DataTables::eloquent($users)
                ->addColumn('formatted_name', function ($user) {
                    return '<span class="fw-bold text-primary">' . e($user->name) . '</span>';
                })
                ->addColumn('user_type', function ($user) {
                    $roles = $user->roles->pluck('name')->toArray();
                    if (empty($roles)) {
                        return '<span class="badge bg-secondary">No Role</span>';
                    }
                    
                    $badges = '';
                    foreach ($roles as $role) {
                        $color = match($role) {
                            'super_admin' => 'danger',
                            'admin' => 'warning',
                            'employee' => 'info',
                            'customer' => 'success',
                            default => 'secondary'
                        };
                        $badges .= '<span class="badge bg-' . $color . ' me-1">' . e(ucfirst($role)) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('roles_list', function ($user) {
                    if ($user->roles->isEmpty()) {
                        return '<span class="text-muted">No roles assigned</span>';
                    }
                    return $user->roles->map(function ($role) {
                        return '<span class="badge bg-info me-1 mb-1">' . e(ucfirst($role->name)) . '</span>';
                    })->implode('');
                })
                ->addColumn('actions', function ($user) {
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
                                       href="' . route('admin.users.edit', $user->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit User
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteUser(' . $user->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete User
                                    </a>
                                </li>
                            </ul>
                        </div>';
                    
                    return $actions;
                })
                ->editColumn('created_at', fn($user) => $user->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns(['formatted_name', 'user_type', 'roles_list', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'roles.required' => 'Please select at least one role',
            'roles.min' => 'Please select at least one role',
            'roles.*.exists' => 'One or more selected roles are invalid',
        ]);

        try {
            DB::beginTransaction();
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign roles
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->assignRole($roles);
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully with ' . count($validated['roles']) . ' role(s)!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('admin.users.edit', get_defined_vars());
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'password.confirmed' => 'Password confirmation does not match',
            'roles.required' => 'Please select at least one role',
            'roles.min' => 'Please select at least one role',
            'roles.*.exists' => 'One or more selected roles are invalid',
        ]);

        try {
            DB::beginTransaction();
            
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Sync roles
            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deletion of super admin users
            if ($user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete super admin user.'
                ], 400);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }
}
