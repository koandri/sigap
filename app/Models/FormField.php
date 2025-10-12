<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_version_id',
        'field_code',
        'field_label',
        'field_type',
        'is_required',
        'order_position',
        'validation_rules',
        'conditional_logic',
        'calculation_formula',
        'calculation_dependencies',
        'help_text',
        'placeholder',
        'api_source_config'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'validation_rules' => 'array',
        'conditional_logic' => 'array',
        'calculation_dependencies' => 'array',
        'api_source_config' => 'array'
    ];

    // Field types constant
    const FIELD_TYPES = [
        'text_short' => 'Short Text',
        'text_long' => 'Long Text',
        'number' => 'Number',
        'decimal' => 'Decimal',
        'date' => 'Date',
        'datetime' => 'Date & Time',
        'select_single' => 'Dropdown',
        'select_multiple' => 'Multiple Select',
        'radio' => 'Radio Button',
        'checkbox' => 'Checkbox',
        'file' => 'File Upload',
        'boolean' => 'Yes/No',
        'calculated' => 'Calculated Field',
        'hidden' => 'Hidden Field',
        'signature' => 'Signature Pad',
        'live_photo' => 'Live Photo'
    ];

    // Add scope for ordering
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_position', 'asc')
                    ->orderBy('created_at', 'asc'); // Fallback to created_at
    }
    
    // Helper methods
    public function hasOptions(): bool
    {
        return in_array($this->field_type, [
            'select_single', 
            'select_multiple', 
            'radio', 
            'checkbox'
        ]);
    }

    public function getOptionsInOrder()
    {
        return $this->options()->orderBy('created_at', 'asc')->get();
    }

    public function isCalculated(): bool
    {
        return $this->field_type === 'calculated';
    }

    public function isHidden(): bool
    {
        return $this->field_type === 'hidden';
    }

    public function getDependentFields()
    {
        if (!$this->calculation_dependencies) {
            return collect();
        }
        
        return $this->formVersion->fields()
            ->whereIn('field_code', $this->calculation_dependencies)
            ->get();
    }

    public function parseFormula(): array
    {
        if (!$this->calculation_formula) {
            return [];
        }
        
        // Parse formula to extract field references
        preg_match_all('/\{([a-z_][a-z0-9_]*)\}/', $this->calculation_formula, $matches);
        
        return $matches[1] ?? [];
    }

    public static function getNextOrderPosition($formVersionId)
    {
        $maxOrder = self::where('form_version_id', $formVersionId)
                        ->max('order_position');
        
        return $maxOrder ? $maxOrder + 10 : 10; // Increment by 10 for easier reordering
    }

    public function getDateRulesSummary()
    {
        if (!$this->validation_rules || !in_array($this->field_type, ['date', 'datetime'])) {
            return null;
        }
        
        $summary = [];
        $rules = $this->validation_rules;
        
        // Min date
        if (isset($rules['date_min'])) {
            $minRule = $rules['date_min'];
            if ($minRule['type'] === 'fixed') {
                $summary[] = 'Min: ' . date('d M Y', strtotime($minRule['value']));
            } elseif ($minRule['type'] === 'today') {
                $summary[] = 'Min: Today';
            } elseif ($minRule['type'] === 'today_minus') {
                $summary[] = 'Min: Today -' . $minRule['days'] . ' days';
            } elseif ($minRule['type'] === 'today_plus') {
                $summary[] = 'Min: Today +' . $minRule['days'] . ' days';
            }
        }
        
        // Max date
        if (isset($rules['date_max'])) {
            $maxRule = $rules['date_max'];
            if ($maxRule['type'] === 'fixed') {
                $summary[] = 'Max: ' . date('d M Y', strtotime($maxRule['value']));
            } elseif ($maxRule['type'] === 'today') {
                $summary[] = 'Max: Today';
            } elseif ($maxRule['type'] === 'today_minus') {
                $summary[] = 'Max: Today -' . $maxRule['days'] . ' days';
            } elseif ($maxRule['type'] === 'today_plus') {
                $summary[] = 'Max: Today +' . $maxRule['days'] . ' days';
            }
        }
        
        // Allowed days
        if (isset($rules['allowed_days']) && count($rules['allowed_days']) < 7) {
            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $allowed = array_map(function($d) use ($days) {
                return $days[intval($d)];
            }, $rules['allowed_days']);
            $summary[] = 'Days: ' . implode(', ', $allowed);
        }
        
        // Disabled dates count
        if (isset($rules['disabled_dates']) && count($rules['disabled_dates']) > 0) {
            $summary[] = count($rules['disabled_dates']) . ' dates disabled';
        }
        
        return empty($summary) ? null : implode(' | ', $summary);
    }

    public function isSignature(): bool
    {
        return $this->field_type === 'signature';
    }

    /**
     * Check if field uses API source for options
     */
    public function hasApiSource(): bool
    {
        return !empty($this->api_source_config) && is_array($this->api_source_config);
    }

    /**
     * Get API source configuration
     */
    public function getApiSourceConfig(): ?array
    {
        return $this->api_source_config;
    }

    /**
     * Get options from API source
     */
    public function getApiOptions(): array
    {
        if (!$this->hasApiSource()) {
            return [];
        }

        try {
            $apiService = app(\App\Services\ApiOptionsService::class);
            return $apiService->fetchOptions($this->api_source_config);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch API options for field: ' . $this->field_code, [
                'error' => $e->getMessage(),
                'config' => $this->api_source_config
            ]);
            return [];
        }
    }

    /**
     * Get all options (static + API)
     */
    public function getAllOptions(): array
    {
        $options = [];

        // Add static options first
        foreach ($this->options as $option) {
            $options[] = [
                'value' => $option->option_value,
                'label' => $option->option_label
            ];
        }

        // Add API options
        if ($this->hasApiSource()) {
            $apiOptions = $this->getApiOptions();
            $options = array_merge($options, $apiOptions);
        }

        return $options;
    }

    // Relationships
    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FormAnswer::class);
    }
}