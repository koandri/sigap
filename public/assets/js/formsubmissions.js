// Form Submissions JavaScript
// Wait for page to be fully loaded to prevent layout forcing
window.addEventListener('load', function() {
    initializeFormSubmissions();
});

function initializeFormSubmissions() {
    // Find the form
    const form = document.getElementById('submissionForm');
    if (!form) {
        return;
    }
    
    // Initialize TomSelect for API-sourced dropdowns
    initializeTomSelect();
    
    // Initialize signature pads
    initializeSignaturePads();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize number/decimal field validation
    initializeNumberDecimalValidation();
    
    // Initialize date field validation
    initializeDateFieldValidation();
    
    // Initialize WYSIWYG editors
    initializeWysiwygEditors();
    
    // Initialize calculated fields
    initializeCalculatedFields();
}

function initializeTomSelect() {
    const apiSelects = document.querySelectorAll('select[data-has-api-source="true"]');
    
    apiSelects.forEach(select => {
        const apiUrl = select.dataset.apiUrl;
        if (!apiUrl) return;
        
        // Load options from API
        loadApiOptions(select, apiUrl);
    });
}

async function loadApiOptions(select, apiUrl) {
    try {
        const response = await fetch(apiUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.options) {
            // Clear existing options except the first one
            select.innerHTML = '<option value="">Select an option...</option>';
            
            // Add new options
            data.options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.label;
                select.appendChild(optionElement);
            });
            
            // Initialize TomSelect
            initializeTomSelectForElement(select);
        }
    } catch (error) {
        console.error('Error loading API options:', error);
        showToast('Failed to load options from API', 'error');
    }
}

function initializeTomSelectForElement(select) {
    if (typeof TomSelect === 'undefined') {
        console.warn('TomSelect not available, using standard select');
        return;
    }
    
    const isMultiple = select.hasAttribute('multiple');
    const originalWidth = select.offsetWidth;
    const originalHeight = select.offsetHeight;
    
    const tomSelect = new TomSelect(select, {
        plugins: isMultiple ? ['remove_button'] : [],
        dropdownClass: 'ts-dropdown',
        optionClass: 'ts-option',
        itemClass: 'ts-item',
        create: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        render: {
            option: function(data, escape) {
                return '<div class="ts-option-content">' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div class="ts-item-content">' + escape(data.text) + '</div>';
            }
        }
    });
    
    // Apply original dimensions and ensure proper width
    setTimeout(() => {
        const tsControl = select.parentElement.querySelector('.ts-control');
        const tsDropdown = select.parentElement.querySelector('.ts-dropdown');
        
        if (tsControl) {
            tsControl.style.width = originalWidth + 'px';
            tsControl.style.minHeight = originalHeight + 'px';
            tsControl.offsetHeight; // Force reflow
        }
        
        if (tsDropdown) {
            tsDropdown.style.width = originalWidth + 'px';
            tsDropdown.style.minWidth = originalWidth + 'px';
        }
    }, 0);
    
    // Also set dropdown width when it opens
    tomSelect.on('dropdown_open', () => {
        const tsDropdown = select.parentElement.querySelector('.ts-dropdown');
        if (tsDropdown) {
            tsDropdown.style.width = originalWidth + 'px';
            tsDropdown.style.minWidth = originalWidth + 'px';
        }
    });
}

function initializeSignaturePads() {
    const signatureCanvases = document.querySelectorAll('.signature-canvas');
    
    signatureCanvases.forEach(canvas => {
        const fieldCode = canvas.id.replace('_canvas', '');
        initializeSignaturePad(canvas, fieldCode);
    });
}

function initializeSignaturePad(canvas, fieldCode) {
    if (typeof SignaturePad === 'undefined') {
        console.warn('SignaturePad not available');
        return;
    }
    
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 1)',
        penColor: 'rgb(0, 0, 0)'
    });
    
    // Handle device pixel ratio
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    canvas.getContext('2d').scale(dpr, dpr);
    
    // Set canvas display size
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    
    // Update hidden input when signature changes
    signaturePad.addEventListener('endStroke', () => {
        const hiddenInput = document.getElementById(fieldCode);
        if (hiddenInput) {
            hiddenInput.value = signaturePad.toDataURL();
        }
    });
    
    // Store signature pad instance for clearing
    window[`signaturePad_${fieldCode}`] = signaturePad;
}

function clearSignature(fieldCode) {
    const signaturePad = window[`signaturePad_${fieldCode}`];
    if (signaturePad) {
        signaturePad.clear();
        const hiddenInput = document.getElementById(fieldCode);
        if (hiddenInput) {
            hiddenInput.value = '';
        }
    }
}

function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', handleFileUpload);
        input.addEventListener('dragover', handleDragOver);
        input.addEventListener('drop', handleFileDrop);
    });
}

