<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'workflow_name',
        'description',
        'flow_type',
        'is_active',
        'conditions',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conditions' => 'array'
    ];

    // Flow types
    const FLOW_SEQUENTIAL = 'sequential'; // One by one
    const FLOW_PARALLEL = 'parallel';     // All at once

    // Relationships
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalFlowStep::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function getOrderedSteps()
    {
        return $this->steps()->orderBy('step_order')->get();
    }

    public function getFirstStep()
    {
        return $this->steps()->orderBy('step_order')->first();
    }

    public function getStepByOrder($order)
    {
        return $this->steps()->where('step_order', $order)->first();
    }

    public function getNextStep($currentOrder)
    {
        return $this->steps()
            ->where('step_order', '>', $currentOrder)
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Check if workflow has been used
     */
    public function hasBeenUsed(): bool
    {
        return $this->steps()
            ->whereHas('approvalLogs')
            ->exists();
    }

    /**
     * Get usage count
     */
    public function getUsageCount(): int
    {
        return $this->steps()
            ->withCount('approvalLogs')
            ->get()
            ->sum('approval_logs_count');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForForm($query, $formId)
    {
        return $query->where('form_id', $formId);
    }
}