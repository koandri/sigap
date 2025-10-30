@extends('layouts.app')

@section('title', 'Form Details')

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
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Form No:</th>
                                                    <td>{{ $form->form_no }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Form Name:</th>
                                                    <td>{{ $form->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Description:</th>
                                                    <td>{{ $form->description ?: '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Requires Approval:</th>
                                                    <td>{!! formatBoolean($form->requires_approval) !!}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Status:</th>
                                                    <td>{!! formatBoolean($form->is_active) !!}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created By:</th>
                                                    <td>{{ $form->creator?->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created At:</th>
                                                    <td>{{ $form->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated:</th>
                                                    <td>{{ $form->updated_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- Departments -->
                                    <div class="mt-3">
                                        <strong>Assigned Departments:</strong>
                                        <div class="mt-2">
                                            {!! $form->getDepartmentNames() !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="{{ route('forms.edit', $form) }}" class="btn btn-primary">Edit</a>
                                    <a href="{{ route('forms.index') }}" class="btn float-end">Cancel</a>
                                </div>
                            </div>
                        </div>

                        @if($form->requires_approval)
                        <!-- Approval Workflow Card -->
                         <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Approval Workflow</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('approval-workflows.index', $form) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-gears"></i>&nbsp;Manage Workflows
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    @php $activeWorkflow = $form->activeApprovalWorkflow; @endphp
                                    @if($activeWorkflow)
                                        <div class="alert alert-success alert-dismissible" role="alert">
                                            <div class="alert-icon">
                                                <i class="far fa-circle-check"></i>
                                            </div>
                                            <div>
                                                <h4 class="alert-heading">Active Workflow: {{ $activeWorkflow->workflow_name }}</h4>
                                                <div class="alert-description">
                                                    <small>
                                                        Type: {{ ucfirst($activeWorkflow->flow_type) }} | 
                                                        Steps: {{ $activeWorkflow->steps->count() }}
                                                        @if($activeWorkflow->steps->sum('sla_hours') > 0)
                                                            Est. Duration: {{ $activeWorkflow->steps->sum('sla_hours') }}h
                                                        @endif
                                                    </small>
                                                    <br>
                                                    <a href="{{ route('approval-workflows.show', [$form, $activeWorkflow]) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="far fa-eye"></i>&nbsp;View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Steps Preview -->
                                        @if($activeWorkflow->steps->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">Step #</th>
                                                        <th>Step Name</th>
                                                        <th style="width: 100px;">Approver Type</th>
                                                        <th style="width: 300px;">Approver</th>
                                                        <th style="width: 100px;">SLA (hours)</th>
                                                        <th style="width: 50px;">Required?</th>
                                                    </tr>

                                                </thead>
                                                <tbody>
                                                    @foreach($activeWorkflow->getOrderedSteps()->take(4) as $index => $step)
                                                    <tr>
                                                        <td style="text-align: center;"><span class="badge badge-outline text-primary">{{ $index + 1 }}</span></td>
                                                        <td>{{ $step->step_name }}</td>
                                                        <td>{{ ucfirst($step->approver_type) }}</td>
                                                        <td>{{ $step->getApproverDisplayName() }}</td>
                                                        <td>{{ $step->sla_hours ? $step->sla_hours . 'h' : '-' }}</td>
                                                        <td>{!! formatBoolean($step->is_required) !!}</td>
                                                    </tr>
                                                    @endforeach
                                                    @if($activeWorkflow->steps->count() > 4)
                                                    <tr>
                                                        <td colspan="6">
                                                            <small class="text-muted">... and {{ $activeWorkflow->steps->count() - 4 }} more steps</small>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="far fa-circle-info fa-2x mb-2"></i>
                                            <p>No active workflow configured</p>
                                            <p>This form requires approval but has no active workflow.<br/>
                                                Submissions will be auto-approved until a workflow is activated.
                                            </p>
                                            <a href="{{ route('approval-workflows.create', $form) }}" class="btn btn-sm btn-primary">
                                                <i class="far fa-circle-plus"></i>&nbsp;Create Workflow
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @endif
                        
                        <div class="col-12">
                            <!-- Form Versions Card -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Form Versions</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('formversions.create', $form) }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-square-plus"></i>&nbsp;Add New Version
                                        </a>
                                    </div>
                                    
                                </div>
                                <div class="card-body">
                                    @if($form->versions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Version</th>
                                                        <th>Description</th>
                                                        <th>Fields</th>
                                                        <th>Status</th>
                                                        <th>Created By</th>
                                                        <th>Created On</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($form->versions->sortByDesc('version_number') as $version)
                                                    <tr>
                                                        <td>v{{ $version->version_number }}</td>
                                                        <td>{{ $version->description ?: '-' }}</td>
                                                        <td>{{ $version->fields->count() }} fields</td>
                                                        <td>{!! formatBoolean($version->is_active) !!}</td>
                                                        <td>{{ $version->creator?->name ?? '-' }}</td>
                                                        <td>{{ $version->created_on->format('d M Y H:i') }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('formversions.show', [$form, $version]) }}" class="btn btn-outline-secondary" title="Manage Fields"><i class="far fa-list-ul"></i></a>
                                                                @if(!$version->is_active)
                                                                <a class="btn btn-outline-success" title="Activate Version" onclick="activateVersion({{ $version->id }})"><i class="far fa-circle-check"></i></a>
                                                                @endif
                                                                @if($version->submissions->count() == 0)
                                                                <a class="btn btn-outline-danger" title="Delete Version" onclick="deleteVersion({{ $version->id }})"><i class="far fa-trash-can"></i></a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-3">No versions created yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $form->versions->count() }}</h3>
                                    <small class="text-muted">Total Versions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">
                                        {{ $form->versions->sum(function($v) { return $v->fields->count(); }) }}
                                    </h3>
                                    <small class="text-muted">Total Fields</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">
                                        {{ $form->versions->sum(function($v) { return $v->submissions->count(); }) }}
                                    </h3>
                                    <small class="text-muted">Total Submissions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $form->departments->count() }}</h3>
                                    <small class="text-muted">Departments</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Create Version -->
                    <div class="modal fade" id="createVersionModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="#">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Create New Version</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Version Number</label>
                                            <input type="text" class="form-control" value="v{{ $form->getNextVersionNumber() }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3" 
                                                    placeholder="What's new in this version?"></textarea>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="copy_fields" id="copy_fields">
                                            <label class="form-check-label" for="copy_fields">
                                                Copy fields from current active version
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Create Version</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection