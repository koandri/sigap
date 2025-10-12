@props(['field' => null, 'hasSubmissions' => false, 'form' => null, 'version' => null])

<div id="optionsSection" style="display: none;">
    <div class="hr-text hr-text-start">Options Configuration</div>
    
    @php
        $hasApiSource = $field ? $field->hasApiSource() : false;
        $apiConfig = $field ? $field->getApiSourceConfig() : [];
    @endphp
    
    <!-- Option Source Selection -->
    <div class="mb-3">
        <label class="form-label">Option Source</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="option_source" id="option_source_static" value="static" 
                   {{ !$hasApiSource ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
            <label class="form-check-label" for="option_source_static">
                <strong>Static Options</strong>
                <br>
                <small class="text-muted">Manually defined options</small>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="option_source" id="option_source_api" value="api" 
                   {{ $hasApiSource ? 'checked' : '' }} {{ $hasSubmissions ? 'disabled' : '' }}>
            <label class="form-check-label" for="option_source_api">
                <strong>API Source</strong>
                <br>
                <small class="text-muted">Fetch options from external API</small>
            </label>
        </div>
        @if($hasSubmissions)
            <input type="hidden" name="option_source" value="{{ $hasApiSource ? 'api' : 'static' }}">
            <small class="text-warning">Cannot change option source after submissions</small>
        @endif
    </div>
    
    <!-- Static Options Container -->
    <div id="staticOptionsContainer" style="{{ $hasApiSource ? 'display: none;' : '' }}">
        @if($field && $field->hasOptions() && !$hasApiSource)
        <div class="alert alert-info alert-dismissible" role="alert">
            <div class="alert-icon">
                <i class="fa-regular fa-list-ul"></i>
            </div>
            <div>
                <h4 class="alert-heading">Current Options</h4>
                <div class="alert-description">
                    This field has <strong>{{ $field->options->count() }}</strong> static option(s).
                    <a href="{{ route('formfields.options', [$form, $version, $field]) }}" class="btn btn-sm btn-primary ms-2">
                        Manage Options
                    </a>
                </div>
            </div>
        </div>
        @elseif($field && !$hasApiSource)
        <div class="alert alert-warning alert-dismissible" role="alert">
            <div class="alert-icon">
                <i class="fa-regular fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="alert-heading">No Options</h4>
                <div class="alert-description">
                    This field has no options configured.
                    <a href="{{ route('formfields.options', [$form, $version, $field]) }}" class="btn btn-sm btn-primary ms-2">
                        Add Options
                    </a>
                </div>
            </div>
        </div>
        @elseif(!$field)
        <div class="alert alert-info alert-dismissible" role="alert">
            <div class="alert-icon">
                <i class="fa-regular fa-circle-info"></i>
            </div>
            <div>
                <h4 class="alert-heading">Options Configuration</h4>
                <div class="alert-description">
                    Choose how to provide options for this field. You can use static options or fetch them from an API.
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- API Source Container -->
    <div id="apiOptionsContainer" style="{{ $hasApiSource ? '' : 'display: none;' }}">
        <div class="alert alert-info alert-dismissible" role="alert">
            <div class="alert-icon">
                <i class="fa-regular fa-cloud"></i>
            </div>
            <div>
                <h4 class="alert-heading">API Source Configuration</h4>
                <div class="alert-description">
                    Options are fetched from an external API.
                </div>
            </div>
        </div>
        
        <!-- Basic API Configuration -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="api_url" class="form-label required">API URL</label>
                    <input type="url" class="form-control" id="api_url" name="api_source_config[url]" value="{{ old('api_source_config.url', $apiConfig['url'] ?? '') }}" placeholder="https://api.example.com/endpoint" {{ $hasSubmissions ? 'readonly' : '' }}>
                </div>
                
                <div class="mb-3">
                    <label for="api_value_field" class="form-label required">Value Field</label>
                    <input type="text" class="form-control" id="api_value_field" name="api_source_config[value_field]" value="{{ old('api_source_config.value_field', $apiConfig['value_field'] ?? 'id') }}" placeholder="id" {{ $hasSubmissions ? 'readonly' : '' }}>
                    <small class="form-text text-muted">Field name in API response for option values</small>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="api_label_field" class="form-label required">Label Field</label>
                    <input type="text" class="form-control" id="api_label_field" name="api_source_config[label_field]" value="{{ old('api_source_config.label_field', $apiConfig['label_field'] ?? 'name') }}" placeholder="name" {{ $hasSubmissions ? 'readonly' : '' }}>
                    <small class="form-text text-muted">
                        Field name in API response for option labels. 
                        <br><strong>For combined labels:</strong> Use format like <code>{name} - {gender} - {location}</code>
                        <br><strong>Examples:</strong> <code>name</code> or <code>{first_name} {last_name}</code> or <code>{name} - {department}</code>
                    </small>
                </div>
                
                <div class="mb-3">
                    <label for="api_data_path" class="form-label">Data Path</label>
                    <input type="text" class="form-control" id="api_data_path" name="api_source_config[data_path]" value="{{ old('api_source_config.data_path', $apiConfig['data_path'] ?? '') }}" placeholder="data" {{ $hasSubmissions ? 'readonly' : '' }}>
                    <small class="form-text text-muted">JSON path to array (e.g., "data" for {"data": [...]})</small>
                </div>
            </div>
        </div>
        
        @if(!$hasSubmissions)
        <div class="mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm" id="testApiButton">
                <i class="fa-regular fa-flask"></i> &nbsp;Test API
            </button>
            <span id="testApiResult" class="ms-2"></span>
        </div>
        @endif
    </div>
</div>