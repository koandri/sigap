<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

final class DocumentService
{
    public function createDocument(array $data): Document
    {
        return DB::transaction(function () use ($data) {
            $document = Document::create($data);
            return $document;
        });
    }

    public function updateDocument(Document $document, array $data): Document
    {
        return DB::transaction(function () use ($document, $data) {
            $document->update($data);
            return $document->fresh();
        });
    }

    public function assignAccessibleDepartments(Document $document, array $departmentIds): void
    {
        $document->accessibleDepartments()->sync($departmentIds);
    }

    public function getDocumentMasterlist(): SupportCollection
    {
        return Document::with(['department', 'activeVersion', 'accessibleDepartments'])
            ->orderBy('department_id')
            ->orderBy('document_type')
            ->orderBy('document_number')
            ->get()
            ->groupBy(['department.name', 'document_type']);
    }

    public function checkUserCanAccess(User $user, Document $document): bool
    {
        // Super Admin and Owner can access any document
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Check if user's department has access
        $hasDepartmentAccess = $document->department_id === $user->role_id ||
            $document->accessibleDepartments()->where('department_id', $user->role_id)->exists();

        if (!$hasDepartmentAccess) {
            return false;
        }

        // For documents that require access requests, check if user has active access
        if ($document->document_type->requiresAccessRequest()) {
            return $this->hasActiveAccess($user, $document);
        }

        return true;
    }

    public function hasActiveAccess(User $user, Document $document): bool
    {
        $activeVersion = $document->activeVersion;
        if (!$activeVersion) {
            return false;
        }

        return $user->documentAccessRequests()
            ->where('document_version_id', $activeVersion->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('approved_expiry_date')
                      ->orWhere('approved_expiry_date', '>', now());
            })
            ->exists();
    }

    public function getPhysicalLocation(Document $document): string
    {
        return $document->physical_location_string;
    }

    public function getDocumentsAccessibleByUser(User $user): Collection
    {
        $query = Document::with(['department', 'activeVersion', 'accessibleDepartments']);

        // Super Admin and Owner can see all documents
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return $query->get();
        }

        // Filter by department access
        return $query->where(function ($q) use ($user) {
            $q->where('department_id', $user->role_id)
              ->orWhereHas('accessibleDepartments', function ($subQ) use ($user) {
                  $subQ->where('department_id', $user->role_id);
              });
        })->get();
    }

}
