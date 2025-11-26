<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\ShelfPosition;
use App\Models\PositionItem;
use App\Services\ItemDropdownService;
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
        $this->middleware('can:warehouses.inventory.view')->only(['index', 'show']);
        $this->middleware('can:warehouses.inventory.edit')->only(['update', 'bulkUpdate', 'bulkAssign', 'bulkClear', 'saveAllChanges']);
    }

    /**
     * Display bulk edit interface for a specific warehouse
     */
    public function index(Warehouse $warehouse, ItemDropdownService $itemDropdowns): View
    {
        // Get all available aisles for navigation
        $availableAisles = $warehouse->shelves()
            ->selectRaw('SUBSTRING(shelf_code, 1, 1) as aisle')
            ->distinct()
            ->orderBy('aisle')
            ->pluck('aisle');

        // No positions loaded initially - lazy load via AJAX when aisle is selected
        $aisles = collect();
        
        // Get all items for dropdowns - only from 'Kerupuk Pack' category
        $items = $itemDropdowns->forCategoryIds(
            \App\Models\ItemCategory::where('name', 'like', '%Kerupuk Pack%')->pluck('id')->all()
        );

        return view('warehouses.warehouses.bulk-edit', compact('warehouse', 'aisles', 'items', 'availableAisles'));
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
        // Optimized query: only load active positions and positionItems with quantity > 0
        $positions = $warehouse->shelves()
            ->where('shelf_code', 'like', $aisle . '-%')
            ->with([
                'shelfPositions' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('position_code')
                          ->with('warehouseShelf'); // Eager load warehouseShelf for full_location_code accessor
                },
                'shelfPositions.positionItems' => function($query) {
                    // Only load positionItems with quantity > 0 to reduce data
                    $query->where('quantity', '>', 0)
                          ->with(['item', 'updatedBy']);
                }
            ])
            ->orderBy('shelf_code')
            ->get()
            ->flatMap(function ($shelf) use ($aisle) {
                return $shelf->shelfPositions->map(function ($position) use ($shelf, $aisle) {
                    // Pre-calculate current_item from eager loaded data
                    $position->current_item = $position->positionItems->first();
                    $position->shelf_code = $shelf->shelf_code;
                    $position->aisle = $aisle;
                    return $position;
                });
            });

        
        // Get filtered items for dropdowns - only from 'Kerupuk Pack' category
        $items = Item::active()
            ->whereHas('itemCategory', function($q) {
                $q->where('name', 'Kerupuk Pack');
            })
            ->orderBy('name')
            ->get();
        return response()->json([
            "success" => true,
            "aisle" => $aisle,
            "positions" => $positions->map([$this, "formatPosition"]),
            "items" => $items->map(function($item) {
                return [
                    "id" => $item->id,
                    "name" => $item->name,
                    "label" => $item->label,
                    "shortname" => $item->shortname,
                    "unit" => $item->unit
                ];
            }),
            "count" => $positions->count()
        ]);
    }

    /**
     * Save all changes for multiple positions
     */
    public function saveAllChanges(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'positions' => 'required|array',
            'positions.*.position_id' => 'required|exists:shelf_positions,id',
            'positions.*.item_id' => 'nullable|exists:items,id',
            'positions.*.quantity' => 'required|numeric|min:0',
            'positions.*.expiry_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $errors = [];

            foreach ($request->positions as $index => $positionData) {
                try {
                    $position = ShelfPosition::findOrFail($positionData['position_id']);
                    
                    // Verify position belongs to this warehouse
                    if ($position->warehouseShelf->warehouse_id !== $warehouse->id) {
                        $errors[] = "Position {$positionData['position_id']} does not belong to this warehouse";
                        continue;
                    }

                    // If quantity is 0, remove the item
                    if ($positionData['quantity'] == 0) {
                        $position->positionItems()->delete();
                        $updatedCount++;
                        continue;
                    }

                    // If item_id is provided, create or update position item
                    if (!empty($positionData['item_id'])) {
                        $positionItem = $position->positionItems()->firstOrNew(['item_id' => $positionData['item_id']]);
                        $positionItem->quantity = $positionData['quantity'];
                        $positionItem->expiry_date = $positionData['expiry_date'] ?? null;
                        $positionItem->last_updated_by = auth()->id();
                        $positionItem->last_updated_at = now();
                        $positionItem->save();

                        // Remove other items from this position
                        $position->positionItems()->where('item_id', '!=', $positionData['item_id'])->delete();
                        $updatedCount++;
                    } else {
                        // If no item_id but quantity > 0, skip (invalid state)
                        $errors[] = "Position {$positionData['position_id']} has quantity but no item selected";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error updating position {$positionData['position_id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully updated {$updatedCount} position(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error saving changes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export warehouse inventory to Excel
     */
    public function export(Warehouse $warehouse): RedirectResponse
    {
        // This would implement Excel export functionality
        // For now, return a success message
        return redirect()
            ->route('warehouses.warehouses.bulk-edit', $warehouse)
            ->with('success', 'Export functionality will be implemented in the next phase.');
    }

    /**
     * Format position data for JSON response
     */
    public function formatPosition(ShelfPosition $position): array
    {
        // Use pre-calculated current_item from eager loaded data to avoid N+1 queries
        $currentItem = $position->current_item ?? ($position->positionItems->where('quantity', '>', 0)->first() ?? null);
        
        // Get shelf code - use property if set, otherwise get from relationship
        $shelfCode = $position->shelf_code ?? ($position->warehouseShelf->shelf_code ?? 'Unknown');
        $aisle = $position->aisle ?? ($shelfCode !== 'Unknown' ? substr($shelfCode, 0, 1) : 'Unknown');
        $fullLocation = $position->full_location_code ?? ($shelfCode . '-' . $position->position_code);
        
        return [
            'id' => $position->id,
            'shelf_code' => $shelfCode,
            'position_code' => $position->position_code,
            'full_location' => $fullLocation,
            'aisle' => $aisle,
            'current_item' => $currentItem ? [
                'id' => $currentItem->item_id,
                'name' => $currentItem->item->name ?? 'Unknown',
                'quantity' => $currentItem->quantity,
                'expiry_date' => $currentItem->expiry_date?->format('Y-m-d'),
                'updated_at' => $currentItem->updated_at->format('Y-m-d H:i:s'),
                'updated_at_human' => $currentItem->updated_at->diffForHumans(),
                'updated_by_name' => $currentItem->updatedBy->name ?? 'Unknown'
            ] : null,
            'is_occupied' => $currentItem !== null
        ];
    }
}
