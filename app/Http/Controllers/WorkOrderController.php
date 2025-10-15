<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\MaintenanceType;
use App\Models\User;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\PositionItem;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class WorkOrderController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {
        $this->middleware('can:maintenance.work-orders.create')->only(['create', 'store']);
        $this->middleware('can:maintenance.work-orders.complete')->only(['updateStatus', 'complete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = WorkOrder::with(['asset', 'maintenanceType', 'assignedUser', 'requestedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by WO number or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $workOrders = $query->latest()->paginate(20);
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.work-orders.index', compact('workOrders', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $assets = Asset::active()->with('assetCategory')->orderBy('name')->get();
        $maintenanceTypes = MaintenanceType::active()->orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.work-orders.create', compact('assets', 'maintenanceTypes', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type_id' => 'required|exists:maintenance_types,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'description' => 'required|string|max:1000',
        ]);

        $validated['wo_number'] = $this->generateWONumber();
        $validated['requested_by'] = auth()->user()?->id;

        $workOrder = WorkOrder::create($validated);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder): View
    {
        $workOrder->load([
            'asset.assetCategory',
            'maintenanceType',
            'assignedUser',
            'requestedBy',
            'parts.item',
            'parts.warehouse',
            'parts.positionItem',
            'maintenanceLogs.performedBy'
        ]);

        // Get available parts for this work order
        $availableParts = PositionItem::with(['item', 'shelfPosition.warehouseShelf.warehouse'])
            ->whereHas('shelfPosition.warehouseShelf.warehouse', function ($query) {
                $query->where('is_active', true);
            })
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('item.name');

        return view('maintenance.work-orders.show', compact('workOrder', 'availableParts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkOrder $workOrder): View
    {
        $assets = Asset::active()->with('assetCategory')->orderBy('name')->get();
        $maintenanceTypes = MaintenanceType::active()->orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.work-orders.edit', compact('workOrder', 'assets', 'maintenanceTypes', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type_id' => 'required|exists:maintenance_types,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $workOrder->update($validated);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order updated successfully.');
    }

    /**
     * Update work order status.
     */
    public function updateStatus(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in-progress,completed,cancelled',
            'actual_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_date'] = now();
        }

        $workOrder->update($validated);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order status updated successfully.');
    }

    /**
     * Complete work order with parts consumption.
     */
    public function complete(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'actual_hours' => 'required|numeric|min:0',
            'action_taken' => 'required|string|max:1000',
            'findings' => 'nullable|string|max:1000',
            'recommendations' => 'nullable|string|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'parts' => 'nullable|array',
            'parts.*.position_item_id' => 'required_with:parts|exists:position_items,id',
            'parts.*.quantity_used' => 'required_with:parts|numeric|min:0.001',
        ]);

        // Update work order
        $workOrder->update([
            'status' => 'completed',
            'completed_date' => now(),
            'actual_hours' => $validated['actual_hours'],
            'notes' => $validated['action_taken'],
        ]);

        // Consume inventory if parts are specified
        if (!empty($validated['parts'])) {
            $this->maintenanceService->consumeInventoryForWorkOrder($workOrder, $validated['parts']);
        }

        // Create maintenance log
        $workOrder->maintenanceLogs()->create([
            'asset_id' => $workOrder->asset_id,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'action_taken' => $validated['action_taken'],
            'findings' => $validated['findings'],
            'recommendations' => $validated['recommendations'],
            'cost' => $validated['cost'] ?? 0,
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order completed successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        if ($workOrder->status === 'completed') {
            return redirect()
                ->route('maintenance.work-orders.index')
                ->with('error', 'Cannot delete completed work orders.');
        }

        $workOrder->delete();

        return redirect()
            ->route('maintenance.work-orders.index')
            ->with('success', 'Work order deleted successfully.');
    }

    /**
     * Generate work order number.
     */
    private function generateWONumber(): string
    {
        $date = now()->format('ymd');
        $lastWO = WorkOrder::where('wo_number', 'like', "WO-{$date}-%")
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWO) {
            $lastNumber = (int) substr($lastWO->wo_number, -3);
            $newNumber = str_pad((string)($lastNumber + 1), 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "WO-{$date}-{$newNumber}";
    }
}