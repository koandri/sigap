<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPhoto;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Services\FirecrawlService;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

final class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:maintenance.assets.manage')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'createMobile', 'storeMobile', 'analyzeImages', 'fetchSpecifications',
            'setPrimaryPhoto', 'deletePhoto'
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

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === 'true');
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
        $categories = AssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        return view('options.assets.create', compact('categories', 'departments', 'users', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
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
            'specifications_text' => 'nullable|string',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
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
        $asset->load([
            'assetCategory',
            'location',
            'department',
            'user',
            'maintenanceSchedules.maintenanceType',
            'workOrders.maintenanceType',
            'maintenanceLogs.performedBy',
            'documents',
            'photos.uploadedBy'
        ]);

        return view('options.assets.show', compact('asset'));
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

        return view('options.assets.edit', compact('asset', 'categories', 'departments', 'users', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
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
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

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

        // Handle multiple photo uploads
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            foreach ($photos as $photo) {
                if ($photo && $photo->isValid()) {
                    $imageData = file_get_contents($photo->getRealPath());
                    $photoBase64 = base64_encode($imageData);
                    $this->processAndStorePhoto($asset, $photoBase64);
                }
            }
        }

        // Check if code changed to regenerate QR
        $codeChanged = $asset->code !== $validated['code'];
        
        $asset->update($validated);

        // Regenerate QR code if code changed
        if ($codeChanged) {
            $this->generateAndStoreQRCode($asset);
        }

        return redirect()
            ->route('options.assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
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
        $query = Asset::with(['assetCategory', 'location'])
            ->whereNotNull('qr_code_path');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        return view('options.assets.qr-index', compact('assets', 'categories'));
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
            'is_active' => 'boolean',
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
    public function setPrimaryPhoto(Asset $asset, AssetPhoto $photo): JsonResponse
    {
        if ($photo->asset_id !== $asset->id) {
            return response()->json([
                'success' => false,
                'error' => 'Photo does not belong to this asset'
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
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto(AssetPhoto $photo): JsonResponse
    {
        $asset = $photo->asset;

        // Delete from S3
        if ($photo->photo_path) {
            Storage::disk('s3')->delete($photo->photo_path);
        }

        $wasPrimary = $photo->is_primary;
        $photo->delete();

        // If deleted photo was primary, set first remaining photo as primary
        if ($wasPrimary && $asset) {
            $firstPhoto = $asset->photos()->orderBy('created_at')->first();
            if ($firstPhoto) {
                $firstPhoto->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
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

            // Resize image
            $image = Image::make($decodedImage);
            $image->resize(1920, 1080, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Generate filename and path
            $filename = 'asset_' . $asset->id . '_' . time() . '_' . uniqid() . '.jpg';
            $folderPath = 'assets/' . $asset->id . '/photos';
            $path = $folderPath . '/' . $filename;

            // Store to S3
            Storage::disk('s3')->put($path, $image->encode('jpg', 90), 'public');

            // Get image dimensions
            $width = $image->width();
            $height = $image->height();
            $fileSize = strlen($image->encode('jpg', 90));

            // Create AssetPhoto record
            AssetPhoto::create([
                'asset_id' => $asset->id,
                'photo_path' => $path,
                'uploaded_at' => $uploadedAt,
                'captured_at' => $capturedAt,
                'is_primary' => $isPrimary,
                'uploaded_by' => auth()->id(),
                'gps_data' => $photoGpsData,
                'metadata' => [
                    'width' => $width,
                    'height' => $height,
                    'file_size' => $fileSize,
                    'exif_data' => $exifData,
                    'timezone' => 'Asia/Jakarta',
                ],
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
}
