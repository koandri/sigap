<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AssetCategoryController extends Controller
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
        $query = AssetCategory::withCount('assets');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->paginate(20);

        return view('maintenance.asset-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('maintenance.asset-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:asset_categories,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        AssetCategory::create($validated);

        return redirect()
            ->route('maintenance.asset-categories.index')
            ->with('success', 'Asset category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetCategory $assetCategory): View
    {
        $assetCategory->load(['assets.assetCategory', 'assets.department', 'assets.user']);
        
        return view('maintenance.asset-categories.show', compact('assetCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetCategory $assetCategory): View
    {
        return view('maintenance.asset-categories.edit', compact('assetCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetCategory $assetCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:asset_categories,code,' . $assetCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $assetCategory->update($validated);

        return redirect()
            ->route('maintenance.asset-categories.index')
            ->with('success', 'Asset category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        if ($assetCategory->assets()->count() > 0) {
            return redirect()
                ->route('maintenance.asset-categories.index')
                ->with('error', 'Cannot delete category with existing assets.');
        }

        $assetCategory->delete();

        return redirect()
            ->route('maintenance.asset-categories.index')
            ->with('success', 'Asset category deleted successfully.');
    }
}
