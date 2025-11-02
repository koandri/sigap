@extends('layouts.app')

@section('title', 'Correspondence: ' . $instance->instance_number)

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">Correspondence: {{ $instance->instance_number }}</h2>
                </div>
                <div class="col-auto">
                    <a href="{{ route('correspondences.index') }}" class="btn btn-outline-secondary">
                        <i class="far fa-arrow-left"></i>&nbsp;
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @include('layouts.alerts')

            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Correspondence Details</h3>
                            <div class="card-actions">
                                @php
                                    $badgeClass = match($instance->status->value) {
                                        'draft' => 'bg-secondary',
                                        'pending_approval' => 'bg-warning',
                                        'approved' => 'bg-success',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-white">
                                    {{ $instance->status->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl>
                                    <dt>Reference Number</dt>
                                    <dd>{{ $instance->instance_number }}</dd>

                                        <dt>Template</dt>
                                        <dd>
                                            {{ $instance->templateVersion->document->title }}
                                            <small class="text-muted">(v{{ $instance->templateVersion->version_number }})</small>
                                        </dd>

                                        <dt>Subject</dt>
                                        <dd>{{ $instance->subject }}</dd>

                                        @if($instance->content_summary)
                                        <dt>Content Summary</dt>
                                        <dd>{{ $instance->content_summary }}</dd>
                                        @endif
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl>
                                        <dt>Created By</dt>
                                        <dd>{{ $instance->creator->name }}</dd>

                                        <dt>Created At</dt>
                                        <dd>{{ formatDate($instance->created_at) }}</dd>

                                        @if($instance->approved_by)
                                        <dt>Approved By</dt>
                                        <dd>{{ $instance->approver->name }}</dd>

                                        <dt>Approved At</dt>
                                        <dd>{{ formatDate($instance->approved_at) }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($instance->canBeEdited())
                                <a href="{{ route('correspondences.edit', $instance) }}" class="btn btn-primary">
                                    <i class="far fa-edit"></i>&nbsp;
                                    Edit
                                </a>
                                <form action="{{ route('correspondences.submit', $instance) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Submit this correspondence for approval?')">
                                        <i class="far fa-paper-plane"></i>&nbsp;
                                        Submit for Approval
                                    </button>
                                </form>
                            @endif

                            @can('approve', $instance)
                                @if($instance->status->value === 'pending_approval')
                                    <form action="{{ route('correspondences.approve', $instance) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this correspondence?')">
                                            <i class="far fa-check"></i>&nbsp;
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('correspondences.reject', $instance) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this correspondence?')">
                                            <i class="far fa-times"></i>&nbsp;
                                            Reject
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

