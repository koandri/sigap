@extends('layouts.app')

@section('title', 'Create Approval Workflow')

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

                        <div class="alert alert-info alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="far fa-circle-info"></i>&nbsp;
                            </div>
                            <div>
                                <h4 class="alert-heading">Info!</h4>
                                <div class="alert-description">
                                    Creating workflow for: <strong>{{ $form->name }}</strong> ({{ $form->form_no }})
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <form class="card" action="{{ route('approval-workflows.store', $form) }}" method="POST" id="workflowForm">
                                @csrf
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="workflow_name" class="form-label required">Workflow Name</label>
                                                <input type="text" class="form-control" id="workflow_name" name="workflow_name" value="{{ old('workflow_name') }}" placeholder="e.g., Standard Leave Approval" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="flow_type" class="form-label required">Flow Type</label>
                                                <select class="form-control" id="flow_type" name="flow_type" required>
                                                    <option value="">-- Select Type --</option>
                                                    <option value="sequential" {{ old('flow_type') == 'sequential' ? 'selected' : '' }}>Sequential (One by One)</option>
                                                    <option value="parallel" {{ old('flow_type') == 'parallel' ? 'selected' : '' }}>Parallel (All at Once)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of this approval workflow">{{ old('description') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="hr-text hr-text-left">
                                        <span>Approval Steps</span>
                                    </div>

                                    <div id="stepsContainer">
                                        <!-- Steps will be added here -->
                                    </div>

                                    <!-- Activation Option -->
                                    <hr>
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="make_active" name="make_active" value="1" {{ old('make_active') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="make_active">
                                                Activate this workflow immediately
                                                <br>
                                                <small class="text-muted">
                                                    This will deactivate any existing active workflow for this form
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary"><i class="far fa-circle-check"></i>&nbsp;Create Workflow</button>
                                    <a href="{{ route('approval-workflows.index', $form) }}" class="btn float-end"><i class="far fa-arrow-left"></i>&nbsp;Cancel</a>
                                </div>
                            </form>

                            <!-- Step Template -->
                            <template id="stepTemplate">
                                
                                <div class="step-card card mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title mb-0">
                                            <span class="step-number badge badge-outline text-primary">1</span>
                                            Step #<span class="step-order-text">1</span>
                                        </h3>
                                        <div class="card-actions">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                                                <i class="far fa-trash-can"></i>&nbsp;Remove
                                            </button>
                                        </div>
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
                                                        <option value="{{ $department->id }}">{{ $department->name }} ({{ $department->code }})</option>
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
                                                        <small class="form-hint">This step cannot be skipped</small>
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
    let stepIndex = 0;

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
</script>
@endpush