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

    public function getDocumentMasterlist(array $filters = []): SupportCollection
    {
        $query = Document::with(['department', 'activeVersion', 'creator', 'accessibleDepartments']);

        // Apply filters
        if (!empty($filters['department'])) {
            $query->where('department_id', $filters['department']);
        }

        if (!empty($filters['type'])) {
            $query->where('document_type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('document_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $documents = $query->orderBy('department_id')
            ->orderBy('document_type')
            ->orderBy('document_number')
            ->get();

        // Group by department first, then by document type
        return $documents->groupBy(function ($document) {
                return $document->department?->name ?? 'N/A';
            })
            ->map(function ($departmentDocuments) {
                return $departmentDocuments->groupBy(function ($document) {
                    // Ensure document_type is valid, fallback to 'other' if null or invalid
                    try {
                        return $document->document_type?->value ?? DocumentType::Other->value;
                    } catch (\Throwable $e) {
                        return DocumentType::Other->value;
                    }
                });
            });
    }

    public function checkUserCanAccess(User $user, Document $document): bool
    {
        // Super Admin, Owner, and Document Control can access any document
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Check if user's departments have access
        $userDepartmentIds = $user->departments->pluck('id')->toArray();
        
        if (empty($userDepartmentIds)) {
            return false;
        }
        
        // Check if document's department matches user's department
        $hasDepartmentAccess = in_array($document->department_id, $userDepartmentIds);
        
        // If not, check if document has accessible departments that match user's departments
        if (!$hasDepartmentAccess) {
            // Load accessible departments if not already loaded
            if (!$document->relationLoaded('accessibleDepartments')) {
                $document->load('accessibleDepartments');
            }
            
            $accessibleDepartmentIds = $document->accessibleDepartments->pluck('id')->toArray();
            $hasDepartmentAccess = !empty(array_intersect($userDepartmentIds, $accessibleDepartmentIds));
        }

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

        // Super Admin, Owner, and Document Control can see all documents
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return $query->get();
        }

        // Filter by department access
        $userDepartmentIds = $user->departments->pluck('id')->toArray();
        
        return $query->where(function ($q) use ($userDepartmentIds) {
            $q->whereIn('department_id', $userDepartmentIds)
              ->orWhereHas('accessibleDepartments', function ($subQ) use ($userDepartmentIds) {
                  $subQ->whereIn('departments.id', $userDepartmentIds);
              });
        })->get();
    }

}
