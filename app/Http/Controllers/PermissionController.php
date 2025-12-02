<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.view') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to view permissions.');
        }

        $permissions = Permission::orderBy('name')->paginate(20);

        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.create') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to create permissions.');
        }

        return view('permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.create') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to create permissions.');
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:permissions',
            'description' => 'nullable|string|max:500',
            'guard_name' => 'required|string',
        ])->validate();

        Permission::create($validated);

        return redirect()->route('permissions.index')->with(['success' => 'A new permission created!']);
    }

    public function edit(Permission $permission)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.edit') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to edit permissions.');
        }

        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.edit') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to edit permissions.');
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:500',
            'guard_name' => 'required|string',
        ])->validate();

        $permission->update($validated);

        return redirect()->route('permissions.index')->with(['success' => 'Permission has been updated!']);
    }

    public function show(Permission $permission)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.permissions.view') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to view permissions.');
        }

        return view('permissions.show', compact('permission'));
    }
}
