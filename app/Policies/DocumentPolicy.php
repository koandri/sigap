<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     * All authenticated users can access the Document Index page.
     * The service method will filter documents based on user's department access.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the Document Index page
        // Documents are filtered by department access in the service layer
        return true;
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // Super Admin, Owner, and Document Control can view any document
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Check if user's departments have access
        // Load departments if not already loaded
        if (!$user->relationLoaded('departments')) {
            $user->load('departments');
        }
        
        $userDepartmentIds = $user->departments->pluck('id')->map(fn($id) => (int)$id)->toArray();
        
        if (empty($userDepartmentIds)) {
            \Log::warning('DocumentPolicy@view: User has no departments', [
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);
            return false;
        }
        
        // Verify document's department_id is valid
        if (!$document->department_id) {
            \Log::warning('DocumentPolicy@view: Document has no department_id', [
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);
            return false;
        }
        
        // Verify document's department exists (in case of data inconsistency)
        // Also ensure we have the department_id as integer for comparison
        $documentDeptId = (int)$document->department_id;
        
        try {
            $documentDepartment = $document->department;
            if (!$documentDepartment) {
                \Log::warning('DocumentPolicy@view: Document department_id does not exist in departments table', [
                    'user_id' => $user->id,
                    'document_id' => $document->id,
                    'document_department_id' => $documentDeptId,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('DocumentPolicy@view: Error loading document department', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'document_department_id' => $documentDeptId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
        
        // Check if document's department matches user's department (ensure both are integers)
        $hasDepartmentAccess = in_array($documentDeptId, $userDepartmentIds, true);
        
        // If not, check if document has accessible departments that match user's departments
        if (!$hasDepartmentAccess) {
            // Load accessible departments if not already loaded
            if (!$document->relationLoaded('accessibleDepartments')) {
                $document->load('accessibleDepartments');
            }
            
            $accessibleDepartmentIds = $document->accessibleDepartments->pluck('id')->map(fn($id) => (int)$id)->toArray();
            $hasDepartmentAccess = !empty(array_intersect($userDepartmentIds, $accessibleDepartmentIds));
        }

        if (!$hasDepartmentAccess) {
            \Log::debug('DocumentPolicy@view: Access denied', [
                'user_id' => $user->id,
                'user_department_ids' => $userDepartmentIds,
                'document_id' => $document->id,
                'document_department_id' => $documentDeptId,
                'accessible_department_ids' => $document->accessibleDepartments->pluck('id')->map(fn($id) => (int)$id)->toArray() ?? [],
            ]);
            return false;
        }

        // For documents that require access requests, check if user has active access
        // BUT: Users in the document's department or accessible departments get automatic access
        // Access requests are only required for users outside these departments
        if ($document->document_type->requiresAccessRequest()) {
            // User has department access, so they get automatic access even for documents requiring access requests
            return true;
        }

        return true;
    }

    /**
     * Determine whether the user can create documents.
     * Only Super Admin, Owner, and Document Control can create documents.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can update the document.
     * Only Super Admin, Owner, and Document Control can update documents.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can delete the document.
     * Only Super Admin, Owner, and Document Control can delete documents.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can request access to the document.
     */
    public function requestAccess(User $user, Document $document): bool
    {
        // Super Admin, Owner, and Document Control don't need to request access
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return false;
        }

        // Check if user has request permission
        if (!$user->hasPermissionTo('dms.access.request')) {
            return false;
        }

        // Check if document requires access request
        if (!$document->document_type->requiresAccessRequest()) {
            return false;
        }

        // Check if user already has active access
        if ($this->hasActiveAccess($user, $document)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has active access to the document
     */
    private function hasActiveAccess(User $user, Document $document): bool
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
}
