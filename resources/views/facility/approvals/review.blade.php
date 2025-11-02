@extends('layouts.app')

@section('title', 'Review Submission')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">Review Submission</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('facility.approvals.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left"></i>&nbsp; Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- Task Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Task Information</h3>
                @if($approval->is_flagged_for_review)
                    <div class="card-actions">
                        <span class="badge bg-warning">
                            <i class="fa fa-star"></i>&nbsp; Flagged for Mandatory Review
                        </span>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-5">Task Number:</dt>
                            <dd class="col-7"><strong>{{ $approval->cleaningSubmission->cleaningTask->task_number }}</strong></dd>
                            
                            <dt class="col-5">Location:</dt>
                            <dd class="col-7">{{ $approval->cleaningSubmission->cleaningTask->location->name }}</dd>
                            
                            <dt class="col-5">Item:</dt>
                            <dd class="col-7">{{ $approval->cleaningSubmission->cleaningTask->item_name }}</dd>
                            
                            @if($approval->cleaningSubmission->cleaningTask->asset)
                            <dt class="col-5">Asset:</dt>
                            <dd class="col-7">
                                {{ $approval->cleaningSubmission->cleaningTask->asset->code }} - 
                                {{ $approval->cleaningSubmission->cleaningTask->asset->name }}
                            </dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-5">Submitted By:</dt>
                            <dd class="col-7">{{ $approval->cleaningSubmission->submittedByUser->name }}</dd>
                            
                            <dt class="col-5">Submitted At:</dt>
                            <dd class="col-7">{{ $approval->cleaningSubmission->submitted_at->format('M d, Y H:i') }}</dd>
                            
                            <dt class="col-5">SLA Status:</dt>
                            <dd class="col-7">
                                <span class="badge bg-{{ $approval->sla_color }}">
                                    @if($approval->hours_overdue > 0)
                                        Overdue by {{ number_format($approval->hours_overdue, 1) }} hours
                                    @else
                                        On Time
                                    @endif
                                </span>
                            </dd>
                            
                            <dt class="col-5">Deadline:</dt>
                            <dd class="col-7">{{ $approval->approval_deadline->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
                
                @if($approval->cleaningSubmission->notes)
                <div class="mt-3">
                    <strong>Notes:</strong>
                    <p class="text-muted">{{ $approval->cleaningSubmission->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Photos -->
        <div class="row row-cards mb-3">
            <!-- Before Photo -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-camera"></i>&nbsp; Before Photo
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $beforePhoto = $approval->cleaningSubmission->before_photo;
                            $beforePath = is_array($beforePhoto) ? ($beforePhoto['file_path'] ?? null) : null;
                        @endphp
                        
                        @if($beforePath)
                            <a href="{{ Storage::disk('sigap')->url($beforePath) }}" data-lightbox="cleaning-photos" data-title="Before Photo">
                                <img src="{{ Storage::disk('sigap')->url($beforePath) }}" 
                                     alt="Before Photo" 
                                     class="img-fluid rounded"
                                     style="max-height: 400px; cursor: pointer;">
                            </a>
                            
                            @if(isset($beforePhoto['gps_data']) && !empty($beforePhoto['gps_data']))
                            <div class="mt-2 text-muted small">
                                <i class="fa fa-map-marker-alt"></i>&nbsp;
                                GPS: {{ $beforePhoto['gps_data']['latitude'] ?? 'N/A' }}, 
                                {{ $beforePhoto['gps_data']['longitude'] ?? 'N/A' }}
                            </div>
                            @endif
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="fa fa-image fa-2x text-muted"></i>&nbsp;
                                </div>
                                <p class="empty-title">No photo available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- After Photo -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-camera"></i>&nbsp; After Photo
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $afterPhoto = $approval->cleaningSubmission->after_photo;
                            $afterPath = is_array($afterPhoto) ? ($afterPhoto['file_path'] ?? null) : null;
                        @endphp
                        
                        @if($afterPath)
                            <a href="{{ Storage::disk('sigap')->url($afterPath) }}" data-lightbox="cleaning-photos" data-title="After Photo">
                                <img src="{{ Storage::disk('sigap')->url($afterPath) }}" 
                                     alt="After Photo" 
                                     class="img-fluid rounded"
                                     style="max-height: 400px; cursor: pointer;">
                            </a>
                            
                            @if(isset($afterPhoto['gps_data']) && !empty($afterPhoto['gps_data']))
                            <div class="mt-2 text-muted small">
                                <i class="fa fa-map-marker-alt"></i>&nbsp;
                                GPS: {{ $afterPhoto['gps_data']['latitude'] ?? 'N/A' }}, 
                                {{ $afterPhoto['gps_data']['longitude'] ?? 'N/A' }}
                            </div>
                            @endif
                        @else
                            <div class="empty">
                                <div class="empty-icon">
                                    <i class="fa fa-image fa-2x text-muted"></i>&nbsp;
                                </div>
                                <p class="empty-title">No photo available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Actions -->
        @if($approval->status === 'pending')
        <div class="row row-cards">
            <!-- Approve -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success-lt">
                        <h3 class="card-title">
                            <i class="fa fa-check"></i>&nbsp; Approve Submission
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('facility.approvals.approve', $approval) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Approval Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="3" 
                                          placeholder="Any comments or observations..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check"></i>&nbsp; Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reject -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger-lt">
                        <h3 class="card-title">
                            <i class="fa fa-times"></i>&nbsp; Reject Submission
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('facility.approvals.reject', $approval) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to reject this submission?');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="notes" class="form-control" rows="3" 
                                          placeholder="Please provide a reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa fa-times"></i>&nbsp; Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-{{ $approval->status === 'approved' ? 'success' : 'danger' }}">
            <h4 class="alert-title">
                <i class="fa fa-{{ $approval->status === 'approved' ? 'check-circle' : 'times-circle' }}"></i>&nbsp;
                {{ ucfirst($approval->status) }}
            </h4>
            <p class="mb-0">
                This submission was {{ $approval->status }} by {{ $approval->approvedByUser?->name ?? 'N/A' }} 
                on {{ $approval->updated_at->format('M d, Y H:i') }}
            </p>
            @if($approval->notes)
            <p class="mt-2 mb-0">
                <strong>Notes:</strong> {{ $approval->notes }}
            </p>
            @endif
        </div>
        @endif

    </div>
</div>
@endsection

