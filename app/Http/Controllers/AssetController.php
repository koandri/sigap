<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ComponentType;
use App\Enums\UsageUnit;
use App\Http\Requests\AttachComponentRequest;
use App\Http\Requests\DetachComponentRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\File;
use App\Enums\FileCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Services\AssetComponentService;
use App\Services\AssetLifetimeService;
use App\Services\FirecrawlService;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

final class AssetController extends Controller
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly AssetComponentService $componentService,
        private readonly AssetLifetimeService $lifetimeService
    ) {
        // Initialize ImageManager with driver
        $driver = config('image.driver', 'gd') === 'imagick' 
            ? new ImagickDriver() 
            : new GdDriver();
            
        $this->imageManager = new ImageManager($driver);
        
        $this->middleware('can:maintenance.assets.manage')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'createMobile', 'storeMobile', 'analyzeImages', 'fetchSpecifications',
            'setPrimaryPhoto', 'deletePhoto', 'showAttachForm', 'attachComponent', 'detachComponent'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Asset::with(['assetCategory', 'location', 'department', 'user']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, code, or serial number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('name')->paginate(20);
        $categories = AssetCategory::active()->orderBy('name')->get();

        return view('options.assets.index', compact('assets', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = AssetCategory::active()->with('usageTypes')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        $assets = Asset::whereNull('parent_asset_id')->orderBy('name')->get();

        return view('options.assets.create', compact('categories', 'departments', 'users', 'locations', 'assets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:assets,code',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance,disposed',
            'specifications' => 'nullable',
            'specifications_text' => 'nullable|string',
            'photos' => 'nullable|array|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'parent_asset_id' => 'nullable|exists:assets,id',
            'component_type' => 'nullable|string|in:consumable,replaceable,integral',
            'installed_date' => 'nullable|date',
            'installed_usage_value' => 'nullable|numeric|min:0',
            'usage_type_id' => 'nullable|exists:asset_category_usage_types,id',
            'lifetime_unit' => 'nullable|string|in:days,kilometers,machine_hours,cycles',
            'expected_lifetime_value' => 'nullable|numeric|min:0',
        ];

        // Only validate photos if files are actually uploaded
        if ($request->hasFile('photos')) {
            $rules['photos.*'] = 'image|mimes:jpeg,png,jpg,gif|max:5120';
        }

        $validated = $request->validate($rules);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateAssetCode($validated['asset_category_id']);
        }

        // Handle specifications - can come as JSON string or array
        if (isset($validated['specifications'])) {
            if (is_string($validated['specifications'])) {
                $decoded = json_decode($validated['specifications'], true);
                $validated['specifications'] = $decoded ?: null;
            }
        } elseif (isset($validated['specifications_text']) && !empty($validated['specifications_text'])) {
            // Parse specifications_text if specifications is not provided
            $text = trim($validated['specifications_text']);
            // Try JSON first
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $validated['specifications'] = $decoded;
            } else {
                // Parse as key-value pairs
                $lines = explode("\n", $text);
                $specs = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $colonIndex = strpos($line, ':');
                    if ($colonIndex > 0) {
                        $key = trim(substr($line, 0, $colonIndex));
                        $value = trim(substr($line, $colonIndex + 1));
                        if ($key && $value) {
                            $specs[$key] = $value;
                        }
                    }
                }
                $validated['specifications'] = !empty($specs) ? $specs : null;
            }
            unset($validated['specifications_text']);
        } else {
            $validated['specifications'] = null;
        }

        $asset = Asset::create($validated);

        // Handle multiple photo uploads
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            $isFirst = true;

            foreach ($photos as $photo) {
                if ($photo && $photo->isValid()) {
                    // Convert file to base64 for processing
                    $imageData = file_get_contents($photo->getRealPath());
                    $photoBase64 = base64_encode($imageData);
                    $this->processAndStorePhoto($asset, $photoBase64, null, $isFirst);
                    $isFirst = false;
                }
            }
        }

        // Generate and store QR code
        $this->generateAndStoreQRCode($asset);

        return redirect()
            ->route('options.assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset): View
    {
        $this->authorize('view', $asset);
        
        // Eager load all relationships with nested relationships
        $asset->load([
            'assetCategory',
            'location',
            'department',
            'user',
            'disposedBy',
            'disposalWorkOrder',
            'parentAsset',
            'childAssets',
            'maintenanceSchedules' => function($query) {
                $query->with(['maintenanceType', 'assignedUser']);
            },
            'workOrders' => function($query) {
                $query->with(['maintenanceType', 'assignedUser', 'requestedBy', 'verifiedBy']);
            },
            'maintenanceLogs' => function($query) {
                $query->with(['performedBy', 'workOrder.maintenanceType'])
                      ->latest('performed_at');
            },
            'documents',
            'photos.uploadedBy',
        ]);

        // Pre-calculate statistics and collections to avoid N+1 queries
        $pendingWorkOrders = $asset->workOrders()
            ->whereNotIn('status', ['completed', 'cancelled', 'closed'])
            ->with(['maintenanceType', 'assignedUser', 'requestedBy'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get();
        
        $completedWorkOrders = $asset->workOrders()
            ->whereIn('status', ['completed', 'closed'])
            ->with(['maintenanceType', 'assignedUser', 'verifiedBy'])
            ->orderBy('completed_date', 'desc')
            ->take(10)
            ->get();
        
        $pendingWorkOrdersCount = $pendingWorkOrders->count();
        $completedWorkOrdersCount = $asset->workOrders()
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        // Get next maintenance due date
        $nextMaintenanceDue = $asset->maintenanceSchedules()
            ->active()
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date', 'asc')
            ->first();

        // Get component status summary
        $componentStatusSummary = [
            'total' => $asset->childAssets->count(),
            'active' => $asset->childAssets->where('status', '!=', 'disposed')->count(),
            'inactive' => $asset->childAssets->where('status', 'disposed')->count(),
        ];

        return view('options.assets.show', compact(
            'asset',
            'pendingWorkOrders',
            'completedWorkOrders',
            'pendingWorkOrdersCount',
            'completedWorkOrdersCount',
            'nextMaintenanceDue',
            'componentStatusSummary'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset): View
    {
        $asset->load('photos.uploadedBy');
        $categories = AssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        $assets = Asset::whereNull('parent_asset_id')->where('id', '!=', $asset->id)->orderBy('name')->get();

        return view('options.assets.edit', compact('asset', 'categories', 'departments', 'users', 'locations', 'assets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset): RedirectResponse
    {
        // Debug: Log request information
        Log::info('Asset update request received', [
            'method' => $request->method(),
            'has_photos' => $request->hasFile('photos'),
            'all_files' => $request->allFiles(),
            'photos_input' => $request->input('photos'),
            'content_type' => $request->header('Content-Type'),
        ]);

        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:assets,code,' . $asset->id,
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance,disposed',
            'specifications' => 'nullable',
            'specifications_text' => 'nullable|string',
            'photos' => 'nullable|array|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'parent_asset_id' => 'nullable|exists:assets,id',
            'component_type' => 'nullable|string|in:consumable,replaceable,integral',
            'installed_date' => 'nullable|date',
            'installed_usage_value' => 'nullable|numeric|min:0',
            'lifetime_unit' => 'nullable|string|in:days,kilometers,machine_hours,cycles',
            'expected_lifetime_value' => 'nullable|numeric|min:0',
        ];

        // Only validate photos if files are actually uploaded
        if ($request->hasFile('photos')) {
            $rules['photos.*'] = 'image|mimes:jpeg,png,jpg,gif|max:5120';
            Log::info('Photos validation rule added');
        } else {
            Log::warning('No photos found in request', [
                'all_input_keys' => array_keys($request->all()),
                'files_keys' => array_keys($request->allFiles()),
            ]);
        }

        $validated = $request->validate($rules);

        // Handle specifications - can come as JSON string or array
        if (isset($validated['specifications'])) {
            if (is_string($validated['specifications'])) {
                $decoded = json_decode($validated['specifications'], true);
                $validated['specifications'] = $decoded ?: null;
            }
        } elseif (isset($validated['specifications_text']) && !empty($validated['specifications_text'])) {
            // Parse specifications_text if specifications is not provided
            $text = trim($validated['specifications_text']);
            // Try JSON first
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $validated['specifications'] = $decoded;
            } else {
                // Parse as key-value pairs
                $lines = explode("\n", $text);
                $specs = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $colonIndex = strpos($line, ':');
                    if ($colonIndex > 0) {
                        $key = trim(substr($line, 0, $colonIndex));
                        $value = trim(substr($line, $colonIndex + 1));
                        if ($key && $value) {
                            $specs[$key] = $value;
                        }
                    }
                }
                $validated['specifications'] = !empty($specs) ? $specs : null;
            }
            unset($validated['specifications_text']);
        } else {
            $validated['specifications'] = null;
        }

        // Check if code changed to regenerate QR
        $codeChanged = $asset->code !== $validated['code'];
        
        $asset->update($validated);

        // Regenerate QR code if code changed
        if ($codeChanged) {
            $this->generateAndStoreQRCode($asset);
        }

        // Handle multiple photo uploads
        $photoUploadErrors = [];
        $photosUploaded = 0;
        
        Log::info('Checking for photos in request', [
            'hasFile_photos' => $request->hasFile('photos'),
            'allFiles' => $request->allFiles(),
            'input_photos' => $request->input('photos'),
        ]);
        
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            Log::info('Photos found in request', [
                'count' => is_array($photos) ? count($photos) : 'not_array',
                'type' => gettype($photos),
            ]);
            
            if (empty($photos)) {
                $photoUploadErrors[] = 'No photos were received in the request';
                Log::warning('Photos array is empty');
            } else {
                foreach ($photos as $index => $photo) {
                    Log::info("Processing photo {$index}", [
                        'photo_exists' => $photo !== null,
                        'is_valid' => $photo ? $photo->isValid() : false,
                        'error_code' => $photo ? $photo->getError() : null,
                    ]);
                    
                    if (!$photo) {
                        $photoUploadErrors[] = 'Photo ' . ($index + 1) . ': File is null';
                        continue;
                    }
                    
                    if (!$photo->isValid()) {
                        $errorMessage = $photo->getError() === UPLOAD_ERR_INI_SIZE || $photo->getError() === UPLOAD_ERR_FORM_SIZE
                            ? 'File size exceeds maximum allowed size (5MB)'
                            : 'Invalid file or upload error (Error code: ' . $photo->getError() . ')';
                        $photoUploadErrors[] = 'Photo ' . ($index + 1) . ': ' . $errorMessage;
                        Log::warning('Photo invalid', [
                            'index' => $index,
                            'error_code' => $photo->getError(),
                            'error_message' => $errorMessage,
                        ]);
                        continue;
                    }
                    
                    try {
                        $imageData = file_get_contents($photo->getRealPath());
                        
                        if ($imageData === false || empty($imageData)) {
                            $photoUploadErrors[] = 'Photo ' . ($index + 1) . ': Could not read file data';
                            Log::error('Could not read photo file data', ['index' => $index]);
                            continue;
                        }
                        
                        $photoBase64 = base64_encode($imageData);
                        $this->processAndStorePhoto($asset, $photoBase64);
                        $photosUploaded++;
                        Log::info('Photo uploaded successfully', ['index' => $index]);
                    } catch (\Exception $e) {
                        Log::error('Failed to upload photo ' . ($index + 1) . ': ' . $e->getMessage(), [
                            'exception' => $e,
                            'file_name' => $photo->getClientOriginalName(),
                            'file_size' => $photo->getSize(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $photoUploadErrors[] = 'Photo ' . ($index + 1) . ' (' . $photo->getClientOriginalName() . '): ' . $e->getMessage();
                    }
                }
            }
        } else {
            Log::warning('No photos in request - hasFile returned false', [
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
            ]);
            $photoUploadErrors[] = 'No photos were detected in the upload. Please ensure the form has enctype="multipart/form-data" and files are selected.';
        }

        // Prepare response message
        $messages = [];
        $messageType = 'success';
        
        if ($photosUploaded > 0) {
            $messages[] = "Asset updated successfully. {$photosUploaded} photo(s) uploaded.";
        } else {
            $messages[] = 'Asset updated successfully.';
        }
        
        if (!empty($photoUploadErrors)) {
            $messageType = 'warning';
            $errorMessage = 'Photo upload issues: ' . implode('; ', $photoUploadErrors);
            $messages[] = $errorMessage;
            Log::warning('Photo upload errors during asset update', [
                'asset_id' => $asset->id,
                'errors' => $photoUploadErrors,
            ]);
        }
        
        // If user selected files but none were uploaded, show error
        if ($request->has('photos_selected') && $photosUploaded === 0 && empty($photoUploadErrors)) {
            $messageType = 'warning';
            $messages[] = 'Photos were selected but could not be uploaded. Please check file size (max 5MB) and format (JPEG, PNG, JPG, GIF).';
        }

        $finalMessage = implode(' ', $messages);
        
        Log::info('Asset update completed', [
            'asset_id' => $asset->id,
            'photos_uploaded' => $photosUploaded,
            'has_errors' => !empty($photoUploadErrors),
            'message_type' => $messageType,
        ]);
        
        return redirect()
            ->route('options.assets.show', $asset)
            ->with($messageType, $finalMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->workOrders()->count() > 0) {
            return redirect()
                ->route('options.assets.index')
                ->with('error', 'Cannot delete asset with existing work orders.');
        }

        // Check for child components
        if ($asset->hasComponents()) {
            return redirect()
                ->route('options.assets.index')
                ->with('error', 'Cannot delete asset with child components. Please detach components first.');
        }

        // Delete all photos
        foreach ($asset->photos as $photo) {
            Storage::disk('s3')->delete($photo->photo_path);
        }

        // Delete all photos
        foreach ($asset->photos as $photo) {
            if ($photo->photo_path) {
                Storage::disk('s3')->delete($photo->photo_path);
            }
            $photo->delete();
        }

        // Delete QR code from S3
        if ($asset->qr_code_path && Storage::disk('s3')->exists($asset->qr_code_path)) {
            Storage::disk('s3')->delete($asset->qr_code_path);
        }

        $asset->delete();

        return redirect()
            ->route('options.assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Display QR code for asset.
     */
    public function generateQR(Asset $asset): View
    {
        // Generate QR if it doesn't exist in S3
        if (!$asset->qr_code_path || !Storage::disk('s3')->exists($asset->qr_code_path)) {
            $this->generateAndStoreQRCode($asset);
            $asset->refresh();
        }
        return view('options.assets.qr-code', compact('asset'));
    }

    /**
     * Display all QR codes.
     */
    public function qrIndex(Request $request): View
    {
        $this->authorize('viewAny', Asset::class);
        
        $query = Asset::with(['assetCategory', 'location'])
            ->whereNotNull('qr_code_path');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }

        // Search by name, code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('code')->paginate(24);
        $categories = AssetCategory::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        return view('options.assets.qr-index', compact('assets', 'categories', 'locations'));
    }

    /**
     * Generate unique asset code based on category and date.
     */
    private function generateAssetCode(int $categoryId): string
    {
        $category = AssetCategory::findOrFail($categoryId);
        $categoryCode = strtoupper($category->code ?? 'AST');
        
        // Sanitize category code to ensure it's filename-safe
        $categoryCode = preg_replace('/[^A-Z0-9]/', '', $categoryCode);
        if (empty($categoryCode)) {
            $categoryCode = 'AST';
        }
        
        $date = now()->format('ymd');
        $prefix = "{$categoryCode}-{$date}-";
        
        // Get the last asset code for this category today
        $lastAsset = Asset::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();
        
        if ($lastAsset) {
            $lastNumber = (int) substr($lastAsset->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and store QR code for an asset.
     */
    private function generateAndStoreQRCode(Asset $asset): void
    {
        // Delete old QR code from S3 if exists
        if ($asset->qr_code_path && Storage::disk('s3')->exists($asset->qr_code_path)) {
            Storage::disk('s3')->delete($asset->qr_code_path);
        }

        // Generate QR code data
        $qrData = route('options.assets.show', $asset);

        // Check if logo exists
        $logoPath = public_path('imgs/qr_logo.png');
        $hasLogo = file_exists($logoPath);

        // Build QR code
        $builder = new Builder(
            writer: new PngWriter(),
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: $hasLogo ? $logoPath : null,
            logoResizeToWidth: $hasLogo ? 80 : null,
            logoPunchoutBackground: $hasLogo
        );

        $result = $builder->build();

        // Save to S3
        $filename = 'qr-' . $asset->code . '.png';
        $folderPath = 'assets/' . $asset->id . '/qr';
        $filePath = $folderPath . '/' . $filename;

        Storage::disk('s3')->put($filePath, $result->getString(), 'public');

        // Update asset with QR path
        $asset->update(['qr_code_path' => $filePath]);
    }

    /**
     * Show the form for creating a new asset via mobile camera.
     */
    public function createMobile(): View
    {
        $categories = AssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        return view('options.assets.create-mobile', compact('categories', 'departments', 'users', 'locations'));
    }

    /**
     * Analyze multiple images using AI.
     */
    public function analyzeImages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'images' => 'required|array|min:1|max:10',
                'images.*' => 'required|string', // Base64 encoded images
            ]);

            Log::info('Analyzing images', [
                'image_count' => count($request->input('images', [])),
                'api_key_configured' => !empty(config('services.openrouter.api_key')),
            ]);

            $openRouterService = new OpenRouterService();
            $result = $openRouterService->analyzeAssetImages($request->input('images'));

            Log::info('Image analysis result', [
                'success' => $result['success'] ?? false,
                'error' => $result['error'] ?? null,
            ]);

            if ($result['success']) {
                $responseData = [
                    'success' => true,
                    'suggested_name' => $result['suggested_name'] ?? null,
                    'suggested_category' => $result['suggested_category'] ?? null,
                    'manufacturer' => $result['manufacturer'] ?? null,
                    'model' => $result['model'] ?? null,
                    'serial_number' => $result['serial_number'] ?? null,
                    'confidence' => $result['confidence'] ?? 0.5,
                ];

                // Automatically fetch specifications if manufacturer and model are available
                if (!empty($result['manufacturer']) && !empty($result['model'])) {
                    try {
                        $firecrawlService = new FirecrawlService();
                        $specResult = $firecrawlService->searchSpecifications(
                            $result['manufacturer'],
                            $result['model']
                        );

                        if ($specResult['success'] && !empty($specResult['specifications'])) {
                            $responseData['specifications'] = $specResult['specifications'];
                            Log::info('AI specifications fetched successfully', [
                                'manufacturer' => $result['manufacturer'],
                                'model' => $result['model'],
                                'specs_count' => count($specResult['specifications']),
                            ]);
                        } else {
                            Log::info('AI specifications fetch failed or empty', [
                                'manufacturer' => $result['manufacturer'],
                                'model' => $result['model'],
                                'error' => $specResult['error'] ?? 'No specifications found',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error fetching AI specifications', [
                            'error' => $e->getMessage(),
                            'manufacturer' => $result['manufacturer'],
                            'model' => $result['model'],
                        ]);
                        // Don't fail the entire request if spec fetching fails
                    }
                }

                return response()->json($responseData);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to analyze images'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Image analysis validation error', [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Invalid request: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image analysis exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test OpenRouter API connection.
     */
    public function testOpenRouter(): JsonResponse
    {
        try {
            $openRouterService = new OpenRouterService();
            $result = $openRouterService->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'API connection successful'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'API connection failed'
            ], 400);
        } catch (\Exception $e) {
            Log::error('OpenRouter test exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch specifications using Firecrawl API.
     */
    public function fetchSpecifications(Request $request): JsonResponse
    {
        $request->validate([
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);

        $firecrawlService = new FirecrawlService();
        $result = $firecrawlService->searchSpecifications(
            $request->input('manufacturer'),
            $request->input('model')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'specifications' => $result['specifications'] ?? [],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to fetch specifications'
        ], 400);
    }

    /**
     * Store a newly created asset from mobile flow.
     */
    public function storeMobile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:assets,code',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance,disposed',
            'specifications' => 'nullable',
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'required|string', // Base64 encoded images
            'gps_data' => 'nullable|array',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateAssetCode($validated['asset_category_id']);
        }

        // Handle specifications - can come as JSON string or array
        if (isset($validated['specifications'])) {
            if (is_string($validated['specifications'])) {
                $decoded = json_decode($validated['specifications'], true);
                $validated['specifications'] = $decoded ?: null;
            }
        } else {
            $validated['specifications'] = null;
        }

        $asset = Asset::create($validated);

        // Process and store photos
        $photos = $request->input('photos', []);
        $gpsDataArray = $request->input('gps_data', []);
        $isFirst = true;

        foreach ($photos as $index => $photoBase64) {
            $gpsData = $gpsDataArray[$index] ?? null;
            $this->processAndStorePhoto($asset, $photoBase64, $gpsData, $isFirst);
            $isFirst = false;
        }

        // Generate and store QR code
        $this->generateAndStoreQRCode($asset);

        return redirect()
            ->route('options.assets.show', $asset)
            ->with('success', 'Asset created successfully with ' . count($photos) . ' photo(s).');
    }

    /**
     * Set a photo as primary.
     */
    public function setPrimaryPhoto(Asset $asset, File $photo): JsonResponse
    {
        try {
            // Verify photo belongs to this asset
            if ($photo->fileable_id !== $asset->id || $photo->fileable_type !== Asset::class) {
                return response()->json([
                    'success' => false,
                    'error' => 'Photo does not belong to this asset'
                ], 400);
            }

            // Verify it's a photo
            if ($photo->file_category !== FileCategory::Photo) {
                return response()->json([
                    'success' => false,
                    'error' => 'File is not a photo'
                ], 400);
            }

            // Unset all other primary photos
            $asset->photos()->update(['is_primary' => false]);

            // Set this photo as primary
            $photo->update(['is_primary' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Primary photo updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set primary photo: ' . $e->getMessage(), [
                'photo_id' => $photo->id ?? null,
                'asset_id' => $asset->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while setting primary photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto(Asset $asset, File $photo): JsonResponse
    {
        try {
            // Verify photo belongs to this asset
            if ($photo->fileable_id !== $asset->id || $photo->fileable_type !== Asset::class) {
                return response()->json([
                    'success' => false,
                    'error' => 'Photo does not belong to this asset'
                ], 400);
            }

            // Verify it's a photo
            if ($photo->file_category !== FileCategory::Photo) {
                return response()->json([
                    'success' => false,
                    'error' => 'File is not a photo'
                ], 400);
            }

            $wasPrimary = $photo->is_primary;

            // Delete from S3 (handled by model's boot method, but check anyway)
            if ($photo->file_path) {
                try {
                    Storage::disk('s3')->delete($photo->file_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete photo from S3: ' . $e->getMessage(), [
                        'photo_id' => $photo->id,
                        'file_path' => $photo->file_path,
                    ]);
                    // Continue with deletion even if S3 delete fails
                }
            }

            // Delete the photo record
            $photo->delete();

            // If deleted photo was primary, set first remaining photo as primary
            if ($wasPrimary) {
                $firstPhoto = $asset->photos()->orderBy('uploaded_at')->first();
                if ($firstPhoto) {
                    $firstPhoto->update(['is_primary' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete photo: ' . $e->getMessage(), [
                'photo_id' => $photo->id ?? null,
                'asset_id' => $asset->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and store a photo from base64 data.
     */
    private function processAndStorePhoto(Asset $asset, string $photoBase64, ?array $gpsData = null, bool $isPrimary = false): void
    {
        try {
            // Decode base64
            if (str_starts_with($photoBase64, 'data:image')) {
                $imageData = explode(',', $photoBase64)[1];
            } else {
                $imageData = $photoBase64;
            }
            $decodedImage = base64_decode($imageData);

            // Extract EXIF data
            $exifData = $this->extractExifData($decodedImage);

            // Get captured_at from EXIF or use current time (UTC+7)
            $capturedAt = null;
            if (!empty($exifData['date_time'])) {
                try {
                    $capturedAt = \Carbon\Carbon::parse($exifData['date_time'])
                        ->setTimezone('Asia/Jakarta');
                } catch (\Exception $e) {
                    Log::warning('Failed to parse EXIF date: ' . $e->getMessage());
                }
            }

            if (!$capturedAt) {
                $capturedAt = now()->setTimezone('Asia/Jakarta');
            }

            $uploadedAt = now()->setTimezone('Asia/Jakarta');

            // Extract GPS data
            $photoGpsData = null;
            if ($gpsData && isset($gpsData['latitude']) && isset($gpsData['longitude'])) {
                $photoGpsData = [
                    'latitude' => (float)$gpsData['latitude'],
                    'longitude' => (float)$gpsData['longitude'],
                ];
            } elseif (!empty($exifData['gps_latitude']) && !empty($exifData['gps_longitude'])) {
                $photoGpsData = [
                    'latitude' => (float)$exifData['gps_latitude'],
                    'longitude' => (float)$exifData['gps_longitude'],
                ];
            }

            // Resize image using Intervention Image v3
            $image = $this->imageManager->read($decodedImage);
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            $originalAspectRatio = $originalWidth / $originalHeight;
            
            // Calculate new dimensions maintaining aspect ratio
            $maxWidth = 1920;
            $maxHeight = 1080;
            $targetAspectRatio = $maxWidth / $maxHeight;
            
            if ($originalAspectRatio > $targetAspectRatio) {
                // Image is wider, scale by width
                $newWidth = min($originalWidth, $maxWidth);
                $newHeight = (int) ($newWidth / $originalAspectRatio);
            } else {
                // Image is taller, scale by height
                $newHeight = min($originalHeight, $maxHeight);
                $newWidth = (int) ($newHeight * $originalAspectRatio);
            }
            
            // Only resize if image is larger than target
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $image->resize($newWidth, $newHeight);
            }

            // Generate filename and path
            $filename = 'asset_' . $asset->id . '_' . time() . '_' . uniqid() . '.jpg';
            $folderPath = 'assets/' . $asset->id . '/photos';
            $path = $folderPath . '/' . $filename;

            // Encode to JPEG and store to S3
            $encoded = $image->toJpeg(90);
            $encodedString = (string) $encoded;
            Storage::disk('s3')->put($path, $encodedString, 'public');

            // Get image dimensions
            $width = $image->width();
            $height = $image->height();
            $fileSize = strlen($encodedString);

            // Create File record for the photo
            $metadata = [
                'width' => $width,
                'height' => $height,
                'file_size' => $fileSize,
                'exif_data' => $exifData,
                'timezone' => 'Asia/Jakarta',
            ];
            
            // Add captured_at to metadata if available
            if ($capturedAt) {
                $metadata['captured_at'] = $capturedAt->toIso8601String();
            }
            
            // Add GPS data to metadata if available
            if ($photoGpsData) {
                $metadata['gps_data'] = $photoGpsData;
            }
            
            File::create([
                'fileable_type' => Asset::class,
                'fileable_id' => $asset->id,
                'file_category' => FileCategory::Photo,
                'file_path' => $path,
                'file_name' => $filename,
                'file_size' => $fileSize,
                'mime_type' => 'image/jpeg',
                'uploaded_at' => $uploadedAt,
                'uploaded_by' => auth()->id(),
                'is_primary' => $isPrimary,
                'metadata' => $metadata,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process asset photo: ' . $e->getMessage(), [
                'asset_id' => $asset->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract EXIF data from image binary data.
     */
    private function extractExifData(string $imageData): array
    {
        try {
            // Create temporary file to read EXIF
            $tempFile = tempnam(sys_get_temp_dir(), 'exif_');
            file_put_contents($tempFile, $imageData);

            $exif = @exif_read_data($tempFile);
            unlink($tempFile);

            if (!$exif) {
                return [
                    'date_time' => null,
                    'gps_latitude' => null,
                    'gps_longitude' => null,
                ];
            }

            // Extract date/time
            $dateTime = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;

            // Extract GPS coordinates
            $gpsLatitude = null;
            $gpsLongitude = null;
            if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude']) &&
                isset($exif['GPSLatitudeRef']) && isset($exif['GPSLongitudeRef'])) {
                $gpsLatitude = $this->getGpsCoordinate($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $gpsLongitude = $this->getGpsCoordinate($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
            }

            return [
                'date_time' => $dateTime,
                'gps_latitude' => $gpsLatitude,
                'gps_longitude' => $gpsLongitude,
            ];

        } catch (\Exception $e) {
            Log::error('EXIF extraction failed: ' . $e->getMessage());
            return [
                'date_time' => null,
                'gps_latitude' => null,
                'gps_longitude' => null,
            ];
        }
    }

    /**
     * Convert GPS coordinate from EXIF format to decimal.
     */
    private function getGpsCoordinate($coordinate, $hemisphere): ?float
    {
        if (!is_array($coordinate) || count($coordinate) != 3) {
            return null;
        }

        $degrees = (float)$coordinate[0];
        $minutes = (float)$coordinate[1];
        $seconds = (float)$coordinate[2];

        $result = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($hemisphere == 'S' || $hemisphere == 'W') {
            $result = -$result;
        }

        return round($result, 6);
    }

    /**
     * Show the form for attaching a component to an asset.
     */
    public function showAttachForm(Asset $asset): View
    {
        $this->authorize('attachComponent', $asset);

        // Get available assets that can be attached as components
        // Exclude: the parent asset itself, assets that are already attached to other parents
        $availableAssets = Asset::where('id', '!=', $asset->id)
            ->where(function ($query) use ($asset) {
                $query->whereNull('parent_asset_id')
                    ->orWhere('parent_asset_id', $asset->id); // Allow reattaching if already attached to this parent
            })
            ->active()
            ->orderBy('name')
            ->get();

        return view('options.assets.components.attach', compact('asset', 'availableAssets'));
    }

    /**
     * Attach a component to an asset.
     */
    public function attachComponent(AttachComponentRequest $request, Asset $asset): RedirectResponse
    {
        $this->authorize('attachComponent', $asset);

        $component = Asset::findOrFail($request->component_id);
        $componentType = ComponentType::from($request->component_type);
        $installedDate = $request->installed_date ? new \DateTime($request->installed_date) : null;
        $installedUsageValue = $request->installed_usage_value !== null && $request->installed_usage_value !== '' 
            ? (float) $request->installed_usage_value 
            : null;

        try {
            $this->componentService->attachComponent(
                $asset,
                $component,
                $componentType,
                $installedDate,
                $installedUsageValue,
                $request->installation_notes
            );

            return redirect()
                ->route('assets.components', $asset)
                ->with('success', 'Component attached successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to attach component: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for detaching a component.
     */
    public function showDetachForm(Asset $component): View
    {
        $this->authorize('detachComponent', $component);

        if (!$component->parentAsset) {
            abort(404, 'Component does not have a parent asset.');
        }

        return view('options.assets.components.detach', compact('component'));
    }

    /**
     * Detach a component from its parent asset.
     */
    public function detachComponent(DetachComponentRequest $request, Asset $component): RedirectResponse
    {
        $this->authorize('detachComponent', $component);

        $parentAsset = $component->parentAsset;
        $disposedDate = $request->disposed_date ? new \DateTime($request->disposed_date) : null;
        $disposedUsageValue = $request->disposed_usage_value !== null && $request->disposed_usage_value !== '' 
            ? (float) $request->disposed_usage_value 
            : null;

        try {
            $this->componentService->detachComponent(
                $component,
                $disposedDate,
                $disposedUsageValue,
                $request->dispose_asset,
                $request->notes
            );

            $message = 'Component detached successfully.';
            if ($request->dispose_asset) {
                $message .= ' Component has been marked as disposed.';
            }

            return redirect()
                ->route('assets.components', $parentAsset)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to detach component: ' . $e->getMessage());
        }
    }

    /**
     * Show components for an asset.
     */
    public function showComponents(Asset $asset): View
    {
        $this->authorize('view', $asset);

        $asset->load(['childAssets.parentAsset', 'parentAsset']);
        $components = $asset->childAssets;
        $componentTree = $this->componentService->getComponentTree($asset);

        return view('options.assets.components.index', compact('asset', 'components', 'componentTree'));
    }

    /**
     * Show lifetime metrics for an asset.
     */
    public function showLifetimeMetrics(Asset $asset): View
    {
        $this->authorize('viewLifetimeMetrics', $asset);

        $asset->load(['assetCategory']);
        $lifetimePercentage = $this->lifetimeService->getLifetimePercentage($asset);
        $remainingLifetime = $this->lifetimeService->getRemainingLifetime($asset);
        $expectedLifetime = $this->lifetimeService->getExpectedLifetime($asset);
        $suggestedLifetime = $this->lifetimeService->suggestExpectedLifetime($asset);

        return view('reports.asset-lifetime.asset', compact(
            'asset',
            'lifetimePercentage',
            'remainingLifetime',
            'expectedLifetime',
            'suggestedLifetime'
        ));
    }
}
