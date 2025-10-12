@section('title', 'Add Form Fields')

@extends('layouts.app')

@section('content')
<x-field-configs.field-header :form="$form" :version="$version" :isEdit="false" />

<!-- BEGIN PAGE BODY -->
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            @include('layouts.alerts')
            <x-field-configs.field-info :form="$form" :version="$version" :isEdit="false" :hasSubmissions="false" />
        </div>
        
        <div class="row row-deck row-cards">
            <div class="col-12">
                <form class="card" action="{{ route('formfields.store', [$form, $version]) }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">@yield('title')</h3>
                    </div>
                    <div class="card-body border-bottom py-3">
                        <!-- Basic Field Information -->
                        <x-field-configs.basic-field-info :field="null" :hasSubmissions="false" :isEdit="false" />

                        <!-- Validation Configuration -->
                        <x-field-configs.validation-config :field="null" :hasSubmissions="false" />

                        <!-- Date/DateTime Validation Rules -->
                        <x-field-configs.date-validation :field="null" :hasSubmissions="false" />

                        <!-- File Upload Settings -->
                        <x-field-configs.file-settings :field="null" :hasSubmissions="false" />

                        <!-- Calculated Field Settings -->
                        <x-field-configs.calculated-field :field="null" :hasSubmissions="false" :form="$form" :version="$version" />

                        <!-- Hidden Field Settings -->
                        <x-field-configs.hidden-field :field="null" :hasSubmissions="false" />

                        <!-- Live Photo Settings -->
                        <x-field-configs.live-photo-settings :field="null" :hasSubmissions="false" />

                        <!-- Options Configuration -->
                        <x-field-configs.options-configuration :field="null" :hasSubmissions="false" :form="$form" :version="$version" />
                    </div>
                    <div class="card-footer clearfix">
                        <button type="submit" class="btn btn-primary">Add Field</button>
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
<x-field-configs.field-form-scripts :form="$form" :version="$version" :field="null" :hasSubmissions="false" :isEdit="false" />
@endpush
