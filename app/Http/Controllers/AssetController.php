<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
        $this->middleware('can:maintenance.assets.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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
            'code' => 'required|string|max:50|unique:assets,code',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance,disposed',
            'specifications' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'asset_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('assets', $filename, 'public');
            
            // Resize image
            $resized = Image::make($image)->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            Storage::disk('public')->put($path, $resized->encode());
            $validated['image_path'] = $path;
        }

        $asset = Asset::create($validated);

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
            'documents'
        ]);

        return view('options.assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset): View
    {
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
            'specifications' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }

            $image = $request->file('image');
            $filename = 'asset_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('assets', $filename, 'public');
            
            // Resize image
            $resized = Image::make($image)->resize(800, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            Storage::disk('public')->put($path, $resized->encode());
            $validated['image_path'] = $path;
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

        // Delete image
        if ($asset->image_path) {
            Storage::disk('public')->delete($asset->image_path);
        }

        // Delete QR code
        if ($asset->qr_code_path && file_exists(public_path($asset->qr_code_path))) {
            unlink(public_path($asset->qr_code_path));
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
        // Generate QR if it doesn't exist
        if (!$asset->qr_code_path || !file_exists(public_path($asset->qr_code_path))) {
            $this->generateAndStoreQRCode($asset);
            $asset->refresh();
        }

        $hasLogo = file_exists(public_path('imgs/qr_logo.png'));

        return view('options.assets.qr-code', compact('asset', 'hasLogo'));
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
     * Generate and store QR code for an asset.
     */
    private function generateAndStoreQRCode(Asset $asset): void
    {
        // Delete old QR code if exists
        if ($asset->qr_code_path && file_exists(public_path($asset->qr_code_path))) {
            unlink(public_path($asset->qr_code_path));
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

        // Save to file
        $filename = 'qr-' . $asset->code . '.png';
        $filePath = 'storage/assets_qr/' . $filename;
        $fullPath = public_path($filePath);

        file_put_contents($fullPath, $result->getString());

        // Update asset with QR path
        $asset->update(['qr_code_path' => $filePath]);
    }
}
