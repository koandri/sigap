<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\ShelfPosition;
use App\Models\PositionItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

final class ShelfInventoryController extends Controller
{
    public function __construct()
    {
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
        $warehouse->load(['shelves.shelfPositions.positionItems.item.itemCategory']);
        
        // Get shelf grid for visual layout (legacy support)
        $shelfGrid = $warehouse->shelf_grid;
        
        // Get shelf columns for 3-column layout
        $shelfColumns = $warehouse->shelf_columns;
        
        // Get statistics
        $stats = $warehouse->shelf_inventory_stats;

        return view('manufacturing.warehouses.shelf-inventory', compact(
            'warehouse', 'shelfGrid', 'shelfColumns', 'stats'
        ));
    }

    /**
     * Show specific shelf details with all positions.
     */
    public function showShelf(Warehouse $warehouse, WarehouseShelf $shelf): View
    {
        $shelf->load([
            'shelfPositions.positionItems.item.itemCategory',
            'shelfPositions.positionItems.lastUpdatedBy'
        ]);
        
        $availableItems = Item::active()
            ->whereNotIn('id', $shelf->shelfPositions()
                ->whereHas('positionItems', function($q) {
                    $q->where('quantity', '>', 0);
                })
                ->with('positionItems')
                ->get()
                ->pluck('positionItems')
                ->flatten()
                ->pluck('item_id')
                ->unique()
            )
            ->with('itemCategory')
            ->orderBy('name')
            ->get();

        return view('manufacturing.warehouses.shelf-detail', compact(
            'warehouse', 'shelf', 'availableItems'
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
            $item = Item::findOrFail($validated['item_id']);
            
            // Check if item is active
            if (!$item->is_active) {
                return back()->with('error', 'Selected item is not active and cannot be added.');
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
            
            // Try HTTP notification first
            $response = Http::timeout(10)->post('https://waha.suryagroup.app/api/sendText', [
                'session' => 'ptsiap',
                'chatId' => '12132132130@c.us', // This should be configurable
                'text' => $message
            ]);

            if (!$response->successful()) {
                throw new \Exception('HTTP notification failed');
            }
        } catch (\Exception $e) {
            // Fallback to email notification
            try {
                Mail::raw($message, function ($mail) use ($warehouse) {
                    $mail->to(config('mail.admin_email', 'admin@example.com'))
                         ->subject("Item Added to Warehouse - {$warehouse->name}");
                });
            } catch (\Exception $emailException) {
                // Log the error but don't fail the main operation
                \Log::error('Failed to send item added notification: ' . $emailException->getMessage());
            }
        }
    }
}
