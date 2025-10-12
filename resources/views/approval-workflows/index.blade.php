@extends('layouts.app')

@section('title', 'Approval Workflows')

@section('title', 'Users')

@extends('layouts.app')

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

                        <!-- Form Status Alert -->      
                        @if(!$form->requires_approval)
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Approval Not Required</h4>
                                <div class="alert-description">
                                    This form is set to not require approval. 
                                    Workflows will not be executed unless you enable "Requires Approval" in form settings.
                                    <a href="{{ route('forms.edit', $form) }}" class="btn btn-sm btn-outline-primary ms-2">
                                        Edit Form Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Approval Workflows for Form: {{ $form->name }} ({{ $form->form_no }})</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('approval-workflows.create', $form) }}" class="btn btn-sm btn-success">
                                            <i class="fa-regular fa-circle-plus"></i>&nbsp;Create Workflow
                                        </a>
                                        <a href="{{ route('forms.show', $form) }}" class="btn btn-sm btn-secondary">
                                            <i class="fa-regular fa-arrow-left"></i>&nbsp;Back to Form
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <!-- Workflows List -->
                                    @if($workflows->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 300px;">Workflow Name</th>
                                                    <th>Details</th>
                                                    <th style="width: 80px;">Status</th>
                                                    <th style="width: 280px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    @foreach($workflows as $workflow)                          
                                                <tr>
                                                    <td>{{ $workflow->workflow_name }}</td>
                                                    <td>
                                                        @if($workflow->description)
                                                            <strong>Description:</strong> 
                                                            <p class="text-muted">{{ $workflow->description }}</p>
                                                        @endif
                                                        
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <strong>Flow Type:</strong> 
                                                                <span class="badge badge-outline text-{{ $workflow->flow_type === 'sequential' ? 'primary' : 'info' }}">
                                                                    {{ ucfirst($workflow->flow_type) }}
                                                                </span>
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <strong># of Steps:</strong> {{ $workflow->steps->count() }}
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <strong>Created by:</strong> {{ $workflow->creator?->name ?? '-' }}
                                                                <br>
                                                                <strong>Created:</strong> {{ $workflow->created_at->timezone('Asia/Jakarta')->format('d M Y') }} WIB
                                                            </small>
                                                        </div>
                                                        
                                                        <!-- Steps Preview -->
                                                        <div class="workflow-preview">
                                                            <small class="text-muted"><strong>Steps:</strong></small>
                                                            <ol class="small">
                                                                @foreach($workflow->getOrderedSteps()->take(3) as $step)
                                                                    <li>{{ $step->step_name }} 
                                                                        <span class="text-muted">({{ $step->getApproverDisplayName() }})</span>
                                                                    </li>
                                                                @endforeach
                                                                @if($workflow->steps->count() > 3)
                                                                    <li class="text-muted">... and {{ $workflow->steps->count() - 3 }} more</li>
                                                                @endif
                                                            </ol>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($workflow->is_active)
                                                        <span class="badge badge-outline text-success">Active</span>
                                                        @else
                                                        <span class="badge badge-outline text--secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('approval-workflows.show', [$form, $workflow]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fa-regular fa-eye"></i>&nbsp;View
                                                        </a>
                                                        <a href="{{ route('approval-workflows.edit', [$form, $workflow]) }}" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa-regular fa-pen-to-square"></i>&nbsp;Edit
                                                        </a>
                                                        @if(!$workflow->is_active)
                                                            <form action="{{ route('approval-workflows.toggle', [$form, $workflow]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Activate this workflow?')">
                                                                    <i class="fa-regular fa-circle-check"></i>&nbsp;Activate
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('approval-workflows.toggle', [$form, $workflow]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PUT')
                                                                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Deactivate this workflow?')">
                                                                    <i class="fa-regular fa-circle-pause"></i>&nbsp;Deactivate
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                    @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <!-- No Workflows -->
                                    <div class="card-body text-center py-5">
                                        <i class="fa-regular fa-diagram-project display-1 text-muted"></i>
                                        <h4 class="mt-3">No Approval Workflows</h4>
                                        <p class="text-muted">Create an approval workflow to define how submissions should be reviewed.</p>
                                        <a href="{{ route('approval-workflows.create', $form) }}" class="btn btn-primary">
                                            <i class="fa-regular fa-circle-plus"></i>&nbsp;Create First Workflow
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection
