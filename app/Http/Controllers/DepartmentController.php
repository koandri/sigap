<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.view') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to view departments.');
        }

        $departments = Department::orderBy('name')->paginate(20);

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.create') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to create departments.');
        }

        return view('departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.create') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to create departments.');
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:departments',
            'shortname' => 'required|string|min:2|max:5|unique:departments',
        ])->validate();

        Department::create($validated);

        return redirect()->route('departments.index')->with(['success' => 'A new department created!']);
    }

    public function edit(Department $department)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.edit') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to edit departments.');
        }

        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.edit') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to edit departments.');
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:departments,name,' . $department->id,
            'shortname' => 'required|string|min:2|max:5|unique:departments,shortname,' . $department->id
        ])->validate();

        $department->update($validated);

        return redirect()->route('departments.index')->with(['success' => 'Department has been updated!']);
    }

    public function show(Department $department)
    {
        // Check permission
        if (!Auth::user()->hasPermissionTo('options.departments.view') && !Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            abort(403, 'You do not have permission to view departments.');
        }

        return view('departments.show', compact('department'));
    }
}
