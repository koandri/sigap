<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Department;

class DepartmentController extends Controller
{
   public function index()
    {
        $departments = Department::orderBy('name')->paginate(20);

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request): RedirectResponse
    {   
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:departments',
            'shortname' => 'required|string|min:2|max:5|unique:departments',
        ])->validate();

        Department::create($validated);

        return redirect()->route('departments.index')->with(['success' => 'A new department created!']);
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:departments,name,' . $department->id,
            'shortname' => 'required|string|min:2|max:5|unique:departments,shortname,' . $department->id
        ])->validate();

        $department->update($validated);

        return redirect()->route('departments.index')->with(['success' => 'Department has been updated!']);
    }

    public function show(Department $department)
    {
        return view('departments.show', compact('department'));
    }
}
