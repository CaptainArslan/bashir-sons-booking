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

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.employees.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Get only users with Employee role
            $employeeRole = Role::where('name', 'Employee')->first();
            
            $employees = User::query()
                ->whereHas('roles', function ($query) use ($employeeRole) {
                    $query->where('role_id', $employeeRole->id);
                })
                ->with(['roles:id,name', 'profile', 'terminal.city'])
                ->select('id', 'name', 'email', 'terminal_id', 'created_at');

            return DataTables::eloquent($employees)
                ->addColumn('employee_info', function ($user) {
                    $employeeInfo = '<div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-primary">' . e($user->name) . '</h6>
                            <small class="text-muted"><i class="bx bx-envelope me-1"></i>' . e($user->email) . '</small>
                        </div>
                    </div>';
                    return $employeeInfo;
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
                ->addColumn('terminal_info', function ($user) {
                    if ($user->terminal) {
                        return '<div>
                            <div class="fw-bold text-success">' . e($user->terminal->name) . '</div>
                            <small class="text-muted">' . e($user->terminal->city->name) . '</small>
                        </div>';
                    }
                    return '<span class="text-danger">No Terminal Assigned</span>';
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
                    
                    // Notes
                    if ($profile->notes) {
                        $addressInfo .= '<div class="mt-1"><i class="bx bx-notepad me-1"></i>' . e($profile->notes) . '</div>';
                    }
                    
                    return $addressInfo ?: '<span class="text-muted">No notes</span>';
                })
                ->addColumn('status_info', function ($user) {
                    $status = $user->terminal ? 'active' : 'inactive';
                    $statusColor = $status === 'active' ? 'success' : 'danger';
                    $statusText = $status === 'active' ? 'Active' : 'No Terminal';
                    
                    return '<span class="badge bg-' . $statusColor . '">' . $statusText . '</span>';
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
                                        <i class="bx bx-edit me-2"></i>Edit Employee
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteEmployee(' . $user->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Employee
                                    </a>
                                </li>
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($user) => $user->created_at->format('d M Y'))
                ->escapeColumns([]) // ensures HTML isn't escaped
                ->rawColumns(['employee_info', 'contact_info', 'personal_info', 'terminal_info', 'address_info', 'status_info', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $roles = Role::all();
        $genders = GenderEnum::getGenders();
        $terminals = Terminal::with('city')->get();
        return view('admin.employees.create', get_defined_vars());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terminal_id' => ['required', 'exists:terminals,id'],
            // Profile fields
            'phone' => ['required', 'string', 'max:20'],
            'cnic' => ['required', 'string', 'max:15', 'unique:profiles,cnic'],
            'gender' => ['required', 'string', 'in:' . implode(',', GenderEnum::getGenders())],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'terminal_id.required' => 'Terminal assignment is required',
            'terminal_id.exists' => 'Selected terminal is invalid',
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
                'notes' => $validated['notes'],
            ]);

            // Assign Employee role
            $employeeRole = Role::where('name', 'Employee')->first();
            $user->assignRole($employeeRole);

            DB::commit();

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create employee: ' . $e->getMessage());
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
                'message' => 'Employee deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        $employeeRole = Role::where('name', 'Employee')->first();
        
        $total = User::whereHas('roles', function ($query) use ($employeeRole) {
            $query->where('role_id', $employeeRole->id);
        })->count();

        $active = User::whereHas('roles', function ($query) use ($employeeRole) {
            $query->where('role_id', $employeeRole->id);
        })->whereNotNull('terminal_id')->count();

        $inactive = User::whereHas('roles', function ($query) use ($employeeRole) {
            $query->where('role_id', $employeeRole->id);
        })->whereNull('terminal_id')->count();

        $newThisMonth = User::whereHas('roles', function ($query) use ($employeeRole) {
            $query->where('role_id', $employeeRole->id);
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'new_this_month' => $newThisMonth,
        ]);
    }
}
