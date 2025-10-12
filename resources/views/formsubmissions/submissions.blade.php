@extends('layouts.app')

@section('title', 'Submissions')

@section('content')
            <!-- BEGIN PAGE HEADER -->
            <div class="page-header d-print-none" aria-label="Page header">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">@yield('title')</h2>
                            @if($hasFullAccess)
                                <span class="badge bg-success text-dark-fg">
                                    <i class="fa-regular fa-shield-check"></i> &nbsp;Full Access
                                </span>
                            @endif
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h4 class="mb-0 text-primary">{{ $stats['total'] ?? 0 }}</h4>
                                    <small class="text-muted">Total Results</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h4 class="mb-0 text-info">{{ $stats['today'] ?? 0 }}</h4>
                                    <small class="text-muted">Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h4 class="mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h4>
                                    <small class="text-muted">Pending Review</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h4 class="mb-0 text-success">{{ $stats['approved'] ?? 0 }}</h4>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <!-- Filter Card -->
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fa-regular fa-filter"></i> &nbsp;Filters
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('formsubmissions.submissions') }}" id="filterForm">
                                        <!-- Filter Mode Row -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Show Submissions:</label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="filter_mode" id="filter_my" value="my" {{ $filterMode == 'my' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="btn btn-outline-primary" for="filter_my">
                                                        <i class="fa-regular fa-person"></i> &nbsp;My Submissions
                                                    </label>

                                                    <input type="radio" class="btn-check" name="filter_mode" id="filter_dept" value="department" {{ $filterMode == 'department' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="btn btn-outline-primary" for="filter_dept">
                                                        <i class="fa-regular fa-building"></i> &nbsp;Department Submissions
                                                    </label>

                                                    @if($hasFullAccess)
                                                    <input type="radio" class="btn-check" name="filter_mode" id="filter_all" value="all" {{ $filterMode == 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                                                    <label class="btn btn-outline-primary" for="filter_all">
                                                        <i class="fa-regular fa-globe"></i> &nbsp;All Submissions
                                                    </label>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Other Filters -->
                                        <div class="row g-3">
                                            <!-- Row 1 -->
                                            <div class="col-md-3">
                                                <label class="form-label">Submission Code</label>
                                                <input type="text" class="form-control" name="submission_code" placeholder="e.g., FRM-202401-0001" value="{{ request('submission_code') }}">
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <label class="form-label">Form Name</label>
                                                <select class="form-select" name="form_id">
                                                    <option value="">All Forms</option>
                                                    @foreach($availableForms as $form)
                                                    <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                                                        {{ $form->name }} ({{ $form->form_no }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="">All Status</option>
                                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                                                        Draft
                                                    </option>
                                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>
                                                        Submitted
                                                    </option>
                                                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>
                                                        Under Review
                                                    </option>
                                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                                        Approved
                                                    </option>
                                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                                        Rejected
                                                    </option>
                                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                                        Cancelled
                                                    </option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <label class="form-label">Submitter</label>
                                                <select class="form-select" name="submitter_id" {{ $filterMode == 'my' ? 'disabled' : '' }}>
                                                    <option value="">All Submitters</option>
                                                    @foreach($availableSubmitters as $submitter)
                                                    <option value="{{ $submitter->id }}" {{ request('submitter_id') == $submitter->id ? 'selected' : '' }}>
                                                        {{ $submitter->name }}
                                                        @if($submitter->id == auth()->id())
                                                        (Me)
                                                        @endif
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row g-3 mt-2">
                                            <!-- Row 2 - Date Range -->
                                            <div class="col-md-3">
                                                <label class="form-label">Date From</label>
                                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from', $dateFrom) }}" max="{{ date('Y-m-d') }}">
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <label class="form-label">Date To</label>
                                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to', $dateTo) }}" max="{{ date('Y-m-d') }}">
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">&nbsp;</label>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa-regular fa-magnifying-glass"></i>&nbsp;Search
                                                    </button>
                                                    <a href="{{ route('formsubmissions.submissions') }}?filter_mode=my" class="btn btn-secondary">
                                                        <i class="fa-regular fa-circle-x"></i>&nbsp;Reset
                                                    </a>
                                                    
                                                    <!-- Quick Date Filters -->
                                                    <div class="btn-group ms-2" role="group">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('today')">
                                                            Today
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('week')">
                                                            7 Days
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('month')">
                                                            30 Days
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Results Table -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        @if($filterMode == 'my')
                                            My Submissions
                                        @elseif($filterMode == 'department')
                                            Department Submissions
                                        @else
                                            All Submissions
                                        @endif
                                        &nbsp;
                                        <span class="badge bg-secondary text-dark-fg">
                                            {{ $submissions->total() }} record(s)
                                        </span>
                                        
                                    </h3>
                                    <div class="card-actions">
                                        <a href="{{ route('formsubmissions.index') }}" class="btn btn-primary text-dark-fg">
                                            <i class="fa-regular fa-circle-plus"></i>&nbsp;Fill New Form
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($submissions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 180px">Submission Code</th>
                                                    <th>Form Name</th>
                                                    <th style="width: 250px">Submitter</th>
                                                    <th style="width: 80px">Status</th>
                                                    <th style="width: 180px">Submitted At</th>
                                                    <th style="width: 80px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($submissions as $submission)
                                                <tr>
                                                    <td>
                                                        <code>{{ $submission->submission_code }}</code>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $submission->formVersion->form->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $submission->formVersion->form->form_no }} 
                                                            (v{{ $submission->formVersion->version_number }})
                                                        </small>
                                                    </td>
                                                    <td>
                                                        {{ $submission->submitter->name }}</td>
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
                                                            @case('cancelled')
                                                                <span class="badge badge-outline text-dark">Cancelled</span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y H:i') : '-' }}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('formsubmissions.show', $submission) }}" class="btn btn-outline-primary" title="View"><i class="fa-regular fa-eye"></i></a>
                                                            @if($submission->status === 'draft' && $submission->submitted_by == auth()->id())
                                                            <a href="{{ route('formsubmissions.edit', $submission) }}" class="btn btn-outline-warning" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                                                            @endif
                                                            <a href="{{ route('formsubmissions.print', $submission) }}" target="_blank" class="btn btn-outline-secondary" title="Print"><i class="fa-regular fa-print"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-3">
                                        {{ $submissions->appends(request()->query())->links('pagination::bootstrap-5') }}
                                    </div>
                                    @else
                                    <div class="text-center py-5">
                                        <i class="fa-regular fa-inbox display-1 text-muted"></i>
                                        <p class="mt-3 text-muted">No submissions found</p>
                                        @if($filterMode == 'my')
                                            <a href="{{ route('formsubmissions.index') }}" class="btn btn-primary">
                                                Fill Your First Form
                                            </a>
                                        @else
                                            <a href="{{ route('formsubmissions.submissions') }}?filter_mode=my" class="btn btn-secondary">
                                                View My Submissions
                                            </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row row-deck row-cards">
                        
                    </div>

                    

                    
                    
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            

        
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection

@push('scripts')
<script>
// Quick date range setter
function setDateRange(range) {
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    const today = new Date();
    
    dateTo.value = today.toISOString().split('T')[0];
    
    switch(range) {
        case 'today':
            dateFrom.value = today.toISOString().split('T')[0];
            break;
        case 'week':
            const weekAgo = new Date(today);
            weekAgo.setDate(today.getDate() - 7);
            dateFrom.value = weekAgo.toISOString().split('T')[0];
            break;
        case 'month':
            const monthAgo = new Date(today);
            monthAgo.setDate(today.getDate() - 30);
            dateFrom.value = monthAgo.toISOString().split('T')[0];
            break;
    }
    
    // Auto submit form
    document.getElementById('filterForm').submit();
}

// Disable/enable submitter dropdown based on filter mode
document.addEventListener('DOMContentLoaded', function() {
    const filterRadios = document.querySelectorAll('input[name="filter_mode"]');
    const submitterSelect = document.querySelector('select[name="submitter_id"]');
    
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'my') {
                submitterSelect.disabled = true;
                submitterSelect.value = '';
            } else {
                submitterSelect.disabled = false;
            }
        });
    });
});
</script>
@endpush