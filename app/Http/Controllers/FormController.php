<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Form;
use App\Models\Department;

class FormController extends Controller
{
    /**
     * Display a listing of forms
     */
    public function index()
    {
        $user = auth()->user();
        $hasFullAccess = $user->hasAnyRole(['Super Admin', 'Owner']);
        
        if ($hasFullAccess) {
            // Show all forms for Super Admin/Owner
            $forms = Form::with(['departments', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Show only forms from user's departments
            $userDepartmentIds = $user->departments->pluck('id');
            
            $forms = Form::whereHas('departments', function($query) use ($userDepartmentIds) {
                    $query->whereIn('departments.id', $userDepartmentIds);
                })
                ->with(['departments', 'creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('forms.index', compact('forms', 'hasFullAccess'));
    }

    /**
     * Show the form for creating a new form
     */
    public function create()
    {
        $departments = Department::orderBy('name')
                                ->get();
            
        return view('forms.create', compact('departments'));
    }

    /**
     * Store a newly created form
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_no' => 'required|string|max:50|unique:forms,form_no',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requires_approval' => 'boolean',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id'
        ]);

        $form = Form::create([
            'form_no' => $validated['form_no'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'requires_approval' => $request->has('requires_approval'),
            'is_active' => true,
            'created_by' => auth()->id()
        ]);

        // Attach departments
        $form->departments()->attach($validated['departments']);

        return redirect()->route('forms.index')
            ->with('success', 'Form created successfully.');
    }

    /**
     * Display the specified form
     */
    public function show(Form $form)
    {
        $user = auth()->user();
    
        // Check access
        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            // Regular users need department access
            $userDepartmentIds = $user->departments->pluck('id');
            $formDepartmentIds = $form->departments->pluck('id');
            
            if ($userDepartmentIds->intersect($formDepartmentIds)->isEmpty()) {
                abort(403, 'You do not have permission to view this form.');
            }
        }
        
        // Load relationships
        $form->load(['departments', 'versions.fields', 'creator']);
        
        // Determine if user can manage this form
        $canManage = false;
        if ($user->hasAnyRole(['Super Admin', 'Owner'])) {
            // Admin/Owner can manage all forms
            $canManage = true;
        } elseif ($user->hasAnyRole(['manager', 'supervisor'])) {
            // Manager/Supervisor can manage forms in their departments
            $userDepartmentIds = $user->departments->pluck('id');
            $formDepartmentIds = $form->departments->pluck('id');
            
            if ($userDepartmentIds->intersect($formDepartmentIds)->isNotEmpty()) {
                $canManage = true;
            }
        }
        
        return view('forms.show', compact('form', 'canManage'));
    }

    /**
     * Show the form for editing
     */
    public function edit(Form $form)
    {
        $user = auth()->user();
    
        // Check access - Super Admin/Owner can edit all, others only their department's forms
        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            $userDepartmentIds = $user->departments->pluck('id');
            $formDepartmentIds = $form->departments->pluck('id');
            
            if ($userDepartmentIds->intersect($formDepartmentIds)->isEmpty()) {
                abort(403, 'You do not have permission to edit this form.');
            }
        }
        
        $departments = Department::orderBy('name')
                                ->get();
            
        $selectedDepartments = $form->departments->pluck('id')->toArray();
        
        return view('forms.edit', compact('form', 'departments', 'selectedDepartments'));
    }

    /**
     * Update the specified form
     */
    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'form_no' => 'required|string|max:50|unique:forms,form_no,' . $form->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id'
        ]);

        $form->update([
            'form_no' => $validated['form_no'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'requires_approval' => $request->has('requires_approval'),
            'is_active' => $request->has('is_active')
        ]);

        // Sync departments
        $form->departments()->sync($validated['departments']);

        return redirect()->route('forms.index')
            ->with('success', 'Form updated successfully.');
    }

    /**
     * Remove the specified form
     */
    public function destroy(Form $form)
    {
        $user = auth()->user();
    
        // Only Super Admin/Owner can delete forms
        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return redirect()->route('forms.index')
                ->with('error', 'You do not have permission to delete forms.');
        }
        
        // Check if form has submissions
        $submissionCount = $form->versions()
            ->withCount('submissions')
            ->get()
            ->sum('submissions_count');
            
        if ($submissionCount > 0) {
            return redirect()->route('forms.index')
                ->with('error', 'Cannot delete form with existing submissions.');
        }

        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Form deleted successfully.');
    }
}