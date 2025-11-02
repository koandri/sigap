<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentInstanceStatus;
use App\Models\DocumentInstance;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DocumentInstanceService
{
    public function createInstance(DocumentVersion $templateVersion, User $user, array $data): DocumentInstance
    {
        return DB::transaction(function () use ($templateVersion, $user, $data) {
            $instanceNumber = $this->generateInstanceNumber($templateVersion->document->document_type->value);
            
            return DocumentInstance::create([
                'template_document_version_id' => $templateVersion->id,
                'instance_number' => $instanceNumber,
                'subject' => $data['subject'],
                'content_summary' => $data['content_summary'] ?? null,
                'created_by' => $user->id,
                'status' => DocumentInstanceStatus::Draft,
            ]);
        });
    }

    public function updateInstance(DocumentInstance $instance, array $data): DocumentInstance
    {
        return DB::transaction(function () use ($instance, $data) {
            $instance->update($data);
            return $instance->fresh();
        });
    }

    public function submitForApproval(DocumentInstance $instance): void
    {
        DB::transaction(function () use ($instance) {
            $instance->update([
                'status' => DocumentInstanceStatus::PendingApproval,
            ]);
        });
    }

    public function approveInstance(DocumentInstance $instance, User $approver, ?string $finalPdfPath = null): void
    {
        DB::transaction(function () use ($instance, $approver, $finalPdfPath) {
            $instance->update([
                'status' => DocumentInstanceStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'final_pdf_path' => $finalPdfPath,
            ]);
        });
    }

    public function rejectInstance(DocumentInstance $instance): void
    {
        DB::transaction(function () use ($instance) {
            $instance->update([
                'status' => DocumentInstanceStatus::Draft,
            ]);
        });
    }

    private function generateInstanceNumber(string $documentType): string
    {
        $prefix = match ($documentType) {
            'internal_memo' => 'IM',
            'outgoing_letter' => 'OL',
            default => 'INST',
        };
        
        $date = now()->format('ymd');
        $prefix = "{$prefix}-{$date}-";
        
        // Get the last instance number for today
        $lastInstance = DocumentInstance::where('instance_number', 'like', $prefix . '%')
            ->orderBy('instance_number', 'desc')
            ->first();
        
        if ($lastInstance) {
            $lastNumber = (int) substr($lastInstance->instance_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }
}

