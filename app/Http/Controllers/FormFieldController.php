<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Services\CalculationService;

use App\Models\Form;
use App\Models\FormVersion;
use App\Models\FormField;
use App\Models\FormFieldOption;

class FormFieldController extends Controller
{
    /**
     * Show create field form
     */
    public function create(Form $form, FormVersion $version)
    {
        // Verify version belongs to form
        if ($version->form_id != $form->id) {
            abort(404);
        }

        // Check if version has submissions
        $hasSubmissions = $version->submissions()->exists();

        return view('formfields.create', compact('form', 'version', 'hasSubmissions'));
    }

    /**
     * Store new field
     */
    public function store(Request $request, Form $form, FormVersion $version)
    {
        // Verify version belongs to form
        if ($version->form_id != $form->id) {
            abort(404);
        }

        $validated = $request->validate([
            'field_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('form_fields')->where(function ($query) use ($version) {
                    return $query->where('form_version_id', $version->id);
                })
            ],
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text_short,text_long,number,decimal,date,datetime,select_single,select_multiple,radio,checkbox,file,boolean,calculated,hidden,signature,live_photo',
            'is_required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'options' => 'nullable|array',
            'api_source_config' => 'nullable|array',
            'api_source_config.url' => 'nullable|url',
            'api_source_config.method' => 'nullable|in:GET,POST,PUT,PATCH,DELETE',
            'api_source_config.value_field' => 'nullable|string',
            'api_source_config.label_field' => 'nullable|string',
            'api_source_config.data_path' => 'nullable|string',
            'api_source_config.auth' => 'nullable|array',
            'api_source_config.params' => 'nullable|array',
            'api_source_config.headers' => 'nullable|array',
            'api_source_config.timeout' => 'nullable|integer|min:1|max:300',
            'api_source_config.cache_ttl' => 'nullable|integer|min:60|max:3600'
        ]);

        // Custom validation for options if field type requires them
        if ($this->fieldTypeHasOptions($validated['field_type'])) {
            // Debug: Log what we received
            
            $hasApiSource = !empty($validated['api_source_config']) && 
                           !empty($validated['api_source_config']['url']);
            
            
            if (!$hasApiSource) {
                // Filter out empty options
                $validOptions = [];
                if (isset($request->options) && is_array($request->options)) {
                    foreach ($request->options as $option) {
                        if (!empty($option['value']) && !empty($option['label'])) {
                            $validOptions[] = [
                                'value' => trim($option['value']),
                                'label' => trim($option['label'])
                            ];
                        }
                    }
                }

                // Check if we have at least 2 valid options for select/radio/checkbox
                if (count($validOptions) < 2) {
                    return back()
                        ->withInput()
                        ->withErrors(['options' => 'Please provide at least 2 options for ' . $validated['field_type'] . ' field type.']);
                }
            } else {
                // Validate API source configuration
                $apiService = app(\App\Services\ApiOptionsService::class);
                $apiErrors = $apiService->validateConfig($validated['api_source_config']);
                if (!empty($apiErrors)) {
                    return back()
                        ->withInput()
                        ->withErrors(['api_source_config' => implode(', ', $apiErrors)]);
                }
            }
        }

        // Build validation rules for date fields
        $validationRules = null;

        // File field validation rules
        if ($validated['field_type'] === 'file') {
            $validationRules = [
                'allow_multiple' => $request->has('allow_multiple'),
                'max_files' => $request->has('allow_multiple') ? ($request->max_files ?? 5) : 1,
                'allowed_extensions' => $request->allowed_extensions ? 
                    array_map('trim', explode(',', $request->allowed_extensions)) : 
                    ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif'],
                'max_file_size' => ($request->max_file_size ?? 10) * 1024 // Convert MB to KB
            ];
        }
       
        //Date Datetime field validation rules
        if (in_array($validated['field_type'], ['date', 'datetime'])) {
            $validationRules = [];
            
            // Minimum date
            if ($request->filled('date_min_type')) {
                $validationRules['date_min'] = [
                    'type' => $request->date_min_type,
                    'value' => null
                ];
                
                if ($request->date_min_type === 'fixed') {
                    $validationRules['date_min']['value'] = $request->date_min_fixed;
                } elseif (in_array($request->date_min_type, ['today_minus', 'today_plus'])) {
                    $validationRules['date_min']['days'] = $request->date_min_days ?? 0;
                }
            }
            
            // Maximum date
            if ($request->filled('date_max_type')) {
                $validationRules['date_max'] = [
                    'type' => $request->date_max_type,
                    'value' => null
                ];
                
                if ($request->date_max_type === 'fixed') {
                    $validationRules['date_max']['value'] = $request->date_max_fixed;
                } elseif (in_array($request->date_max_type, ['today_minus', 'today_plus'])) {
                    $validationRules['date_max']['days'] = $request->date_max_days ?? 0;
                }
            }
            
            // Allowed days
            if ($request->has('allowed_days')) {
                $validationRules['allowed_days'] = $request->allowed_days;
            }
            
            // Disabled dates
            if ($request->filled('disabled_dates')) {
                $dates = array_filter(array_map('trim', explode("\n", $request->disabled_dates)));
                if (!empty($dates)) {
                    $validationRules['disabled_dates'] = $dates;
                }
            }
        }

        //Calculated field validation rules
        if ($request->field_type === 'calculated') {
            $request->validate([
                'calculation_formula' => 'required|string',
                'calculation_format' => 'nullable|in:number,currency,percentage,decimal_2,decimal_0',
                'auto_calculate' => 'boolean'
            ]);
            
            // Parse and validate formula
            $dependencies = $this->parseFormulaDependencies($request->calculation_formula);

            if (empty($dependencies)) {
                return back()
                    ->withInput()
                    ->withErrors(['calculation_formula' => 'Formula must reference at least one field using {field_code} syntax']);
            }
            
            // Check if referenced fields exist and are numeric
            $invalidFields = $this->validateCalculationDependencies($version, $dependencies);
            if (!empty($invalidFields)) {
                return back()
                    ->withInput()
                    ->withErrors(['calculation_formula' => 'Referenced fields not found or not numeric: ' . implode(', ', $invalidFields)]);
            }
        }

        // Initialize validation rules
        $validationRules = null;

        // Build validation rules for calculated fields
        if ($request->field_type === 'calculated') {
            $validationRules = [
                'formula' => $request->calculation_formula,
                'dependencies' => $dependencies,
                'format' => $request->calculation_format ?? 'number',
                'auto_calculate' => $request->has('auto_calculate')
            ];
        }

        // Add hidden field validation
        if ($request->field_type === 'hidden') {
            $request->validate([
                'default_value' => 'required|string',
                'value_type' => 'required|in:static,dynamic',
                'dynamic_type' => 'required_if:value_type,dynamic|in:current_date,current_datetime,user_id,user_name,department_code,department_name,submission_code,random_number'
            ]);
            
            $validationRules = [
                'default_value' => $request->default_value,
                'value_type' => $request->value_type
            ];
            
            if ($request->value_type === 'dynamic') {
                $validationRules['dynamic_type'] = $request->dynamic_type;
            }
        }

        // Add signature field validation
        if ($request->field_type === 'signature') {
            $request->validate([
                'signature_width' => 'nullable|integer|min:200|max:800',
                'signature_height' => 'nullable|integer|min:100|max:400',
                'pen_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'signature_required_draw' => 'boolean',
                'save_as_image' => 'boolean'
            ]);
            
            $validationRules = [
                'width' => $request->signature_width ?? 400,
                'height' => $request->signature_height ?? 200,
                'pen_color' => $request->pen_color ?? '#000000',
                'background_color' => $request->background_color ?? '#ffffff',
                'required_draw' => $request->has('signature_required_draw'),
                'save_as_image' => $request->has('save_as_image')
            ];
        }

        // Add live photo field validation
        if ($request->field_type === 'live_photo') {
            
            $request->validate([
                'max_photos' => 'nullable|integer|min:1|max:10',
                'photo_quality' => 'nullable|numeric|min:0.1|max:1.0',
                'require_location' => 'boolean'
            ]);
            
            $validationRules = [
                'max_photos' => $request->max_photos ?? 1,
                'photo_quality' => $request->photo_quality ?? 0.8,
                'require_location' => $request->has('require_location')
            ];
            
        }

        // Add number field validation
        if ($request->field_type === 'number') {
            $request->validate([
                'min_value' => 'nullable|numeric',
                'max_value' => 'nullable|numeric|gte:min_value',
                'step_value' => 'nullable|numeric|min:0.01'
            ]);
            
            $validationRules = [];
            if ($request->filled('min_value')) {
                $validationRules['min'] = (float) $request->min_value;
            }
            if ($request->filled('max_value')) {
                $validationRules['max'] = (float) $request->max_value;
            }
            if ($request->filled('step_value')) {
                $validationRules['step'] = (float) $request->step_value;
            }
        }

        // Add decimal field validation
        if ($request->field_type === 'decimal') {
            $request->validate([
                'decimal_min_value' => 'nullable|numeric',
                'decimal_max_value' => 'nullable|numeric|gte:decimal_min_value',
                'decimal_places' => 'nullable|integer|min:0|max:10',
                'decimal_step_value' => 'nullable|numeric|min:0.001'
            ]);
            
            $validationRules = [];
            if ($request->filled('decimal_min_value')) {
                $validationRules['min'] = (float) $request->decimal_min_value;
            }
            if ($request->filled('decimal_max_value')) {
                $validationRules['max'] = (float) $request->decimal_max_value;
            }
            if ($request->filled('decimal_places')) {
                $validationRules['decimal_places'] = (int) $request->decimal_places;
            }
            if ($request->filled('decimal_step_value')) {
                $validationRules['step'] = (float) $request->decimal_step_value;
            }
        }

        // Automatically set is_required to false for calculated and hidden fields
        $isRequired = false;
        if (!in_array($request->field_type, ['calculated', 'hidden'])) {
            $isRequired = $request->has('is_required');
        }

        // Create field
        $fieldData = [
            'field_code' => $validated['field_code'],
            'field_label' => $validated['field_label'],
            'field_type' => $validated['field_type'],
            'is_required' => $validated['field_type'] === 'hidden' ? false : $request->has('is_required'),
            'order_position' => FormField::getNextOrderPosition($version->id),
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'calculation_formula' => $request->field_type === 'calculated' ? $request->calculation_formula : null,
            'calculation_dependencies' => $request->field_type === 'calculated' ? $dependencies : null,
            'validation_rules' => $validationRules,
            'api_source_config' => $this->fieldTypeUsesApiSource($validated['field_type']) ? ($validated['api_source_config'] ?? null) : null
        ];
        
        
        $field = $version->fields()->create($fieldData);

        // Create options if field type requires them
        if ($this->fieldTypeHasOptions($validated['field_type']) && isset($validated['options'])) {
            foreach ($validated['options'] as $option) {
                if (!empty($option['value']) && !empty($option['label'])) {
                    $field->options()->create([
                        'option_value' => $option['value'],
                        'option_label' => $option['label']
                    ]);
                }
            }
        }

        return redirect()->route('formversions.show', [$form, $version])
            ->with('success', 'Field added successfully.');
    }

    /**
     * Show edit field form
     */
    public function edit(Form $form, FormVersion $version, FormField $field)
    {
        // Verify relationships
        if ($version->form_id != $form->id || $field->form_version_id != $version->id) {
            abort(404);
        }

        // Check if version has submissions
        $hasSubmissions = $version->submissions()->exists();

        return view('formfields.edit', compact('form', 'version', 'field', 'hasSubmissions'));
    }

    /**
     * Update field
     */
    public function update(Request $request, Form $form, FormVersion $version, FormField $field)
    {
        // Verify relationships
        if ($version->form_id != $form->id || $field->form_version_id != $version->id) {
            abort(404);
        }

        $hasSubmissions = $version->submissions()->exists();

        // Different validation rules based on whether there are submissions
        $rules = [
            'field_label' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
        ];

        // Only allow certain changes if there are no submissions
        if (!$hasSubmissions) {
            $rules['field_code'] = [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('form_fields')->where(function ($query) use ($version) {
                    return $query->where('form_version_id', $version->id);
                })->ignore($field->id)
            ];
            $rules['field_type'] = 'required|in:text_short,text_long,number,decimal,date,datetime,select_single,select_multiple,radio,checkbox,file,boolean,calculated,hidden,signature,live_photo';
            $rules['is_required'] = 'boolean';
            $rules['api_source_config'] = 'nullable|array';
            $rules['api_source_config.url'] = 'nullable|url';
            $rules['api_source_config.method'] = 'nullable|in:GET,POST,PUT,PATCH,DELETE';
            $rules['api_source_config.value_field'] = 'nullable|string';
            $rules['api_source_config.label_field'] = 'nullable|string';
            $rules['api_source_config.data_path'] = 'nullable|string';
            $rules['api_source_config.auth'] = 'nullable|array';
            $rules['api_source_config.params'] = 'nullable|array';
            $rules['api_source_config.headers'] = 'nullable|array';
            $rules['api_source_config.timeout'] = 'nullable|integer|min:1|max:300';
            $rules['api_source_config.cache_ttl'] = 'nullable|integer|min:60|max:3600';
            
            // Calculated field specific validation
            if ($request->field_type === 'calculated') {
                $rules['calculation_formula'] = 'required|string';
                $rules['calculation_format'] = 'nullable|in:number,currency,percentage,decimal_2,decimal_0';
                $rules['auto_calculate'] = 'boolean';
            }
        }
        elseif ($field->field_type === 'calculated') {
            // If has submissions and is calculated field, allow format change only
            $rules['calculation_format'] = 'nullable|in:number,currency,percentage,decimal_2,decimal_0';
        }

        $validated = $request->validate($rules);

        // Custom validation for options if field type requires them
        if ($this->fieldTypeHasOptions($validated['field_type'])) {
            $hasApiSource = !empty($validated['api_source_config']) && 
                           !empty($validated['api_source_config']['url']);
            
            if (!$hasApiSource) {
                // For static options, we don't need to validate here since options are managed separately
                // But we should ensure the field has options if it's not an API source
                if (!$field->hasOptions() && !$field->hasApiSource()) {
                    return back()
                        ->withInput()
                        ->withErrors(['options' => 'Please provide at least 2 options for ' . $validated['field_type'] . ' field type.']);
                }
            } else {
                // Validate API source configuration
                $apiService = app(\App\Services\ApiOptionsService::class);
                $apiErrors = $apiService->validateConfig($validated['api_source_config']);
                if (!empty($apiErrors)) {
                    return back()
                        ->withInput()
                        ->withErrors(['api_source_config' => implode(', ', $apiErrors)]);
                }
            }
        }

        // Build validation rules for date fields
        $validationRules = $field->validation_rules ?? null;
        
        if (in_array($field->field_type, ['date', 'datetime'])) {
            $validationRules = [];
            
            // Minimum date
            if ($request->filled('date_min_type')) {
                $validationRules['date_min'] = [
                    'type' => $request->date_min_type,
                    'value' => null
                ];
                
                if ($request->date_min_type === 'fixed') {
                    $validationRules['date_min']['value'] = $request->date_min_fixed;
                } elseif (in_array($request->date_min_type, ['today_minus', 'today_plus'])) {
                    $validationRules['date_min']['days'] = $request->date_min_days ?? 0;
                }
            }
            
            // Maximum date
            if ($request->filled('date_max_type')) {
                $validationRules['date_max'] = [
                    'type' => $request->date_max_type,
                    'value' => null
                ];
                
                if ($request->date_max_type === 'fixed') {
                    $validationRules['date_max']['value'] = $request->date_max_fixed;
                } elseif (in_array($request->date_max_type, ['today_minus', 'today_plus'])) {
                    $validationRules['date_max']['days'] = $request->date_max_days ?? 0;
                }
            }
            
            // Allowed days (only update if no submissions)
            if (!$hasSubmissions && $request->has('allowed_days')) {
                $validationRules['allowed_days'] = $request->allowed_days;
            } elseif ($hasSubmissions && isset($field->validation_rules['allowed_days'])) {
                // Keep existing allowed days if has submissions
                $validationRules['allowed_days'] = $field->validation_rules['allowed_days'];
            }
            
            // Disabled dates (only update if no submissions)
            if (!$hasSubmissions && $request->filled('disabled_dates')) {
                $dates = array_filter(array_map('trim', explode("\n", $request->disabled_dates)));
                if (!empty($dates)) {
                    $validationRules['disabled_dates'] = $dates;
                }
            } elseif ($hasSubmissions && isset($field->validation_rules['disabled_dates'])) {
                // Keep existing disabled dates if has submissions
                $validationRules['disabled_dates'] = $field->validation_rules['disabled_dates'];
            }
        }

        // Update field
        $updateData = [
            'field_label' => $validated['field_label'],
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null
        ];

        // Update validation rules for specific field types
        if (in_array($field->field_type, ['date', 'datetime'])) {
            $updateData['validation_rules'] = $validationRules;
        } elseif ($field->field_type === 'number') {
            // Handle Number field validation rules
            $request->validate([
                'min_value' => 'nullable|numeric',
                'max_value' => 'nullable|numeric|gte:min_value',
                'step_value' => 'nullable|numeric|min:0.01'
            ]);
            
            $numberValidationRules = [];
            if ($request->filled('min_value')) {
                $numberValidationRules['min'] = (float) $request->min_value;
            }
            if ($request->filled('max_value')) {
                $numberValidationRules['max'] = (float) $request->max_value;
            }
            if ($request->filled('step_value')) {
                $numberValidationRules['step'] = (float) $request->step_value;
            }
            
            $updateData['validation_rules'] = $numberValidationRules;
        } elseif ($field->field_type === 'decimal') {
            // Handle Decimal field validation rules
            $request->validate([
                'decimal_min_value' => 'nullable|numeric',
                'decimal_max_value' => 'nullable|numeric|gte:decimal_min_value',
                'decimal_places' => 'nullable|integer|min:0|max:10',
                'decimal_step_value' => 'nullable|numeric|min:0.001'
            ]);
            
            $decimalValidationRules = [];
            if ($request->filled('decimal_min_value')) {
                $decimalValidationRules['min'] = (float) $request->decimal_min_value;
            }
            if ($request->filled('decimal_max_value')) {
                $decimalValidationRules['max'] = (float) $request->decimal_max_value;
            }
            if ($request->filled('decimal_places')) {
                $decimalValidationRules['decimal_places'] = (int) $request->decimal_places;
            }
            if ($request->filled('decimal_step_value')) {
                $decimalValidationRules['step'] = (float) $request->decimal_step_value;
            }
            
            $updateData['validation_rules'] = $decimalValidationRules;
        } elseif ($field->field_type === 'live_photo') {
            // Handle Live Photo validation rules
            
            $request->validate([
                'max_photos' => 'nullable|integer|min:1|max:10',
                'photo_quality' => 'nullable|numeric|min:0.1|max:1.0',
                'require_location' => 'boolean'
            ]);
            
            $updateData['validation_rules'] = [
                'max_photos' => $request->max_photos ?? 1,
                'photo_quality' => $request->photo_quality ?? 0.8,
                'require_location' => $request->has('require_location')
            ];
            
        }

        if (!$hasSubmissions) {
            $updateData['field_code'] = $validated['field_code'];
            $updateData['field_type'] = $validated['field_type'];
            $updateData['is_required'] = $field->field_type === 'calculated' ? false : $request->has('is_required');
            $updateData['api_source_config'] = $this->fieldTypeUsesApiSource($validated['field_type']) ? ($validated['api_source_config'] ?? null) : null;

            // Clear validation rules if field type changed from date to non-date
            // But preserve validation rules for field types that have them (live_photo, signature, etc.)
            if (!in_array($validated['field_type'], ['date', 'datetime', 'live_photo', 'signature', 'hidden', 'calculated', 'number', 'decimal'])) {
                $updateData['validation_rules'] = null;
            }

            // Handle calculated field data
            if ($validated['field_type'] === 'calculated') {
                $dependencies = $this->parseFormulaDependencies($validated['calculation_formula']);
                
                // Validate dependencies
                $invalidFields = $this->validateCalculationDependencies($version, $dependencies);
                if (!empty($invalidFields)) {
                    return back()
                        ->withInput()
                        ->withErrors(['calculation_formula' => 'Referenced fields not found or not numeric: ' . implode(', ', $invalidFields)]);
                }
                
                $updateData['calculation_formula'] = $validated['calculation_formula'];
                $updateData['calculation_dependencies'] = $dependencies;
                $updateData['validation_rules'] = [
                    'formula' => $validated['calculation_formula'],
                    'dependencies' => $dependencies,
                    'format' => $validated['calculation_format'] ?? 'number',
                    'auto_calculate' => $request->has('auto_calculate')
                ];
            } elseif ($validated['field_type'] === 'number') {
                // Handle Number field validation rules when field type changes
                $request->validate([
                    'min_value' => 'nullable|numeric',
                    'max_value' => 'nullable|numeric|gte:min_value',
                    'step_value' => 'nullable|numeric|min:0.01'
                ]);
                
                $numberValidationRules = [];
                if ($request->filled('min_value')) {
                    $numberValidationRules['min'] = (float) $request->min_value;
                }
                if ($request->filled('max_value')) {
                    $numberValidationRules['max'] = (float) $request->max_value;
                }
                if ($request->filled('step_value')) {
                    $numberValidationRules['step'] = (float) $request->step_value;
                }
                
                $updateData['validation_rules'] = $numberValidationRules;
            } elseif ($validated['field_type'] === 'decimal') {
                // Handle Decimal field validation rules when field type changes
                $request->validate([
                    'decimal_min_value' => 'nullable|numeric',
                    'decimal_max_value' => 'nullable|numeric|gte:decimal_min_value',
                    'decimal_places' => 'nullable|integer|min:0|max:10',
                    'decimal_step_value' => 'nullable|numeric|min:0.001'
                ]);
                
                $decimalValidationRules = [];
                if ($request->filled('decimal_min_value')) {
                    $decimalValidationRules['min'] = (float) $request->decimal_min_value;
                }
                if ($request->filled('decimal_max_value')) {
                    $decimalValidationRules['max'] = (float) $request->decimal_max_value;
                }
                if ($request->filled('decimal_places')) {
                    $decimalValidationRules['decimal_places'] = (int) $request->decimal_places;
                }
                if ($request->filled('decimal_step_value')) {
                    $decimalValidationRules['step'] = (float) $request->decimal_step_value;
                }
                
                $updateData['validation_rules'] = $decimalValidationRules;
            } else {
                // Clear calculation data if changing from calculated to other type
                $updateData['calculation_formula'] = null;
                $updateData['calculation_dependencies'] = null;
            }

            // Handle is_required logic
            if (in_array($validated['field_type'], ['calculated', 'hidden'])) {
                $updateData['is_required'] = false; // Force false for calculated/hidden
            } else {
                $updateData['is_required'] = $request->has('is_required');
            }
        }

        // Update format for existing calculated fields
        if ($field->field_type === 'calculated' && $request->filled('calculation_format')) {
            $existingRules = $field->validation_rules ?? [];
            $existingRules['format'] = $validated['calculation_format'];
            $updateData['validation_rules'] = $existingRules;
        }

        
        $field->update($updateData);

        // Recalculate existing submissions if format changed
        if ($field->field_type === 'calculated' && $hasSubmissions && $request->filled('calculation_format')) {
            $this->recalculateExistingSubmissions($field);
        }

        return redirect()->route('formversions.show', [$form, $version])
            ->with('success', 'Field updated successfully.');
    }

    /**
     * Delete field
     */
    public function destroy(Form $form, FormVersion $version, FormField $field)
    {
        // Verify relationships
        if ($version->form_id != $form->id || $field->form_version_id != $version->id) {
            abort(404);
        }

        // Check if version has submissions
        if ($version->submissions()->exists()) {
            return back()->with('error', 'Cannot delete field from version with submissions.');
        }

        $field->delete();

        return redirect()->route('formversions.show', [$form, $version])
            ->with('success', 'Field deleted successfully.');
    }

    /**
     * Manage field options
     */
    public function options(Form $form, FormVersion $version, FormField $field)
    {
        // Verify relationships
        if ($version->form_id != $form->id || $field->form_version_id != $version->id) {
            abort(404);
        }

        // Check if field type supports options
        if (!$field->hasOptions()) {
            return redirect()->route('formversions.show', [$form, $version])
                ->with('error', 'This field type does not support options.');
        }

        $hasSubmissions = $version->submissions()->exists();
        $options = $field->options()->orderBy('created_at', 'asc')->get();

        return view('formfields.options', compact('form', 'version', 'field', 'options', 'hasSubmissions'));
    }

    /**
     * Update field options
     */
    public function updateOptions(Request $request, Form $form, FormVersion $version, FormField $field)
    {
        // Verify relationships
        if ($version->form_id != $form->id || $field->form_version_id != $version->id) {
            abort(404);
        }

        $validated = $request->validate([
            'options' => 'required|array|min:1',
            'options.*.id' => 'nullable|exists:form_field_options,id',
            'options.*.value' => 'required|string|max:255',
            'options.*.label' => 'required|string|max:255',
            'options.*.is_default' => 'boolean'
        ]);

        $hasSubmissions = $version->submissions()->exists();

        // Process options
        $existingIds = [];
        foreach ($validated['options'] as $optionData) {
            if (!empty($optionData['id'])) {
                // Update existing option
                $option = FormFieldOption::find($optionData['id']);
                if ($option && $option->form_field_id == $field->id) {
                    // If has submissions, only update label
                    if ($hasSubmissions) {
                        $option->update([
                            'option_label' => $optionData['label'],
                            'is_default' => $request->has("options.{$optionData['id']}.is_default")
                        ]);
                    } else {
                        $option->update([
                            'option_value' => $optionData['value'],
                            'option_label' => $optionData['label'],
                            'is_default' => $request->has("options.{$optionData['id']}.is_default")
                        ]);
                    }
                    $existingIds[] = $option->id;
                }
            } else {
                // Create new option (only if no submissions)
                if (!$hasSubmissions) {
                    $newOption = $field->options()->create([
                        'option_value' => $optionData['value'],
                        'option_label' => $optionData['label'],
                        'is_default' => $request->has("options.new.is_default")
                    ]);
                    $existingIds[] = $newOption->id;
                }
            }
        }

        // Delete removed options (only if no submissions)
        if (!$hasSubmissions) {
            $field->options()->whereNotIn('id', $existingIds)->delete();
        }

        return redirect()->route('formversions.show', [$form, $version])
            ->with('success', 'Options updated successfully.');
    }

    /**
     * Check if field type has options
     */
    private function fieldTypeHasOptions($type)
    {
        return in_array($type, ['select_single', 'select_multiple', 'radio', 'checkbox']);
    }

    private function fieldTypeUsesApiSource($type)
    {
        return in_array($type, ['select_single', 'select_multiple', 'radio', 'checkbox']);
    }

    // Add new method for reordering
    public function reorder(Request $request, Form $form, FormVersion $version)
    {
        // Verify relationships
        if ($version->form_id != $form->id) {
            abort(404);
        }

        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:form_fields,id',
            'fields.*.order' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['fields'] as $fieldData) {
                FormField::where('id', $fieldData['id'])
                        ->where('form_version_id', $version->id)
                        ->update(['order_position' => $fieldData['order']]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Fields reordered successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to reorder fields'], 500);
        }
    }

    /**
     * Parse formula to get dependencies
     */
    private function parseFormulaDependencies($formula): array
    {
        preg_match_all('/\{([a-z_][a-z0-9_]*)\}/', $formula, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate calculation dependencies
     */
    private function validateCalculationDependencies($version, $dependencies): array
    {
        $invalidFields = [];
        
        foreach ($dependencies as $fieldCode) {
            $field = $version->fields()->where('field_code', $fieldCode)->first();
            
            if (!$field) {
                $invalidFields[] = $fieldCode . ' (not found)';
            } else {
                // Check if field is numeric OR hidden field with numeric default
                $isNumericField = in_array($field->field_type, ['number', 'decimal']);
                $isNumericHidden = $field->field_type === 'hidden' && $this->isHiddenFieldNumeric($field);
                
                if (!$isNumericField && !$isNumericHidden) {
                    $invalidFields[] = $fieldCode . ' (not numeric)';
                }
            }
        }
        
        return $invalidFields;
    }

    /**
     * Recalculate existing submissions for a calculated field
     */
    private function recalculateExistingSubmissions(FormField $field)
    {
        try {
            $submissions = $field->formVersion->submissions;
            $calculationService = app(CalculationService::class);
            
            foreach ($submissions as $submission) {
                $newValue = $calculationService->calculateFieldValue($field, $submission);
                
                if ($newValue !== null) {
                    $answer = $submission->answers()->where('form_field_id', $field->id)->first();
                    
                    if ($answer) {
                        $answer->update(['answer_value' => $newValue]);
                    } else {
                        $submission->answers()->create([
                            'form_field_id' => $field->id,
                            'answer_value' => $newValue
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to recalculate existing submissions: ' . $e->getMessage());
        }
    }

    /**
     * Get available fields for calculation - FIXED untuk hidden fields
     */
    public function getAvailableFields(Form $form, FormVersion $version)
    {
        // Verify relationships
        if ($version->form_id != $form->id) {
            abort(404);
        }
        
        // Get all fields first, then filter in PHP (more reliable than complex SQL)
        $allFields = $version->fields()
            ->orderBy('order_position')
            ->get(['id', 'field_code', 'field_label', 'field_type', 'validation_rules']);
        
        $numericFields = $allFields->filter(function($field) {
            // Include regular numeric fields
            if (in_array($field->field_type, ['number', 'decimal'])) {
                return true;
            }
            
            // Include numeric hidden fields
            if ($field->field_type === 'hidden') {
                return $this->isHiddenFieldNumeric($field);
            }
            
            return false;
        });
        
        // Format response
        $availableFields = $numericFields->map(function($field) {
            $fieldInfo = [
                'field_code' => $field->field_code,
                'field_label' => $field->field_label,
                'field_type' => $field->field_type,
                'display_name' => $field->field_label,
                'is_hidden' => $field->field_type === 'hidden'
            ];
            
            // Add additional info for hidden fields
            if ($field->field_type === 'hidden') {
                $rules = $field->validation_rules ?? [];
                $valueType = $rules['value_type'] ?? 'static';
                
                if ($valueType === 'static') {
                    $defaultValue = $rules['default_value'] ?? '';
                    $fieldInfo['default_value'] = $defaultValue;
                    $fieldInfo['display_name'] = $field->field_label . ' (Hidden: ' . $defaultValue . ')';
                } else {
                    $dynamicType = $rules['dynamic_type'] ?? '';
                    $fieldInfo['dynamic_type'] = $dynamicType;
                    $fieldInfo['display_name'] = $field->field_label . ' (Hidden: ' . ucfirst(str_replace('_', ' ', $dynamicType)) . ')';
                }
            }
            
            return $fieldInfo;
        })->values(); // Reset array keys
        
        
        return response()->json([
            'success' => true,
            'fields' => $availableFields,
            'total' => $availableFields->count(),
            'debug' => [
                'total_fields_in_version' => $allFields->count(),
                'numeric_fields_found' => $numericFields->count(),
                'hidden_fields_total' => $allFields->where('field_type', 'hidden')->count()
            ]
        ]);
    }

    /**
     * Check if hidden field contains numeric value - Enhanced
     */
    private function isHiddenFieldNumeric(FormField $field): bool
    {
        if ($field->field_type !== 'hidden') {
            return false;
        }
        
        $rules = $field->validation_rules;
        
        // If no validation rules, not numeric
        if (!$rules || !is_array($rules)) {
            return false;
        }
        
        $valueType = $rules['value_type'] ?? 'static';
        
        if ($valueType === 'static') {
            // Check if default value is numeric
            $defaultValue = $rules['default_value'] ?? '';
            $isNumeric = is_numeric($defaultValue);
            
            return $isNumeric;
        } else {
            // Check if dynamic type generates numeric value
            $dynamicType = $rules['dynamic_type'] ?? '';
            $numericDynamicTypes = ['user_id', 'random_number'];
            $isNumeric = in_array($dynamicType, $numericDynamicTypes);
            
            return $isNumeric;
        }
    }
}