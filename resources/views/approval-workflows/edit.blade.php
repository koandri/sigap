@extends('layouts.app')

@section('title', 'Edit Approval Workflow')

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE HEADER -->
            <!-- BEGIN PAGE BODY -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="row">
                        @include('layouts.alerts')

                        @if($hasUsage)
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Limited Editing</h4>
                                <div class="alert-description">
                                    <ul class="list-unstyled">
                                        <li>This workflow has been used in submissions. You can only edit workflow name, description, and activation status.</li>
                                        <li>This workflow has been used in {{ $workflow->steps->sum(function($step) { return $step->approvalLogs->count(); }) }} approval(s). <a href="{{ route('approval-workflows.show', [$form, $workflow]) }}">View Usage Details</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <form class="card" action="{{ route('approval-workflows.update', [$form, $workflow]) }}" method="POST" id="workflowForm">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h3 class="card-title">Editing workflow for Form: <strong>{{ $form->name }}</strong> ({{ $form->form_no }})</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="workflow_name" class="form-label required">Workflow Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="workflow_name" name="workflow_name" value="{{ old('workflow_name', $workflow->workflow_name) }}" placeholder="e.g., Standard Leave Approval" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of this approval workflow">{{ old('description', $workflow->description) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="flow_type" class="form-label required">Flow Type</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="flow_type" name="flow_type" {{ $hasUsage ? 'disabled' : 'required' }}>
                                                <option value="">-- Select Type --</option>
                                                <option value="sequential" {{ old('flow_type', $workflow->flow_type) == 'sequential' ? 'selected' : '' }}>
                                                    Sequential (One by One)
                                                </option>
                                                <option value="parallel" {{ old('flow_type', $workflow->flow_type) == 'parallel' ? 'selected' : '' }}>
                                                    Parallel (All at Once)
                                                </option>
                                            </select>
                                            @if($hasUsage)
                                                <input type="hidden" name="flow_type" value="{{ $workflow->flow_type }}">
                                                <small class="text-muted">Cannot change flow type after usage</small>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!$hasUsage)
                                    <!-- Approval Steps (only editable if no usage) -->
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4>Approval Steps</h4>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addStep()">
                                            <i class="fa-solid fa-plus"></i>&nbsp;Add Step
                                        </button>
                                    </div>

                                    <div id="stepsContainer">
                                        <!-- Existing steps will be loaded here -->
                                    </div>
                                    @else
                                    <!-- Display existing steps (read-only) -->
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4>Current Steps (Read-Only)</h4>
                                    </div>

                                    <div class="list-group list-group-flush list-group-hoverable">
                                        @foreach($workflow->steps as $index => $step)
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto"><span class="badge badge-outline text-primary">{{ $index + 1 }}</span></div>
                                                <div class="col text-truncate">
                                                    <span>{{ $step->step_name }}</span>
                                                    <div class="d-block text-secondary text-truncate mt-n1">
                                                        Approver: {{ $step->getApproverDisplayName() }}
                                                        @if($step->sla_hours)
                                                            | SLA: {{ $step->sla_hours }} hours
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="badge badge-sm badge-outline text-{{ $step->is_required ? 'primary' : 'secondary' }}">{{ $step->is_required ? 'Required' : 'Optional' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>                    
                                    @endif

                                    <!-- Activation Status -->
                                    <hr>
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="make_active" name="make_active" value="1" {{ old('make_active', $workflow->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="make_active">
                                                Active Workflow
                                                <br>
                                                <small class="text-muted">
                                                    @if($workflow->is_active)
                                                        This workflow is currently active for the form
                                                    @else
                                                        Activate this workflow (will deactivate others)
                                                    @endif
                                                </small>
                                            </label>
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="card-footer clearfix">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i>&nbsp;Update Workflow</button>
                                        <a href="{{ route('approval-workflows.index', $form) }}" class="btn float-end"><i class="fa-regular fa-arrow-left"></i>&nbsp;Cancel</a>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            <i class="fa-regular fa-clock"></i>&nbsp;Created: {{ $workflow->created_at->format('d M Y H:i') }}
                                            @if($workflow->creator)
                                                by {{ $workflow->creator->name }}
                                            @endif
                                            <br>
                                            <i class="fa-regular fa-clock-rotate-left"></i>&nbsp;Last updated: {{ $workflow->updated_at->format('d M Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </form>

                            <!-- Step Template -->
                            <template id="stepTemplate">
                                <div class="step-card card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <span class="step-number badge bg-primary">1</span>
                                            Step <span class="step-order-text">1</span>
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                                            <i class="fa-regular fa-trash-can"></i>&nbsp;Remove
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">Step Name</label>
                                                    <input type="text" class="form-control step-name" name="steps[INDEX][step_name]" placeholder="e.g., Manager Review" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">SLA (Hours)</label>
                                                    <input type="number" class="form-control" name="steps[INDEX][sla_hours]" placeholder="e.g., 24" min="1" max="720">
                                                    <small class="text-muted">Maximum hours to complete this step</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label required">Approver Type</label>
                                                    <select class="form-control approver-type" name="steps[INDEX][approver_type]" onchange="toggleApproverFields(this)" required>
                                                        <option value="">-- Select Type --</option>
                                                        <option value="user">Specific User</option>
                                                        <option value="role">Role</option>
                                                        <option value="department">Department</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <!-- User Selection -->
                                                <div class="approver-field approver-user" style="display: none;">
                                                    <label class="form-label">Select User</label>
                                                    <select class="form-control" name="steps[INDEX][approver_user_id]">
                                                        <option value="">-- Select User --</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->departments->pluck('shortname')->join(', ') }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <!-- Role Selection -->
                                                <div class="approver-field approver-role" style="display: none;">
                                                    <label class="form-label">Select Role</label>
                                                    <select class="form-control" name="steps[INDEX][approver_role]">
                                                        <option value="">-- Select Role --</option>
                                                        @foreach($roles as $role)
                                                        <option value="{{ $role->code }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <!-- Department Selection -->
                                                <div class="approver-field approver-department" style="display: none;">
                                                    <label class="form-label">Select Department</label>
                                                    <select class="form-control" name="steps[INDEX][approver_department_id]">
                                                        <option value="">-- Select Department --</option>
                                                        @foreach($departments as $department)
                                                        <option value="{{ $department->id }}">{{ $department->name }} ({{ $department->shortname }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="steps[INDEX][is_required]" value="1" checked>
                                                    <label class="form-check-label">
                                                        <strong>Required Step</strong>
                                                        <br>
                                                        <small class="text-muted">This step cannot be skipped</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection

@push('scripts')
<script>
    let stepIndex = {{ $workflow->steps->count() }};

    // Show flow type info
    document.getElementById('flow_type').addEventListener('change', function(e) {
        const infoDiv = document.getElementById('flowTypeInfo');
        const sequentialInfo = document.getElementById('sequentialInfo');
        const parallelInfo = document.getElementById('parallelInfo');
        
        sequentialInfo.style.display = 'none';
        parallelInfo.style.display = 'none';
        
        if (e.target.value === 'sequential') {
            infoDiv.style.display = 'block';
            sequentialInfo.style.display = 'block';
        } else if (e.target.value === 'parallel') {
            infoDiv.style.display = 'block';
            parallelInfo.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    });

    // Add step
    function addStep() {
        const container = document.getElementById('stepsContainer');
        const template = document.getElementById('stepTemplate');
        const stepClone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        const stepDiv = stepClone.querySelector('.step-card');
        stepDiv.innerHTML = stepDiv.innerHTML.replace(/INDEX/g, stepIndex);
        
        // Update step number display
        const stepNumber = stepIndex + 1;
        stepClone.querySelector('.step-number').textContent = stepNumber;
        stepClone.querySelector('.step-order-text').textContent = stepNumber;
        
        container.appendChild(stepClone);
        stepIndex++;
        
        updateStepNumbers();
    }

    // Remove step
    function removeStep(button) {
        if (document.querySelectorAll('.step-card').length <= 1) {
            alert('At least one approval step is required');
            return;
        }
        
        button.closest('.step-card').remove();
        updateStepNumbers();
    }

    // Update step numbers
    function updateStepNumbers() {
        document.querySelectorAll('.step-card').forEach((card, index) => {
            const stepNumber = index + 1;
            card.querySelector('.step-number').textContent = stepNumber;
            card.querySelector('.step-order-text').textContent = stepNumber;
        });
    }

    // Toggle approver fields based on type
    function toggleApproverFields(select) {
        const stepCard = select.closest('.step-card');
        const approverFields = stepCard.querySelectorAll('.approver-field');
        
        // Hide all approver fields
        approverFields.forEach(field => {
            field.style.display = 'none';
            const selectEl = field.querySelector('select');
            if (selectEl) {
                selectEl.required = false;
                selectEl.value = '';
            }
        });
        
        // Show relevant field
        if (select.value) {
            const targetField = stepCard.querySelector('.approver-' + select.value);
            if (targetField) {
                targetField.style.display = 'block';
                const selectEl = targetField.querySelector('select');
                if (selectEl) {
                    selectEl.required = true;
                }
            }
        }
    }

    // Form validation
    document.getElementById('workflowForm').addEventListener('submit', function(e) {
        const steps = document.querySelectorAll('.step-card');
        
        if (steps.length === 0) {
            e.preventDefault();
            alert('Please add at least one approval step');
            return false;
        }
        
        // Validate each step
        let hasErrors = false;
        steps.forEach((step, index) => {
            const stepName = step.querySelector('.step-name').value;
            const approverType = step.querySelector('.approver-type').value;
            
            if (!stepName.trim()) {
                alert(`Step ${index + 1}: Step name is required`);
                hasErrors = true;
                return;
            }
            
            if (!approverType) {
                alert(`Step ${index + 1}: Approver type is required`);
                hasErrors = true;
                return;
            }
            
            // Check if appropriate approver is selected
            const approverField = step.querySelector('.approver-' + approverType + ' select');
            if (approverField && !approverField.value) {
                alert(`Step ${index + 1}: Please select an approver`);
                hasErrors = true;
                return;
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize with one step
    document.addEventListener('DOMContentLoaded', function() {
        addStep();
    });

    // Load existing steps
    document.addEventListener('DOMContentLoaded', function() {
        @if(!$hasUsage)
            // Load existing steps for editing
            @foreach($workflow->steps as $index => $step)
                addStep();
                const stepCard = document.querySelectorAll('.step-card')[{{ $index }}];
                
                // Populate step data
                stepCard.querySelector('.step-name').value = '{{ $step->step_name }}';
                stepCard.querySelector('.approver-type').value = '{{ $step->approver_type }}';
                
                // Set SLA
                const slaInput = stepCard.querySelector('input[name*="[sla_hours]"]');
                if (slaInput) slaInput.value = '{{ $step->sla_hours ?? '' }}';
                
                // Set required checkbox
                const requiredCheckbox = stepCard.querySelector('input[name*="[is_required]"]');
                if (requiredCheckbox) requiredCheckbox.checked = {{ $step->is_required ? 'true' : 'false' }};
                
                // Trigger approver type change to show appropriate field
                toggleApproverFields(stepCard.querySelector('.approver-type'));
                
                // Set approver value
                @if($step->approver_type === 'user')
                    stepCard.querySelector('select[name*="[approver_user_id]"]').value = '{{ $step->approver_user_id }}';
                @elseif($step->approver_type === 'role')
                    stepCard.querySelector('select[name*="[approver_role]"]').value = '{{ $step->approver_role }}';
                @elseif($step->approver_type === 'department')
                    stepCard.querySelector('select[name*="[approver_department_id]"]').value = '{{ $step->approver_department_id }}';
                @endif
            @endforeach
        @endif
        
        // If no existing steps and no usage, add one step
        @if(!$hasUsage && $workflow->steps->count() === 0)
            addStep();
        @endif
    });
</script>
@endpush