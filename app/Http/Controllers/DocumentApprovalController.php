<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocumentVersionApproval;
use App\Services\DocumentVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class DocumentApprovalController extends Controller
{
    public function __construct(
        private readonly DocumentVersionService $versionService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', DocumentVersionApproval::class);

        $user = Auth::user();

        // Get approvals assigned to the current user or all if admin
        $query = DocumentVersionApproval::with(['documentVersion.document.creator', 'documentVersion.creator', 'approver'])
            ->where('status', 'pending');

        // Filter by approver unless user is admin
        if (! $user->hasRole(['Super Admin', 'Owner'])) {
            $query->where('approver_id', $user->id);
        }

        $pendingApprovals = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('document-approvals.index', compact('pendingApprovals'));
    }

    public function approve(Request $request, DocumentVersionApproval $approval): RedirectResponse
    {
        $this->authorize('approve', $approval);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->versionService->approveVersion(
                $approval,
                Auth::user(),
                $request->notes
            );

            return redirect()->route('document-approvals.index')
                ->with('success', 'Document version approved successfully.');
        } catch (\Exception $e) {
            \Log::error('Document approval failed', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to approve document: '.$e->getMessage());
        }
    }

    public function reject(Request $request, DocumentVersionApproval $approval): RedirectResponse
    {
        $this->authorize('reject', $approval);

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        try {
            $this->versionService->rejectVersion(
                $approval,
                Auth::user(),
                $request->notes
            );

            return redirect()->route('document-approvals.index')
                ->with('success', 'Document version rejected.');
        } catch (\Exception $e) {
            \Log::error('Document rejection failed', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject document: '.$e->getMessage());
        }
    }
}
