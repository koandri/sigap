<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UsageUnit;
use App\Http\Requests\StoreUsageTypeRequest;
use App\Models\AssetCategory;
use App\Models\AssetCategoryUsageType;
use App\Services\AssetLifetimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AssetCategoryUsageTypeController extends Controller
{
    public function __construct(
        private readonly AssetLifetimeService $lifetimeService
    ) {
        $this->middleware('can:maintenance.assets.manage')->except(['index', 'show']);
    }

    /**
     * Display a listing of usage types for a category.
     */
    public function index(AssetCategory $category): View
    {
        $this->authorize('view', $category);
        
        $usageTypes = $category->usageTypes()->orderBy('name')->get();

        return view('asset-categories.usage-types.index', compact('category', 'usageTypes'));
    }

    /**
     * Show the form for creating a new usage type.
     */
    public function create(AssetCategory $category): View
    {
        $this->authorize('update', $category);

        return view('asset-categories.usage-types.create', compact('category'));
    }

    /**
     * Store a newly created usage type.
     */
    public function store(StoreUsageTypeRequest $request, AssetCategory $category): RedirectResponse
    {
        $this->authorize('create', AssetCategoryUsageType::class);

        $validated = $request->validated();
        $validated['asset_category_id'] = $category->id;

        AssetCategoryUsageType::create($validated);

        return redirect()
            ->route('options.asset-categories.usage-types.index', $category)
            ->with('success', 'Usage type created successfully.');
    }

    /**
     * Show the form for editing the specified usage type.
     */
    public function edit(AssetCategoryUsageType $usageType): View
    {
        $this->authorize('update', $usageType);

        $category = $usageType->assetCategory;

        return view('asset-categories.usage-types.edit', compact('usageType', 'category'));
    }

    /**
     * Update the specified usage type.
     */
    public function update(StoreUsageTypeRequest $request, AssetCategoryUsageType $usageType): RedirectResponse
    {
        $this->authorize('update', $usageType);

        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : false;

        $usageType->update($validated);

        return redirect()
            ->route('options.asset-categories.usage-types.index', $usageType->assetCategory)
            ->with('success', 'Usage type updated successfully.');
    }

    /**
     * Remove the specified usage type.
     */
    public function destroy(AssetCategoryUsageType $usageType): RedirectResponse
    {
        $this->authorize('delete', $usageType);

        $category = $usageType->assetCategory;

        // Check if any assets are using this usage type
        if ($usageType->assets()->exists()) {
            return redirect()
                ->route('options.asset-categories.usage-types.index', $category)
                ->with('error', 'Cannot delete usage type that is in use by assets.');
        }

        $usageType->delete();

        return redirect()
            ->route('options.asset-categories.usage-types.index', $category)
            ->with('success', 'Usage type deleted successfully.');
    }

    /**
     * Recalculate lifetime metrics for a usage type.
     */
    public function recalculateMetrics(AssetCategoryUsageType $usageType): RedirectResponse
    {
        $this->authorize('recalculateMetrics', $usageType);

        try {
            $this->lifetimeService->recalculateCategoryMetrics(
                $usageType->asset_category_id,
                $usageType->id
            );

            return redirect()
                ->route('options.asset-categories.usage-types.index', $usageType->assetCategory)
                ->with('success', 'Lifetime metrics recalculated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to recalculate metrics: ' . $e->getMessage());
        }
    }
}
