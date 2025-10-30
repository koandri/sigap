@section('title', 'Manage Form Field Options')

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
                                <i class="far fa-circle-info"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Field: <strong>{{ $field->field_label }}</strong> ({{ $field->field_code }})</h4>
                                <div class="alert-description">
                                    Form: <strong>{{ $form->name }}</strong> - Version {{ $version->version_number }}
                                </div>
                            </div>
                        </div>

                        @if($hasSubmissions)
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <div class="alert-icon">
                                <i class="fa-sharp fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h4 class="alert-heading">Limited Editing</h4>
                                <div class="alert-description">
                                    This version has submissions. You can only edit option labels, not values.
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="row row-deck row-cards">
                        <div class="col-12">
                            <form class="card" action="{{ route('formfields.options.update', [$form, $version, $field]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h3 class="card-title">Manage Options for: {{ $field->field_label }}</h3>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <div id="optionsContainer">
                                        @forelse($options as $index => $option)
                                        <div class="option-row mb-3 p-3 border rounded">
                                            <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Option Value</label>
                                                    <input type="text" class="form-control" name="options[{{ $index }}][value]" value="{{ $option->option_value }}" placeholder="Value" {{ $hasSubmissions ? 'readonly' : 'required' }}>
                                                    @if($hasSubmissions)
                                                        <small class="text-muted">Cannot change after submissions</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Display Label</label>
                                                    <input type="text" class="form-control" name="options[{{ $index }}][label]" value="{{ $option->option_label }}" placeholder="Display Label" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Default?</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="options[{{ $index }}][is_default]" value="1" {{ $option->is_default ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    @if(!$hasSubmissions)
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeOption(this)"><i class="far fa-trash-can"></i></button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted">No options defined yet.</p>
                                        @endforelse
                                    </div>

                                    @if(!$hasSubmissions)
                                    <button type="button" class="btn btn-secondary mb-3" onclick="addOption()"><i class="far fa-plus"></i> &nbsp;Add New Option</button>
                                    @endif
                                </div>
                                <div class="card-footer clearfix">
                                    <button type="submit" class="btn btn-primary">Submit</button>
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
<script>
    let optionIndex = {{ $options->count() }};

    @if(!$hasSubmissions)
    function addOption() {
        const container = document.getElementById('optionsContainer');
        const newOption = document.createElement('div');
        newOption.className = 'option-row mb-3 p-3 border rounded';
        newOption.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Option Value</label>
                    <input type="text" class="form-control" name="options[new_${optionIndex}][value]" placeholder="Value" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Display Label</label>
                    <input type="text" class="form-control" name="options[new_${optionIndex}][label]" placeholder="Display Label" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Default</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="options[new_${optionIndex}][is_default]" value="1">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeOption(this)"><i class="far fa-trash-can"></i></button>
                </div>
            </div>
        `;
        container.appendChild(newOption);
        optionIndex++;
    }

    function removeOption(button) {
        if(confirm('Remove this option?')) {
            button.closest('.option-row').remove();
        }
    }
    @endif
</script>
@endpush