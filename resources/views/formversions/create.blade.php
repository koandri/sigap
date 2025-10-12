@section('title', 'Create New Form Version')

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
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-circle-info"></i>
                            </div>
                            <div>
                                <div class="alert-description">
                                    You will be creating version <strong>v{{ $nextVersion }}</strong> for form: <strong>{{ $form->name }}</strong>
                                </div>
                            </div>
                        </div>
                        @if($activeVersion)
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-regular fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <div class="alert-description">
                                    Current active version is <strong>v{{ $activeVersion->version_number }}</strong> with {{ $activeVersion->fields->count() }} fields.
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <form class="card" action="{{ route('formversions.store', [$form]) }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h3 class="card-title">@yield('title')</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div class="row mb-3">
                                        <label for="description" class="col-sm-2 col-form-label required">Version Description</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="What's new or changed in this version?">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                    @if($form->versions->count() > 0)
                                    <div class="row mb-3">
                                        <label for="copy_from_version" class="col-sm-2 col-form-label">Copy Fields From</label>
                                        <div class="col-sm-10">
                                            <select class="form-select" id="copy_from_version" name="copy_from_version">
                                                <option value="">-- Start Fresh --</option>
                                                @foreach($form->versions->sortByDesc('version_number') as $version)
                                                <option value="{{ $version->id }}" {{ old('copy_from_version') == $version->id ? 'selected' : '' }}>v{{ $version->version_number }} ({{ $version->fields->count() }} fields){{ $version->is_active ? '- Currently Active' : '' }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Select a version to copy all its fields and settings</small>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row mb-3">
                                        <label for="make_active" class="col-sm-2 col-form-label pt-0 required">Activate Immediately?</label>
                                        <div class="col-sm-10">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="make_active" name="make_active" value="1" {{ old('make_active') ? 'checked' : '' }}>
                                            </div>
                                            <small class="text-muted">This will deactivate the current active version (if any)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary">Create Version</button>
                                    <a href="{{ route('forms.show', $form) }}" class="btn float-end">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PAGE BODY --> 
@endsection