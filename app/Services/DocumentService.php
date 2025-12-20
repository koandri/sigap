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
        // Load departments if not already loaded
        if (!$user->relationLoaded('departments')) {
            $user->load('departments');
        }
        
        $userDepartmentNames = $user->departments->pluck('name')->toArray();
        
        if (empty($userDepartmentNames)) {
            return false;
        }
        
        // Get document's department role (document's department_id references roles table)
        // Get the role name from the document's department_id (which is a role ID)
        $documentDepartmentRole = \Spatie\Permission\Models\Role::find($document->department_id);
        $hasDepartmentAccess = false;
        
        if ($documentDepartmentRole && in_array($documentDepartmentRole->name, $userDepartmentNames)) {
            $hasDepartmentAccess = true;
        }
        
        // If not, check if document has accessible departments (roles) that match user's department names
        if (!$hasDepartmentAccess) {
            // Load accessible departments if not already loaded
            if (!$document->relationLoaded('accessibleDepartments')) {
                $document->load('accessibleDepartments');
            }
            
            // Get role IDs from accessible departments and check their names
            $accessibleDepartmentRoleIds = $document->accessibleDepartments->pluck('id')->toArray();
            if (!empty($accessibleDepartmentRoleIds)) {
                $accessibleRoleNames = \Spatie\Permission\Models\Role::whereIn('id', $accessibleDepartmentRoleIds)
                    ->pluck('name')
                    ->toArray();
                $hasDepartmentAccess = !empty(array_intersect($userDepartmentNames, $accessibleRoleNames));
            }
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

        // Filter by department access (using user's departments)
        // Load departments if not already loaded
        if (!$user->relationLoaded('departments')) {
            $user->load('departments');
        }
        
        $userDepartmentNames = $user->departments->pluck('name')->toArray();
        
        if (empty($userDepartmentNames)) {
            return collect();
        }
        
        // Get role IDs that match user's department names
        $matchingRoleIds = \Spatie\Permission\Models\Role::whereIn('name', $userDepartmentNames)
            ->pluck('id')
            ->toArray();
        
        if (empty($matchingRoleIds)) {
            return collect();
        }
        
        return $query->where(function ($q) use ($matchingRoleIds) {
            $q->whereIn('department_id', $matchingRoleIds)
              ->orWhereHas('accessibleDepartments', function ($subQ) use ($matchingRoleIds) {
                  $subQ->whereIn('roles.id', $matchingRoleIds);
              });
        })->get();
    }

}