function handleFileUpload(event) {
    const input = event.target;
    const maxFiles = parseInt(input.dataset.maxFiles) || 1;
    const allowedTypes = JSON.parse(input.dataset.allowedTypes || '[]');
    const maxSize = parseInt(input.dataset.maxSize) || 10240; // KB
    
    const files = Array.from(input.files);
    
    // Validate file count
    if (files.length > maxFiles) {
        showToast(`Maximum ${maxFiles} file(s) allowed`, 'error');
        input.value = '';
        return;
    }
    
    // Validate file types and sizes
    for (const file of files) {
        if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
            showToast(`File type ${file.type} is not allowed`, 'error');
            input.value = '';
            return;
        }
        
        if (file.size > maxSize * 1024) {
            showToast(`File size exceeds ${maxSize}KB limit`, 'error');
            input.value = '';
            return;
        }
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.closest('.file-upload-area')?.classList.add('dragover');
}

function handleFileDrop(event) {
    event.preventDefault();
    event.currentTarget.closest('.file-upload-area')?.classList.remove('dragover');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        event.currentTarget.files = files;
        handleFileUpload({ target: event.currentTarget });
    }
}

function initializeFormValidation() {
    const form = document.getElementById('submissionForm');
    if (!form) {
        return;
    }
    
    form.addEventListener('submit', handleFormSubmit);
}

function handleFormSubmit(event) {
    const form = event.target;
    
    // Remove required attributes from TinyMCE fields before validation
    const wysiwygTextareas = form.querySelectorAll('.wysiwyg-editor');
    wysiwygTextareas.forEach(textarea => {
        if (textarea.dataset.required === 'true') {
            textarea.removeAttribute('required');
        }
    });
    
    // Validate TinyMCE fields manually
    let isValid = true;
    wysiwygTextareas.forEach(textarea => {
        if (textarea.dataset.required === 'true' && !textarea.value.trim()) {
            isValid = false;
            showToast(`Field "${textarea.name}" is required`, 'error');
        }
    });
    
    if (!isValid) {
        event.preventDefault();
        return;
    }
    
    // Show loading state - only disable the clicked button AFTER form submission
    const clickedButton = event.submitter;
    if (clickedButton && clickedButton.type === 'submit') {
        const originalText = clickedButton.innerHTML;
        clickedButton.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i>&nbsp;Processing...';
        
        // Store original text to restore if needed
        clickedButton.dataset.originalText = originalText;
        
        // Disable button after a short delay to allow form submission
        setTimeout(() => {
            clickedButton.disabled = true;
        }, 100);
    }
}

function initializeWysiwygEditors() {
    const wysiwygTextareas = document.querySelectorAll('.wysiwyg-editor');
    
    wysiwygTextareas.forEach(textarea => {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                license_key: 'gpl',
                target: textarea,
                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic forecolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
            });
        }
    });
}

function initializeCalculatedFields() {
    const calculatedFields = document.querySelectorAll('.calculated-field input');
    
    if (calculatedFields.length === 0) return;
    
    // Debounce calculation to prevent too many requests
    let calculationTimeout;
    const debouncedRecalculate = () => {
        clearTimeout(calculationTimeout);
        calculationTimeout = setTimeout(() => {
            recalculateAllFields();
        }, 500); // Wait 500ms after user stops typing
    };
    
    // Add event listener to recalculate when dependencies change
    const form = document.getElementById('submissionForm');
    if (form) {
        // Listen for input changes on all form fields (not just calculated ones)
        form.addEventListener('input', (event) => {
            // Only recalculate if the changed field is not a calculated field itself
            if (!event.target.closest('.calculated-field')) {
                debouncedRecalculate();
            }
        });
        
        // Also listen for change events (for selects, checkboxes, etc.)
        form.addEventListener('change', (event) => {
            if (!event.target.closest('.calculated-field')) {
                debouncedRecalculate();
            }
        });
    }
}

