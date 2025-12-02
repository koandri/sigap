<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreYieldGuidelineRequest;
use App\Http\Requests\UpdateYieldGuidelineRequest;
use App\Models\Item;
use App\Models\YieldGuideline;
use App\Services\ItemDropdownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class YieldGuidelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.yield-guidelines.view')->only(['index', 'show', 'getItemsForStage']);
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
    public function create(ItemDropdownService $itemDropdowns): View
    {
        // Get items that can be used for yield guidelines (id => label)
        $adonanItems = $itemDropdowns->forDoughItems();
        $gelondonganItems = $itemDropdowns->forGelondonganItems();
        $kerupukKgItems = $itemDropdowns->forKerupukKgItems();
        $kerupukPackItems = $itemDropdowns->forKerupukPackItems();

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
    public function edit(YieldGuideline $yieldGuideline, ItemDropdownService $itemDropdowns): View
    {
        $yieldGuideline->load(['fromItem', 'toItem']);

        // Get items that can be used for yield guidelines (id => label)
        $adonanItems = $itemDropdowns->forDoughItems();
        $gelondonganItems = $itemDropdowns->forGelondonganItems();
        $kerupukKgItems = $itemDropdowns->forKerupukKgItems();
        $kerupukPackItems = $itemDropdowns->forKerupukPackItems();

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
    public function getItemsForStage(Request $request, ItemDropdownService $itemDropdowns): \Illuminate\Http\JsonResponse
    {
        $stage = $request->input('stage');

        if (!$stage) {
            return response()->json([]);
        }

        $options = match ($stage) {
            'adonan' => $itemDropdowns->forDoughItems(),
            'gelondongan' => $itemDropdowns->forGelondonganItems(),
            'kerupuk_kg' => $itemDropdowns->forKerupukKgItems(),
            'packing' => $itemDropdowns->forKerupukPackItems(),
            default => collect(),
        };

        // Normalize to array of {id, name, label} for consumers
        $items = $options->map(
            static fn (string $label, int $id): array => [
                'id' => $id,
                'name' => $label,
                'label' => $label,
            ]
        )->values();

        return response()->json($items);
    }
}
















