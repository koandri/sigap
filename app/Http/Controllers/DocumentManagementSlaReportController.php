<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FormRequestService;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

final class DocumentManagementSlaReportController extends Controller
{
    public function __construct(
        private readonly FormRequestService $formRequestService
    ) {}

    public function index(): View
    {
        $this->authorize('dms.sla.report.view');

        $slaMetrics = $this->calculateSLAMetrics();
        $circulatingForms = $this->formRequestService->getCirculatingForms();
        $overdueRequests = $this->formRequestService->getOverdueRequests();
        
        return view('dashboards.sla', compact('slaMetrics', 'circulatingForms', 'overdueRequests'));
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

