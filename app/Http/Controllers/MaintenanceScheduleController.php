<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\MaintenanceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class MaintenanceScheduleController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {
        $this->middleware('can:maintenance.schedules.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = MaintenanceSchedule::with(['asset', 'maintenanceType', 'assignedUser']);

        // Filter by asset
        if ($request->filled('asset')) {
            $query->where('asset_id', $request->asset);
        }

        // Filter by maintenance type
        if ($request->filled('type')) {
            $query->where('maintenance_type_id', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } elseif ($request->status === 'upcoming') {
                $query->upcoming(7);
            } elseif ($request->status === 'active') {
                $query->active();
            }
        }

        // Search by asset name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($assetQuery) use ($search) {
                      $assetQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $schedules = $query->orderBy('next_due_date')->paginate(20);
        $assets = Asset::active()->orderBy('name')->get();
        $maintenanceTypes = MaintenanceType::active()->orderBy('name')->get();

        return view('maintenance.schedules.index', compact('schedules', 'assets', 'maintenanceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $assets = Asset::active()->with('assetCategory')->orderBy('name')->get();
        $maintenanceTypes = MaintenanceType::active()->orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.schedules.create', compact('assets', 'maintenanceTypes', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type_id' => 'required|exists:maintenance_types,id',
            'frequency_type' => 'required|in:hourly,daily,weekly,monthly,yearly',
            'frequency_config' => 'required|array',
            'frequency_days' => 'nullable|integer|min:1|max:365',
            'description' => 'required|string|max:1000',
            'checklist' => 'required|array|min:1',
            'checklist.*' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ], [
            'checklist.required' => 'At least one checklist item is required.',
            'checklist.min' => 'At least one checklist item is required.',
            'checklist.*.required' => 'Checklist item cannot be empty.',
        ]);

        // Create a temporary schedule instance to calculate next_due_date
        // We need to set created_at for the calculation, and ensure enum is cast properly
        $tempSchedule = new MaintenanceSchedule();
        $tempSchedule->fill($validated);
        $tempSchedule->created_at = now();
        
        // Calculate next due date before creating the schedule
        $validated['next_due_date'] = $this->maintenanceService->calculateNextDueDate($tempSchedule);

        // Create the schedule with next_due_date included
        $schedule = MaintenanceSchedule::create($validated);

        return redirect()
            ->route('maintenance.schedules.index')
            ->with('success', 'Maintenance schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceSchedule $schedule): View
    {
        $schedule->load([
            'asset.assetCategory',
            'maintenanceType',
            'assignedUser'
        ]);

        // Get related work orders for this schedule (same asset and maintenance type)
        $workOrders = WorkOrder::where('asset_id', $schedule->asset_id)
            ->where('maintenance_type_id', $schedule->maintenance_type_id)
            ->with(['assignedUser', 'maintenanceType'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('maintenance.schedules.show', compact('schedule', 'workOrders'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaintenanceSchedule $schedule): View
    {
        $assets = Asset::active()->with('assetCategory')->orderBy('name')->get();
        $maintenanceTypes = MaintenanceType::active()->orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.schedules.edit', compact('schedule', 'assets', 'maintenanceTypes', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type_id' => 'required|exists:maintenance_types,id',
            'frequency_type' => 'required|in:hourly,daily,weekly,monthly,yearly',
            'frequency_config' => 'required|array',
            'frequency_days' => 'nullable|integer|min:1|max:365',
            'description' => 'required|string|max:1000',
            'checklist' => 'required|array|min:1',
            'checklist.*' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ], [
            'checklist.required' => 'At least one checklist item is required.',
            'checklist.min' => 'At least one checklist item is required.',
            'checklist.*.required' => 'Checklist item cannot be empty.',
        ]);

        // Check if frequency configuration changed
        $frequencyChanged = $schedule->frequency_type->value != $validated['frequency_type'] 
            || $schedule->frequency_config != $validated['frequency_config'];

        $schedule->update($validated);

        // Recalculate next due date if frequency changed
        if ($frequencyChanged) {
            $schedule->update([
                'next_due_date' => $this->maintenanceService->calculateNextDueDate($schedule)
            ]);
        }

        return redirect()
            ->route('maintenance.schedules.show', $schedule)
            ->with('success', 'Maintenance schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()
            ->route('maintenance.schedules.index')
            ->with('success', 'Maintenance schedule deleted successfully.');
    }

    /**
     * Trigger manual work order generation for a schedule.
     */
    public function trigger(MaintenanceSchedule $schedule): RedirectResponse
    {
        if (!$schedule->is_active) {
            return redirect()
                ->route('maintenance.schedules.show', $schedule)
                ->with('error', 'Cannot trigger inactive schedule.');
        }

        // Generate work order with auto-assignment for scheduled maintenance
        $workOrder = $schedule->asset->workOrders()->create([
            'wo_number' => $this->generateWONumber(),
            'maintenance_type_id' => $schedule->maintenance_type_id,
            'priority' => 'medium',
            'status' => 'assigned', // Auto-assign scheduled maintenance
            'scheduled_date' => $schedule->next_due_date,
            'assigned_to' => $schedule->assigned_to,
            'assigned_by' => $schedule->assigned_to, // Self-assigned from schedule
            'assigned_at' => now(),
            'requested_by' => auth()->id(),
            'description' => $schedule->description,
        ]);

        // Update schedule
        $this->maintenanceService->updateScheduleAfterCompletion($schedule);

        return redirect()
            ->route('maintenance.work-orders.show', $workOrder)
            ->with('success', 'Work order generated successfully.');
    }

    /**
     * Generate work order number.
     */
    private function generateWONumber(): string
    {
        $date = now()->format('ymd');
        $lastWO = \App\Models\WorkOrder::where('wo_number', 'like', "WO-{$date}-%")
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
