<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_no',
        'name',
        'description',
        'requires_approval',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected function form_no(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtoupper($value),
            set: fn (string $value) => strtoupper($value),
        );
    }

    public function getDepartmentNames()
    {
        return $this->departments->pluck('name')->implode(', ');
    }

    public function getDepartmentShortNames()
    {
        return $this->departments->pluck('shortname')->implode(', ');
    }

    public function hasApprovalWorkflow(): bool
    {
        return $this->requires_approval && $this->activeApprovalWorkflow()->exists();
    }

    public function getLatestVersion()
    {
        return $this->versions()->orderBy('version_number', 'desc')->first();
    }

    public function getNextVersionNumber(): int
    {
        $latest = $this->versions()->max('version_number');
        return $latest !== null ? $latest + 1 : 0;
    }

    // Relationships
    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class);
    }

    public function activeVersion()
    {
        return $this->hasOne(FormVersion::class)->where('is_active', true);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'form_department');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(ApprovalWorkflow::class);
    }

    public function activeApprovalWorkflow()
    {
        return $this->hasOne(ApprovalWorkflow::class)->where('is_active', true);
    }

    
}