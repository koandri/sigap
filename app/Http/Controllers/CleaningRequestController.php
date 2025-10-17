<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningRequest;
use App\Models\CleaningTask;
use App\Models\Location;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CleaningRequestController extends Controller
{
    /**
     * Show guest request form (public).
     */
    public function guestForm(): View
    {
        $locations = Location::active()->orderBy('name')->get();
        
        return view('facility.requests.guest-form', compact('locations'));
    }

    /**
     * Store guest request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_phone' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'request_type' => 'required|in:cleaning,repair',
            'description' => 'required|string|max:1000',
            'photo' => 'nullable|image|max:5120', // 5MB max
            'cf-turnstile-response' => ['required', Rule::turnstile()],
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('cleaning_requests', 'sigap');
        }

        $requestNumber = $this->generateRequestNumber();

        CleaningRequest::create([
            'request_number' => $requestNumber,
            'requester_name' => $validated['requester_name'],
            'requester_phone' => $validated['requester_phone'],
            'requester_user_id' => auth()->id(), // Will be null for guests
            'location_id' => $validated['location_id'],
            'request_type' => $validated['request_type'],
            'description' => $validated['description'],
            'photo' => $photoPath,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('facility.requests.guest-form')
            ->with('success', "Your request has been submitted successfully! Request number: {$requestNumber}");
    }

    /**
     * Display requests list (for GA staff).
     */
    public function index(Request $request): View
    {
        $this->authorize('facility.requests.view');

        $status = $request->input('status');
        $type = $request->input('type');

        $query = CleaningRequest::with(['location', 'handledByUser'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('request_type', $type);
        }

        $requests = $query->paginate(20);

        return view('facility.requests.index', compact('requests', 'status', 'type'));
    }

    /**
     * Show request handling form.
     */
    public function handleForm(CleaningRequest $cleaningRequest): View
    {
        $this->authorize('facility.requests.handle');

        $cleaningRequest->load('location');

        // For cleaning requests, get available cleaners
        $cleaners = null;
        if ($cleaningRequest->isCleaningRequest()) {
            $cleaners = \App\Models\User::role('Cleaner')->orderBy('name')->get();
        }

        return view('facility.requests.handle', compact('cleaningRequest', 'cleaners'));
    }

    /**
     * Handle a cleaning/repair request.
     */
    public function handle(Request $request, CleaningRequest $cleaningRequest): RedirectResponse
    {
        $this->authorize('facility.requests.handle');

        if ($cleaningRequest->isCleaningRequest()) {
            return $this->handleCleaningRequest($request, $cleaningRequest);
        } else {
            return $this->handleRepairRequest($request, $cleaningRequest);
        }
    }

    /**
     * Handle cleaning request by creating a cleaning task.
     */
    private function handleCleaningRequest(Request $request, CleaningRequest $cleaningRequest): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'assigned_to' => 'required|exists:users,id',
            'item_name' => 'required|string|max:255',
            'handling_notes' => 'nullable|string|max:1000',
        ]);

        $taskNumber = $this->generateTaskNumber();

        CleaningTask::create([
            'task_number' => $taskNumber,
            'cleaning_schedule_id' => 0, // Special ID for ad-hoc tasks
            'cleaning_schedule_item_id' => 0, // Special ID for ad-hoc tasks
            'location_id' => $cleaningRequest->location_id,
            'asset_id' => null,
            'item_name' => $validated['item_name'],
            'item_description' => $cleaningRequest->description,
            'scheduled_date' => $validated['scheduled_date'],
            'assigned_to' => $validated['assigned_to'],
            'status' => 'pending',
        ]);

        $cleaningRequest->update([
            'status' => 'completed',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
            'handling_notes' => $validated['handling_notes'] ?? "Created cleaning task: {$taskNumber}",
        ]);

        return redirect()
            ->route('facility.requests.index')
            ->with('success', 'Cleaning task created successfully.');
    }

    /**
     * Handle repair request by creating a work order.
     */
    private function handleRepairRequest(Request $request, CleaningRequest $cleaningRequest): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'nullable|string|max:1000',
            'handling_notes' => 'nullable|string|max:1000',
        ]);

        // Generate work order number
        $woNumber = $this->generateWorkOrderNumber();

        // Create work order in maintenance module
        WorkOrder::create([
            'wo_number' => $woNumber,
            'asset_id' => null, // Can be assigned later
            'maintenance_type_id' => null, // Can be assigned later
            'priority' => $validated['priority'],
            'status' => 'submitted',
            'scheduled_date' => now(),
            'requested_by' => auth()->id(),
            'description' => $validated['description'] ?? $cleaningRequest->description,
            'notes' => "Created from facility cleaning request: {$cleaningRequest->request_number}",
        ]);

        $cleaningRequest->update([
            'status' => 'completed',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
            'handling_notes' => $validated['handling_notes'] ?? "Created work order: {$woNumber}",
        ]);

        return redirect()
            ->route('facility.requests.index')
            ->with('success', 'Work order created successfully in Maintenance module.');
    }

    /**
     * Generate unique request number.
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'CR-' . date('ymd');
        $lastRequest = CleaningRequest::where('request_number', 'like', $prefix . '%')
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->request_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique task number for ad-hoc tasks.
     */
    private function generateTaskNumber(): string
    {
        $prefix = 'CT-' . date('ymd');
        $lastTask = CleaningTask::where('task_number', 'like', $prefix . '%')
            ->orderBy('task_number', 'desc')
            ->first();

        if ($lastTask) {
            $lastNumber = (int) substr($lastTask->task_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique work order number.
     */
    private function generateWorkOrderNumber(): string
    {
        $date = now()->format('ymd');
        $prefix = 'WO-' . $date;
        
        $lastWO = WorkOrder::where('wo_number', 'like', $prefix . '%')
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWO) {
            $lastNumber = (int) substr($lastWO->wo_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }
}
