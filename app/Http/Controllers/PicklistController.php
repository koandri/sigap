<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Item;
use App\Models\PositionItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class PicklistController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.inventory.view')->only(['index', 'generate']);
    }

    /**
     * Show the global picklist generation form (across all warehouses).
     */
    public function index(): View
    {
        // Get all warehouses
        $warehouses = Warehouse::active()->orderBy('name')->get();
        
        // Get all available items across all warehouses
        $availableItems = Item::whereHas('positionItems.shelfPosition.warehouseShelf', function($q) {
            $q->whereHas('warehouse', function($warehouseQuery) {
                $warehouseQuery->where('is_active', true);
            });
        })
        ->with(['itemCategory', 'positionItems.shelfPosition.warehouseShelf.warehouse'])
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        return view('manufacturing.warehouses.picklist', compact('warehouses', 'availableItems'));
    }

    /**
     * Generate picklist based on expiry dates (FIFO) across all warehouses.
     */
    public function generate(Request $request): View
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        $picklistItems = collect($validated['items']);
        $picklistResults = collect();
        $unfulfilledItems = collect();

        foreach ($picklistItems as $requestedItem) {
            $item = Item::findOrFail($requestedItem['item_id']);
            $requestedQuantity = (float) $requestedItem['quantity'];
            
            // Get all positions for this item across ALL warehouses, ordered by expiry date (FIFO)
            $availablePositions = PositionItem::whereHas('shelfPosition.warehouseShelf', function($q) {
                $q->whereHas('warehouse', function($warehouseQuery) {
                    $warehouseQuery->where('is_active', true);
                });
            })
            ->where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->with(['shelfPosition.warehouseShelf.warehouse', 'item'])
            ->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'asc') // Secondary sort for items without expiry dates
            ->get();

            $remainingQuantity = $requestedQuantity;
            $itemPicklist = collect();
            $totalAvailable = $availablePositions->sum('quantity');

            if ($totalAvailable < $requestedQuantity) {
                $unfulfilledItems->push([
                    'item' => $item,
                    'requested' => $requestedQuantity,
                    'available' => $totalAvailable,
                    'shortage' => $requestedQuantity - $totalAvailable
                ]);
            }

            foreach ($availablePositions as $position) {
                if ($remainingQuantity <= 0) break;

                $quantityToTake = min($remainingQuantity, $position->quantity);
                
                $itemPicklist->push([
                    'position' => $position,
                    'quantity_to_take' => $quantityToTake,
                    'remaining_after' => $position->quantity - $quantityToTake,
                    'expiry_date' => $position->expiry_date,
                    'days_until_expiry' => $position->expiry_date ? now()->diffInDays($position->expiry_date, false) : null,
                    'location' => $position->shelfPosition->full_location_code,
                    'shelf' => $position->shelfPosition->warehouseShelf->shelf_code,
                    'warehouse' => $position->shelfPosition->warehouseShelf->warehouse
                ]);

                $remainingQuantity -= $quantityToTake;
            }

            $picklistResults->push([
                'item' => $item,
                'requested_quantity' => $requestedQuantity,
                'picklist_positions' => $itemPicklist,
                'total_pickable' => $itemPicklist->sum('quantity_to_take'),
                'shortage' => max(0, $requestedQuantity - $itemPicklist->sum('quantity_to_take'))
            ]);
        }

        // Calculate summary statistics
        $summary = [
            'total_items_requested' => $picklistItems->count(),
            'total_quantity_requested' => $picklistItems->sum('quantity'),
            'total_quantity_pickable' => $picklistResults->sum('total_pickable'),
            'total_shortage' => $picklistResults->sum('shortage'),
            'items_with_shortage' => $picklistResults->where('shortage', '>', 0)->count(),
            'expiring_soon_count' => $picklistResults->sum(function($item) {
                return $item['picklist_positions']->where('days_until_expiry', '<=', 7)->count();
            }),
            'expired_count' => $picklistResults->sum(function($item) {
                return $item['picklist_positions']->where('days_until_expiry', '<', 0)->count();
            })
        ];

        return view('manufacturing.warehouses.picklist-results', compact(
            'picklistResults', 'unfulfilledItems', 'summary'
        ));
    }
}
