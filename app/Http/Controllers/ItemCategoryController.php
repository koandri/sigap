<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:options.item-categories.view')->only(['index', 'show']);
        $this->middleware('can:options.item-categories.create')->only(['create', 'store']);
        $this->middleware('can:options.item-categories.edit')->only(['edit', 'update']);
        $this->middleware('can:options.item-categories.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = ItemCategory::withCount('items')
            ->orderBy('name')
            ->paginate(15);

        return view('options.item-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('options.item-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30|unique:item_categories',
            'description' => 'nullable|string|max:500',
        ]);

        $category = ItemCategory::create($validated);

        return redirect()
            ->route('options.item-categories.index')
            ->with('success', "Item category '{$category->name}' created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemCategory $itemCategory): View
    {
        $itemCategory->load(['items' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('options.item-categories.show', compact('itemCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCategory $itemCategory): View
    {
        return view('options.item-categories.edit', compact('itemCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30|unique:item_categories,name,' . $itemCategory->id,
            'description' => 'nullable|string|max:500',
        ]);

        $itemCategory->update($validated);

        return redirect()
            ->route('options.item-categories.index')
            ->with('success', "Item category '{$itemCategory->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCategory $itemCategory): RedirectResponse
    {
        // Check if category has items
        if ($itemCategory->items()->count() > 0) {
            return redirect()
                ->route('options.item-categories.index')
                ->with('error', "Cannot delete category '{$itemCategory->name}' because it has items associated with it.");
        }

        $name = $itemCategory->name;
        $itemCategory->delete();

        return redirect()
            ->route('options.item-categories.index')
            ->with('success', "Item category '{$name}' deleted successfully.");
    }
}
