@section('title', 'Edit')

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
                            <form class="card" action="{{ route('forms.update', $form) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="form_no" class="col-sm-2 col-form-label required">Form No</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="form_no_disabled" class="form-control" value="{{ $form->form_no }}" disabled />
                                            <input type="hidden" name="form_no" value="{{ $form->form_no }}" />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="name" class="col-sm-2 col-form-label required">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $form->name) }}" />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="description" class="col-sm-2 col-form-label">Description</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of what this form is for">{{ old('description', $form->description) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="departments[]" class="col-sm-2 col-form-label pt-0 required">Assign to Departments</label>
                                        <div class="col-sm-10">
                                            @foreach($departments as $department)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]" value="{{ $department->id }}" id="dept_{{ $department->id }}"{{ in_array($department->id, old('departments', $selectedDepartments)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dept_{{ $department->id }}">
                                                    <strong>{{ $department->shortname }}</strong> - {{ $department->name }}
                                                </label>
                                            </div>
                                            @endforeach
                                            <small class="form-text text-muted">Select at least one department</small>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="requires_approval" class="col-sm-2 col-form-label pt-0">Approval Required?</label>
                                        <div class="col-sm-10">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" value="1" {{ old('requires_approval', $form->requires_approval) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="is_active" class="col-sm-2 col-form-label">Active?</label>
                                        <div class="col-sm-10">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $form->is_active) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <a href="{{ route('forms.index') }}" class="btn float-end">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection