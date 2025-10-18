@extends('layouts.app')

@section('title', 'Cleaning Requests')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Facility Management</div>
                <h2 class="page-title">
                    <i class="fa fa-clipboard-list"></i> Cleaning Requests
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('facility.requests.guest-form') }}" class="btn btn-primary" target="_blank">
                        <i class="fa fa-external-link-alt"></i> Open Guest Form
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        @include('layouts.alerts')

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('facility.requests.index') }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="cleaning" {{ $type === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                <option value="repair" {{ $type === 'repair' ? 'selected' : '' }}>Repair</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('facility.requests.index') }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Requests ({{ $requests->total() }})</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Date</th>
                            <th>Requester</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="w-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>
                                <strong>{{ $request->request_number }}</strong>
                            </td>
                            <td>
                                <div>{{ $request->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div><strong>{{ $request->requester_name }}</strong></div>
                                <small class="text-muted">
                                    <i class="fa fa-phone"></i> {{ $request->requester_phone }}
                                </small>
                            </td>
                            <td>
                                <i class="fa fa-map-marker-alt text-muted"></i>
                                {{ $request->location->name }}
                            </td>
                            <td>
                                @if($request->request_type === 'cleaning')
                                    <span class="badge bg-blue"><i class="fa fa-broom"></i> Cleaning</span>
                                @else
                                    <span class="badge bg-orange"><i class="fa fa-wrench"></i> Repair</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;">
                                    {{ $request->description }}
                                </div>
                                @if($request->photo)
                                    <a href="#" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#photoModal{{ $request->id }}">
                                        <i class="fa fa-image"></i> View Photo
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($request->status === 'completed')
                                    <span class="badge bg-success"><i class="fa fa-check"></i> Completed</span>
                                @else
                                    <span class="badge bg-warning"><i class="fa fa-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    @if($request->status === 'pending')
                                        <a href="{{ route('facility.requests.handle-form', $request) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-check-circle"></i> Handle
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fa fa-check"></i> Handled
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Photo Modal -->
                        @if($request->photo)
                        <div class="modal fade" id="photoModal{{ $request->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Request Photo - {{ $request->request_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ Storage::disk('sigap')->url($request->photo) }}" 
                                             class="img-fluid" 
                                             alt="Request Photo">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa fa-inbox fa-3x"></i>
                                    </div>
                                    <p class="empty-title">No requests found</p>
                                    <p class="empty-subtitle text-muted">
                                        Try adjusting your filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

