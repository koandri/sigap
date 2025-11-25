<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\PositionItem;
use App\Models\ShelfPosition;
use Illuminate\View\View;

final class WarehouseDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:warehouses.dashboard.view')->only(['index']);
    }

    /**
     * Display the warehouse dashboard.
     */
    public function index(): View
    {
        // Get key statistics for the dashboard
        $stats = [
            'total_items' => Item::active()->count(),
            'total_categories' => ItemCategory::count(),
            'total_warehouses' => Warehouse::active()->count(),
            'total_positions' => PositionItem::where('quantity', '>', 0)->count(),
            'total_locations' => ShelfPosition::active()->count(),
        ];

        // Get items by category for quick overview
        $itemsByCategory = ItemCategory::withCount('items')->get();

        // Get items expiring soon (next 30 days)
        $expiringItems = PositionItem::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->with(['item', 'shelfPosition.warehouseShelf.warehouse'])
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Get warehouses with stock counts
        $warehousesWithStock = Warehouse::active()
            ->withCount(['shelves as stocked_shelves_count' => function ($query) {
                $query->whereHas('shelfPositions.positionItems', function($q) {
                    $q->where('quantity', '>', 0);
                });
            }])
            ->get();

        return view('warehouses.dashboard', compact(
            'stats',
            'itemsByCategory',
            'expiringItems',
            'warehousesWithStock'
        ));
    }
}


