<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\profile;
use App\Models\Terminal;
use App\Enums\GenderEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                ->with(['roles:id,name', 'profile', 'terminal.city'])
                ->select('id', 'name', 'email', 'terminal_id', 'created_at');

            return DataTables::eloquent($users)
                ->addColumn('user_info', function ($user) {
                    // User name and email only
                    $userInfo = '<div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-primary">' . e($user->name) . '</h6>
                            <small class="text-muted"><i class="bx bx-envelope me-1"></i>' . e($user->email) . '</small>
                        </div>
                    </div>';
                    return $userInfo;
                })
                ->addColumn('contact_info', function ($user) {
                    $profile = $user->profile;
                    if (!$profile) {
                        return '<span class="text-muted">No profile</span>';
                    }
                    
                    $contactInfo = '';
                    if ($profile->phone) {
                        $contactInfo .= '<div><i class="bx bx-phone me-1"></i>' . e($profile->phone) . '</div>';
                    }
                    if ($profile->cnic) {
                        $contactInfo .= '<div><i class="bx bx-id-card me-1"></i>' . e($profile->cnic) . '</div>';
                    }
                    
                    return $contactInfo ?: '<span class="text-muted">No contact info</span>';
                })
                ->addColumn('personal_info', function ($user) {
                    $profile = $user->profile;
                    if (!$profile) {
                        return '<span class="text-muted">No profile</span>';
                    }
                    
                    $personalInfo = '';
                    
                    // Gender
                    if ($profile->gender) {
                        $genderColor = $profile->gender->value === 'male' ? 'primary' : ($profile->gender->value === 'female' ? 'danger' : 'secondary');
                        $personalInfo .= '<div><span class="badge bg-' . $genderColor . '">' . e(GenderEnum::getGenderName($profile->gender->value)) . '</span></div>';
                    }
                    
                    // Date of Birth
                    if ($profile->date_of_birth) {
                        $personalInfo .= '<div class="mt-1"><i class="bx bx-calendar me-1"></i>' . e($profile->date_of_birth->format('d M Y')) . '</div>';
                    }
                    
                    return $personalInfo ?: '<span class="text-muted">No personal info</span>';
                })
                ->addColumn('address_info', function ($user) {
                    $profile = $user->profile;
                    if (!$profile) {
                        return '<span class="text-muted">No profile</span>';
                    }
                    
                    $addressInfo = '';
                    
                    // Address
                    if ($profile->address) {
                        $addressInfo .= '<div><i class="bx bx-map me-1"></i>' . e(Str::limit($profile->address, 60)) . '</div>';
                    }
                    
                    // Reference ID
                    if ($profile->reference_id) {
                        $addressInfo .= '<div class="mt-1"><i class="bx bx-link me-1"></i>Ref: ' . e($profile->reference_id) . '</div>';
                    }
                    
                    return $addressInfo ?: '<span class="text-muted">No address info</span>';
                })
                ->addColumn('roles_info', function ($user) {
                    $roles = $user->roles->pluck('name')->toArray();
                    if (empty($roles)) {
                        return '<span class="badge bg-secondary">No Role</span>';
                    }

                    $badges = '';
                    foreach ($roles as $role) {
                        $color = match ($role) {
                            'super_admin' => 'danger',
                            'admin' => 'warning',
                            'employee' => 'info',
                            'customer' => 'success',
                            default => 'secondary'
                        };
                        $badges .= '<span class="badge bg-' . $color . ' me-1 mb-1">' . e(ucfirst($role)) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('terminal_info', function ($user) {
                    if ($user->terminal) {
                        return '<div>
                            <div class="fw-bold text-primary">' . e($user->terminal->name) . '</div>
                            <small class="text-muted">' . e($user->terminal->city->name) . '</small>
                        </div>';
                    }
                    return '<span class="text-muted">No Terminal</span>';
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
                ->rawColumns(['user_info', 'contact_info', 'personal_info', 'address_info', 'roles_info', 'terminal_info', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $roles = Role::all();
        $genders = GenderEnum::getGenders();
        $terminals = Terminal::with('city')->get();
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
            'terminal_id' => ['nullable', 'exists:terminals,id'],
            // Profile fields
            'phone' => ['required', 'string', 'max:20'],
            'cnic' => ['required', 'string', 'max:15', 'unique:profiles,cnic'],
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
            'terminal_id.exists' => 'Selected terminal is invalid',
            'phone.required' => 'Phone number is required',
            'cnic.required' => 'CNIC is required',
            'cnic.unique' => 'CNIC already exists',
            'gender.required' => 'Gender is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'address.required' => 'Address is required',
        ]);

        // Additional validation: If Employee role is selected, terminal_id is required
        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole && in_array($employeeRole->id, $validated['roles'])) {
            if (empty($validated['terminal_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terminal assignment is required when assigning Employee role.');
            }
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'terminal_id' => $validated['terminal_id'],
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
        $user = User::with(['profile', 'terminal'])->findOrFail($id);
        $roles = Role::all();
        $genders = GenderEnum::getGenders();
        $terminals = Terminal::with('city')->get();
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
            'terminal_id' => ['nullable', 'exists:terminals,id'],
            // Profile fields
            'phone' => ['required', 'string', 'max:20'],
            'cnic' => ['required', 'string', 'max:15', 'unique:profiles,cnic,' . ($user->profile->id ?? 0)],
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
            'terminal_id.exists' => 'Selected terminal is invalid',
            'phone.required' => 'Phone number is required',
            'cnic.required' => 'CNIC is required',
            'cnic.unique' => 'CNIC already exists',
            'gender.required' => 'Gender is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be in the past',
            'address.required' => 'Address is required',
        ]);

        // Additional validation: If Employee role is selected, terminal_id is required
        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole && in_array($employeeRole->id, $validated['roles'])) {
            if (empty($validated['terminal_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terminal assignment is required when assigning Employee role.');
            }
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'terminal_id' => $validated['terminal_id'],
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
