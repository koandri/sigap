<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\FormPrefillHelper;
use App\Models\User;
use App\Models\Form;
use App\Services\HomeAssistantService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(
        private readonly HomeAssistantService $homeAssistantService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * Get temperature data from HomeAssistant for the dashboard widget
     */
    public function getTemperatureData(Request $request): JsonResponse
    {
        // Check if user has required role
        if (!auth()->user()->hasAnyRole(['QC', 'Production', 'IT Staff', 'Super Admin', 'Owner'])) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to view temperature data.',
            ], 403);
        }

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

    // In controller, redirect with prefill
    public function redirectToForm($userId) 
    {
        $form = Form::findOrFail(1);

        $user = User::find($userId);
        
        $prefillData = [
            'employee_name' => $user->name,
            'department' => $user->departments->first()?->code,
            'employee_id' => $user->id,
            'current_date' => now()->format('Y-m-d')
        ];
        
        $prefillUrl = FormPrefillHelper::generatePrefillUrl($form, $prefillData);
        
        return redirect($prefillUrl);
        /*
        // Basic prefill
/formsubmissions/form/1?employee_name=John Doe&department=HR&salary=5000000

// Date prefill  
/formsubmissions/form/1?start_date=2024-01-15&end_date=2024-01-20

// Multiple select prefill
/formsubmissions/form/1?skills=php,laravel,javascript&departments=HR,IT

// Boolean prefill
/formsubmissions/form/1?is_permanent=true&has_experience=1

// Mixed prefill
/formsubmissions/form/1?name=Ahmad&age=25&department=IT&salary=8000000&start_date=2024-02-01&is_active=true
*/
    }
}
