<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CleaningSubmission extends Model
{
    use HasFiles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cleaning_task_id',
        'submitted_by',
        'submitted_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the cleaning task this submission belongs to.
     */
    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    /**
     * Get the user who submitted this.
     */
    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the approval record for this submission.
     */
    public function approval(): HasOne
    {
        return $this->hasOne(CleaningApproval::class);
    }

    /**
     * Scope to filter submissions from yesterday.
     */
    public function scopeYesterday($query)
    {
        return $query->whereDate('submitted_at', today()->subDay());
    }

    /**
     * Scope to filter submissions for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('submitted_at', $date);
    }

    /**
     * Get the before photo.
     */
    public function beforePhoto(): ?File
    {
        return $this->photos()
            ->where('metadata->type', 'before')
            ->first();
    }

    /**
     * Get the after photo.
     */
    public function afterPhoto(): ?File
    {
        return $this->photos()
            ->where('metadata->type', 'after')
            ->first();
    }
}
