<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'shortname',
    ];

    protected function shortname(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtoupper($value),
            set: fn (string $value) => strtoupper($value),
        );
    }

    /**
     * Get active forms for this department
     */
    public function activeForms()
    {
        return $this->forms()->where('forms.is_active', true)->get();
    }

    /**
     * Get all supervisors in this department
     */
    public function supervisors()
    {
        return $this->getUsersByRole('Supervisor');
    }

    /**
     * Get all managers in this department
     */
    public function managers()
    {
        return $this->getUsersByRole('Manager');
    }

    /**
     * Get all staff in this department
     */
    public function staff()
    {
        return $this->getUsersByRole('Staff');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_department');
    }
}
