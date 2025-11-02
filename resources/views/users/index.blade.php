@section('title', 'Users')

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
                                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-square-plus"></i>&nbsp;Add new user
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px">#</th>
                                                    <th style="width: 250px">Name</th>
                                                    <th>Departments</th>
                                                    <th>Roles</th>
                                                    <th style="width: 250px">Manager</th>
                                                    <th style="width: 100px">Active?</th>
                                                    <th style="width: 100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($users as $user)
                                                <tr>
                                                    <td>{{ $user->id }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->getDepartmentShortNames() }}</td>
                                                    <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                                                    <td>{{ !is_null($user->manager_id) ? $user->manager->name : "" }}</td>
                                                    <td>{!! formatBoolean($user->active) !!}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            @canBeImpersonated($user)
                                                            <a href="{{ route('impersonate', $user->id) }}" class="btn btn-outline-warning" title="Impersonate">
                                                                <i class="far fa-user-dashed"></i>&nbsp; &nbsp;   
                                                            </a>
                                                            @endCanBeImpersonated
                                                            <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary" title="View">
                                                                <i class="far fa-eye"></i>&nbsp;
                                                            </a>
                                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Edit">
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
                                @if ($users->hasPages())
                                <div class="card-footer clearfix">
                                    {{ $users->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection
