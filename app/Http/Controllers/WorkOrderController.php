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
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_captions.*' => 'nullable|string|max:255',
        ]);

        $validated['wo_number'] = $this->generateWONumber();
        $validated['requested_by'] = auth()->id();

        $workOrder = WorkOrder::create($validated);

        // Handle photo uploads if provided
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                if ($photo) {
                    $photoPath = $photo->store('work-order-photos', 'public');
                    $workOrder->photos()->create([
                        'uploaded_by' => auth()->id(),
                        'photo_path' => $photoPath,
                        'photo_type' => 'initial',
                        'caption' => $validated['photo_captions'][$index] ?? null,
                    ]);
                }
            }
        }

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
            'assignedBy',
            'verifiedBy',
            'parts.item',
            'parts.warehouse',
            'parts.positionItem',
            'maintenanceLogs.performedBy',
            'progressLogs.loggedBy',
            'actions.performedBy',
            'photos.uploadedBy'
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

    /**
     * Assign work order to operator (Engineering Staff).
     */
    public function assign(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $workOrder->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'scheduled_date' => $validated['scheduled_date'],
            'estimated_hours' => $validated['estimated_hours'],
            'notes' => $validated['notes'],
            'status' => 'assigned',
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order assigned successfully.');
    }

    /**
     * Start work on work order (Operator).
     */
    public function startWork(WorkOrder $workOrder): RedirectResponse
    {
        $workOrder->update([
            'work_started_at' => now(),
            'status' => 'in-progress',
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work started successfully.');
    }

    /**
     * Log progress on work order (Operator).
     */
    public function logProgress(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'hours_worked' => 'required|numeric|min:0.1',
            'progress_notes' => 'required|string|max:1000',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'logged_at' => 'nullable|date',
        ]);

        $workOrder->progressLogs()->create([
            'logged_by' => auth()->id(),
            'logged_at' => $validated['logged_at'] ?? now(),
            'hours_worked' => $validated['hours_worked'],
            'progress_notes' => $validated['progress_notes'],
            'completion_percentage' => $validated['completion_percentage'],
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Progress logged successfully.');
    }

    /**
     * Add action to work order (Operator).
     */
    public function addAction(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'action_type' => 'required|in:spare-part-replacement,send-for-repair,retire-equipment,cleaning,adjustment,calibration,enhancement,other',
            'action_description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'performed_at' => 'nullable|date',
        ]);

        $workOrder->actions()->create([
            'performed_by' => auth()->id(),
            'action_type' => $validated['action_type'],
            'action_description' => $validated['action_description'],
            'notes' => $validated['notes'],
            'performed_at' => $validated['performed_at'] ?? now(),
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Action added successfully.');
    }

    /**
     * Upload photo to work order (Operator).
     */
    public function uploadPhoto(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_type' => 'required|in:progress,before,after,issue',
            'caption' => 'nullable|string|max:255',
        ]);

        $photoPath = $request->file('photo')->store('work-order-photos', 'public');

        $workOrder->photos()->create([
            'uploaded_by' => auth()->id(),
            'photo_path' => $photoPath,
            'photo_type' => $validated['photo_type'],
            'caption' => $validated['caption'],
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Photo uploaded successfully.');
    }

    /**
     * Submit work order for verification (Operator).
     */
    public function submitForVerification(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        // Calculate total hours from progress logs
        $totalHours = $workOrder->progressLogs()->sum('hours_worked');

        $workOrder->update([
            'work_finished_at' => now(),
            'actual_hours' => $totalHours,
            'notes' => $validated['completion_notes'],
            'status' => 'pending-verification',
        ]);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order submitted for verification.');
    }

    /**
     * Verify work order (Engineering Staff).
     */
    public function verify(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,rework',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'approve') {
            $workOrder->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'verification_notes' => $validated['verification_notes'],
            ]);

            $message = 'Work order approved successfully.';
        } else {
            $workOrder->update([
                'status' => 'rework',
                'verification_notes' => $validated['verification_notes'],
            ]);

            $message = 'Work order sent back for rework.';
        }

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', $message);
    }

    /**
     * Close work order (Requester).
     */
    public function close(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:close,rework',
            'closing_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'close') {
            $workOrder->update([
                'status' => 'completed',
                'completed_date' => now(),
                'notes' => $validated['closing_notes'],
            ]);

            $message = 'Work order closed successfully.';
        } else {
            $workOrder->update([
                'status' => 'rework',
                'notes' => $validated['closing_notes'],
            ]);

            $message = 'Work order sent back for rework.';
        }

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', $message);
    }
}