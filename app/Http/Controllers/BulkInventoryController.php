<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\ShelfPosition;
use App\Models\PositionItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class BulkInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.inventory.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.inventory.edit')->only(['update', 'bulkUpdate', 'bulkAssign', 'bulkClear']);
    }

    /**
     * Display bulk edit interface for a specific warehouse
     */
    public function index(Warehouse $warehouse): View
    {
        // Get all available aisles for navigation
        $availableAisles = $warehouse->shelves()
            ->selectRaw('SUBSTRING(shelf_code, 1, 1) as aisle')
            ->distinct()
            ->orderBy('aisle')
            ->pluck('aisle');

        // Load only the first aisle initially to prevent memory issues
        $initialAisles = $availableAisles->take(1);
        
        // Get positions for initial aisles only
        $positions = collect();
        foreach ($initialAisles as $aisle) {
            $aislePositions = $warehouse->shelves()
                ->where('shelf_code', 'like', $aisle . '-%')
                ->with(['shelfPositions.positionItems.item', 'shelfPositions.positionItems.updatedBy'])
                ->orderBy('shelf_code')
                ->get()
                ->flatMap(function ($shelf) {
                    return $shelf->shelfPositions->map(function ($position) use ($shelf) {
                        $position->shelf_code = $shelf->shelf_code;
                        $position->aisle = $shelf->shelf_code ? substr($shelf->shelf_code, 0, 1) : 'Unknown';
                        return $position;
                    });
                });
            $positions = $positions->merge($aislePositions);
        }

        // Group by aisle for better organization
        $aisles = $positions->groupBy('aisle');
        
        // Get all items for dropdowns
        $items = Item::active()->whereIn('item_category_id', [12, 16, 7, 6])->orderBy('name')->get();

        return view('manufacturing.warehouses.bulk-edit', compact('warehouse', 'aisles', 'items', 'availableAisles'));
    }

    /**
     * Update a single position item via AJAX
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|exists:shelf_positions,id',
            'item_id' => 'nullable|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $position = ShelfPosition::findOrFail($request->position_id);
            
            // Verify position belongs to this warehouse
            if ($position->warehouseShelf->warehouse_id !== $warehouse->id) {
                return response()->json(['success' => false, 'message' => 'Position does not belong to this warehouse'], 403);
            }

            // If quantity is 0, remove the item
            if ($request->quantity == 0) {
                $position->positionItems()->delete();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from position',
                    'position' => $this->formatPosition($position)
                ]);
            }

            // If item_id is provided, create or update position item
            if ($request->item_id) {
                $positionItem = $position->positionItems()->firstOrNew(['item_id' => $request->item_id]);
                $positionItem->quantity = $request->quantity;
                $positionItem->expiry_date = $request->expiry_date;
                $positionItem->last_updated_by = auth()->id();
                $positionItem->last_updated_at = now();
                $positionItem->save();

                // Remove other items from this position
                $position->positionItems()->where('item_id', '!=', $request->item_id)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully',
                'position' => $this->formatPosition($position->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error updating position: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle bulk operations (assign, update, clear)
     */
    public function bulkUpdate(Request $request, Warehouse $warehouse)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:assign,update,clear',
            'position_ids' => 'required|array',
            'position_ids.*' => 'exists:shelf_positions,id',
            'item_id' => 'required_if:action,assign|nullable|exists:items,id',
            "quantity" => $request->action === "assign" ? "required|numeric|min:0" : "nullable|numeric|min:0",
            'expiry_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator->errors())
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $positions = ShelfPosition::whereIn('id', $request->position_ids)
                ->whereHas('warehouseShelf', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                })->get();

            if ($positions->count() !== count($request->position_ids)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Some positions do not belong to this warehouse'], 403);
                }
                
                return redirect()->back()
                    ->with('error', 'Some positions do not belong to this warehouse');
            }

            $updatedCount = 0;

            foreach ($positions as $position) {
                switch ($request->action) {
                    case 'assign':
                        if ($request->item_id && $request->quantity > 0) {
                            $positionItem = $position->positionItems()->firstOrNew(['item_id' => $request->item_id]);
                            $positionItem->quantity = $request->quantity;
                            $positionItem->expiry_date = $request->expiry_date;
                            $positionItem->last_updated_by = auth()->id();
                            $positionItem->last_updated_at = now();
                            $positionItem->save();

                            // Remove other items
                            $position->positionItems()->where('item_id', '!=', $request->item_id)->delete();
                            $updatedCount++;
                        }
                        break;

                    case "update":
                        $updateData = [
                            "last_updated_by" => auth()->id(),
                            "last_updated_at" => now()
                        ];
                        
                        if ($request->has("quantity") && $request->quantity !== null) {
                            if ($request->quantity == 0) {
                                $position->positionItems()->delete();
                            } else {
                                $updateData["quantity"] = $request->quantity;
                            }
                        }
                        
                        if ($request->has("expiry_date") && $request->expiry_date !== null) {
                            $updateData["expiry_date"] = $request->expiry_date;
                        }
                        
                        if (count($updateData) > 2) { // More than just last_updated_by and last_updated_at
                            $position->positionItems()->update($updateData);
                        }
                        
                        $updatedCount++;
                        break;
                        break;

                    case 'clear':
                        $position->positionItems()->delete();
                        $updatedCount++;
                        break;
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Bulk operation completed. {$updatedCount} positions updated.",
                    'updated_count' => $updatedCount
                ]);
            }
            
            return redirect()->back()
                ->with('success', "Bulk operation completed. {$updatedCount} positions updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error performing bulk operation: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error performing bulk operation: ' . $e->getMessage());
        }
    }

    /**
     * Get positions for a specific aisle
     */
    public function getAislePositions(Warehouse $warehouse, string $aisle): JsonResponse
    {
        $positions = $warehouse->shelves()
            ->where('shelf_code', 'like', $aisle . '-%')
            ->with(['shelfPositions.positionItems.item', 'shelfPositions.positionItems.updatedBy'])
            ->orderBy('shelf_code')
            ->get()
            ->flatMap(function ($shelf) use ($aisle) {
                return $shelf->shelfPositions->map(function ($position) use ($shelf, $aisle) {
                    $position->shelf_code = $shelf->shelf_code;
                    $position->aisle = $aisle;
                    return $position;
                });
            });

        
        // Get filtered items for dropdowns
        $items = Item::active()->whereIn('item_category_id', [12, 16, 7, 6])->orderBy('name')->get();
        return response()->json([
            "success" => true,
            "aisle" => $aisle,
            "positions" => $positions->map([$this, "formatPosition"]),
            "items" => $items->map(function($item) {
                return [
                    "id" => $item->id,
                    "name" => $item->name,
                    "shortname" => $item->shortname,
                    "unit" => $item->unit
                ];
            }),
            "count" => $positions->count()
        ]);
    }

    /**
     * Export warehouse inventory to Excel
     */
    public function export(Warehouse $warehouse): RedirectResponse
    {
        // This would implement Excel export functionality
        // For now, return a success message
        return redirect()
            ->route('manufacturing.warehouses.bulk-edit', $warehouse)
            ->with('success', 'Export functionality will be implemented in the next phase.');
    }

    /**
     * Format position data for JSON response
     */
    public function formatPosition(ShelfPosition $position): array
    {
        $currentItem = $position->positionItems()->where('quantity', '>', 0)->first();
        
        return [
            'id' => $position->id,
            'shelf_code' => $position->shelf_code,
            'position_code' => $position->position_code,
            'full_location' => $position->full_location_code,
            'aisle' => $position->aisle ?? ($position->shelf_code ? substr($position->shelf_code, 0, 1) : 'Unknown'),
            'current_item' => $currentItem ? [
                'id' => $currentItem->item_id,
                'name' => $currentItem->item->name,
                'quantity' => $currentItem->quantity,
                'expiry_date' => $currentItem->expiry_date?->format('Y-m-d'),
                'updated_at' => $currentItem->updated_at->format('Y-m-d H:i:s'),
                'updated_at_human' => $currentItem->updated_at->diffForHumans(),
                'updated_by_name' => $currentItem->updatedBy->name ?? 'Unknown'
            ] : null,
            'is_occupied' => $position->is_occupied
        ];
    }
}
