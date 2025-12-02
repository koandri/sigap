@section('title', 'Permissions')

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
                                        <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-square-plus"></i>&nbsp;Add new permission
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px">#</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th style="width: 100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($permissions as $permission)
                                                <tr>
                                                    <td>{{ $permission->id }}</td>
                                                    <td>{{ $permission->name }}</td>
                                                    <td>{{ $permission->description ?? '—' }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('permissions.show', $permission) }}" class="btn btn-outline-secondary" title="View">
                                                                <i class="far fa-eye"></i>&nbsp;
                                                            </a>
                                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-outline-primary" title="Edit">
                                                                <i class="far fa-pen-to-square"></i>&nbsp;
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if ($permissions->hasPages())
                                <div class="card-footer clearfix">
                                    {{ $permissions->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection
