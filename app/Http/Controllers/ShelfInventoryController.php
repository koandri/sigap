<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\ShelfPosition;
use App\Models\PositionItem;
use App\Models\Item;
use App\Services\WhatsAppService;
use App\Services\PushoverService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ShelfInventoryController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {
        $this->middleware('can:manufacturing.inventory.view')->only(['index', 'showShelf']);
        $this->middleware('can:manufacturing.inventory.create')->only(['addItemToPosition']);
        $this->middleware('can:manufacturing.inventory.edit')->only(['updatePositionItem']);
        $this->middleware('can:manufacturing.inventory.delete')->only(['removeFromPosition']);
    }

    /**
     * Show shelf-based inventory management dashboard.
     */
    public function index(Warehouse $warehouse): View
    {
        // Eager load all necessary relationships with optimized queries
        $warehouse->load([
            'shelves' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('shelf_code')
                    ->withCount([
                        'shelfPositions as total_positions_count',
                        'shelfPositions as occupied_positions_count' => function ($q) {
                            $q->whereHas('positionItems', function ($subQ) {
                                $subQ->where('quantity', '>', 0);
                            });
                        }
                    ])
                    ->with(['shelfPositions' => function ($q) {
                        $q->withCount([
                            'positionItems as has_items' => function ($subQ) {
                                $subQ->where('quantity', '>', 0);
                            }
                        ]);
                    }]);
            }
        ]);
        
        // Get shelf columns using loaded relationships (no additional queries)
        $shelfColumns = $this->buildShelfColumns($warehouse->shelves);
        
        // Get shelf grid for visual layout (legacy support) - reuse loaded shelves
        $shelfGrid = $this->buildShelfGrid($warehouse->shelves);
        
        // Get statistics using efficient queries
        $stats = $this->calculateShelfInventoryStats($warehouse);

        return view('manufacturing.warehouses.shelf-inventory', compact(
            'warehouse', 'shelfGrid', 'shelfColumns', 'stats'
        ));
    }

    /**
     * Build shelf columns from loaded shelves collection.
     */
    private function buildShelfColumns($shelves): array
    {
        $columns = [
            'column_1' => [],
            'column_2' => [],
            'column_3' => []
        ];
        
        foreach ($shelves as $shelf) {
            // Parse shelf code format: A-01-04
            $parts = explode('-', $shelf->shelf_code);
            $row = $parts[0] ?? 'A';
            $section = (int) ($parts[1] ?? 1);
            
            // Create row-section combination key (A-01, A-02, etc.)
            $rowSectionKey = $row . '-' . str_pad((string)$section, 2, '0', STR_PAD_LEFT);
            
            // Determine which column this row-section belongs to
            $columnNumber = (($section - 1) % 3) + 1;
            $columnKey = "column_{$columnNumber}";
            
            // Group by row-section combination
            if (!isset($columns[$columnKey][$rowSectionKey])) {
                $columns[$columnKey][$rowSectionKey] = [];
            }
            
            $columns[$columnKey][$rowSectionKey][] = $shelf;
        }
        
        // Sort row-section combinations within each column
        foreach ($columns as $columnKey => $rowSections) {
            ksort($rowSections);
            $columns[$columnKey] = $rowSections;
        }
        
        return $columns;
    }

    /**
     * Build shelf grid from loaded shelves collection.
     */
    private function buildShelfGrid($shelves): array
    {
        $grid = [];
        
        foreach ($shelves as $shelf) {
            // Parse shelf code format: A-01-04
            $parts = explode('-', $shelf->shelf_code);
            $row = $parts[0] ?? 'A';
            $section = (int) ($parts[1] ?? 1);
            
            if (!isset($grid[$row])) {
                $grid[$row] = [];
            }
            
            if (!isset($grid[$row][$section])) {
                $grid[$row][$section] = [];
            }
            $grid[$row][$section][] = $shelf;
        }
        
        return $grid;
    }

    /**
     * Calculate shelf inventory statistics efficiently.
     */
    private function calculateShelfInventoryStats(Warehouse $warehouse): array
    {
        // Use a single query with joins and aggregations
        $stats = DB::table('warehouse_shelves')
            ->leftJoin('shelf_positions', 'warehouse_shelves.id', '=', 'shelf_positions.warehouse_shelf_id')
            ->leftJoin('position_items', function ($join) {
                $join->on('shelf_positions.id', '=', 'position_items.shelf_position_id')
                    ->where('position_items.quantity', '>', 0);
            })
            ->where('warehouse_shelves.warehouse_id', $warehouse->id)
            ->selectRaw('
                COUNT(DISTINCT warehouse_shelves.id) as total_shelves,
                COUNT(DISTINCT CASE WHEN position_items.id IS NOT NULL THEN warehouse_shelves.id END) as occupied_shelves,
                COUNT(DISTINCT shelf_positions.id) as total_positions,
                COUNT(DISTINCT CASE WHEN position_items.id IS NOT NULL THEN shelf_positions.id END) as occupied_positions
            ')
            ->first();

        // Get expiring items count
        $expiringItems = PositionItem::whereHas('shelfPosition.warehouseShelf', function($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })->expiring(30)->count();
        
        $totalPositions = (int) ($stats->total_positions ?? 0);
        $occupiedPositions = (int) ($stats->occupied_positions ?? 0);
        
        return [
            'total_shelves' => (int) ($stats->total_shelves ?? 0),
            'occupied_shelves' => (int) ($stats->occupied_shelves ?? 0),
            'total_positions' => $totalPositions,
            'occupied_positions' => $occupiedPositions,
            'expiring_items' => $expiringItems,
            'occupancy_rate' => $totalPositions > 0 ? round(($occupiedPositions / $totalPositions) * 100, 1) : 0
        ];
    }

    /**
     * Show specific shelf details with all positions.
     */
    public function showShelf(Warehouse $warehouse, WarehouseShelf $shelf): View
    {
        // Eager load all necessary relationships
        $shelf->load([
            'warehouse',
            'shelfPositions.warehouseShelf',
            'shelfPositions.positionItems.item.itemCategory',
            'shelfPositions.positionItems.lastUpdatedBy'
        ]);
        
        // Pre-calculate statistics from loaded relationships to avoid N+1 queries
        $shelfPositions = $shelf->shelfPositions;
        $totalPositions = $shelfPositions->count();
        
        $occupiedPositions = $shelfPositions->filter(function ($position) {
            return $position->positionItems->where('quantity', '>', 0)->isNotEmpty();
        })->count();
        
        $availablePositions = $totalPositions - $occupiedPositions;
        $occupancyRate = $totalPositions > 0 
            ? round(($occupiedPositions / $totalPositions) * 100, 1) 
            : 0;
        
        // Get item IDs that are already in this shelf (using subquery for efficiency)
        $existingItemIds = PositionItem::whereHas('shelfPosition', function($q) use ($shelf) {
                $q->where('warehouse_shelf_id', $shelf->id);
            })
            ->where('quantity', '>', 0)
            ->distinct()
            ->pluck('item_id');
        
        // Optimize availableItems query using subquery instead of loading everything
        $availableItems = Item::active()
            ->whereHas('itemCategory', function($q) {
                $q->where('name', 'Kerupuk Pack');
            })
            ->whereNotIn('id', $existingItemIds)
            ->with('itemCategory')
            ->orderBy('name')
            ->get();

        return view('manufacturing.warehouses.shelf-detail', compact(
            'warehouse', 
            'shelf', 
            'availableItems',
            'totalPositions',
            'occupiedPositions',
            'availablePositions',
            'occupancyRate'
        ));
    }

    /**
     * Add item to a specific position.
     */
    public function addItemToPosition(Request $request, Warehouse $warehouse, ShelfPosition $position): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.001|max:999999.999',
            'expiry_date' => 'nullable|date|after_or_equal:today'
        ]);

        // Check if position is already occupied
        if ($position->is_occupied) {
            return back()->with('error', 'Position is already occupied. Please choose an empty position.');
        }

        // Check if position belongs to the warehouse
        if ($position->warehouseShelf->warehouse_id !== $warehouse->id) {
            return back()->with('error', 'Position does not belong to this warehouse.');
        }

        // Check if position is active
        if (!$position->is_active) {
            return back()->with('error', 'Position is not active and cannot be used.');
        }

        try {
            DB::beginTransaction();

            // Get the item details for validation
            $item = Item::with('itemCategory')->findOrFail($validated['item_id']);
            
            // Check if item is active
            if (!$item->is_active) {
                return back()->with('error', 'Selected item is not active and cannot be added.');
            }

            // Check if item belongs to 'Kerupuk Pack' category
            if (!$item->itemCategory || $item->itemCategory->name !== 'Kerupuk Pack') {
                return back()->with('error', 'Only items from Kerupuk Pack category can be added to shelf positions.');
            }

            // Create the position item record
            $positionItem = PositionItem::create([
                'shelf_position_id' => $position->id,
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'expiry_date' => $validated['expiry_date'],
                'last_updated_by' => Auth::id(),
                'last_updated_at' => now()
            ]);

            DB::commit();

            // Send notification if configured
            $this->sendItemAddedNotification($warehouse, $position, $item, $positionItem);

            return redirect()
                ->route('manufacturing.warehouses.shelf-detail', [$warehouse, $position->warehouseShelf])
                ->with('success', "Item '{$item->name}' added to position {$position->full_location_code} successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to add item: ' . $e->getMessage());
        }
    }

    /**
     * Update item in a specific position.
     */
    public function updatePositionItem(Request $request, Warehouse $warehouse, PositionItem $positionItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();

            $positionItem->update([
                'quantity' => $validated['quantity'],
                'expiry_date' => $validated['expiry_date'],
                'last_updated_by' => Auth::id(),
                'last_updated_at' => now()
            ]);

            // If quantity is 0, remove the item
            if ($validated['quantity'] == 0) {
                $positionItem->delete();
            }

            DB::commit();

            return back()->with('success', 'Position item updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    /**
     * Remove item from position.
     */
    public function removeFromPosition(Warehouse $warehouse, PositionItem $positionItem): RedirectResponse
    {
        $shelf = $positionItem->shelfPosition->warehouseShelf;
        $itemName = $positionItem->item->name;
        
        $positionItem->delete();

        return redirect()
            ->route('manufacturing.warehouses.shelf-detail', [$warehouse, $shelf])
            ->with('success', "Item '{$itemName}' removed from position successfully.");
    }

    /**
     * Move item from one position to another.
     */
    public function moveItem(Request $request, Warehouse $warehouse, PositionItem $positionItem): RedirectResponse
    {
        $validated = $request->validate([
            'target_position_id' => 'required|exists:shelf_positions,id',
            'quantity' => 'required|numeric|min:0.001|max:' . $positionItem->quantity
        ]);

        $targetPosition = ShelfPosition::findOrFail($validated['target_position_id']);

        // Check if target position is available
        if ($targetPosition->is_occupied) {
            return back()->with('error', 'Target position is already occupied.');
        }

        try {
            DB::beginTransaction();

            // Create new position item
            PositionItem::create([
                'shelf_position_id' => $targetPosition->id,
                'item_id' => $positionItem->item_id,
                'quantity' => $validated['quantity'],
                'expiry_date' => $positionItem->expiry_date,
                'last_updated_by' => Auth::id(),
                'last_updated_at' => now()
            ]);

            // Update source position
            $remainingQuantity = $positionItem->quantity - $validated['quantity'];
            if ($remainingQuantity > 0) {
                $positionItem->update([
                    'quantity' => $remainingQuantity,
                    'last_updated_by' => Auth::id(),
                    'last_updated_at' => now()
                ]);
            } else {
                $positionItem->delete();
            }

            DB::commit();

            return back()->with('success', 'Item moved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to move item: ' . $e->getMessage());
        }
    }

    /**
     * Get available positions for moving items.
     */
    public function getAvailablePositions(Warehouse $warehouse, PositionItem $positionItem): \Illuminate\Http\JsonResponse
    {
        $availablePositions = ShelfPosition::whereHas('warehouseShelf', function($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })
        ->where('id', '!=', $positionItem->shelf_position_id)
        ->whereDoesntHave('positionItems', function($q) {
            $q->where('quantity', '>', 0);
        })
        ->with('warehouseShelf')
        ->get()
        ->map(function($position) {
            return [
                'id' => $position->id,
                'code' => $position->full_location_code,
                'name' => $position->full_location_name
            ];
        });

        return response()->json($availablePositions);
    }

    /**
     * Bulk update multiple positions.
     */
    public function bulkUpdate(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.position_id' => 'required|exists:shelf_positions,id',
            'updates.*.quantity' => 'required|numeric|min:0',
            'updates.*.expiry_date' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['updates'] as $update) {
                $position = ShelfPosition::findOrFail($update['position_id']);
                $positionItem = $position->positionItems()->where('quantity', '>', 0)->first();

                if ($positionItem) {
                    if ($update['quantity'] > 0) {
                        $positionItem->update([
                            'quantity' => $update['quantity'],
                            'expiry_date' => $update['expiry_date'],
                            'last_updated_by' => Auth::id(),
                            'last_updated_at' => now()
                        ]);
                    } else {
                        $positionItem->delete();
                    }
                }
            }

            DB::commit();

            return back()->with('success', 'Bulk update completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to perform bulk update: ' . $e->getMessage());
        }
    }

    /**
     * Get warehouse inventory report.
     */
    public function report(Warehouse $warehouse): View
    {
        $warehouse->load([
            'shelves.shelfPositions.positionItems.item.itemCategory',
            'shelves.shelfPositions.positionItems.lastUpdatedBy'
        ]);

        $stats = $warehouse->shelf_inventory_stats;
        
        $expiringItems = PositionItem::whereHas('shelfPosition.warehouseShelf', function($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })->expiring(30)->with(['item.itemCategory', 'shelfPosition.warehouseShelf'])->get();

        $expiredItems = PositionItem::whereHas('shelfPosition.warehouseShelf', function($q) use ($warehouse) {
            $q->where('warehouse_id', $warehouse->id);
        })->expired()->with(['item.itemCategory', 'shelfPosition.warehouseShelf'])->get();

        return view('manufacturing.warehouses.shelf-report', compact(
            'warehouse', 'stats', 'expiringItems', 'expiredItems'
        ));
    }

    /**
     * Send notification when item is added to position.
     */
    private function sendItemAddedNotification(Warehouse $warehouse, ShelfPosition $position, Item $item, PositionItem $positionItem): void
    {
        try {
            $message = "Item '{$item->name}' (Qty: {$positionItem->quantity} {$item->unit}) has been added to position {$position->full_location_code} in warehouse '{$warehouse->name}' by " . Auth::user()->name;
            
            $groupId = env('WAREHOUSE_WHATSAPP_GROUP');
            
            if (!$groupId) {
                Log::warning('WAREHOUSE_WHATSAPP_GROUP not configured');
                return;
            }
            
            // Try WhatsApp notification first
            $waSuccess = $this->whatsAppService->sendMessage($groupId, $message);

            if (!$waSuccess) {
                // Fallback to Pushover notification
                $this->pushoverService->sendWhatsAppFailureNotification(
                    'Warehouse Item Added',
                    $groupId,
                    $message
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send item added notification: ' . $e->getMessage());
        }
    }
}
