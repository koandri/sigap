<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\ShelfPosition;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

final class ShelfManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.inventory.view')->only(['index', 'showShelf']);
        $this->middleware('can:manufacturing.inventory.create')->only(['createShelf', 'storeShelf', 'createPosition', 'storePosition']);
        $this->middleware('can:manufacturing.inventory.edit')->only(['editShelf', 'updateShelf', 'editPosition', 'updatePosition']);
        $this->middleware('can:manufacturing.inventory.delete')->only(['destroyShelf', 'destroyPosition']);
    }

    /**
     * Display shelf management for a warehouse.
     */
    public function index(Warehouse $warehouse): View
    {
        $shelves = $warehouse->shelves()
            ->withCount('shelfPositions')
            ->orderBy('shelf_code')
            ->get();

        return view('manufacturing.warehouses.shelf-management', compact('warehouse', 'shelves'));
    }

    /**
     * Show the form for creating a new shelf.
     */
    public function createShelf(Warehouse $warehouse): View
    {
        return view('manufacturing.warehouses.shelf-create', compact('warehouse'));
    }

    /**
     * Store a newly created shelf.
     */
    public function storeShelf(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'shelf_code' => 'required|string|max:10|unique:warehouse_shelves,shelf_code,NULL,id,warehouse_id,' . $warehouse->id,
            'shelf_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'max_capacity' => 'required|integer|min:1|max:20',
        ]);

        $shelf = $warehouse->shelves()->create($validated);

        return redirect()
            ->route('manufacturing.warehouses.shelf-management', $warehouse)
            ->with('success', "Shelf '{$shelf->shelf_code}' created successfully.");
    }

    /**
     * Show the form for editing a shelf.
     */
    public function editShelf(Warehouse $warehouse, WarehouseShelf $shelf): View
    {
        return view('manufacturing.warehouses.shelf-edit', compact('warehouse', 'shelf'));
    }

    /**
     * Update the specified shelf.
     */
    public function updateShelf(Request $request, Warehouse $warehouse, WarehouseShelf $shelf): RedirectResponse
    {
        $validated = $request->validate([
            'shelf_code' => 'required|string|max:10|unique:warehouse_shelves,shelf_code,' . $shelf->id . ',id,warehouse_id,' . $warehouse->id,
            'shelf_name' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'max_capacity' => 'required|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        $shelf->update($validated);

        return redirect()
            ->route('manufacturing.warehouses.shelf-management', $warehouse)
            ->with('success', "Shelf '{$shelf->shelf_code}' updated successfully.");
    }

    /**
     * Remove the specified shelf.
     */
    public function destroyShelf(Warehouse $warehouse, WarehouseShelf $shelf): RedirectResponse
    {
        // Check if shelf has positions with items
        if ($shelf->shelfPositions()->whereHas('positionItems', function($q) {
            $q->where('quantity', '>', 0);
        })->exists()) {
            return redirect()
                ->route('manufacturing.warehouses.shelf-management', $warehouse)
                ->with('error', "Cannot delete shelf '{$shelf->shelf_code}' because it has items in positions.");
        }

        $shelfCode = $shelf->shelf_code;
        $shelf->delete();

        return redirect()
            ->route('manufacturing.warehouses.shelf-management', $warehouse)
            ->with('success', "Shelf '{$shelfCode}' deleted successfully.");
    }

    /**
     * Display positions for a specific shelf.
     */
    public function showShelf(Warehouse $warehouse, WarehouseShelf $shelf): View
    {
        $positions = $shelf->shelfPositions()
            ->with(['positionItems.item'])
            ->orderBy('position_code')
            ->get();

        return view('manufacturing.warehouses.shelf-positions', compact('warehouse', 'shelf', 'positions'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function createPosition(Warehouse $warehouse, WarehouseShelf $shelf): View
    {
        return view('manufacturing.warehouses.position-create', compact('warehouse', 'shelf'));
    }

    /**
     * Store a newly created position.
     */
    public function storePosition(Request $request, Warehouse $warehouse, WarehouseShelf $shelf): RedirectResponse
    {
        $validated = $request->validate([
            'position_code' => 'required|string|max:2|unique:shelf_positions,position_code,NULL,id,warehouse_shelf_id,' . $shelf->id,
            'position_name' => 'required|string|max:20',
            'max_capacity' => 'required|integer|min:1|max:10',
        ]);

        $position = $shelf->shelfPositions()->create($validated);

        return redirect()
            ->route('manufacturing.warehouses.shelf-positions', [$warehouse, $shelf])
            ->with('success', "Position '{$position->position_code}' created successfully.");
    }

    /**
     * Show the form for editing a position.
     */
    public function editPosition(Warehouse $warehouse, WarehouseShelf $shelf, ShelfPosition $position): View
    {
        return view('manufacturing.warehouses.position-edit', compact('warehouse', 'shelf', 'position'));
    }

    /**
     * Update the specified position.
     */
    public function updatePosition(Request $request, Warehouse $warehouse, WarehouseShelf $shelf, ShelfPosition $position): RedirectResponse
    {
        $validated = $request->validate([
            'position_code' => 'required|string|max:2|unique:shelf_positions,position_code,' . $position->id . ',id,warehouse_shelf_id,' . $shelf->id,
            'position_name' => 'required|string|max:20',
            'max_capacity' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()
            ->route('manufacturing.warehouses.shelf-positions', [$warehouse, $shelf])
            ->with('success', "Position '{$position->position_code}' updated successfully.");
    }

    /**
     * Remove the specified position.
     */
    public function destroyPosition(Warehouse $warehouse, WarehouseShelf $shelf, ShelfPosition $position): RedirectResponse
    {
        // Check if position has items
        if ($position->positionItems()->where('quantity', '>', 0)->exists()) {
            return redirect()
                ->route('manufacturing.warehouses.shelf-positions', [$warehouse, $shelf])
                ->with('error', "Cannot delete position '{$position->position_code}' because it has items.");
        }

        $positionCode = $position->position_code;
        $position->delete();

        return redirect()
            ->route('manufacturing.warehouses.shelf-positions', [$warehouse, $shelf])
            ->with('success', "Position '{$positionCode}' deleted successfully.");
    }
}