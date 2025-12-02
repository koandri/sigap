<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentBorrowStatus;
use App\Enums\DocumentType;
use App\Enums\DocumentVersionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'title',
        'description',
        'document_type',
        'department_id',
        'created_by',
        'physical_location',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'physical_location' => 'array',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class, 'document_id')
            ->where('status', DocumentVersionStatus::Active);
    }

    public function accessibleDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'document_accessible_departments', 'document_id', 'department_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(DocumentInstance::class);
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(DocumentAccessRequest::class);
    }

    public function borrows(): HasMany
    {
        return $this->hasMany(DocumentBorrow::class);
    }

    public function activeBorrow(): ?DocumentBorrow
    {
        return $this->borrows()
            ->whereIn('status', [DocumentBorrowStatus::Pending, DocumentBorrowStatus::Approved, DocumentBorrowStatus::CheckedOut])
            ->first();
    }

    public function isCurrentlyBorrowed(): bool
    {
        return $this->borrows()
            ->where('status', DocumentBorrowStatus::CheckedOut)
            ->exists();
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByType($query, DocumentType $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeAccessibleByUser($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereHas('department', function ($deptQuery) use ($user) {
                $deptQuery->whereHas('users', function ($userQuery) use ($user) {
                    $userQuery->where('users.id', $user->id);
                });
            })
              ->orWhereHas('accessibleDepartments', function ($subQ) use ($user) {
                  $subQ->whereHas('users', function ($userQuery) use ($user) {
                      $userQuery->where('users.id', $user->id);
                  });
              });
        });
    }

    public function getPhysicalLocationStringAttribute(): string
    {
        if (!$this->physical_location) {
            return 'Not specified';
        }

        $location = $this->physical_location;
        return sprintf(
            'Room: %s, Shelf: %s, Folder: %s',
            $location['room_no'] ?? 'N/A',
            $location['shelf_no'] ?? 'N/A',
            $location['folder_no'] ?? 'N/A'
        );
    }
}
