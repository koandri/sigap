@section('title', 'Edit Form Fields')

@extends('layouts.app')

@section('content')
<x-field-configs.field-header :form="$form" :version="$version" :isEdit="true" />

<!-- BEGIN PAGE BODY -->
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            @include('layouts.alerts')
            <x-field-configs.field-info :form="$form" :version="$version" :isEdit="true" :field="$field" :hasSubmissions="$hasSubmissions" />
        </div>
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <form class="card" action="{{ route('formfields.update', [$form, $version, $field]) }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h3 class="card-title">Edit Field: {{ $field->field_label }}</h3>
                    </div>
                    <div class="card-body border-bottom py-3">
                        <!-- Basic Field Information -->
                        <x-field-configs.basic-field-info :field="$field" :hasSubmissions="$hasSubmissions" :isEdit="true" />

                        <!-- Validation Configuration -->
                        <x-field-configs.validation-config :field="$field" :hasSubmissions="$hasSubmissions" />

                        <!-- Date/DateTime Validation Rules -->
                        <x-field-configs.date-validation :field="$field" :hasSubmissions="$hasSubmissions" />

                        <!-- File Upload Settings -->
                        <x-field-configs.file-settings :field="$field" :hasSubmissions="$hasSubmissions" />

                        <!-- Calculated Field Settings -->
                        <x-field-configs.calculated-field :field="$field" :hasSubmissions="$hasSubmissions" :form="$form" :version="$version" />

                        <!-- Hidden Field Settings -->
                        <x-field-configs.hidden-field :field="$field" :hasSubmissions="$hasSubmissions" />

                        <!-- Live Photo Settings -->
                        <x-field-configs.live-photo-settings :field="$field" :hasSubmissions="$hasSubmissions" />

                        <!-- Options Configuration -->
                        <x-field-configs.options-configuration :field="$field" :hasSubmissions="$hasSubmissions" :form="$form" :version="$version" />

                        <!-- Usage Statistics -->
                        @if($field->answers->count() > 0)
                        <div class="alert alert-secondary alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="far fa-database"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Info!</h4>
                                <div class="alert-description">
                                    This field has been answered <strong>{{ $field->answers->count() }}</strong> time(s).
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer clearfix">
                        <button type="submit" class="btn btn-primary">Update Field</button>
                        <a href="{{ route('formversions.show', [$form, $version]) }}" class="btn float-end">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE BODY --> 
@endsection

@push('scripts')
<x-field-configs.field-form-scripts :form="$form" :version="$version" :field="$field" :hasSubmissions="$hasSubmissions" :isEdit="true" />
@endpush
