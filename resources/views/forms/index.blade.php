@section('title', 'Forms')

@extends('layouts.app')

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
                                        <a href="{{ route('forms.create') }}" class="btn btn-primary btn-sm">
                                            <i class="far fa-square-plus"></i>&nbsp;Add new form
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 150px">Form No</th>
                                                    <th style="width: 300px">Name</th>
                                                    <th>Departments</th>
                                                    <th style="width: 100px">Approval</th>
                                                    <th style="width: 100px">Status</th>
                                                    <th style="width: 100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($forms as $form)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $form->form_no }}</strong>
                                                    </td>
                                                    <td>{{ $form->name }}</td>
                                                    <td>{{ $form->getDepartmentNames() }}</td>
                                                    <td>{!! formatBoolean($form->requires_approval) !!}</td>
                                                    <td>{!! formatBoolean($form->is_active) !!}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('forms.show', $form) }}" class="btn btn-outline-secondary" title="View">
                                                                <i class="far fa-eye"></i>&nbsp;
                                                            </a>
                                                            <a href="{{ route('forms.edit', $form) }}" class="btn btn-outline-primary" title="Edit">
                                                                <i class="far fa-pen-to-square"></i>&nbsp;
                                                            </a>
                                                            <a href="#" class="btn btn-outline-danger" onclick="confirm('Are you sure?'); event.preventDefault(); document.getElementById('delete-{{ $form->id }}').submit();" title="Delete">
                                                                <i class="far fa-trash-can"></i>&nbsp;
                                                            </a>
                                                            <form id="delete-{{ $form->id }}" action="{{ route('forms.destroy', $form) }}" method="POST" style="display: none;" onsubmit="return confirm('Are you sure?')">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <p class="mb-0">No forms found.</p>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if ($forms->hasPages())
                                <div class="card-footer clearfix">
                                    {{ $forms->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection