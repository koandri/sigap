<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreYieldGuidelineRequest;
use App\Http\Requests\UpdateYieldGuidelineRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\YieldGuideline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class YieldGuidelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.yield-guidelines.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.yield-guidelines.create')->only(['create', 'store']);
        $this->middleware('can:manufacturing.yield-guidelines.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.yield-guidelines.delete')->only(['destroy']);
    }

    /**
     * Display a listing of yield guidelines.
     */
    public function index(Request $request): View
    {
        $query = YieldGuideline::with(['fromItem', 'toItem']);

        // Filter by from_stage
        if ($request->filled('from_stage')) {
            $query->where('from_stage', $request->from_stage);
        }

        // Filter by to_stage
        if ($request->filled('to_stage')) {
            $query->where('to_stage', $request->to_stage);
        }

        // Search by item name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('fromItem', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('toItem', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $guidelines = $query->orderBy('from_stage')->orderBy('to_stage')->orderBy('yield_quantity')->paginate(20);

        return view('manufacturing.yield-guidelines.index', compact('guidelines'));
    }

    /**
     * Show the form for creating a new yield guideline.
     */
    public function create(): View
    {
        // Get items that can be used for yield guidelines
        $adonanCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $gelondonganCategory = ItemCategory::where('name', 'like', '%Gelondongan%')->first();
        $kerupukKgCategory = ItemCategory::where('name', 'like', '%Kerupuk Kg%')->first()
            ?? ItemCategory::where('name', 'like', '%Finished Products%')->first();
        $kerupukPackCategory = ItemCategory::where('name', 'like', '%Kerupuk Pack%')->first();

        $adonanItems = $adonanCategory
            ? Item::where('item_category_id', $adonanCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $gelondonganItems = $gelondonganCategory
            ? Item::where('item_category_id', $gelondonganCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $kerupukKgItems = $kerupukKgCategory
            ? Item::where('item_category_id', $kerupukKgCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $kerupukPackItems = $kerupukPackCategory
            ? Item::where('item_category_id', $kerupukPackCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        return view('manufacturing.yield-guidelines.create', compact(
            'adonanItems',
            'gelondonganItems',
            'kerupukKgItems',
            'kerupukPackItems'
        ));
    }

    /**
     * Store a newly created yield guideline.
     */
    public function store(StoreYieldGuidelineRequest $request): RedirectResponse
    {
        $guideline = YieldGuideline::create($request->validated());

        return redirect()
            ->route('manufacturing.yield-guidelines.show', $guideline)
            ->with('success', 'Yield guideline created successfully.');
    }

    /**
     * Display the specified yield guideline.
     */
    public function show(YieldGuideline $yieldGuideline): View
    {
        $yieldGuideline->load(['fromItem', 'toItem']);

        return view('manufacturing.yield-guidelines.show', compact('yieldGuideline'));
    }

    /**
     * Show the form for editing the specified yield guideline.
     */
    public function edit(YieldGuideline $yieldGuideline): View
    {
        $yieldGuideline->load(['fromItem', 'toItem']);

        // Get items that can be used for yield guidelines
        $adonanCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $gelondonganCategory = ItemCategory::where('name', 'like', '%Gelondongan%')->first();
        $kerupukKgCategory = ItemCategory::where('name', 'like', '%Kerupuk Kg%')->first()
            ?? ItemCategory::where('name', 'like', '%Finished Products%')->first();
        $kerupukPackCategory = ItemCategory::where('name', 'like', '%Kerupuk Pack%')->first();

        $adonanItems = $adonanCategory
            ? Item::where('item_category_id', $adonanCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $gelondonganItems = $gelondonganCategory
            ? Item::where('item_category_id', $gelondonganCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $kerupukKgItems = $kerupukKgCategory
            ? Item::where('item_category_id', $kerupukKgCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        $kerupukPackItems = $kerupukPackCategory
            ? Item::where('item_category_id', $kerupukPackCategory->id)->where('is_active', true)->orderBy('name')->get()
            : collect([]);

        return view('manufacturing.yield-guidelines.edit', compact(
            'yieldGuideline',
            'adonanItems',
            'gelondonganItems',
            'kerupukKgItems',
            'kerupukPackItems'
        ));
    }

    /**
     * Update the specified yield guideline.
     */
    public function update(UpdateYieldGuidelineRequest $request, YieldGuideline $yieldGuideline): RedirectResponse
    {
        $yieldGuideline->update($request->validated());

        return redirect()
            ->route('manufacturing.yield-guidelines.show', $yieldGuideline)
            ->with('success', 'Yield guideline updated successfully.');
    }

    /**
     * Remove the specified yield guideline.
     */
    public function destroy(YieldGuideline $yieldGuideline): RedirectResponse
    {
        $fromItem = $yieldGuideline->fromItem->name ?? 'N/A';
        $toItem = $yieldGuideline->toItem->name ?? 'N/A';

        $yieldGuideline->delete();

        return redirect()
            ->route('manufacturing.yield-guidelines.index')
            ->with('success', "Yield guideline from '{$fromItem}' to '{$toItem}' deleted successfully.");
    }

    /**
     * Get items for a specific stage (AJAX).
     */
    public function getItemsForStage(Request $request): \Illuminate\Http\JsonResponse
    {
        $stage = $request->input('stage');

        if (!$stage) {
            return response()->json([]);
        }

        $items = match ($stage) {
            'adonan' => Item::whereHas('itemCategory', function ($q) {
                $q->where('name', 'like', '%Adonan%');
            })->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'gelondongan' => Item::whereHas('itemCategory', function ($q) {
                $q->where('name', 'like', '%Gelondongan%');
            })->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'kerupuk_kg' => Item::whereHas('itemCategory', function ($q) {
                $q->where('name', 'like', '%Kerupuk Kg%')
                    ->orWhere('name', 'like', '%Finished Products%');
            })->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'packing' => Item::whereHas('itemCategory', function ($q) {
                $q->where('name', 'like', '%Kerupuk Pack%');
            })->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            default => collect([]),
        };

        return response()->json($items);
    }
}
















