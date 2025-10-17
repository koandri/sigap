<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobilephone_no',
        'password',
        'manager_id',
        'asana_id',
        'locations',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locations' => 'array',
            'active' => 'boolean'
        ];
    }

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canBeImpersonated()
    {
        if ($this->hasRole('Super Admin')) {
            return false;
        }

        if ($this->email == 'no-reply@suryagroup.app') {
            return false;
        }

        return true;
    }

    public function getDepartmentNames()
    {
        return $this->departments->pluck('name')->implode(', ');
    }

    public function getDepartmentShortNames()
    {
        return $this->departments->pluck('shortname')->implode(', ');
    }

    /**
     * Get users with specific role in specific department
     */
    public function scopeWithRoleInDepartment($query, $roleCode, $departmentId)
    {
        return $query->whereHas('roles', function($q) use ($roleCode) {
                    $q->where('code', $roleCode);
                })
                ->whereHas('departments', function($q) use ($departmentId) {
                    $q->where('departments.id', $departmentId);
                });
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function createdForms()
    {
        return $this->hasMany(Form::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class, 'submitted_by');
    }

    /**
     * Get users who report to this user (staff members).
     */
    public function staff()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Check if user is a manager (has staff reporting to them).
     */
    public function isManager(): bool
    {
        return $this->staff()->exists();
    }

    /**
     * Scope to get users with Engineering Operator role.
     */
    public function scopeEngineeringOperators($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Engineering Operator');
        })->where('active', true);
    }
}
