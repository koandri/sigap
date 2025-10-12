<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalFlowStep;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowController extends Controller
{
    /**
     * Display workflows for a form
     */
    public function index(Form $form)
    {
        $workflows = $form->approvalWorkflows()
            ->with(['steps', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('approval-workflows.index', compact('form', 'workflows'));
    }

    /**
     * Show create workflow form
     */
    public function create(Form $form)
    {
        $users = User::where('active', 1)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('approval-workflows.create', compact('form', 'users', 'roles', 'departments'));
    }

    /**
     * Store new workflow
     */
    public function store(Request $request, Form $form)
    {
        $validated = $request->validate([
            'workflow_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'flow_type' => 'required|in:sequential,parallel',
            'steps' => 'required|array|min:1',
            'steps.*.step_name' => 'required|string|max:255',
            'steps.*.approver_type' => 'required|in:user,role,department',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
            'steps.*.approver_role' => 'nullable|exists:roles,code',
            'steps.*.approver_department_id' => 'nullable|exists:departments,id',
            'steps.*.sla_hours' => 'nullable|integer|min:1|max:720', // Max 30 days
            'steps.*.is_required' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Deactivate existing workflows if making this one active
            if ($request->has('make_active')) {
                $form->approvalWorkflows()->update(['is_active' => false]);
            }

            // Create workflow
            $workflow = $form->approvalWorkflows()->create([
                'workflow_name' => $validated['workflow_name'],
                'description' => $validated['description'],
                'flow_type' => $validated['flow_type'],
                'is_active' => $request->has('make_active'),
                'created_by' => auth()->id()
            ]);

            // Create steps
            foreach ($validated['steps'] as $index => $stepData) {
                $workflow->steps()->create([
                    'step_order' => $index + 1,
                    'step_name' => $stepData['step_name'],
                    'approver_type' => $stepData['approver_type'],
                    'approver_user_id' => $stepData['approver_user_id'] ?? null,
                    'approver_role' => $stepData['approver_role'] ?? null,
                    'approver_department_id' => $stepData['approver_department_id'] ?? null,
                    'sla_hours' => $stepData['sla_hours'] ?? null,
                    'is_required' => $request->has("steps.{$index}.is_required")
                ]);
            }

            DB::commit();

            return redirect()->route('approval-workflows.index', $form)
                ->with('success', 'Approval workflow created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create workflow: ' . $e->getMessage());
        }
    }

    /**
     * Show workflow details
     */
    public function show(Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }
        
        $workflow->load(['steps' => function($query) {
            $query->orderBy('step_order');
        }, 'creator']);

        // Get usage statistics
        $stats = $this->getWorkflowStats($workflow);

        return view('approval-workflows.show', compact('form', 'workflow', 'stats'));
    }

    /**
     * Show edit workflow form
     */
    public function edit(Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }

        // Check if workflow has been used
        $hasUsage = $this->checkWorkflowUsage($workflow);

        $workflow->load(['steps' => function($query) {
            $query->orderBy('step_order');
        }]);

        $users = User::where('active', 1)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('approval-workflows.edit', compact('form', 'workflow', 'users', 'roles', 'departments', 'hasUsage'));
    }

    /**
     * Update workflow
     */
    public function update(Request $request, Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }

        $hasUsage = $this->checkWorkflowUsage($workflow);

        // Different validation rules based on usage
        $rules = [
            'workflow_name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ];

        if (!$hasUsage) {
            // Can modify structure if not used
            $rules['flow_type'] = 'required|in:sequential,parallel';
            $rules['steps'] = 'required|array|min:1';
            $rules['steps.*.step_name'] = 'required|string|max:255';
            $rules['steps.*.approver_type'] = 'required|in:user,role,department';
            $rules['steps.*.approver_user_id'] = 'nullable|exists:users,id';
            $rules['steps.*.approver_role'] = 'nullable|exists:roles,code';
            $rules['steps.*.approver_department_id'] = 'nullable|exists:departments,id';
            $rules['steps.*.sla_hours'] = 'nullable|integer|min:1|max:720';
            $rules['steps.*.is_required'] = 'boolean';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Deactivate other workflows if making this active
            if ($request->has('make_active') && !$workflow->is_active) {
                $form->approvalWorkflows()->where('id', '!=', $workflow->id)->update(['is_active' => false]);
            }

            // Update workflow
            $workflow->update([
                'workflow_name' => $validated['workflow_name'],
                'description' => $validated['description'],
                'flow_type' => $hasUsage ? $workflow->flow_type : $validated['flow_type'],
                'is_active' => $request->has('make_active')
            ]);

            // Update steps only if no usage
            if (!$hasUsage && isset($validated['steps'])) {
                // Delete existing steps
                $workflow->steps()->delete();

                // Create new steps
                foreach ($validated['steps'] as $index => $stepData) {
                    $workflow->steps()->create([
                        'step_order' => $index + 1,
                        'step_name' => $stepData['step_name'],
                        'approver_type' => $stepData['approver_type'],
                        'approver_user_id' => $stepData['approver_user_id'] ?? null,
                        'approver_role' => $stepData['approver_role'] ?? null,
                        'approver_department_id' => $stepData['approver_department_id'] ?? null,
                        'sla_hours' => $stepData['sla_hours'] ?? null,
                        'is_required' => $request->has("steps.{$index}.is_required")
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('approval-workflows.show', [$form, $workflow])
                ->with('success', 'Approval workflow updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update workflow: ' . $e->getMessage());
        }
    }

    /**
     * Activate/deactivate workflow
     */
    public function toggleActive(Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            if (!$workflow->is_active) {
                // Activating - deactivate others
                $form->approvalWorkflows()->where('id', '!=', $workflow->id)->update(['is_active' => false]);
                $workflow->update(['is_active' => true]);
                $message = 'Workflow activated successfully.';
            } else {
                // Deactivating
                $workflow->update(['is_active' => false]);
                $message = 'Workflow deactivated successfully.';
            }

            DB::commit();

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to toggle workflow status.');
        }
    }

    /**
     * Delete workflow
     */
    public function destroy(Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }

        // Check if workflow has been used
        if ($this->checkWorkflowUsage($workflow)) {
            return back()->with('error', 'Cannot delete workflow that has been used in submissions.');
        }

        $workflow->delete();

        return redirect()->route('approval-workflows.index', $form)
            ->with('success', 'Approval workflow deleted successfully.');
    }

    /**
     * Test workflow with sample data
     */
    public function test(Form $form, ApprovalWorkflow $workflow)
    {
        // Verify workflow belongs to form
        if ($workflow->form_id != $form->id) {
            abort(404);
        }

        $workflow->load(['steps' => function($query) {
            $query->orderBy('step_order');
        }]);

        // Simulate workflow execution
        $simulation = $this->simulateWorkflow($workflow);

        return view('approval-workflows.test', compact('form', 'workflow', 'simulation'));
    }

    /**
     * Helper: Get workflow statistics
     */
    private function getWorkflowStats(ApprovalWorkflow $workflow): array
    {
        $stepIds = $workflow->steps->pluck('id');
        
        return [
            'total_approvals' => \App\Models\ApprovalLog::whereIn('approval_flow_step_id', $stepIds)->count(),
            'approved' => \App\Models\ApprovalLog::whereIn('approval_flow_step_id', $stepIds)
                ->where('status', 'approved')->count(),
            'rejected' => \App\Models\ApprovalLog::whereIn('approval_flow_step_id', $stepIds)
                ->where('status', 'rejected')->count(),
            'pending' => \App\Models\ApprovalLog::whereIn('approval_flow_step_id', $stepIds)
                ->where('status', 'pending')->count(),
            'overdue' => \App\Models\ApprovalLog::whereIn('approval_flow_step_id', $stepIds)
                ->where('status', 'pending')
                ->where('due_at', '<', now())->count()
        ];
    }

    /**
     * Helper: Simulate workflow execution
     */
    private function simulateWorkflow(ApprovalWorkflow $workflow): array
    {
        $simulation = [
            'flow_type' => $workflow->flow_type,
            'total_steps' => $workflow->steps->count(),
            'estimated_duration' => 0,
            'steps' => []
        ];

        foreach ($workflow->getOrderedSteps() as $step) {
            $approvers = $step->getApprovers();
            $stepSimulation = [
                'step_name' => $step->step_name,
                'order' => $step->step_order,
                'approvers' => $approvers->map(function($user) {
                    return [
                        'name' => $user->name,
                        'roles' => $user->roles->pluck('name')->toArray(),
                        'departments' => $user->departments->pluck('name')->toArray()
                    ];
                }),
                'sla_hours' => $step->sla_hours,
                'is_required' => $step->is_required
            ];

            $simulation['steps'][] = $stepSimulation;
            
            if ($step->sla_hours) {
                $simulation['estimated_duration'] += $step->sla_hours;
            }
        }

        return $simulation;
    }

    /**
     * Helper: Check if workflow has been used (add to controller)
     */
    private function checkWorkflowUsage(ApprovalWorkflow $workflow): bool
    {
        return $workflow->steps()
            ->whereHas('approvalLogs')
            ->exists();
    }
}