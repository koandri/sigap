@section('title', 'Form Versions: ' . $form->name)

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
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                    <div class="card-actions">
                                        <a href="{{ route('formversions.create', $form) }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-square-plus"></i>&nbsp;Add new version
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        @if($versions->count() > 0)
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px">Ver.</th>
                                                    <th>Description</th>
                                                    <th style="width: 100px">Fields</th>
                                                    <th style="width: 100px">Submissions</th>
                                                    <th style="width: 50px">Active?</th>
                                                    <th style="width: 200px">Created By</th>
                                                    <th style="width: 100px">Created On</th>
                                                    <th style="width: 100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($versions as $version)
                                                    <tr>
                                                        <td>{{ $version->version_number }}</td>
                                                        <td>{{ Str::limit($version->description ?: 'No description', 50) }}</td>
                                                        <td>{{ $version->fields->count() }}</td>
                                                        <td>{{ $version->submissions->count() }}</td>
                                                        <td>{!! formatBoolean($version->is_active) !!}</td>
                                                        <td>{{ $version->creator?->name ?? '-' }}</td>
                                                        <td>{{ $version->created_on->format('d M Y') }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('formversions.show', [$form, $version]) }}" class="btn btn-outline-info btn-sm" title="View Details"><i class="far fa-eye"></i></a>
                                                                
                                                                @if(!$version->is_active && $version->fields->count() > 0)
                                                                <a href="#" class="btn btn-outline-info btn-sm" onclick="confirm('Activate this version?'); ; event.preventDefault(); document.getElementById('activate-version-{{ $version->id }}').submit();"><i class="far fa-circle-check"></i></a>
                                                                <form action="{{ route('formversions.activate', [$form, $version]) }}" method="POST" class="d-inline" id="activate-version-{{ $version->id }}" style="display: none;">
                                                                    @csrf
                                                                    @method('PUT')
                                                                </form>
                                                                @endif
                                                                
                                                                @if(!$version->is_active && $version->submissions->count() == 0 && $versions->count() > 1)
                                                                <a href="#" class="btn btn-outline-info btn-sm" onclick="confirm('Delete this version?'); ; event.preventDefault(); document.getElementById('delete-version-{{ $version->id }}').submit();"><i class="far fa-circle-check"></i></a>
                                                                <form action="{{ route('formversions.destroy', [$form, $version]) }}" method="POST" class="d-inline" id="delete-version-{{ $version->id }}" style="display: none;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                            </tbody>
                                        </table>
                                        @else
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-3">No versions created yet.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection