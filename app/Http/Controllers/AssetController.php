<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
        $query = Asset::with(['assetCategory', 'department', 'user']);

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

        return view('maintenance.assets.index', compact('assets', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = AssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.assets.create', compact('categories', 'departments', 'users'));
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
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance',
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

        Asset::create($validated);

        return redirect()
            ->route('maintenance.assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset): View
    {
        $asset->load([
            'assetCategory',
            'department',
            'user',
            'maintenanceSchedules.maintenanceType',
            'workOrders.maintenanceType',
            'maintenanceLogs.performedBy',
            'documents'
        ]);

        return view('maintenance.assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset): View
    {
        $categories = AssetCategory::active()->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('active', true)->orderBy('name')->get();

        return view('maintenance.assets.edit', compact('asset', 'categories', 'departments', 'users'));
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
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|in:operational,down,maintenance',
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

        $asset->update($validated);

        return redirect()
            ->route('maintenance.assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->workOrders()->count() > 0) {
            return redirect()
                ->route('maintenance.assets.index')
                ->with('error', 'Cannot delete asset with existing work orders.');
        }

        // Delete image
        if ($asset->image_path) {
            Storage::disk('public')->delete($asset->image_path);
        }

        $asset->delete();

        return redirect()
            ->route('maintenance.assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Generate QR code for asset.
     */
    public function generateQR(Asset $asset): View
    {
        $qrData = [
            'asset_id' => $asset->id,
            'asset_code' => $asset->code,
            'asset_name' => $asset->name,
            'url' => route('maintenance.assets.show', $asset)
        ];

        return view('maintenance.assets.qr-code', compact('asset', 'qrData'));
    }
}
