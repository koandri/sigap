<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

final class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.warehouses.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.warehouses.create')->only(['create', 'store']);
        $this->middleware('can:manufacturing.warehouses.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.warehouses.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $warehouses = Warehouse::withCount([
            'shelves as total_shelves',
            'shelves as occupied_shelves' => function($query) {
                $query->whereHas('shelfPositions.positionItems');
            }
        ])->orderBy('name')->paginate(15);

        return view('manufacturing.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('manufacturing.warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:warehouses',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()
            ->route('manufacturing.warehouses.index')
            ->with('success', "Warehouse '{$warehouse->name}' created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse): View
    {
        $shelfStats = $warehouse->shelf_inventory_stats;
        
        return view('manufacturing.warehouses.show', compact('warehouse', 'shelfStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse): View
    {
        return view('manufacturing.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()
            ->route('manufacturing.warehouses.index')
            ->with('success', "Warehouse '{$warehouse->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        // Check if warehouse has items with stock
        if ($warehouse->shelves()->whereHas('shelfPositions.positionItems', function($q) {
            $q->where('quantity', '>', 0);
        })->exists()) {
            return redirect()
                ->route('manufacturing.warehouses.index')
                ->with('error', "Cannot delete warehouse '{$warehouse->name}' because it has items with current stock.");
        }

        $name = $warehouse->name;
        $warehouse->delete();

        return redirect()
            ->route('manufacturing.warehouses.index')
            ->with('success', "Warehouse '{$name}' deleted successfully.");
    }

}
