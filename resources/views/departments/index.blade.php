@section('title', 'Departments')

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
                                        <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fa-regular fa-square-plus"></i>&nbsp;Add new department
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px">#</th>
                                                    <th style="width: 100px">Short Name</th>
                                                    <th>Name</th>
                                                    <th style="width: 100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($departments as $department)
                                                <tr>
                                                    <td>{{ $department->id }}</td>
                                                    <td>{{ $department->shortname }}</td>
                                                    <td>{{ $department->name }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('departments.show', $department) }}" class="btn btn-outline-secondary" title="View">
                                                                <i class="fa-regular fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('departments.edit', $department) }}" class="btn btn-outline-primary" title="Edit">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if ($departments->hasPages())
                                <div class="card-footer clearfix">
                                    {{ $departments->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection