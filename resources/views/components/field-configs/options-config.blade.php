@props(['field' => null, 'hasSubmissions' => false])

<!-- Options Configuration -->
<div id="options-config" class="field-config-section" style="display: none;">
    <h4 class="mb-3">Options Configuration</h4>
    
    <!-- Option Source Selection -->
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Option Source</label>
        <div class="col-sm-10">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="option_source" id="static_options" value="static" checked>
                <label class="form-check-label" for="static_options">
                    Static Options
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="option_source" id="api_source" value="api">
                <label class="form-check-label" for="api_source">
                    API Source
                </label>
            </div>
        </div>
    </div>

    <!-- Static Options -->
    <div id="static-options-config">
        <div class="row mb-3">
            <div class="col-sm-2"></div>
            <div class="col-sm-10">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Options</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-option">
                        <i class="far fa-plus"></i> Add Option
                    </button>
                </div>
                <div id="options-container">
                    <!-- Options will be added here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- API Source Configuration -->
    <div id="api-source-config" style="display: none;">
        <div class="row mb-3">
            <label for="api_url" class="col-sm-2 col-form-label required">API URL</label>
            <div class="col-sm-10">
                <input type="url" class="form-control" id="api_url" name="api_url" placeholder="https://api.example.com/endpoint">
                <small class="form-text">Full URL to the API endpoint</small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="value_field" class="col-sm-2 col-form-label required">Value Field</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="value_field" name="value_field" placeholder="e.g., id">
                <small class="form-text">Field name in API response to use as option value</small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="label_field" class="col-sm-2 col-form-label required">Label Field</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="label_field" name="label_field" placeholder="e.g., name">
                <small class="form-text">Field name in API response to use as option label</small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="data_path" class="col-sm-2 col-form-label">Data Path</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="data_path" name="data_path" placeholder="e.g., data">
                <small class="form-text">JSON path to the array of options (optional)</small>
            </div>
        </div>

        <!-- Authentication -->
        <div class="row mb-3">
            <label for="auth_type" class="col-sm-2 col-form-label">Authentication</label>
            <div class="col-sm-10">
                <select class="form-select" id="auth_type" name="auth_type">
                    <option value="">No Authentication</option>
                    <option value="bearer">Bearer Token</option>
                    <option value="basic">Basic Authentication</option>
                    <option value="api_key">API Key</option>
                </select>
            </div>
        </div>

        <div id="auth-config" style="display: none;">
            <!-- Auth fields will be shown based on selection -->
        </div>

        <!-- Test API Button -->
        <div class="row mb-3">
            <div class="col-sm-2"></div>
            <div class="col-sm-10">
                <button type="button" class="btn btn-outline-info" id="test-api">
                    <i class="far fa-flask"></i> Test API
                </button>
                <span id="api-test-result" class="ms-2"></span>
            </div>
        </div>
    </div>
</div>