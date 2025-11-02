@extends('layouts.app')

@section('title', 'Workflow Details')

@push('css')
<style>
    .workflow-steps {
        position: relative;
    }

    .step-item {
        position: relative;
    }

    .step-circle {
        position: relative;
        z-index: 2;
        background: white;
    }

    .step-connector {
        width: 2px;
        height: 60px;
        background: linear-gradient(to bottom, #0d6efd, #6c757d);
        margin: 10px auto;
        position: relative;
        z-index: 1;
    }

    .step-connector::before {
        content: '';
        position: absolute;
        bottom: -5px;
        left: -3px;
        width: 8px;
        height: 8px;
        background: #6c757d;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">
                                Form:&nbsp;<strong>{{ $form->name }}</strong> ({{ $form->form_no }})&nbsp;
                                @if($workflow->is_active)
                                <span class="badge badge-sm badge-outline text-success">Active</span>
                                @else
                                <span class="badge badge-sm badge-outline text-secondary">Inactive</span>
                                @endif
                            </h2>
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
                            <!-- Workflow Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Workflow Information</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('approval-workflows.edit', [$form, $workflow]) }}" class="btn btn-sm btn-primary">
                                            <i class="far fa-pen-to-square"></i>&nbsp;Edit Workflow
                                        </a>
                                        <a href="{{ route('approval-workflows.test', [$form, $workflow]) }}" class="btn btn-sm btn-outline-info">
                                            <i class="far fa-circle-play"></i>&nbsp;Test Workflow
                                        </a>
                                        <a href="{{ route('approval-workflows.index', $form) }}" class="btn btn-sm btn-secondary">
                                            <i class="far fa-arrow-left"></i>&nbsp;Back
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Workflow Name:</th>
                                                    <td>{{ $workflow->workflow_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Description:</th>
                                                    <td>{{ $workflow->description ?: '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Flow Type:</th>
                                                    <td>
                                                        <span class="badge badge-outline text-{{ $workflow->flow_type === 'sequential' ? 'primary' : 'info' }}">
                                                            {{ ucfirst($workflow->flow_type) }}
                                                        </span>
                                                        @if($workflow->flow_type === 'sequential')
                                                            <br><small class="text-muted">Approvers work one by one in order</small>
                                                        @else
                                                            <br><small class="text-muted">All approvers work simultaneously</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                                <tr>
                                                    <th>Total Steps:</th>
                                                    <td>{{ $workflow->steps->count() }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="40%">Status:</th>
                                                    <td>
                                                        @if($workflow->is_active)
                                                            <span class="badge badge-outline text-success">Active</span>
                                                        @else
                                                            <span class="badge badge-outline text-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Created By:</th>
                                                    <td>{{ $workflow->creator?->name ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Created At:</th>
                                                    <td>{{ formatDate($workflow->created_at, 'd M Y H:i') }} WIB</td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated:</th>
                                                    <td>{{ formatDate($workflow->updated_at, 'd M Y H:i') }} WIB</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <!-- Usage Statistics -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Usage Statistics</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h3 class="mb-0 text-primary">{{ $stats['total_approvals'] }}</h3>
                                                <small class="text-muted">Total Approvals</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h3 class="mb-0 text-success">{{ $stats['approved'] }}</h3>
                                                <small class="text-muted">Approved</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h3 class="mb-0 text-danger">{{ $stats['rejected'] }}</h3>
                                                <small class="text-muted">Rejected</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                                                <small class="text-muted">Pending</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($stats['overdue'] > 0)
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="alert-icon">
                                            <i class="far fa-octagon-exclamation"></i>&nbsp;
                                        </div>
                                        <div>
                                            <div class="alert-description">
                                                <strong>{{ $stats['overdue'] }}</strong> approval(s) are overdue and need attention.
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <!-- Workflow Steps -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Workflow Steps</h3>
                                </div>
                                <div class="card-body">
                                    @if($workflow->steps->count() > 0)
                                    <div class="workflow-steps">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30px;">Step #</th>
                                                    <th>Details</th>
                                                    <th style="text-align: center;">Statistics</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($workflow->steps as $index => $step)
                                                <tr>
                                                    <td style="text-align: center; vertical-align: top;">
                                                        <span class="badge badge-outline text-primary">{{ $index + 1 }}</span>
                                                        @if($index < $workflow->steps->count() - 1)
                                                        <div class="step-connector"></div>
                                                        @endif
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        {{ $step->step_name }}
                                                        <div class="mb-2">
                                                            <span class="badge badge-outline text-secondary">
                                                                {{ ucfirst($step->approver_type) }} Approver
                                                            </span>
                                                            @if($step->is_required)
                                                            <span class="badge badge-outline text-danger">Required</span>
                                                            @else
                                                            <span class="badge badge-outline text-info">Optional</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <p class="mb-2">
                                                            <strong>Approver:</strong> {{ $step->getApproverDisplayName() }}
                                                        </p>
                                                        
                                                        @if($step->sla_hours)
                                                        <p class="mb-2">
                                                            <strong>SLA:</strong> {{ $step->sla_hours }} hours
                                                            <small class="text-muted">({{ round($step->sla_hours / 24, 1) }} days)</small>
                                                        </p>
                                                        @endif
                                                        
                                                        <!-- Show actual approvers -->
                                                        <div class="mt-2">
                                                            <small class="text-muted"><strong>Current Approver(s):</strong></small>
                                                            <div class="mt-1">
                                                                @foreach($step->getApprovers() as $approver)
                                                                {{ $approver->name }}
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <!-- Step Statistics -->
                                                        @php
                                                            $stepStats = [
                                                                'total' => $step->approvalLogs->count(),
                                                                'approved' => $step->approvalLogs->where('status', 'approved')->count(),
                                                                'rejected' => $step->approvalLogs->where('status', 'rejected')->count(),
                                                                'pending' => $step->approvalLogs->where('status', 'pending')->count()
                                                            ];
                                                        @endphp
                                                        
                                                        <div class="text-center">
                                                            <div class="row mt-1">
                                                                <div class="col-6">
                                                                    <div class="border rounded py-1">
                                                                        <small class="text-success"><i class="far fa-thumbs-up"></i>&nbsp; &nbsp;{{ $stepStats['approved'] }}</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="border rounded py-1">
                                                                        <small class="text-danger"><i class="far fa-hand"></i>&nbsp; &nbsp;{{ $stepStats['rejected'] }}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if($stepStats['pending'] > 0)
                                                            <div class="mt-1">
                                                                <small class="text-warning"><i class="far fa-hourglass-clock"></i>&nbsp; &nbsp;{{ $stepStats['pending'] }} pending</small>
                                                            </div>
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
                                        <i class="far fa-diagram-project display-1 text-muted"></i>&nbsp;
                                        <p class="mt-3 text-muted">No steps defined for this workflow</p>
                                        <a href="{{ route('approval-workflows.edit', [$form, $workflow]) }}" class="btn btn-primary">
                                            <i class="far fa-circle-plus"></i>&nbsp;Add Steps
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <!-- Actions -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if(!$workflow->is_active)
                                                <span class="text-warning">
                                                    <i class="far fa-circle-exclamation"></i>&nbsp;
                                                    This workflow is inactive. Activate it to use for form approvals.
                                                </span>
                                            @else
                                                <span class="text-success">
                                                    <i class="far fa-circle-check"></i>&nbsp;
                                                    This workflow is active and will be used for new submissions.
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            @if(!$workflow->is_active && $workflow->steps->count() > 0)
                                                <form action="{{ route('approval-workflows.toggle', [$form, $workflow]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-success" onclick="return confirm('Activate this workflow?')">
                                                        <i class="far fa-circle-check"></i>&nbsp;Activate Workflow
                                                    </button>
                                                </form>
                                            @elseif($workflow->is_active)
                                                <form action="{{ route('approval-workflows.toggle', [$form, $workflow]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Deactivate this workflow?')">
                                                        <i class="far fa-circle-pause"></i>&nbsp;Deactivate
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(!$workflow->hasBeenUsed())
                                                <form action="{{ route('approval-workflows.destroy', [$form, $workflow]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this workflow? This action cannot be undone.')">
                                                        <i class="far fa-trash-can"></i>&nbsp;Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection

