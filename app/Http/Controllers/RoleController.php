<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
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
        // Only Super Admin can see all roles
        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::orderBy('name')->paginate(20);
        }
        // Owner can see all roles except Super Admin
        elseif (Auth::user()->hasRole('Owner')) {
            $roles = Role::whereNotIn('name', ['Super Admin'])
                        ->orderBy('name')
                        ->paginate(20);
        }
        // Other roles cannot see Super Admin or Owner roles
        else {
            $roles = Role::whereNotIn('name', ['Super Admin', 'Owner'])
                        ->orderBy('name')
                        ->paginate(20);
        }

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissionsByPrefix($permissions);
        return view('roles.create', compact('permissions', 'groupedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {   
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles',
            'guard_name' => 'required|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ])->validate();

        $role = Role::create($validated);

        // Sync permissions if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with(['success' => 'A new role created!']);
    }

    public function edit(Role $role)
    {
        // Only Super Admin can edit Super Admin or Owner roles
        if (in_array($role->name, ['Super Admin', 'Owner'])) {
            if (!Auth::user()->hasRole('Super Admin')) {
                abort(403, 'You do not have permission to edit this role.');
            }
        }

        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $groupedPermissions = $this->groupPermissionsByPrefix($permissions);

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'groupedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Only Super Admin can update Super Admin or Owner roles
        if (in_array($role->name, ['Super Admin', 'Owner'])) {
            if (!Auth::user()->hasRole('Super Admin')) {
                abort(403, 'You do not have permission to update this role.');
            }
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
            'guard_name' => 'required|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ])->validate();

        $role->update($validated);

        // Sync permissions (empty array if not provided)
        $permissions = $request->has('permissions') ? $request->permissions : [];
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with(['success' => 'Role has been updated!']);
    }

    public function show(Role $role)
    {
        // Only Super Admin can view Super Admin or Owner roles
        if (in_array($role->name, ['Super Admin', 'Owner'])) {
            if (!Auth::user()->hasRole('Super Admin')) {
                abort(403, 'You do not have permission to view this role.');
            }
        }

        $permissions = $role->permissions()->orderBy('name')->get();
        $usersCount = $role->users()->count();
        $groupedPermissions = $this->groupPermissionsByPrefix($permissions);

        return view('roles.show', compact('role', 'permissions', 'usersCount', 'groupedPermissions'));
    }
}
