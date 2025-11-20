<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\PositionItem;
use App\Models\ShelfPosition;
use App\Services\HomeAssistantService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ManufacturingController extends Controller
{
    public function __construct(
        private readonly HomeAssistantService $homeAssistantService
    ) {
        $this->middleware('can:manufacturing.dashboard.view')->only(['index']);
    }

    /**
     * Display the manufacturing dashboard.
     */
    public function index(): View
    {
        // Get key statistics for the dashboard
        $stats = [
            'total_items' => Item::active()->count(),
            'total_categories' => ItemCategory::count(),
            'total_warehouses' => Warehouse::active()->count(),
            'total_positions' => PositionItem::where('quantity', '>', 0)->count(),
            'total_locations' => ShelfPosition::active()->count(),
        ];

        // Get items by category for quick overview
        $itemsByCategory = ItemCategory::withCount('items')->get();

        // Get items expiring soon (next 30 days)
        $expiringItems = PositionItem::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->with(['item', 'shelfPosition.warehouseShelf.warehouse'])
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Get warehouses with stock counts
        $warehousesWithStock = Warehouse::active()
            ->withCount(['shelves as stocked_shelves_count' => function ($query) {
                $query->whereHas('shelfPositions.positionItems', function($q) {
                    $q->where('quantity', '>', 0);
                });
            }])
            ->get();

        return view('manufacturing.dashboard', compact(
            'stats',
            'itemsByCategory',
            'expiringItems',
            'warehousesWithStock'
        ));
    }

    /**
     * Get temperature data from HomeAssistant for the dashboard widget
     */
    public function getTemperatureData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
                'interval' => 'nullable|integer|in:5,15,30,60',
            ]);

            $entityId = 'sensor.tes_temperature';
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $intervalMinutes = (int) ($request->input('interval') ?? 30);

            // Default to last 8 hours if not provided
            if ($startTime === null) {
                $startTime = Carbon::now()->subHours(8)->toIso8601String();
            } else {
                $startTime = Carbon::parse($startTime)->toIso8601String();
            }

            if ($endTime === null) {
                $endTime = Carbon::now()->toIso8601String();
            } else {
                $endTime = Carbon::parse($endTime)->toIso8601String();
            }

            // Fetch data from HomeAssistant
            $rawData = $this->homeAssistantService->getTemperatureHistory(
                $entityId,
                $startTime,
                $endTime
            );

            // Transform data for Chart.js
            $labels = [];
            $temperatures = [];
            $unit = '°C'; // Default unit

            // The API returns an array containing an array of state objects
            if (is_array($rawData) && isset($rawData[0]) && is_array($rawData[0])) {
                $stateChanges = $rawData[0];

                // Extract unit from first entry if available
                if (!empty($stateChanges[0]['attributes']['unit_of_measurement'])) {
                    $unit = $stateChanges[0]['attributes']['unit_of_measurement'];
                }

                // Sample data at specified interval to reduce data points
                $sampledData = [];

                // Find the time range
                $firstTimestamp = null;
                $lastTimestamp = null;

                foreach ($stateChanges as $state) {
                    if (isset($state['last_changed'])) {
                        try {
                            $ts = Carbon::parse($state['last_changed']);
                            if ($firstTimestamp === null || $ts->lt($firstTimestamp)) {
                                $firstTimestamp = $ts->copy();
                            }
                            if ($lastTimestamp === null || $ts->gt($lastTimestamp)) {
                                $lastTimestamp = $ts->copy();
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }

                // If we have a valid time range, sample the data
                if ($firstTimestamp && $lastTimestamp) {
                    // Round down to the nearest interval
                    $currentInterval = $firstTimestamp->copy();
                    $currentInterval->minute = (int)($currentInterval->minute / $intervalMinutes) * $intervalMinutes;
                    $currentInterval->second = 0;
                    $currentInterval->microsecond = 0;

                    $lastInterval = $lastTimestamp->copy();
                    $lastInterval->minute = (int)($lastInterval->minute / $intervalMinutes) * $intervalMinutes;
                    $lastInterval->second = 0;
                    $lastInterval->microsecond = 0;

                    // Create intervals and find closest data point for each
                    while ($currentInterval->lte($lastInterval)) {
                        $intervalEnd = $currentInterval->copy()->addMinutes($intervalMinutes);

                        // Find the closest data point to this interval
                        $closestState = null;
                        $closestDiff = null;
                        $intervalStart = $currentInterval->copy();

                        foreach ($stateChanges as $state) {
                            if (!isset($state['last_changed'])) {
                                continue;
                            }

                            try {
                                $stateTime = Carbon::parse($state['last_changed']);
                                $diff = abs($stateTime->diffInSeconds($intervalStart));

                                // Prefer states within this interval
                                if ($stateTime->gte($intervalStart) && $stateTime->lt($intervalEnd)) {
                                    if ($closestDiff === null || $diff < $closestDiff) {
                                        $closestDiff = $diff;
                                        $closestState = $state;
                                    }
                                }
                            } catch (\Exception $e) {
                                continue;
                            }
                        }

                        // If no data found in the interval, find the closest one overall
                        if ($closestState === null) {
                            foreach ($stateChanges as $state) {
                                if (!isset($state['last_changed'])) {
                                    continue;
                                }

                                try {
                                    $stateTime = Carbon::parse($state['last_changed']);
                                    $diff = abs($stateTime->diffInSeconds($intervalStart));

                                    if ($closestDiff === null || $diff < $closestDiff) {
                                        $closestDiff = $diff;
                                        $closestState = $state;
                                    }
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }
                        }

                        if ($closestState) {
                            $sampledData[] = $closestState;
                        }

                        $currentInterval = $intervalEnd;
                    }

                    // Use sampled data instead of all data
                    $stateChanges = $sampledData;
                }

                foreach ($stateChanges as $state) {
                    // Extract timestamp from last_changed
                    $timestamp = null;
                    if (isset($state['last_changed'])) {
                        try {
                            $timestamp = Carbon::parse($state['last_changed']);
                        } catch (\Exception $e) {
                            // Skip entries with invalid timestamps
                            continue;
                        }
                    } else {
                        // Skip entries without timestamps
                        continue;
                    }

                    // Extract temperature value (convert string to float)
                    $temperature = null;
                    if (isset($state['state'])) {
                        $stateValue = trim($state['state']);
                        // Skip common non-numeric states
                        if (!in_array(strtolower($stateValue), ['unavailable', 'unknown', 'none', ''])) {
                            $temperature = filter_var($stateValue, FILTER_VALIDATE_FLOAT);
                            if ($temperature === false) {
                                $temperature = null;
                            }
                        }
                    }

                    // Add both label and temperature (keeping arrays in sync)
                    $labels[] = $timestamp->format('Y-m-d H:i:s');
                    $temperatures[] = $temperature;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'temperatures' => $temperatures,
                    'unit' => $unit,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch temperature data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
