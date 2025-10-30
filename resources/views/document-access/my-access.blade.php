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
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessibleDocuments as $version)
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
                                            <td>{{ $version->document->department->name }}</td>
                                            <td>
                                                @php
                                                    $accessRequest = $version->accessRequests->where('user_id', auth()->id())->first();
                                                @endphp
                                                @if($accessRequest)
                                                    <span class="badge bg-info text-white">{{ $accessRequest->getEffectiveAccessType()->label() }}</span>
                                                @else
                                                    <span class="badge bg-success text-white">Full Access</span>
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
                                                <a href="{{ route('document-versions.view', $version) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-eye"></i>
                            </div>
                            <p class="empty-title">No accessible documents</p>
                            <p class="empty-subtitle text-muted">
                                You don't have access to any documents yet. Request access to documents you need.
                            </p>
                            <div class="empty-action">
                                <a href="{{ route('documents.index') }}" class="btn btn-primary">
                                    <i class="ti ti-folder"></i>
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
