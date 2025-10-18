<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningTask;
use App\Models\CleaningSubmission;
use App\Models\CleaningApproval;
use App\Models\User;
use App\Services\CleaningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

final class CleaningTaskController extends Controller
{
    public function __construct(
        private readonly CleaningService $cleaningService
    ) {}

    /**
     * Display tasks list (for GA staff).
     */
    public function index(Request $request): View
    {
        $this->authorize('facility.tasks.view');

        $date = $request->input('date', today()->toDateString());
        $locationIds = $request->input('location_ids', []);
        $status = $request->input('status');

        $query = CleaningTask::with(['location', 'assignedUser', 'cleaningSchedule'])
            ->whereDate('scheduled_date', $date);

        if (!empty($locationIds)) {
            $query->whereIn('location_id', $locationIds);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $tasks = $query->orderBy('location_id')
            ->orderBy('item_name')
            ->paginate(50);

        $locations = \App\Models\Location::active()->orderBy('name')->get();

        return view('facility.tasks.index', compact('tasks', 'locations', 'date', 'locationIds', 'status'));
    }

    /**
     * Display today's tasks for current cleaner.
     */
    public function myTasks(): View
    {
        $this->authorize('facility.tasks.view');

        $user = auth()->user();

        // Tasks assigned to current user (priority)
        $myTasks = CleaningTask::today()
            ->where('assigned_to', $user->id)
            ->with(['location', 'cleaningSchedule', 'submission'])
            ->orderBy('status')
            ->orderBy('item_name')
            ->get();

        // Unassigned or other tasks, grouped by location
        $otherTasks = CleaningTask::today()
            ->where('assigned_to', '!=', $user->id)
            ->where('status', 'pending')
            ->with(['location', 'cleaningSchedule', 'assignedUser'])
            ->orderBy('location_id')
            ->orderBy('item_name')
            ->get()
            ->groupBy('location.name');

        return view('facility.tasks.my-tasks', compact('myTasks', 'otherTasks'));
    }

    /**
     * Show task details.
     */
    public function show(CleaningTask $task): View
    {
        $this->authorize('facility.tasks.view');

        $task->load(['location', 'asset', 'cleaningSchedule', 'assignedUser', 'startedByUser', 'completedByUser', 'submission.approval']);

        return view('facility.tasks.show', compact('task'));
    }

    /**
     * Start a task (lock it to current user).
     */
    public function startTask(CleaningTask $task): RedirectResponse
    {
        $this->authorize('facility.tasks.complete');

        $userId = auth()->id();

        if (!$task->canBeStartedBy($userId)) {
            return back()->with('error', 'This task cannot be started at this time.');
        }

        $task->update([
            'status' => 'in-progress',
            'started_by' => $userId,
            'started_at' => now(),
        ]);

        return redirect()
            ->route('facility.tasks.submit', $task)
            ->with('success', 'Task started. Please take a before photo.');
    }

    /**
     * Show task submission form (with photo capture).
     */
    public function submitForm(CleaningTask $task): View
    {
        $this->authorize('facility.tasks.complete');

        $userId = auth()->id();

        if ($task->status !== 'in-progress' || $task->started_by !== $userId) {
            abort(403, 'You cannot submit this task.');
        }

        $task->load(['location', 'cleaningSchedule']);

        return view('facility.tasks.submit', compact('task'));
    }

    /**
     * Submit task with before and after photos.
     */
    public function submitTask(Request $request, CleaningTask $task): RedirectResponse
    {
        $this->authorize('facility.tasks.complete');

        $userId = auth()->id();

        if ($task->status !== 'in-progress' || $task->started_by !== $userId) {
            return back()->with('error', 'You cannot submit this task.');
        }

        $validated = $request->validate([
            'before_photo' => 'required|string', // base64 image
            'before_gps' => 'nullable|array',
            'after_photo' => 'required|string', // base64 image
            'after_gps' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Process before photo
            $beforePhoto = $this->processPhoto($validated['before_photo'], $validated['before_gps'] ?? [], $task, 'before');
            
            // Process after photo
            $afterPhoto = $this->processPhoto($validated['after_photo'], $validated['after_gps'] ?? [], $task, 'after');

            // Create submission
            $submission = CleaningSubmission::create([
                'cleaning_task_id' => $task->id,
                'submitted_by' => $userId,
                'submitted_at' => now(),
                'before_photo' => $beforePhoto,
                'after_photo' => $afterPhoto,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create approval record
            CleaningApproval::create([
                'cleaning_submission_id' => $submission->id,
                'status' => 'pending',
            ]);

            // Update task status
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => $userId,
            ]);

            return redirect()
                ->route('facility.tasks.my-tasks')
                ->with('success', 'Task submitted successfully!');

        } catch (\Exception $e) {
            \Log::error('Task submission failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to submit task. Please try again.');
        }
    }

    /**
     * Process and watermark photo.
     */
    private function processPhoto(string $photoBase64, array $gpsData, CleaningTask $task, string $type): array
    {
        // Convert base64 to image data
        $imageData = explode(',', $photoBase64)[1];
        $decodedImage = base64_decode($imageData);

        // Generate filename
        $filename = "cleaning_{$type}_{$task->id}_" . time() . '.jpg';
        
        // Create folder path
        $folderPath = 'cleaning/' . $task->location_id . '/' . date('Y') . '/' . date('m');
        $filePath = $folderPath . '/' . $filename;

        // Store original
        Storage::disk('sigap')->put($filePath, $decodedImage);

        // Add watermark
        $watermarkedPath = $this->addWatermark($filePath, $decodedImage, $task, $gpsData, $type);

        return [
            'file_path' => $watermarkedPath,
            'original_path' => $filePath,
            'captured_at' => now()->toISOString(),
            'gps_data' => $gpsData,
            'watermarked' => true,
        ];
    }

    /**
     * Add watermark to photo (reusing Forms implementation logic).
     */
    private function addWatermark(string $filePath, string $imageData, CleaningTask $task, array $gpsData, string $type): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageData);

            // Build watermark text
            $watermarkText = strtoupper($type) . " PHOTO\n";
            $watermarkText .= now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . " WIB\n";
            $watermarkText .= "Task: " . $task->task_number . "\n";
            $watermarkText .= "Location: " . $task->location->name . "\n";
            $watermarkText .= "By: " . auth()->user()->name . "\n";

            // Add GPS if available
            if (!empty($gpsData['latitude']) && !empty($gpsData['longitude'])) {
                $watermarkText .= sprintf("GPS: %.6f, %.6f", $gpsData['latitude'], $gpsData['longitude']);
            }

            // Add watermark text
            $this->addWatermarkText($image, $watermarkText);

            // Save watermarked image
            $watermarkedData = $image->toJpeg(90);
            Storage::disk('sigap')->put($filePath, $watermarkedData);

            return $filePath;
        } catch (\Exception $e) {
            \Log::error('Watermarking failed', ['error' => $e->getMessage()]);
            // Return original if watermarking fails
            return $filePath;
        }
    }

    /**
     * Add watermark text to image.
     */
    private function addWatermarkText($image, string $text): void
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        
        try {
            $fontPath = public_path('fonts/Montserrat-VariableFont_wght.ttf');
            $fontSize = max(12, min($imageWidth / 30, $imageHeight / 30));
            
            $x = 15;
            $y = $imageHeight - 15;
            
            // Shadow
            $image->text($text, $x + 2, $y + 2, function ($font) use ($fontSize, $fontPath) {
                $font->filename($fontPath);
                $font->size($fontSize);
                $font->color('rgba(0, 0, 0, 0.8)');
                $font->align('left');
                $font->valign('bottom');
            });
            
            // Main text
            $image->text($text, $x, $y, function ($font) use ($fontSize, $fontPath) {
                $font->filename($fontPath);
                $font->size($fontSize);
                $font->color('rgba(255, 255, 255, 0.9)');
                $font->align('left');
                $font->valign('bottom');
            });
        } catch (\Exception $e) {
            \Log::error('Watermark text failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk assign tasks.
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $this->authorize('facility.tasks.bulk-assign');

        $validated = $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'start_date' => 'nullable|date',
        ]);

        $startDate = $validated['start_date'] ? \Carbon\Carbon::parse($validated['start_date']) : today();

        $count = $this->cleaningService->bulkReassignTasks(
            $validated['from_user_id'],
            $validated['to_user_id'],
            $startDate
        );

        return back()->with('success', "Successfully reassigned {$count} task(s).");
    }
}