async function recalculateAllFields() {
    const form = document.getElementById('submissionForm');
    if (!form) return;
    
    const calculatedFields = form.querySelectorAll('.calculated-field input');
    if (calculatedFields.length === 0) return;
    
    try {
        // Get current form data
        const formData = new FormData(form);
        const currentValues = {};
        
        // Collect current field values
        formData.forEach((value, key) => {
            if (key.startsWith('fields[') && key.endsWith(']')) {
                const fieldCode = key.slice(7, -1); // Remove 'fields[' and ']'
                currentValues[fieldCode] = value;
            }
        });
        
        // Make AJAX request to recalculate
        const response = await fetch('/api/calculate-fields', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                field_values: currentValues,
                form_version_id: form.dataset.formVersionId || null
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.calculated_values) {
            // Update calculated field values
            Object.entries(data.calculated_values).forEach(([fieldCode, value]) => {
                const field = form.querySelector(`input[name="fields[${fieldCode}]"]`);
                if (field) {
                    field.value = value;
                }
            });
        }
        
    } catch (error) {
        console.error('Error recalculating fields:', error);
        // Don't show toast for calculation errors as they might be frequent
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Add CSS animations after page is loaded
function addToastAnimations() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

// Initialize number and decimal field validation
function initializeNumberDecimalValidation() {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    
    numberInputs.forEach(input => {
        // Add event listeners for real-time validation
        input.addEventListener('input', function() {
            validateNumberInput(this);
        });
        
        input.addEventListener('blur', function() {
            validateNumberInput(this);
        });
    });
}

function validateNumberInput(input) {
    const value = parseFloat(input.value);
    const min = parseFloat(input.min);
    const max = parseFloat(input.max);
    const decimalPlaces = input.dataset.decimalPlaces;
    
    // Clear previous validation messages
    clearValidationMessage(input);
    
    // Skip validation if input is empty
    if (input.value === '' || isNaN(value)) {
        return;
    }
    
    let isValid = true;
    let errorMessage = '';
    
    // Check min/max validation
    if (!isNaN(min) && value < min) {
        isValid = false;
        errorMessage = `Value must be at least ${min}`;
    } else if (!isNaN(max) && value > max) {
        isValid = false;
        errorMessage = `Value must be at most ${max}`;
    }
    
    // Check decimal places validation
    if (decimalPlaces !== undefined && decimalPlaces !== null) {
        const decimalPlacesCount = parseInt(decimalPlaces);
        const valueStr = input.value.toString();
        const decimalIndex = valueStr.indexOf('.');
        
        if (decimalIndex !== -1) {
            const actualDecimalPlaces = valueStr.length - decimalIndex - 1;
            if (actualDecimalPlaces > decimalPlacesCount) {
                isValid = false;
                errorMessage = `Maximum ${decimalPlacesCount} decimal places allowed`;
            }
        }
    }
    
    // Show validation message if invalid
    if (!isValid) {
        showValidationMessage(input, errorMessage);
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
}

function showValidationMessage(input, message) {
    // Remove existing validation message
    clearValidationMessage(input);
    
    // Create validation message element
    const validationDiv = document.createElement('div');
    validationDiv.className = 'invalid-feedback';
    validationDiv.textContent = message;
    validationDiv.id = input.id + '-validation';
    
    // Insert after the input
    input.parentNode.insertBefore(validationDiv, input.nextSibling);
}

function clearValidationMessage(input) {
    const existingMessage = document.getElementById(input.id + '-validation');
    if (existingMessage) {
        existingMessage.remove();
    }
}

// Initialize date field validation
function initializeDateFieldValidation() {
    const dateInputs = document.querySelectorAll('input[type="date"], input[type="datetime-local"]');
    
    dateInputs.forEach(input => {
        // Add event listeners for real-time validation
        input.addEventListener('change', function() {
            validateDateInput(this);
        });
        
        input.addEventListener('blur', function() {
            validateDateInput(this);
        });
        
        // Handle disabled dates
        const disabledDates = input.dataset.disabledDates;
        if (disabledDates) {
            try {
                const disabledDatesArray = JSON.parse(disabledDates);
                input.addEventListener('change', function() {
                    checkDisabledDates(this, disabledDatesArray);
                });
            } catch (e) {
                console.warn('Invalid disabled dates data:', disabledDates);
            }
        }
    });
}

function validateDateInput(input) {
    const value = input.value;
    const min = input.min;
    const max = input.max;
    
    // Clear previous validation messages
    clearValidationMessage(input);
    
    // Skip validation if input is empty
    if (!value) {
        return;
    }
    
    let isValid = true;
    let errorMessage = '';
    
    // Check min/max validation
    if (min && value < min) {
        isValid = false;
        errorMessage = `Date must be on or after ${formatDateForDisplay(min)}`;
    } else if (max && value > max) {
        isValid = false;
        errorMessage = `Date must be on or before ${formatDateForDisplay(max)}`;
    }
    
    // Show validation message if invalid
    if (!isValid) {
        showValidationMessage(input, errorMessage);
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
}

function checkDisabledDates(input, disabledDates) {
    if (!input.value) return;
    
    const selectedDate = input.value.split('T')[0]; // Get date part for datetime-local inputs
    
    if (disabledDates.includes(selectedDate)) {
        clearValidationMessage(input);
        showValidationMessage(input, 'This date is not available for selection');
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    }
}

function formatDateForDisplay(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
}

// Initialize animations when page loads
window.addEventListener('load', addToastAnimations);