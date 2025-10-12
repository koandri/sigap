@extends('layouts.app')

@section('title', 'Test Workflow: ' . $workflow->workflow_name)

@push('css')
<style>
    .simulation-timeline {
        position: relative;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-line {
        width: 2px;
        height: 80px;
        background: linear-gradient(to bottom, #0d6efd, #6c757d);
        margin: 10px auto;
        position: relative;
    }

    .timeline-line::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: -3px;
        width: 8px;
        height: 8px;
        background: #6c757d;
        border-radius: 50%;
    }

    .timeline-badge {
        position: relative;
        z-index: 2;
        background: white;
        padding: 5px 0;
    }
</style>
@endpush

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
                                <i class="fa-regular fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Info!</h4>
                                <div class="alert-description">
                                    This is a simulation of how the workflow will execute for form: <strong>{{ $form->name }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <!-- Workflow Summary -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h5 class="mb-0">{{ $simulation['total_steps'] }}</h5>
                                            <small class="text-muted">Total Steps</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h5 class="mb-0">{{ ucfirst($simulation['flow_type']) }}</h5>
                                            <small class="text-muted">Flow Type</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h5 class="mb-0">{{ $simulation['estimated_duration'] }}h</h5>
                                            <small class="text-muted">Est. Duration</small>
                                        </div>
                                        <div class="col-md-3">
                                            <h5 class="mb-0">
                                                {{ collect($simulation['steps'])->sum(function($step) { return count($step['approvers']); }) }}
                                            </h5>
                                            <small class="text-muted">Total Approvers</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Execution Flow</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                @if(count($simulation['steps']) > 0)

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">Step #</th>
                                                <th>Details</th>
                                                <th style="width: 200px;">Flow Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($simulation['steps'] as $index => $stepSim)
                                            <tr>
                                                <td style="text-align: center;">
                                                    <span class="badge badge-outline text-primary rounded-circle">{{ $stepSim['order'] }}</span>
                                                    @if($index < count($simulation['steps']) - 1)
                                                        <div class="timeline-line"></div>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $stepSim['step_name'] }}
                                                    <div class="mb-2">
                                                        @if($stepSim['is_required'])
                                                        <span class="badge badge-outline text-danger">Required</span>
                                                        @else
                                                        <span class="badge badge-outline text-info">Optional</span>
                                                        @endif
                                                        
                                                        @if($stepSim['sla_hours'])
                                                        <span class="badge badge-outline text-warning">{{ $stepSim['sla_hours'] }}h SLA</span>
                                                        @endif
                                                        
                                                        @if($workflow->flow_type === 'parallel' && $index > 0)
                                                        <span class="badge badge-outline text-info">Parallel</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Approvers List -->
                                                    <div>
                                                        <small class="text-muted"><strong>Approvers:</strong></small>
                                                        @if(count($stepSim['approvers']) > 0)
                                                        <div class="mt-1">
                                                            @foreach($stepSim['approvers'] as $approver)
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="fa-regular fa-circle-user"></i>
                                                                    <div>
                                                                        <strong>{{ $approver['name'] }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            Roles: {{ implode(', ', $approver['roles']) }}
                                                                            @if(!empty($approver['departments']))
                                                                                | Depts: {{ implode(', ', $approver['departments']) }}
                                                                            @endif
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @else
                                                        <div class="alert alert-warning alert-dismissible" role="alert">
                                                            <div class="alert-icon">
                                                                <i class="fa-regular fa-triangle-exclamation"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="alert-heading">Warning!</h4>
                                                                <div class="alert-description">
                                                                    No approvers found for this step configuration
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($workflow->flow_type === 'sequential')
                                                                    @if($index === 0)
                                                                        <span class="badge badge-outline text-success">Starts Immediately</span>
                                                                    @else
                                                                        <span class="badge badge-outline text-secondary">Waits for Step {{ $index }}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-outline text-info">Starts with Submission</span>
                                                                @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <hr>                                   
                                    <!-- Simulation Results -->
                                    <div class="alert alert-success alert-dismissible mt-4" role="alert">
                                        <div class="alert-icon">
                                            <i class="fa-regular fa-circle-check"></i>
                                        </div>
                                        <div>
                                            <h4 class="alert-heading">Simulation Results</h4>
                                            <div class="alert-description">
                                                @if($workflow->flow_type === 'sequential')
                                                <p class="mb-1">
                                                    <strong>Sequential Flow:</strong> Approval will proceed step by step. 
                                                    Total estimated time: <strong>{{ $simulation['estimated_duration'] }} hours</strong>
                                                </p>
                                                <p class="mb-0">
                                                    Steps will execute in order: 
                                                    @foreach($simulation['steps'] as $step)
                                                        {{ $step['step_name'] }}{{ !$loop->last ? ' → ' : '' }}
                                                    @endforeach
                                                </p>
                                            @else
                                                <p class="mb-1">
                                                    <strong>Parallel Flow:</strong> All approvers will receive notifications simultaneously.
                                                </p>
                                                <p class="mb-0">
                                                    Total approvers: <strong>{{ collect($simulation['steps'])->sum(function($step) { return count($step['approvers']); }) }}</strong> people
                                                </p>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if(collect($simulation['steps'])->contains(function($step) { return empty($step['approvers']); }))
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="alert-icon">
                                            <i class="fa-regular fa-octagon-exclamation"></i>
                                        </div>
                                        <div>
                                            <h4 class="alert-heading">Configuration Issues</h4>
                                            <div class="alert-description">
                                                <p class="mb-0">Some steps have no approvers assigned. Please review the workflow configuration:</p>
                                                <ul class="mb-0 mt-2">
                                                    @foreach($simulation['steps'] as $step)
                                                        @if(empty($step['approvers']))
                                                            <li>{{ $step['step_name'] }} - No approvers found</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @else
                                <div class="text-center py-4">
                                    <i class="fa-regular fa-triangle-exclamation display-1 text-warning"></i>
                                    <h4 class="mt-3">No Steps Defined</h4>
                                    <p class="text-muted">Add steps to this workflow before testing</p>
                                    <a href="{{ route('approval-workflows.edit', [$form, $workflow]) }}" class="btn btn-primary">
                                        <i class="fa-solid fa-circle-plus"></i>&nbsp;Add Steps
                                    </a>
                                </div>
                                
                            @endif
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="{{ route('approval-workflows.show', [$form, $workflow]) }}" class="btn btn-secondary">
                                        <i class="fa-regular fa-arrow-left"></i>&nbsp;Back to Workflow
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection