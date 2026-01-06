@extends('layouts.app')

@section('title', 'My Document Access')

@section('content')
<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        My Document Access
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('my-document-access') }}">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Document Type</label>
                                <select name="document_type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->value }}" {{ $filters['document_type'] == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ $filters['department'] == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Access Type</label>
                                <select name="access_type" class="form-select">
                                    <option value="">All Access Types</option>
                                    <option value="full" {{ $filters['access_type'] == 'full' ? 'selected' : '' }}>Full Access</option>
                                    @foreach($accessTypes as $accessType)
                                        <option value="{{ $accessType->value }}" {{ $filters['access_type'] == $accessType->value ? 'selected' : '' }}>
                                            {{ $accessType->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search documents..." value="{{ $filters['search'] }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="far fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accessible Documents -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Accessible Documents</h3>
                </div>
                <div class="card-body">
                    @if($accessibleDocuments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Department</th>
                                        <th>Access Type</th>
                                        <th>Status</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessibleDocuments as $version)
                                        @php
                                            // Get the most recent approved access request for this user
                                            $accessRequest = $version->accessRequests
                                                ->where('user_id', auth()->id())
                                                ->where('status', 'approved')
                                                ->sortByDesc('approved_at')
                                                ->sortByDesc('id')
                                                ->first();
                                            
                                            // Check if access is active
                                            $isActive = false;
                                            if ($accessRequest) {
                                                if ($accessRequest->getEffectiveAccessType()->isOneTime()) {
                                                    // For one-time access, check if it's been used
                                                    $isActive = $accessRequest->relationLoaded('accessLogs') 
                                                        ? $accessRequest->accessLogs->isEmpty()
                                                        : !$accessRequest->accessLogs()->exists();
                                                } else {
                                                    // For multiple access, check if not expired
                                                    $isActive = $accessRequest->isActive();
                                                }
                                            } else {
                                                // Full access (no access request)
                                                $isActive = true;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-bold">{{ $version->document->title }}</div>
                                                        <div class="text-muted">{{ $version->document->document_number }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-blue-lt">{{ $version->document->document_type->label() }}</span>
                                            </td>
                                            <td>{{ $version->document->department?->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($accessRequest)
                                                    <span class="badge bg-info text-white">{{ $accessRequest->getEffectiveAccessType()->label() }}</span>
                                                @else
                                                    <span class="badge bg-success text-white">Full Access</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($accessRequest)
                                                    @if($isActive)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Expired</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-success">Active</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($accessRequest && $accessRequest->getEffectiveExpiryDate())
                                                    {{ $accessRequest->getEffectiveExpiryDate()->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">No expiry</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($isActive)
                                                    <a href="{{ route('document-access.view', $version) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="far fa-eye"></i>&nbsp;
                                                        View
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Access expired or already used">
                                                        <i class="far fa-eye-slash"></i>&nbsp;
                                                        View
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($accessibleDocuments->hasPages())
                        <div class="mt-3">
                            {{ $accessibleDocuments->links() }}
                        </div>
                        @endif
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="far fa-eye"></i>
                            </div>
                            <p class="empty-title">No accessible documents</p>
                            <p class="empty-subtitle text-muted">
                                You don't have access to any documents yet. Request access to documents you need.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('documents.index') }}" class="btn btn-primary">
                                    <i class="far fa-folder"></i>
                                    Browse Documents
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
