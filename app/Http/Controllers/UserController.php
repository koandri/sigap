<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelOptions\Options;
use Illuminate\Support\Facades\View;

use App\Services\WhatsAppService;
use App\Services\PushoverService;

use App\Enums\Location;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Permission;

final class UserController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {}

    /**
     * Group permissions by their prefix (module name)
     * 
     * @param \Illuminate\Database\Eloquent\Collection $permissions
     * @return array
     */
    private function groupPermissionsByPrefix($permissions): array
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            // Extract prefix: first part before dot, or whole name if no dot
            $parts = explode('.', $permission->name);
            $prefix = $parts[0];
            
            // Handle hyphenated prefixes (e.g., "asset-categories" -> "Asset Categories")
            $groupName = ucwords(str_replace(['-', '_'], ' ', $prefix)) . ' Permissions';
            
            if (!isset($grouped[$prefix])) {
                $grouped[$prefix] = [
                    'name' => $groupName,
                    'permissions' => []
                ];
            }
            
            $grouped[$prefix]['permissions'][] = $permission;
        }
        
        // Sort groups alphabetically
        ksort($grouped);
        
        return $grouped;
    }

    public function index()
    {
        $users = User::orderBy('name')->paginate(25);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $locations = Options::forEnum(Location::class);
        $managers = Options::forModels(User::class);

        $departments = Department::all();

        // Only Super Admin can assign Super Admin and Owner roles
        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::orderBy('name')->get();
            $permissions = Permission::orderBy('name')->get();
        }
        // Owner can assign Owner role
        elseif (Auth::user()->hasRole('Owner')) {
            $roles = Role::whereNotIn('name', array('Super Admin'))
                        ->orderBy('name')
                        ->get();
            $permissions = Permission::orderBy('name')->get();
        }
        // Other roles cannot assign Super Admin or Owner roles
        else {
            $roles = Role::whereNotIn('name', array('Super Admin', 'Owner'))
                        ->orderBy('name')
                        ->get();
            $permissions = Permission::orderBy('name')->get();
        }

        $groupedPermissions = $this->groupPermissionsByPrefix($permissions);

        return view('users.create', compact('locations', 'managers', 'departments', 'roles', 'permissions', 'groupedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'mobilephone_no' => 'required|string|max:16|starts_with:628|unique:users,mobilephone_no',
            'manager_id' => 'nullable|integer|exists:users,id',
            'locations' => 'nullable|array',
            'departments' => 'nullable|array',
            'departments.*' => 'integer|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect('/users/create')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Validate role assignments based on current user's role
        if ($request->has('roles')) {
            $requestedRoles = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
            
            // Only Super Admin can assign Super Admin or Owner roles
            if (in_array('Super Admin', $requestedRoles) || in_array('Owner', $requestedRoles)) {
                if (!Auth::user()->hasRole('Super Admin')) {
                    return redirect('/users/create')
                        ->withErrors(['roles' => 'You do not have permission to assign Super Admin or Owner roles.'])
                        ->withInput();
                }
            }
            
            // Owner cannot assign Super Admin role
            if (in_array('Super Admin', $requestedRoles)) {
                if (!Auth::user()->hasRole('Super Admin')) {
                    return redirect('/users/create')
                        ->withErrors(['roles' => 'You do not have permission to assign Super Admin role.'])
                        ->withInput();
                }
            }
        }

        //create a new random password
        $plain_password = Str::password(8);
        $hashed_password = array('password' => Hash::make($plain_password));

        $validated = array_merge($validated, $hashed_password);

        $user = User::create($validated);

        //Update User Departments
        $user->departments()->sync($request->departments);

        //Update User Roles
        $user->roles()->sync($request->roles);

        //Update User Permissions
        $user->permissions()->sync($request->permissions);

        //Send WA Notification containing Login Details to User
        $chatId = validateMobileNumber($user->mobilephone_no);

        $message = View::make('messages.user_registration', [
                            'user' => $user,
                            'plain_password' => $plain_password,
                        ])->render();

        $waSuccess = $this->whatsAppService->sendMessage($chatId, $message);

        if (!$waSuccess) {
            // Fallback to Pushover notification
            $this->pushoverService->sendWhatsAppFailureNotification(
                'User Registration Notification',
                $chatId,
                $message
            );
        }

        return redirect()->route('roles.index')->with(['success' => 'A new user created!']);
    }

    public function edit(User $user)
    {
        $managers = Options::forModels(User::class);
        $locations = Options::forEnum(Location::class);

        if ($user->hasRole('Super Admin|Owner')) {
            if (Auth::user()->hasRole('Super Admin|Owner')) {
                //allow
            }
            else {
                abort(403);
            }
        }

        $departments = Department::whereNotIn('id', $user->departments()->pluck('id'))
                                ->orderBy('name')
                                ->get();

        // Only Super Admin can assign Super Admin and Owner roles
        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::whereNotIn('id', $user->roles()->pluck('id'))
                        ->orderBy('name')
                        ->get();
            
            $permissions = Permission::whereNotIn('id', $user->permissions()->pluck('id'))
                                    ->orderBy('name')
                                    ->get();
        }
        // Owner can assign Owner role
        elseif (Auth::user()->hasRole('Owner')) {
            $roles = Role::whereNotIn('name', array('Super Admin'))
                        ->whereNotIn('id', $user->roles()->pluck('id'))
                        ->orderBy('name')
                        ->get();
            
            $permissions = Permission::whereNotIn('id', $user->permissions()->pluck('id'))
                                    ->orderBy('name')
                                    ->get();
        }
        // Other roles cannot assign Super Admin or Owner roles
        else {
            $roles = Role::whereNotIn('name', array('Super Admin', 'Owner'))
                        ->whereNotIn('id', $user->roles()->pluck('id'))
                        ->orderBy('name')
                        ->get();
            
            $permissions = Permission::whereNotIn('id', $user->permissions()->pluck('id'))
                                    ->orderBy('name')
                                    ->get();
        }

        $groupedPermissions = $this->groupPermissionsByPrefix($permissions);

        return view('users.edit', compact('user', 'managers', 'locations', 'departments', 'roles', 'permissions', 'groupedPermissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobilephone_no' => 'required|string|max:16|starts_with:628|unique:users,mobilephone_no,'.$user->id,
            'manager_id' => 'nullable|integer|exists:users,id',
            'locations' => 'nullable|array',
            'departments' => 'nullable|array',
            'departments.*' => 'integer|exists:departments,id',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'active' => 'required|boolean',
        ])->validate();

        // Validate role assignments based on current user's role
        if ($request->has('roles')) {
            $requestedRoles = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
            
            // Only Super Admin can assign Super Admin or Owner roles
            if (in_array('Super Admin', $requestedRoles) || in_array('Owner', $requestedRoles)) {
                if (!Auth::user()->hasRole('Super Admin')) {
                    return redirect()->route('users.edit', $user)
                        ->withErrors(['roles' => 'You do not have permission to assign Super Admin or Owner roles.'])
                        ->withInput();
                }
            }
            
            // Owner cannot assign Super Admin role
            if (in_array('Super Admin', $requestedRoles)) {
                if (!Auth::user()->hasRole('Super Admin')) {
                    return redirect()->route('users.edit', $user)
                        ->withErrors(['roles' => 'You do not have permission to assign Super Admin role.'])
                        ->withInput();
                }
            }
        }

        //Update User Details
        $user->update($validated);

        //Update User Locations
        if (!$request->has('locations')) {
            $user->locations = null;
            $user->save();
        }
        
        //Update User Departments
        $user->departments()->sync($request->departments);

        //Update User Roles
        $user->roles()->sync($request->roles);

        //Update User Permissions
        $user->permissions()->sync($request->permissions);

        return redirect()->route('users.index')->with(['success' => 'User has been updated!']);
    }

    public function show(User $user)
    {
        // Get all permissions the user has (via roles and direct)
        $allPermissions = $user->getAllPermissions();
        
        // Get direct permissions (not via roles)
        $directPermissions = $user->permissions;
        
        // Get permissions inherited from roles (all permissions minus direct permissions)
        $directPermissionIds = $directPermissions->pluck('id')->toArray();
        $rolePermissions = $allPermissions->reject(function ($permission) use ($directPermissionIds) {
            return in_array($permission->id, $directPermissionIds);
        });

        // Group permissions for display
        $groupedRolePermissions = $this->groupPermissionsByPrefix($rolePermissions);
        $groupedDirectPermissions = $this->groupPermissionsByPrefix($directPermissions);

        return view('users.show', compact('user', 'allPermissions', 'rolePermissions', 'directPermissions', 'groupedRolePermissions', 'groupedDirectPermissions'));
    }

    public function editmyprofile(Request $request)
    {
        return view('profile.editmyprofile');
    }
}
