<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningApproval;
use App\Models\CleaningSubmission;
use App\Services\CleaningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CleaningApprovalController extends Controller
{
    public function __construct(
        private readonly CleaningService $cleaningService
    ) {}

    /**
     * Display pending approvals.
     */
    public function index(Request $request): View
    {
        $this->authorize('facility.submissions.review');

        $date = $request->input('date', today()->subDay()->toDateString());
        $slaFilter = $request->input('sla_filter');
        $flaggedOnly = $request->has('flagged_only');

        $query = CleaningApproval::pending()
            ->with([
                'cleaningSubmission.cleaningTask.location',
                'cleaningSubmission.cleaningTask.cleaningSchedule',
                'cleaningSubmission.submittedByUser'
            ])
            ->whereHas('cleaningSubmission', function($q) use ($date) {
                $q->whereDate('submitted_at', $date);
            });

        if ($flaggedOnly) {
            $query->where('is_flagged_for_review', true);
        }

        $approvals = $query->get()
            ->sortByDesc(function ($approval) {
                return $approval->hours_overdue;
            });

        // Filter by SLA status if requested
        if ($slaFilter) {
            $approvals = $approvals->filter(function ($approval) use ($slaFilter) {
                return $approval->sla_status === $slaFilter;
            });
        }

        // Check if batch can be approved
        $batchCheck = $this->cleaningService->canApproveBatch(\Carbon\Carbon::parse($date));

        // Statistics
        $totalPending = $approvals->count();
        $flaggedCount = $approvals->where('is_flagged_for_review', true)->count();
        $reviewedFlagged = $approvals->where('is_flagged_for_review', true)->whereNotNull('reviewed_at')->count();

        return view('facility.approvals.index', compact(
            'approvals',
            'date',
            'slaFilter',
            'flaggedOnly',
            'batchCheck',
            'totalPending',
            'flaggedCount',
            'reviewedFlagged'
        ));
    }

    /**
     * Show submission for review.
     */
    public function review(CleaningApproval $approval): View
    {
        $this->authorize('facility.submissions.review');

        $approval->load([
            'cleaningSubmission.cleaningTask.location',
            'cleaningSubmission.cleaningTask.asset',
            'cleaningSubmission.cleaningTask.cleaningSchedule',
            'cleaningSubmission.submittedByUser'
        ]);

        // Mark as reviewed if flagged
        if ($approval->is_flagged_for_review && !$approval->reviewed_at) {
            $approval->update(['reviewed_at' => now()]);
        }

        return view('facility.approvals.review', compact('approval'));
    }

    /**
     * Approve a submission.
     */
    public function approve(Request $request, CleaningApproval $approval): RedirectResponse
    {
        $this->authorize('facility.submissions.approve');

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'reviewed_at' => $approval->reviewed_at ?? now(),
        ]);

        // Update task status
        $approval->cleaningSubmission->cleaningTask->update([
            'status' => 'approved',
        ]);

        return redirect()
            ->route('facility.approvals.index')
            ->with('success', 'Submission approved successfully.');
    }

    /**
     * Reject a submission.
     */
    public function reject(Request $request, CleaningApproval $approval): RedirectResponse
    {
        $this->authorize('facility.submissions.approve');

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'notes' => $validated['notes'],
            'reviewed_at' => $approval->reviewed_at ?? now(),
        ]);

        // Update task status
        $approval->cleaningSubmission->cleaningTask->update([
            'status' => 'rejected',
        ]);

        return redirect()
            ->route('facility.approvals.index')
            ->with('success', 'Submission rejected.');
    }

    /**
     * Mass approve all pending submissions for a date.
     */
    public function massApprove(Request $request): RedirectResponse
    {
        $this->authorize('facility.submissions.approve');

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $date = \Carbon\Carbon::parse($validated['date']);

        // Check if batch can be approved
        $batchCheck = $this->cleaningService->canApproveBatch($date);

        if (!$batchCheck['can_approve']) {
            return back()->with('error', $batchCheck['message']);
        }

        // Get all pending approvals for the date
        $approvals = CleaningApproval::pending()
            ->whereHas('cleaningSubmission', function($q) use ($date) {
                $q->whereDate('submitted_at', $date);
            })
            ->get();

        $count = 0;
        foreach ($approvals as $approval) {
            $approval->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'reviewed_at' => $approval->reviewed_at ?? now(),
            ]);

            $approval->cleaningSubmission->cleaningTask->update([
                'status' => 'approved',
            ]);

            $count++;
        }

        return redirect()
            ->route('facility.approvals.index')
            ->with('success', "Successfully approved {$count} submission(s).");
    }
}
