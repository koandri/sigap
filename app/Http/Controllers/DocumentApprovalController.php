<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocumentVersionApproval;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class DocumentApprovalController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DocumentVersionApproval::class);
        
        $pendingApprovals = DocumentVersionApproval::with(['version.document', 'approver'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('document-approvals.index', compact('pendingApprovals'));
    }

    public function approve(Request $request, DocumentVersionApproval $approval): RedirectResponse
    {
        $this->authorize('approve', $approval);
        
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);
        
        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'comments' => $request->comments,
        ]);
        
        // Check if all approvals are complete
        $version = $approval->version;
        if ($version->approvals()->where('status', 'pending')->count() === 0) {
            $version->update(['status' => 'approved']);
        }
        
        return redirect()->route('document-approvals.index')
            ->with('success', 'Document version approved successfully.');
    }

    public function reject(Request $request, DocumentVersionApproval $approval): RedirectResponse
    {
        $this->authorize('reject', $approval);
        
        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);
        
        $approval->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'comments' => $request->comments,
        ]);
        
        // Reject the version
        $approval->version->update(['status' => 'rejected']);
        
        return redirect()->route('document-approvals.index')
            ->with('success', 'Document version rejected.');
    }
}
