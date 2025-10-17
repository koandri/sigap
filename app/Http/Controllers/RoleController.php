<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->paginate(20);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request): RedirectResponse
    {   
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles',
            'guard_name' => 'required|string',
        ])->validate();

        Role::create($validated);

        return redirect()->route('roles.index')->with(['success' => 'A new role created!']);
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
            'guard_name' => 'required|string',
        ])->validate();

        $role->update($validated);

        return redirect()->route('roles.index')->with(['success' => 'Role has been updated!']);
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }
}
