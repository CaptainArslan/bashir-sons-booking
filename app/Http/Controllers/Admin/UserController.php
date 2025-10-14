<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\profile;
use App\Enums\GenderEnum;
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
                ->with(['roles:id,name', 'profile'])
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
                ->addColumn('contact_info', function ($user) {
                    $profile = $user->profile;
                    if (!$profile) {
                        return '<span class="text-muted">No profile</span>';
                    }
                    
                    $phone = $profile->phone ? '<div><i class="bx bx-phone me-1"></i>' . e($profile->phone) . '</div>' : '';
                    $cnic = $profile->cnic ? '<div><i class="bx bx-id-card me-1"></i>' . e($profile->cnic) . '</div>' : '';
                    return $phone . $cnic;
                })
                ->addColumn('profile_info', function ($user) {
                    $profile = $user->profile;
                    if (!$profile) {
                        return '<span class="text-muted">No profile</span>';
                    }
                    
                    $gender = $profile->gender ? 
                        '<span class="badge bg-' . ($profile->gender->value === 'male' ? 'primary' : ($profile->gender->value === 'female' ? 'danger' : 'secondary')) . '">' . 
                        e(GenderEnum::getGenderName($profile->gender->value)) . '</span>' : '';
                    
                    $dob = $profile->date_of_birth ? 
                        '<div class="mt-1"><i class="bx bx-calendar me-1"></i>' . e($profile->date_of_birth->format('d M Y')) . '</div>' : '';
                    
                    return $gender . $dob;
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
                ->rawColumns(['formatted_name', 'contact_info', 'profile_info', 'user_type', 'roles_list', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $roles = Role::all();
        $genders = GenderEnum::getGenders();
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
            // Profile fields
            'phone' => ['required', 'string', 'max:20'],
            'cnic' => ['required', 'string', 'max:15', 'unique:user_profiles,cnic'],
            'gender' => ['required', 'string', 'in:' . implode(',', GenderEnum::getGenders())],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'reference_id' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'roles.required' => 'Please select at least one role',
            'roles.min' => 'Please select at least one role',
            'roles.*.exists' => 'One or more selected roles are invalid',
            'phone.required' => 'Phone number is required',
            'cnic.required' => 'CNIC is required',
            'cnic.unique' => 'CNIC already exists',
            'gender.required' => 'Gender is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'address.required' => 'Address is required',
        ]);

        try {
            DB::beginTransaction();
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create user profile
            profile::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'cnic' => $validated['cnic'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'reference_id' => $validated['reference_id'] ?? null,
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
        $user = User::with('profile')->findOrFail($id);
        $roles = Role::all();
        $genders = GenderEnum::getGenders();
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
            // Profile fields
            'phone' => ['required', 'string', 'max:20'],
            'cnic' => ['required', 'string', 'max:15', 'unique:user_profiles,cnic,' . ($user->profile->id ?? 0)],
            'gender' => ['required', 'string', 'in:' . implode(',', GenderEnum::getGenders())],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'reference_id' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'password.confirmed' => 'Password confirmation does not match',
            'roles.required' => 'Please select at least one role',
            'roles.min' => 'Please select at least one role',
            'roles.*.exists' => 'One or more selected roles are invalid',
            'phone.required' => 'Phone number is required',
            'cnic.required' => 'CNIC is required',
            'cnic.unique' => 'CNIC already exists',
            'gender.required' => 'Gender is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'address.required' => 'Address is required',
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

            // Update or create user profile
            $profileData = [
                'phone' => $validated['phone'],
                'cnic' => $validated['cnic'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'reference_id' => $validated['reference_id'] ?? null,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $profileData['user_id'] = $user->id;
                profile::create($profileData);
            }

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
