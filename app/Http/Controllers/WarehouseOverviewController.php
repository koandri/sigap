<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\PositionItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

final class WarehouseOverviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:warehouses.inventory.view')->only(['index']);
    }

    /**
     * Show warehouse overview report with all items across all warehouses.
     */
    public function index(Request $request): View
    {
        $query = PositionItem::with([
            'item.itemCategory',
            'shelfPosition.warehouseShelf.warehouse',
            'lastUpdatedBy'
        ])
        ->where('quantity', '>', 0);

        // Apply filters
        if ($request->filled('warehouse')) {
            $query->whereHas('shelfPosition.warehouseShelf', function($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse);
            });
        }

        if ($request->filled('item_name')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('item.itemCategory', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('expiry_filter')) {
            $today = now()->startOfDay();
            switch ($request->expiry_filter) {
                case 'expired':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<', $today);
                    break;
                case 'expiring_7':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', $today)
                          ->where('expiry_date', '<=', $today->copy()->addDays(7));
                    break;
                case 'expiring_30':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', $today)
                          ->where('expiry_date', '<=', $today->copy()->addDays(30));
                    break;
                case 'no_expiry':
                    $query->whereNull('expiry_date');
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'item_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        switch ($sortBy) {
            case 'item_name':
                $query->join('items', 'position_items.item_id', '=', 'items.id')
                      ->orderBy('items.name', $sortDirection);
                break;
            case 'warehouse':
                $query->join('warehouse_shelves', 'shelf_positions.warehouse_shelf_id', '=', 'warehouse_shelves.id')
                      ->join('warehouses', 'warehouse_shelves.warehouse_id', '=', 'warehouses.id')
                      ->orderBy('warehouses.name', $sortDirection);
                break;
            case 'expiry_date':
                $query->orderBy('expiry_date', $sortDirection === 'asc' ? 'asc' : 'desc');
                break;
            case 'quantity':
                $query->orderBy('quantity', $sortDirection);
                break;
            case 'location':
                $query->join('shelf_positions', 'position_items.shelf_position_id', '=', 'shelf_positions.id')
                      ->orderBy('shelf_positions.position_code', $sortDirection);
                break;
            default:
                $query->join('items', 'position_items.item_id', '=', 'items.id')
                      ->orderBy('items.name', 'asc');
        }

        // Get the results
        $items = $query->select('position_items.*')->paginate(50);

        // Get filter options
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $categories = \App\Models\ItemCategory::whereHas('items.positionItems', function($q) {
            $q->where('quantity', '>', 0);
        })->orderBy('name')->get();

        // Get summary statistics
        $summary = $this->getSummaryStatistics($request);

        return view('warehouses.warehouses.overview-report', compact(
            'items', 'warehouses', 'categories', 'summary'
        ));
    }

    /**
     * Show warehouse overview report for printing.
     */
    public function print(Request $request): View
    {
        $query = PositionItem::with([
            'item.itemCategory',
            'shelfPosition.warehouseShelf.warehouse',
            'lastUpdatedBy'
        ])
        ->where('quantity', '>', 0);

        // Apply filters
        if ($request->filled('warehouse')) {
            $query->whereHas('shelfPosition.warehouseShelf', function($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse);
            });
        }

        if ($request->filled('item_name')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('item.itemCategory', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('expiry_filter')) {
            $today = now()->startOfDay();
            switch ($request->expiry_filter) {
                case 'expired':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<', $today);
                    break;
                case 'expiring_7':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', $today)
                          ->where('expiry_date', '<=', $today->copy()->addDays(7));
                    break;
                case 'expiring_30':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '>=', $today)
                          ->where('expiry_date', '<=', $today->copy()->addDays(30));
                    break;
                case 'no_expiry':
                    $query->whereNull('expiry_date');
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'item_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        switch ($sortBy) {
            case 'item_name':
                $query->join('items', 'position_items.item_id', '=', 'items.id')
                      ->orderBy('items.name', $sortDirection);
                break;
            case 'warehouse':
                $query->join('warehouse_shelves', 'shelf_positions.warehouse_shelf_id', '=', 'warehouse_shelves.id')
                      ->join('warehouses', 'warehouse_shelves.warehouse_id', '=', 'warehouses.id')
                      ->orderBy('warehouses.name', $sortDirection);
                break;
            case 'expiry_date':
                $query->orderBy('expiry_date', $sortDirection === 'asc' ? 'asc' : 'desc');
                break;
            case 'quantity':
                $query->orderBy('quantity', $sortDirection);
                break;
            case 'location':
                $query->join('shelf_positions', 'position_items.shelf_position_id', '=', 'shelf_positions.id')
                      ->orderBy('shelf_positions.position_code', $sortDirection);
                break;
            default:
                $query->join('items', 'position_items.item_id', '=', 'items.id')
                      ->orderBy('items.name', 'asc');
        }

        // Get the results
        $items = $query->select('position_items.*')->paginate(50);

        // Get filter options
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $categories = \App\Models\ItemCategory::whereHas('items.positionItems', function($q) {
            $q->where('quantity', '>', 0);
        })->orderBy('name')->get();

        // Get summary statistics
        $summary = $this->getSummaryStatistics($request);

        return view('warehouses.warehouses.overview-report-print', compact(
            'items', 'warehouses', 'categories', 'summary'
        ));
    }

    /**
     * Get summary statistics for the overview report.
     */
    private function getSummaryStatistics(Request $request): array
    {
        $baseQuery = PositionItem::where('quantity', '>', 0);

        // Apply same filters as main query
        if ($request->filled('warehouse')) {
            $baseQuery->whereHas('shelfPosition.warehouseShelf', function($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse);
            });
        }

        if ($request->filled('item_name')) {
            $baseQuery->whereHas('item', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('category')) {
            $baseQuery->whereHas('item.itemCategory', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('expiry_filter')) {
            $today = now()->startOfDay();
            switch ($request->expiry_filter) {
                case 'expired':
                    $baseQuery->whereNotNull('expiry_date')
                              ->where('expiry_date', '<', $today);
                    break;
                case 'expiring_7':
                    $baseQuery->whereNotNull('expiry_date')
                              ->where('expiry_date', '>=', $today)
                              ->where('expiry_date', '<=', $today->copy()->addDays(7));
                    break;
                case 'expiring_30':
                    $baseQuery->whereNotNull('expiry_date')
                              ->where('expiry_date', '>=', $today)
                              ->where('expiry_date', '<=', $today->copy()->addDays(30));
                    break;
                case 'no_expiry':
                    $baseQuery->whereNull('expiry_date');
                    break;
            }
        }

        $totalItems = $baseQuery->count();
        $totalQuantity = $baseQuery->sum('quantity');
        // Note: Total value calculation removed as items table doesn't have unit_price column

        // Expiry statistics
        $expiredCount = (clone $baseQuery)->whereNotNull('expiry_date')
                                         ->where('expiry_date', '<', now()->startOfDay())
                                         ->count();

        $expiringSoonCount = (clone $baseQuery)->whereNotNull('expiry_date')
                                              ->where('expiry_date', '>=', now()->startOfDay())
                                              ->where('expiry_date', '<=', now()->addDays(7))
                                              ->count();

        $expiring30DaysCount = (clone $baseQuery)->whereNotNull('expiry_date')
                                                ->where('expiry_date', '>=', now()->startOfDay())
                                                ->where('expiry_date', '<=', now()->addDays(30))
                                                ->count();

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'expired_count' => $expiredCount,
            'expiring_soon_count' => $expiringSoonCount,
            'expiring_30_days_count' => $expiring30DaysCount,
        ];
    }
}
