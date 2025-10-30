<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\FormRequest;
use App\Models\PrintedForm;
use App\Services\FormRequestService;
use App\Enums\FormRequestStatus;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly FormRequestService $formRequestService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        
        $stats = [
            'total_documents' => Document::count(),
            'pending_approvals' => $this->getPendingApprovalsCount(),
            'pending_form_requests' => FormRequest::where('status', 'requested')->count(),
            'circulating_forms' => PrintedForm::whereIn('status', ['issued', 'circulating'])->count(),
        ];

        $recentActivities = collect($this->getRecentActivities());
        $overdueRequests = $this->formRequestService->getOverdueRequests();
        
        return view('dashboards.dms', compact('stats', 'recentActivities', 'overdueRequests'));
    }

    public function sla(): View
    {
        $slaMetrics = $this->calculateSLAMetrics();
        $circulatingForms = $this->formRequestService->getCirculatingForms();
        $overdueRequests = $this->formRequestService->getOverdueRequests();
        
        return view('dashboards.sla', compact('slaMetrics', 'circulatingForms', 'overdueRequests'));
    }

    private function getPendingApprovalsCount(): int
    {
        return DB::table('document_version_approvals')
            ->where('status', 'pending')
            ->count();
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

    private function calculateSLAMetrics(): array
    {
        $metrics = [
            'request_to_acknowledgment' => $this->calculateAverageTime('request_date', 'acknowledged_at'),
            'acknowledgment_to_ready' => $this->calculateAverageTime('acknowledged_at', 'ready_at'),
            'ready_to_collected' => $this->calculateAverageTime('ready_at', 'collected_at'),
            'total_processing_time' => $this->calculateAverageTime('request_date', 'collected_at'),
        ];

        return $metrics;
    }

    private function calculateAverageTime(string $startColumn, string $endColumn): ?float
    {
        $result = DB::table('form_requests')
            ->whereNotNull($startColumn)
            ->whereNotNull($endColumn)
            ->selectRaw("AVG(TIMESTAMPDIFF(HOUR, {$startColumn}, {$endColumn})) as avg_hours")
            ->first();

        return $result && $result->avg_hours !== null ? round((float)$result->avg_hours, 2) : null;
    }
}
