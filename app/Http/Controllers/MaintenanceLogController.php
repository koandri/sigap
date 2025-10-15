<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MaintenanceLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:maintenance.reports.view');
    }

    /**
     * Display a listing of maintenance logs.
     */
    public function index(Request $request): View
    {
        $query = MaintenanceLog::with(['asset', 'performedBy', 'workOrder']);

        // Filter by asset
        if ($request->filled('asset')) {
            $query->where('asset_id', $request->asset);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // Filter by performed by
        if ($request->filled('performed_by')) {
            $query->where('performed_by', $request->performed_by);
        }

        $logs = $query->latest('performed_at')->paginate(20);
        $assets = Asset::active()->orderBy('name')->get();

        return view('maintenance.logs.index', compact('logs', 'assets'));
    }

    /**
     * Display maintenance history for a specific asset.
     */
    public function assetHistory(Asset $asset): View
    {
        $logs = $asset->maintenanceLogs()
            ->with(['performedBy', 'workOrder'])
            ->latest('performed_at')
            ->paginate(20);

        return view('maintenance.logs.asset', compact('asset', 'logs'));
    }
}
