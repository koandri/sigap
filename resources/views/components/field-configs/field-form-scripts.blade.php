@props(['form' => null, 'version' => null, 'field' => null, 'hasSubmissions' => false, 'isEdit' => false])

@push('scripts')
<script>
/**
 * FormField Editor - Consolidated JavaScript
 * Handles both creating and editing of form fields with submission protection
 */
(function() {
    'use strict';
    
    // ===== GLOBAL VARIABLES =====
    const hasSubmissions = {{ $hasSubmissions ? 'true' : 'false' }};
    const isEdit = {{ $isEdit ? 'true' : 'false' }};
    let fieldElements = {}; // Cache for DOM elements
    
    // ===== INITIALIZATION =====
    // Wait for page to be fully loaded to prevent layout forcing
    window.addEventListener('load', function() {
        cacheElements();
        initializeFieldTypeHandling();
        initializeDateValidation();
        initializeFileSettings();
        initializeValueTypeHandling();
        initializeApiConfiguration();
        initializeFormValidation();
        setInitialStates();
        loadInitialData();
    });
    
    function cacheElements() {
        fieldElements = {
            fieldCode: document.getElementById('field_code'),
            fieldLabel: document.getElementById('field_label'),
            fieldType: document.getElementById('field_type'),
            isRequired: document.getElementById('is_required'),
            requiredSection: document.getElementById('requiredFieldSection'),
            dateValidationSection: document.getElementById('dateValidationSection'),
            fileSettingsSection: document.getElementById('fileSettingsSection'),
            calculatedFieldSection: document.getElementById('calculatedFieldSection'),
            hiddenFieldSection: document.getElementById('hiddenFieldSection'),
            optionsSection: document.getElementById('optionsSection'),
            livePhotoSettingsSection: document.getElementById('livePhotoSettingsSection'),
            availableFields: document.getElementById('availableFields'),
            calculationFormula: document.getElementById('calculation_formula'),
            form: document.querySelector('form')
        };
        
    }
    
    function setInitialStates() {
        // Store original field code value for edit mode
        if (fieldElements.fieldCode && isEdit) {
            fieldElements.fieldCode.dataset.original = fieldElements.fieldCode.value || '';
        }
        
        // Set initial section visibility based on current field type
        const currentFieldType = fieldElements.fieldType?.value;
        if (currentFieldType) {
            handleFieldTypeDisplay(currentFieldType);
        }
        
        // Ensure API fields are properly configured for initial state
        updateApiFieldRequirements();
        
        // Add visual feedback for disabled elements
        if (hasSubmissions) {
            document.querySelectorAll('input[disabled], select[disabled], textarea[readonly]').forEach(element => {
                element.style.backgroundColor = '#f8f9fa';
                element.style.cursor = 'not-allowed';
            });
        }
    }
    
    function loadInitialData() {
        // Load available fields if this is a calculated field
        const fieldType = fieldElements.fieldType?.value;
        if (fieldType === 'calculated') {
            setTimeout(loadAvailableFields, 500);
        }
        
        // Initialize date validation displays based on current values
        initializeDateValidationDisplay();
    }
    
    // ===== FIELD TYPE HANDLING =====
    function initializeFieldTypeHandling() {
        if (!fieldElements.fieldType || hasSubmissions) return;
        
        fieldElements.fieldType.addEventListener('change', function(e) {
            handleFieldTypeChange(e.target.value);
        });
    }
    
    function handleFieldTypeChange(fieldType) {
        handleFieldTypeDisplay(fieldType);
        handleRequiredFieldVisibility(fieldType);
        clearOtherSections(fieldType);
    }
    
    function handleFieldTypeDisplay(fieldType) {
        const sections = {
            dateValidation: fieldElements.dateValidationSection,
            fileSettings: fieldElements.fileSettingsSection,
            calculatedField: fieldElements.calculatedFieldSection,
            hiddenField: fieldElements.hiddenFieldSection,
            options: fieldElements.optionsSection,
            livePhotoSettings: fieldElements.livePhotoSettingsSection
        };
        
        // Hide all sections first
        Object.values(sections).forEach(section => {
            if (section) section.style.display = 'none';
        });
        
        // Hide validation sections
        const validationConfig = document.getElementById('validation-config');
        const numberValidation = document.getElementById('number-validation');
        const decimalValidation = document.getElementById('decimal-validation');
        if (validationConfig) validationConfig.style.display = 'none';
        if (numberValidation) numberValidation.style.display = 'none';
        if (decimalValidation) decimalValidation.style.display = 'none';
        
        // Show relevant section
        if (fieldType === 'date' || fieldType === 'datetime') {
            if (sections.dateValidation) sections.dateValidation.style.display = 'block';
        } else if (fieldType === 'file') {
            if (sections.fileSettings) sections.fileSettings.style.display = 'block';
        } else if (fieldType === 'calculated') {
            if (sections.calculatedField) {
                sections.calculatedField.style.display = 'block';
                setTimeout(loadAvailableFields, 100);
            }
        } else if (fieldType === 'hidden') {
            if (sections.hiddenField) sections.hiddenField.style.display = 'block';
        } else if (fieldType === 'select_single' || fieldType === 'select_multiple') {
            if (sections.options) sections.options.style.display = 'block';
        } else if (fieldType === 'live_photo') {
            if (sections.livePhotoSettings) sections.livePhotoSettings.style.display = 'block';
        } else if (fieldType === 'number') {
            if (validationConfig) validationConfig.style.display = 'block';
            if (numberValidation) numberValidation.style.display = 'block';
        } else if (fieldType === 'decimal') {
            if (validationConfig) validationConfig.style.display = 'block';
            if (decimalValidation) decimalValidation.style.display = 'block';
        }
        
        // Update API field requirements based on visibility
        updateApiFieldRequirements();
    }
    
    function handleRequiredFieldVisibility(fieldType) {
        const requiredSection = fieldElements.requiredSection;
        const requiredCheckbox = fieldElements.isRequired;
        
        if (fieldType === 'calculated' || fieldType === 'hidden') {
            if (requiredSection) requiredSection.style.display = 'none';
            if (requiredCheckbox) {
                requiredCheckbox.checked = false;
                requiredCheckbox.disabled = true;
            }
        } else {
            if (requiredSection) requiredSection.style.display = 'block';
            if (requiredCheckbox) requiredCheckbox.disabled = false;
        }
    }
    
    function clearOtherSections(currentFieldType) {
        if (currentFieldType !== 'date' && currentFieldType !== 'datetime') {
            clearDateValidationInputs();
        }
        
        if (currentFieldType !== 'calculated') {
            clearCalculationInputs();
        }
        
        if (currentFieldType !== 'hidden') {
            clearHiddenFieldInputs();
        }
        
        if (currentFieldType !== 'select_single' && currentFieldType !== 'select_multiple') {
            clearOptionsInputs();
        }
        
        if (currentFieldType !== 'number') {
            clearNumberValidationInputs();
        }
        
        if (currentFieldType !== 'decimal') {
            clearDecimalValidationInputs();
        }
    }
    
    // ===== DATE VALIDATION =====
    function initializeDateValidation() {
        setupDateTypeHandlers();
        initializeDateValidationDisplay();
    }
    
    function setupDateTypeHandlers() {
        const minTypeSelect = document.getElementById('date_min_type');
        const maxTypeSelect = document.getElementById('date_max_type');
        
        if (minTypeSelect) {
            minTypeSelect.addEventListener('change', function(e) {
                handleDateTypeChange('min', e.target.value);
            });
        }
        
        if (maxTypeSelect) {
            maxTypeSelect.addEventListener('change', function(e) {
                handleDateTypeChange('max', e.target.value);
            });
        }
    }
    
    function initializeDateValidationDisplay() {
        const minType = document.getElementById('date_min_type');
        const maxType = document.getElementById('date_max_type');
        
        if (minType && minType.value) {
            minType.dispatchEvent(new Event('change'));
        }
        
        if (maxType && maxType.value) {
            maxType.dispatchEvent(new Event('change'));
        }
    }
    
    function handleDateTypeChange(type, value) {
        const fixedInput = document.getElementById(`date_${type}_fixed`);
        const daysInput = document.getElementById(`date_${type}_days`);
        
        // Hide all inputs first
        if (fixedInput) {
            fixedInput.style.display = 'none';
            fixedInput.required = false;
        }
        if (daysInput) {
            daysInput.style.display = 'none';
            daysInput.required = false;
        }
        
        // Show relevant input
        if (value === 'fixed' && fixedInput) {
            fixedInput.style.display = 'block';
            if (!hasSubmissions) fixedInput.required = true;
        } else if ((value === 'today_minus' || value === 'today_plus') && daysInput) {
            daysInput.style.display = 'block';
            if (!hasSubmissions) daysInput.required = true;
        }
    }
    
    function clearDateValidationInputs() {
        const elements = {
            minType: document.getElementById('date_min_type'),
            minFixed: document.getElementById('date_min_fixed'),
            minDays: document.getElementById('date_min_days'),
            maxType: document.getElementById('date_max_type'),
            maxFixed: document.getElementById('date_max_fixed'),
            maxDays: document.getElementById('date_max_days')
        };
        
        Object.values(elements).forEach(element => {
            if (element && !element.disabled && !element.readOnly) {
                if (element.type === 'checkbox') {
                    element.checked = element.defaultChecked;
                } else {
                    element.value = '';
                    element.style.display = 'none';
                }
            }
        });
    }
    
    // ===== FILE SETTINGS =====
    function initializeFileSettings() {
        const allowMultipleCheckbox = document.getElementById('allow_multiple');
        if (!allowMultipleCheckbox || hasSubmissions) return;
        
        allowMultipleCheckbox.addEventListener('change', function(e) {
            const maxFilesDiv = document.getElementById('maxFilesDiv');
            const maxFilesInput = document.getElementById('max_files');
            
            if (e.target.checked) {
                if (maxFilesDiv) maxFilesDiv.style.display = 'block';
            } else {
                if (maxFilesDiv) maxFilesDiv.style.display = 'none';
                if (maxFilesInput) maxFilesInput.value = '';
            }
        });
        
        // Set initial state
        if (allowMultipleCheckbox.checked) {
            const maxFilesDiv = document.getElementById('maxFilesDiv');
            if (maxFilesDiv) maxFilesDiv.style.display = 'block';
        }
    }
    
    // ===== HIDDEN FIELD VALUE TYPE HANDLING =====
    function initializeValueTypeHandling() {
        const valueTypeSelect = document.getElementById('value_type');
        if (!valueTypeSelect || hasSubmissions) return;
        
        valueTypeSelect.addEventListener('change', function(e) {
            handleValueTypeChange(e.target.value);
        });
        
        // Initialize display based on current value
        if (valueTypeSelect.value) {
            handleValueTypeChange(valueTypeSelect.value);
        }
        
        // Setup dynamic type radio handlers
        document.querySelectorAll('input[name="dynamic_type"]').forEach(radio => {
            if (!hasSubmissions) {
                radio.addEventListener('change', function() {
                    handleDynamicTypeChange(this.value);
                });
            }
        });
    }
    
    function handleValueTypeChange(valueType) {
        const dynamicOptions = document.getElementById('dynamicValueOptions');
        const defaultValueInput = document.getElementById('default_value');
        
        if (valueType === 'dynamic') {
            if (dynamicOptions) dynamicOptions.style.display = 'block';
            if (defaultValueInput) {
                defaultValueInput.placeholder = 'Will be auto-generated based on selection below';
                defaultValueInput.readOnly = true;
            }
        } else {
            if (dynamicOptions) dynamicOptions.style.display = 'none';
            if (defaultValueInput) {
                defaultValueInput.placeholder = 'Enter static value';
                defaultValueInput.readOnly = false;
            }
        }
    }
    
    function handleDynamicTypeChange(dynamicType) {
        const defaultValueInput = document.getElementById('default_value');
        if (!defaultValueInput) return;
        
        const examples = {
            'current_date': '2024-01-15',
            'current_datetime': '2024-01-15 14:30:00',
            'user_id': '123',
            'user_name': 'John Doe',
            'department_code': 'HR',
            'department_name': 'Human Resources',
            'submission_code': 'FRM-202401-0001',
            'random_number': '789456'
        };
        
        defaultValueInput.value = `{${dynamicType}}`;
        defaultValueInput.placeholder = `Example: ${examples[dynamicType]}`;
    }
    
    // ===== API CONFIGURATION =====
    function initializeApiConfiguration() {
        if (hasSubmissions) return;
        
        // Initialize option source radio buttons
        const optionSourceRadios = document.querySelectorAll('input[name="option_source"]');
        optionSourceRadios.forEach(radio => {
            radio.addEventListener('change', toggleOptionSource);
        });
        
        // Initialize test API button
        const testApiButton = document.getElementById('testApiButton');
        if (testApiButton) {
            testApiButton.addEventListener('click', testApiConfiguration);
        }
        
        // Set initial state
        const selectedSource = document.querySelector('input[name="option_source"]:checked');
        if (selectedSource) {
            toggleOptionSource();
        } else {
            // If no option source is selected, update requirements anyway
            updateApiFieldRequirements();
        }
    }
    
    function toggleOptionSource() {
        const selectedSource = document.querySelector('input[name="option_source"]:checked');
        const staticContainer = document.getElementById('staticOptionsContainer');
        const apiContainer = document.getElementById('apiOptionsContainer');
        
        if (selectedSource && selectedSource.value === 'api') {
            if (staticContainer) staticContainer.style.display = 'none';
            if (apiContainer) apiContainer.style.display = 'block';
        } else {
            if (staticContainer) staticContainer.style.display = 'block';
            if (apiContainer) apiContainer.style.display = 'none';
        }
        
        // Update API field requirements based on visibility
        updateApiFieldRequirements();
    }
    
    function testApiConfiguration() {
        const resultSpan = document.getElementById('testApiResult');
        if (resultSpan) {
            resultSpan.innerHTML = '<i class="far fa-spinner fa-spin"></i> &nbsp;Testing...';
        }
        
        const apiConfig = buildApiConfig();
        
        fetch('{{ route("api.test-api-config") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(apiConfig)
        })
        .then(response => response.json())
        .then(data => {
            if (resultSpan) {
                if (data.success) {
                    resultSpan.innerHTML = `<span class="text-success"><i class="far fa-check"></i> &nbsp;Found ${data.options_count} options</span>`;
                } else {
                    resultSpan.innerHTML = `<span class="text-danger"><i class="far fa-xmark"></i> &nbsp;${data.message}</span>`;
                }
            }
        })
        .catch(error => {
            if (resultSpan) {
                resultSpan.innerHTML = `<span class="text-danger"><i class="far fa-xmark"></i> &nbsp;Error: ${error.message}</span>`;
            }
        });
    }
    
    function buildApiConfig() {
        return {
            url: document.getElementById('api_url')?.value || '',
            method: document.getElementById('api_method')?.value || 'GET',
            value_field: document.getElementById('api_value_field')?.value || '',
            label_field: document.getElementById('api_label_field')?.value || '',
            data_path: document.getElementById('api_data_path')?.value || null,
            timeout: parseInt(document.getElementById('api_timeout')?.value) || 30,
            cache_ttl: parseInt(document.getElementById('api_cache_ttl')?.value) || 300,
            auth: {
                type: document.getElementById('api_auth_type')?.value || '',
                token: document.getElementById('api_bearer_token')?.value || '',
                username: document.getElementById('api_basic_username')?.value || '',
                password: document.getElementById('api_basic_password')?.value || '',
                key_name: document.getElementById('api_key_name')?.value || '',
                key_value: document.getElementById('api_key_value')?.value || ''
            },
            params: parseJsonField('api_params'),
            headers: parseJsonField('api_headers')
        };
    }
    
    function parseJsonField(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field || !field.value.trim()) return null;
        
        try {
            return JSON.parse(field.value);
        } catch (e) {
            return null;
        }
    }

    // ===== AVAILABLE FIELDS FOR CALCULATION =====
    function loadAvailableFields() {
        const container = fieldElements.availableFields;
        if (!container) return;
        
        // Show loading
        container.innerHTML = `
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0 text-muted small">Loading available fields...</p>
            </div>
        `;
        
        // Fetch available fields from server
        const url = "{{ route('formfields.available', [$form, $version]) }}";
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {           
            if (data.success && data.fields) {
                displayAvailableFields(data.fields, data.debug);
            } else {
                throw new Error('Invalid response format: ' + JSON.stringify(data));
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <div class="alert-icon">
                        <i class="far fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="alert-heading">Warning!</h4>
                        <div class="alert-description">
                            Failed to load available fields: ${error.message}
                            <br><a href="#" onclick="loadAvailableFields()" class="text-decoration-none">Click to retry</a>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    // Display available fields
    function displayAvailableFields(fields, debug = null) {
        const container = fieldElements.availableFields;
        
        if (fields.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info alert-dismissible" role="alert">
                    <div class="alert-icon">
                        <i class="far fa-circle-info"></i>
                    </div>
                    <div>
                        <h4 class="alert-heading">Info!</h4>
                        <div class="alert-description">
                            No numeric fields available for calculation.
                            <br>Create number, decimal, or numeric hidden fields first.
                            ${debug ? `<br><strong>Debug:</strong> Total fields: ${debug.total_fields_in_version}, Hidden: ${debug.hidden_fields_total}` : ''}
                        </div>
                    </div>
                </div>
            `;
            return;
        }
        
        // Group fields by type
        const regularFields = fields.filter(f => !f.is_hidden);
        const hiddenFields = fields.filter(f => f.is_hidden);
        
        let html = `
            <div class="mb-2">
                <small class="text-success">
                    <i class="far fa-circle-check"></i>&nbsp;Found ${fields.length} field(s) available for calculation
                    ${debug ? ` (${debug.total_fields_in_version} total fields in version)` : ''}
                </small>
            </div>
        `;
        
        // Regular numeric fields
        if (regularFields.length > 0) {
            html += `<h6 class="mt-2 mb-1 small">Regular Numeric Fields (${regularFields.length}):</h6>`;
            html += '<div class="mb-2">';
            regularFields.forEach(field => {
                html += `
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 mb-1" onclick="insertFieldCode('${field.field_code}')" title="${field.display_name}" ${document.getElementById('calculation_formula').readOnly ? 'disabled' : ''}>
                        <i class="far fa-input-numeric"></i>&nbsp;{${field.field_code}}
                    </button>
                `;
            });
            html += '</div>';
        }
        
        // Hidden numeric fields
        if (hiddenFields.length > 0) {
            html += `<h6 class="mt-2 mb-1 small">Hidden Numeric Fields (${hiddenFields.length}):</h6>`;
            html += '<div class="mb-2">';
            hiddenFields.forEach(field => {
                const iconClass = field.dynamic_type ? 'far fa-gear' : 'far fa-eye-slash';
                html += `
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 mb-1" onclick="insertFieldCode('${field.field_code}')" title="${field.display_name}" ${document.getElementById('calculation_formula').readOnly ? 'disabled' : ''}>
                        <i class="${iconClass}"></i>&nbsp;{${field.field_code}}
                    </button>
                `;
            });
            html += '</div>';
        }
        
        // Add current formula info if editing
        const currentFormula = document.getElementById('calculation_formula').value;
        if (currentFormula.trim()) {
            // Extract current dependencies
            const currentDeps = [...new Set((currentFormula.match(/\{([^}]+)\}/g) || []).map(match => match.slice(1, -1)))];
            
            html += `
                <hr class="my-2">
                <h6 class="small">Current Formula Dependencies:</h6>
                <div class="mb-2">
            `;
            
            currentDeps.forEach(dep => {
                const field = fields.find(f => f.field_code === dep);
                if (field) {
                    html += `<span class="badge badge-outline text-success me-1">${dep} <i class="far fa-check"></i></span>`;
                } else {
                    html += `<span class="badge badge-outline text-danger me-1">${dep} <i class="far fa-xmark"></i></span>`;
                }
            });
            
            html += '</div>';
        }
        
        // Add formula examples (only if formula is editable)
        if (!document.getElementById('calculation_formula').readOnly) {
            html += `
                <hr class="my-2">
                <h6 class="small">Formula Examples:</h6>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="insertFormulaExample('basic')">
                        Basic: {a} + {b}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="insertFormulaExample('percentage')">
                        Percentage: {amount} * {rate} / 100
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="insertFormulaExample('tax')">
                        Tax: {subtotal} * (1 + {tax_rate})
                    </button>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }
    
    // ===== FORM VALIDATION =====
    function initializeFormValidation() {
        if (!fieldElements.form) return;
        
        fieldElements.form.addEventListener('submit', function(e) {
            
            // Prevent browser validation for hidden API fields
            const apiContainer = document.getElementById('apiOptionsContainer');
            if (apiContainer && apiContainer.style.display === 'none') {
                const apiFields = ['api_url', 'api_value_field', 'api_label_field'];
                apiFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.setCustomValidity(''); // Clear any validation messages
                    }
                });
            }
            
            const isValid = validateForm();
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
        });
        
        // Also handle invalid event to prevent browser validation errors
        fieldElements.form.addEventListener('invalid', function(e) {
            const target = e.target;
            if (target.name && target.name.startsWith('api_source_config')) {
                const apiContainer = document.getElementById('apiOptionsContainer');
                if (apiContainer && apiContainer.style.display === 'none') {
                    e.preventDefault();
                    target.setCustomValidity('');
                    return false;
                }
            }
        }, true);
    }
    
    function validateForm() {
        try {
            const fieldType = fieldElements.fieldType?.value;
            
            // Validate date fields if applicable
            if (fieldType === 'date' || fieldType === 'datetime') {
                return validateDateFields();
            }
            
            // Validate options for select fields
            if (fieldType === 'select_single' || fieldType === 'select_multiple') {
                return validateOptions();
            }
            
            return true;
        } catch (error) {
            return true; // Allow form submission if validation fails
        }
    }
    
    function validateOptions() {
        try {
            const optionSource = document.querySelector('input[name="option_source"]:checked');
            
            // If no option source is selected, it might be a static options field
            // Check if there are any static options defined
            const staticOptions = document.querySelectorAll('input[name="options[][option_label]"]');
            if (!optionSource && staticOptions.length === 0) {
                // No options configured, but this might be intentional for some field types
                return true;
            }
            
            if (optionSource && optionSource.value === 'api') {
                // Validate API configuration
                const url = document.getElementById('api_url')?.value;
                const valueField = document.getElementById('api_value_field')?.value;
                const labelField = document.getElementById('api_label_field')?.value;
                
                if (!url || !valueField || !labelField) {
                    alert('Please fill in all required API configuration fields (URL, Value Field, Label Field).');
                    return false;
                }
            }
            
            return true;
        } catch (error) {
            return true; // Allow form submission if validation fails
        }
    }
    
    function validateDateFields() {
        // Add specific date validation logic here
        return true;
    }
    
    // ===== API FIELD REQUIREMENTS =====
    function updateApiFieldRequirements() {
        const apiContainer = document.getElementById('apiOptionsContainer');
        const apiUrl = document.getElementById('api_url');
        const apiValueField = document.getElementById('api_value_field');
        const apiLabelField = document.getElementById('api_label_field');
        
        
        if (apiContainer && apiUrl && apiValueField && apiLabelField) {
            const isVisible = apiContainer.style.display !== 'none';
            
            if (isVisible) {
                // Add required attribute when visible and enable fields
                apiUrl.setAttribute('required', 'required');
                apiValueField.setAttribute('required', 'required');
                apiLabelField.setAttribute('required', 'required');
                apiUrl.disabled = false;
                apiValueField.disabled = false;
                apiLabelField.disabled = false;
                // Restore name attributes
                apiUrl.setAttribute('name', 'api_source_config[url]');
                apiValueField.setAttribute('name', 'api_source_config[value_field]');
                apiLabelField.setAttribute('name', 'api_source_config[label_field]');
            } else {
                // Remove required attribute when hidden and disable fields
                apiUrl.removeAttribute('required');
                apiValueField.removeAttribute('required');
                apiLabelField.removeAttribute('required');
                apiUrl.disabled = true;
                apiValueField.disabled = true;
                apiLabelField.disabled = true;
                // Remove name attributes to prevent validation
                apiUrl.removeAttribute('name');
                apiValueField.removeAttribute('name');
                apiLabelField.removeAttribute('name');
            }
        } else {
            // Force remove required attributes and disable fields even if elements aren't found
            if (apiUrl) {
                apiUrl.removeAttribute('required');
                apiUrl.disabled = true;
                apiUrl.removeAttribute('name');
            }
            if (apiValueField) {
                apiValueField.removeAttribute('required');
                apiValueField.disabled = true;
                apiValueField.removeAttribute('name');
            }
            if (apiLabelField) {
                apiLabelField.removeAttribute('required');
                apiLabelField.disabled = true;
                apiLabelField.removeAttribute('name');
            }
        }
    }

    // ===== CLEAR FUNCTIONS =====
    function clearCalculationInputs() {
        const formula = fieldElements.calculationFormula;
        if (formula && !formula.readOnly) {
            formula.value = '';
        }
        
        const format = document.getElementById('calculation_format');
        if (format) format.value = 'number';
        
        const autoCalc = document.getElementById('auto_calculate');
        if (autoCalc) autoCalc.checked = true;
    }
    
    function clearHiddenFieldInputs() {
        const defaultValue = document.getElementById('default_value');
        if (defaultValue && !defaultValue.readOnly) {
            defaultValue.value = '';
        }
        
        const valueType = document.getElementById('value_type');
        if (valueType && !valueType.disabled) {
            valueType.value = 'static';
        }
        
        // Uncheck dynamic type radios
        document.querySelectorAll('input[name="dynamic_type"]').forEach(radio => {
            if (!radio.disabled) radio.checked = false;
        });
    }
    
    function clearOptionsInputs() {
        // Clear option source selection
        const staticRadio = document.getElementById('option_source_static');
        if (staticRadio && !staticRadio.disabled) {
            staticRadio.checked = true;
        }
        
        // Clear API configuration
        const apiUrl = document.getElementById('api_url');
        const apiValueField = document.getElementById('api_value_field');
        const apiLabelField = document.getElementById('api_label_field');
        const apiDataPath = document.getElementById('api_data_path');
        
        if (apiUrl && !apiUrl.readOnly) apiUrl.value = '';
        if (apiValueField && !apiValueField.readOnly) apiValueField.value = '';
        if (apiLabelField && !apiLabelField.readOnly) apiLabelField.value = '';
        if (apiDataPath && !apiDataPath.readOnly) apiDataPath.value = '';
        
        // Toggle to static options
        toggleOptionSource();
    }
    
    function clearNumberValidationInputs() {
        const elements = {
            minValue: document.getElementById('min_value'),
            maxValue: document.getElementById('max_value'),
            stepValue: document.getElementById('step_value')
        };
        
        Object.values(elements).forEach(element => {
            if (element && !element.disabled && !element.readOnly) {
                if (element.id === 'step_value') {
                    element.value = '1';
                } else {
                    element.value = '';
                }
            }
        });
    }
    
    function clearDecimalValidationInputs() {
        const elements = {
            minValue: document.getElementById('decimal_min_value'),
            maxValue: document.getElementById('decimal_max_value'),
            decimalPlaces: document.getElementById('decimal_places'),
            stepValue: document.getElementById('decimal_step_value')
        };
        
        Object.values(elements).forEach(element => {
            if (element && !element.disabled && !element.readOnly) {
                if (element.id === 'decimal_places') {
                    element.value = '2';
                } else if (element.id === 'decimal_step_value') {
                    element.value = '0.01';
                } else {
                    element.value = '';
                }
            }
        });
    }
    
    // ===== HELPER FUNCTIONS =====
    function insertFieldCode(fieldCode) {
        const formula = fieldElements.calculationFormula;
        
        if (!formula || formula.readOnly || formula.disabled) {
            return;
        }
        
        const cursorPos = formula.selectionStart || formula.value.length;
        const textBefore = formula.value.substring(0, cursorPos);
        const textAfter = formula.value.substring(cursorPos);
        
        const fieldReference = '{' + fieldCode + '}';
        formula.value = textBefore + fieldReference + textAfter;
        
        // Set cursor position after inserted text
        const newPos = cursorPos + fieldReference.length;
        formula.focus();
        formula.setSelectionRange(newPos, newPos);
        
        // Trigger change event for any listeners
        formula.dispatchEvent(new Event('input'));
    }

    function insertFormulaExample(type) {
        const formula = fieldElements.calculationFormula;
        
        if (!formula || formula.readOnly || formula.disabled) {
            return;
        }
        
        const examples = {
            'basic': '{field_a} + {field_b}',
            'percentage': '{amount} * {percentage_rate} / 100',
            'tax': '{subtotal} * (1 + {tax_rate})',
            'discount': '{price} - ({price} * {discount_rate} / 100)',
            'total': '({unit_price} * {quantity}) + {shipping_cost}'
        };
        
        if (examples[type]) {
            formula.value = examples[type];
            formula.focus();
        }
    }

    function selectWeekdays() {
        if (hasSubmissions) return;
        document.querySelectorAll('input[name="allowed_days[]"]').forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = ['1', '2', '3', '4', '5'].includes(checkbox.value);
            }
        });
    }
    
    function selectWeekends() {
        if (hasSubmissions) return;
        document.querySelectorAll('input[name="allowed_days[]"]').forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = ['0', '6'].includes(checkbox.value);
            }
        });
    }
    
    function selectAllDays() {
        if (hasSubmissions) return;
        document.querySelectorAll('input[name="allowed_days[]"]').forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = true;
            }
        });
    }
    
    function testFormula() {
        // Formula testing logic here
    }
    
    // ===== EXPOSE GLOBAL FUNCTIONS =====
    window.loadAvailableFields = loadAvailableFields;
    window.insertFieldCode = insertFieldCode;
    window.insertFormulaExample = insertFormulaExample;
    window.selectWeekdays = selectWeekdays;
    window.selectWeekends = selectWeekends;
    window.selectAllDays = selectAllDays;
    window.testFormula = testFormula;
    
})();
</script>
@endpush
