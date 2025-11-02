@extends('layouts.app')

@section('title', 'Fill Forms')

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
                    <!-- Available Forms -->
                    <div class="row mb-4">
                        @forelse($forms as $form)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $form->name }}</h5>
                                    <p class="text-muted small">Form No: {{ $form->form_no }}</p>
                                    
                                    @if($form->description)
                                        <p class="card-text">{{ Str::limit($form->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="mb-3">
                                        @foreach($form->departments->take(3) as $dept)
                                            <span class="badge bg-secondary text-dark-fg">{{ $dept->shortname }}</span>
                                        @endforeach
                                        @if($form->departments->count() > 3)
                                            <span class="badge bg-secondary text-dark-fg">+{{ $form->departments->count() - 3 }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge {{ $form->requires_approval ? 'bg-warning' : 'bg-success' }} text-dark-fg">
                                            {{ $form->requires_approval ? 'Requires Approval' : 'Auto Approved' }}
                                        </span>
                                        <a href="{{ route('formsubmissions.create', $form) }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-pen-to-square"></i>&nbsp;Fill Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="far fa-circle-info"></i>&nbsp; &nbsp;No forms available for your department(s).
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <div class="row row-deck row-cards">
                        <!-- Recent Submissions -->
                        @if($recentSubmissions->count() > 0)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Your Recent Submissions</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 180px">Submission Code</th>
                                                    <th>Form Name</th>
                                                    <th style="width: 80px">Status</th>
                                                    <th style="width: 180px">Submitted At</th>
                                                    <th style="width: 80px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentSubmissions as $submission)
                                                <tr>
                                                    <td>
                                                        <code>{{ $submission->submission_code }}</code>
                                                    </td>
                                                    <td>{{ $submission->formVersion->form->name }}</td>
                                                    <td>
                                                        @switch($submission->status)
                                                            @case('draft')
                                                                <span class="badge badge-outline text-secondary">Draft</span>
                                                                @break
                                                            @case('submitted')
                                                                <span class="badge badge-outline text-info">Submitted</span>
                                                                @break
                                                            @case('under_review')
                                                                <span class="badge badge-outline text-warning">Under Review</span>
                                                                @break
                                                            @case('approved')
                                                                <span class="badge badge-outline text-success">Approved</span>
                                                                @break
                                                            @case('rejected')
                                                                <span class="badge badge-outline text-danger">Rejected</span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    <td>{{ formatDate($submission->submitted_at, 'd M Y H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('formsubmissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="far fa-eye"></i>&nbsp;View
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="text-center">
                                        <a href="{{ route('formsubmissions.submissions') }}" class="btn btn-outline-primary">
                                            View All Submissions&nbsp;<i class="far fa-arrow-right"></i>&nbsp;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection