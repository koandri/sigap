@extends('layouts.app')

@section('title', 'Submission Details')

@push('css')
<link rel="stylesheet" href="/assets/css/submission-show.css">
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <ol class="breadcrumb mt-3 mb-3" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('formsubmissions.submissions') }}">Submissions</a></li>
            <li class="breadcrumb-item active">{{ $submission->submission_code }}</li>
        </ol>

        @include('layouts.alerts')
        
        <div class="row row-deck row-cards">
        <!-- Submission Header -->
        @include('formsubmissions.partials.header', ['submission' => $submission])

        <!-- Submission Information -->
        @include('formsubmissions.partials.submission-info', ['submission' => $submission])

        <!-- Approval Status (if applicable) -->
        @if($submission->needsApproval())
            @include('formsubmissions.partials.approval-status', [
                'submission' => $submission,
                'approvalSummary' => $approvalSummary ?? null,
                'canApprove' => $canApprove ?? false,
                'pendingApproval' => $pendingApproval ?? null
            ])
                                        @endif

                            <!-- Form Answers -->
        @include('formsubmissions.partials.form-answers', [
            'submission' => $submission,
            'fields' => $submission->formVersion->getFieldsInOrder()
        ])
        </div>
    </div>
@endsection

@push('scripts')
<script src="/assets/js/submission-show.js"></script>
@endpush