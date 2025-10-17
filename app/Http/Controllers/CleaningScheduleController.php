<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningSchedule;
use App\Models\CleaningScheduleItem;
use App\Models\Location;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CleaningScheduleController extends Controller
{
    /**
     * Display a listing of cleaning schedules.
     */
    public function index(): View
    {
        $this->authorize('facility.schedules.view');

        $schedules = CleaningSchedule::with(['location', 'items'])
            ->latest()
            ->paginate(15);

        return view('facility.schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new cleaning schedule.
     */
    public function create(): View
    {
        $this->authorize('facility.schedules.create');

        $locations = Location::active()->orderBy('name')->get();
        $assets = Asset::where('is_active', true)->orderBy('asset_code')->get();

        return view('facility.schedules.create', compact('locations', 'assets'));
    }

    /**
     * Store a newly created cleaning schedule.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('facility.schedules.create');

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_config' => 'nullable|array',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_description' => 'nullable|string',
        ]);

        $schedule = CleaningSchedule::create([
            'location_id' => $validated['location_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'frequency_type' => $validated['frequency_type'],
            'frequency_config' => $validated['frequency_config'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create schedule items
        foreach ($validated['items'] as $index => $itemData) {
            $schedule->items()->create([
                'asset_id' => $itemData['asset_id'] ?? null,
                'item_name' => $itemData['item_name'],
                'item_description' => $itemData['item_description'] ?? null,
                'order' => $index,
            ]);
        }

        return redirect()
            ->route('facility.schedules.show', $schedule)
            ->with('success', 'Cleaning schedule created successfully.');
    }

    /**
     * Display the specified cleaning schedule.
     */
    public function show(CleaningSchedule $schedule): View
    {
        $this->authorize('facility.schedules.view');

        $schedule->load(['location', 'items.asset', 'alerts' => function($q) {
            $q->unresolved()->with('asset');
        }]);

        // Get recent tasks from this schedule
        $recentTasks = $schedule->tasks()
            ->with(['assignedUser', 'location'])
            ->latest('scheduled_date')
            ->limit(10)
            ->get();

        return view('facility.schedules.show', compact('schedule', 'recentTasks'));
    }

    /**
     * Show the form for editing the specified cleaning schedule.
     */
    public function edit(CleaningSchedule $schedule): View
    {
        $this->authorize('facility.schedules.edit');

        $schedule->load('items.asset');
        $locations = Location::active()->orderBy('name')->get();
        $assets = Asset::where('is_active', true)->orderBy('asset_code')->get();

        return view('facility.schedules.edit', compact('schedule', 'locations', 'assets'));
    }

    /**
     * Update the specified cleaning schedule.
     */
    public function update(Request $request, CleaningSchedule $schedule): RedirectResponse
    {
        $this->authorize('facility.schedules.edit');

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_config' => 'nullable|array',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:cleaning_schedule_items,id',
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_description' => 'nullable|string',
        ]);

        $schedule->update([
            'location_id' => $validated['location_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'frequency_type' => $validated['frequency_type'],
            'frequency_config' => $validated['frequency_config'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update items - delete old ones not in the list, update existing, create new
        $existingIds = collect($validated['items'])
            ->pluck('id')
            ->filter()
            ->toArray();

        // Delete items not in the update
        $schedule->items()->whereNotIn('id', $existingIds)->delete();

        // Update or create items
        foreach ($validated['items'] as $index => $itemData) {
            if (isset($itemData['id'])) {
                // Update existing
                CleaningScheduleItem::where('id', $itemData['id'])->update([
                    'asset_id' => $itemData['asset_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['item_description'] ?? null,
                    'order' => $index,
                ]);
            } else {
                // Create new
                $schedule->items()->create([
                    'asset_id' => $itemData['asset_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['item_description'] ?? null,
                    'order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('facility.schedules.show', $schedule)
            ->with('success', 'Cleaning schedule updated successfully. Note: Changes only affect new tasks generated after midnight.');
    }

    /**
     * Remove the specified cleaning schedule.
     */
    public function destroy(CleaningSchedule $schedule): RedirectResponse
    {
        $this->authorize('facility.schedules.delete');

        $schedule->delete();

        return redirect()
            ->route('facility.schedules.index')
            ->with('success', 'Cleaning schedule deleted successfully.');
    }
}
