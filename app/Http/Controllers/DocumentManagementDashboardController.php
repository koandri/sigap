<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentBorrow;
use App\Models\FormRequest;
use App\Models\PrintedForm;
use App\Models\DocumentInstance;
use App\Models\User;
use App\Enums\DocumentInstanceStatus;
use App\Services\FormRequestService;
use App\Services\DocumentBorrowService;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class DocumentManagementDashboardController extends Controller
{
    public function __construct(
        private readonly FormRequestService $formRequestService,
        private readonly DocumentBorrowService $borrowService
    ) {
        $this->middleware('can:dms.dashboard.view')->only(['index']);
    }

    public function index(): View
    {
        $user = Auth::user();
        
        $borrowStats = $this->borrowService->getStatistics();
        
        $stats = [
            'total_documents' => Document::count(),
            'pending_document_approvals' => $this->getPendingDocumentApprovalsCount(),
            'pending_correspondence_approvals' => $this->getPendingCorrespondenceApprovalsCount($user),
            'pending_form_requests' => FormRequest::where('status', 'requested')->count(),
            'circulating_forms' => PrintedForm::whereIn('status', ['issued', 'circulating'])->count(),
            'documents_borrowed' => $borrowStats['total_borrowed'],
            'overdue_borrows' => $borrowStats['total_overdue'],
            'pending_borrow_approvals' => $borrowStats['pending_approvals'],
        ];

        $recentActivities = collect($this->getRecentActivities());
        $overdueRequests = $this->formRequestService->getOverdueRequests();
        $overdueBorrows = $this->borrowService->getOverdueBorrows();
        
        return view('dashboards.dms', compact('stats', 'recentActivities', 'overdueRequests', 'overdueBorrows'));
    }

    private function getPendingDocumentApprovalsCount(): int
    {
        return DB::table('document_version_approvals')
            ->where('status', 'pending')
            ->count();
    }

    private function getPendingCorrespondenceApprovalsCount(User $user): int
    {
        // Get instances pending approval that the user can approve
        $pendingInstances = DocumentInstance::where('status', DocumentInstanceStatus::PendingApproval)
            ->with('creator')
            ->get();

        // Filter by approval policy
        return $pendingInstances->filter(function ($instance) use ($user) {
            // Super Admin and Owner can approve all
            if ($user->hasRole(['Super Admin', 'Owner'])) {
                return true;
            }

            // Check if user is the creator's manager
            $creator = $instance->creator;
            if ($creator && $creator->manager_id === $user->id) {
                return true;
            }

            // Check if user has approve permission
            return $user->hasPermissionTo('dms.instances.approve');
        })->count();
    }

    private function getRecentActivities(): array
    {
        $activities = [];
        
        // Recent document versions
        $recentVersions = DB::table('document_versions')
            ->join('documents', 'document_versions.document_id', '=', 'documents.id')
            ->join('users', 'document_versions.created_by', '=', 'users.id')
            ->select('documents.title', 'document_versions.version_number', 'document_versions.status', 'users.name as creator', 'document_versions.created_at')
            ->orderBy('document_versions.created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentVersions as $version) {
            $activities[] = [
                'type' => 'document_version',
                'message' => "New version {$version->version_number} created for {$version->title} by {$version->creator}",
                'timestamp' => $version->created_at,
            ];
        }

        // Recent form requests
        $recentRequests = DB::table('form_requests')
            ->join('users', 'form_requests.requested_by', '=', 'users.id')
            ->select('form_requests.status', 'users.name as requester', 'form_requests.created_at')
            ->orderBy('form_requests.created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentRequests as $request) {
            $activities[] = [
                'type' => 'form_request',
                'message' => "Form request {$request->status} by {$request->requester}",
                'timestamp' => $request->created_at,
            ];
        }

        // Sort by timestamp and return latest 10
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, 10);
    }
}

