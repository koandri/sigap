@props(['form', 'version', 'submission' => null, 'fields', 'prefillData' => [], 'formAction', 'formMethod' => 'POST'])

@extends('layouts.app')

@section('title', $submission ? 'Edit Submission' : 'Fill Form')

@push('css')
<link rel="stylesheet" href="/assets/css/formsubmissions.css" />
<link rel="stylesheet" href="/assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css" />
@endpush

@section('content')
    <!-- Form Header -->
    @include('formsubmissions.partials.form-header', [
        'form' => $form,
        'version' => $version,
        'submission' => $submission
    ])

    <!-- BEGIN PAGE BODY -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    @include('layouts.alerts')
                    
                    <!-- Form Description -->
                    @include('formsubmissions.partials.form-description', ['form' => $form])
                </div>
            </div>
            
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <form class="card" action="{{ $formAction }}" method="{{ $formMethod }}" enctype="multipart/form-data" id="submissionForm" data-form-version-id="{{ $version->id }}">
                        @csrf
                        @if($formMethod !== 'POST')
                            @method($formMethod)
                        @elseif(isset($submission) && $submission)
                            @method('PUT')
                        @endif
                        
                        <div class="card-header">
                            <h3 class="card-title">@yield('title')</h3>
                        </div>
                        
                        <div class="card-body border-bottom py-3">
                            @foreach($fields as $field)
                                @php
                                    $fieldValue = $submission ? 
                                        ($submission->answers->where('form_field_id', $field->id)->first()?->answer_value ?? '') : 
                                        '';
                                @endphp
                                
                                <x-form-field :field="$field" :value="$fieldValue" :prefillData="$prefillData" />
                            @endforeach
                        </div>
                        
                        <div class="card-footer">
                            <div class="row">
                                <div class="col">
                                    <a href="{{ $submission ? route('formsubmissions.show', $submission) : route('formsubmissions.submissions') }}" 
                                       class="btn btn-secondary">
                                        <i class="fa-regular fa-arrow-left"></i>
                                        Cancel
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group" role="group">
                                        <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-save"></i>&nbsp;Save Draft
                                        </button>
                                        <button type="submit" name="action" value="submit" class="btn btn-primary">
                                            <i class="fa-regular fa-paper-plane"></i>
                                            {{ $submission ? 'Update & Submit' : 'Submit Form' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE BODY -->
@endsection

@push('scripts')
<script src="/assets/tabler/libs/tom-select/dist/js/tom-select.base.min.js"></script>
<script src="/assets/js/signature_pad.umd.min.js"></script>
<script src="/assets/js/formsubmissions.js"></script>

@if(config('app.tinymce_enabled', true))
<script src="/assets/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
@endif
@endpush