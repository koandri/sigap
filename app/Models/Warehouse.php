<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class Warehouse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * Scope to get only active warehouses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total items count in this warehouse.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->shelves()
            ->whereHas('shelfPositions.positionItems', function($q) {
                $q->where('quantity', '>', 0);
            })
            ->withCount(['shelfPositions as item_count' => function($q) {
                $q->whereHas('positionItems', function($subQ) {
                    $subQ->where('quantity', '>', 0);
                });
            }])
            ->get()
            ->sum('item_count');
    }

    /**
     * Get locations with items that have expiry dates approaching.
     */
    public function expiringItems(int $days = 30): HasMany
    {
        return $this->shelves()
            ->whereHas('shelfPositions.positionItems', function($q) use ($days) {
                $q->whereNotNull('expiry_date')
                  ->whereDate('expiry_date', '<=', now()->addDays($days));
            });
    }

    /**
     * Get all shelves in this warehouse.
     */
    public function shelves(): HasMany
    {
        return $this->hasMany(WarehouseShelf::class);
    }

    /**
     * Get active shelves in this warehouse.
     */
    public function activeShelves(): HasMany
    {
        return $this->hasMany(WarehouseShelf::class)->where('is_active', true);
    }

    /**
     * Get shelves by row (A, B, C, etc.).
     */
    public function getShelvesByRow(string $row): HasMany
    {
        return $this->shelves()->where('shelf_code', 'like', $row . '-%');
    }

    /**
     * Get the shelf grid for visual display.
     */
    public function getShelfGridAttribute(): array
    {
        $shelves = $this->activeShelves()->orderBy('shelf_code')->get();
        $grid = [];
        
        foreach ($shelves as $shelf) {
            // Parse shelf code format: A-01-04
            $parts = explode('-', $shelf->shelf_code);
            $row = $parts[0]; // A, B, C, D, etc.
            $section = (int) $parts[1]; // 01, 02, 03, etc.
            
            if (!isset($grid[$row])) {
                $grid[$row] = [];
            }
            
            // Group all shelves by row and section, showing all levels
            if (!isset($grid[$row][$section])) {
                $grid[$row][$section] = [];
            }
            $grid[$row][$section][] = $shelf;
        }
        
        return $grid;
    }

    /**
     * Get shelves organized in 3-column layout grouped by row-section combination.
     * Optimized to use loaded relationships when available.
     */
    public function getShelfColumnsAttribute(): array
    {
        // Use loaded shelves if available, otherwise query
        if ($this->relationLoaded('shelves')) {
            $shelves = $this->shelves->where('is_active', true)->sortBy('shelf_code');
        } else {
            $shelves = $this->activeShelves()
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
                }])
                ->get();
        }
        
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
            // Column 1: sections 1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34, 37, 40, 43, 46, 49
            // Column 2: sections 2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35, 38, 41, 44, 47, 50
            // Column 3: sections 3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36, 39, 42, 45, 48
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
     * Get shelf inventory statistics.
     * Optimized to use efficient queries with joins instead of multiple whereHas.
     */
    public function getShelfInventoryStatsAttribute(): array
    {
        // Use a single query with joins and aggregations for better performance
        $stats = DB::table('warehouse_shelves')
            ->leftJoin('shelf_positions', 'warehouse_shelves.id', '=', 'shelf_positions.warehouse_shelf_id')
            ->leftJoin('position_items', function ($join) {
                $join->on('shelf_positions.id', '=', 'position_items.shelf_position_id')
                    ->where('position_items.quantity', '>', 0);
            })
            ->where('warehouse_shelves.warehouse_id', $this->id)
            ->selectRaw('
                COUNT(DISTINCT warehouse_shelves.id) as total_shelves,
                COUNT(DISTINCT CASE WHEN position_items.id IS NOT NULL THEN warehouse_shelves.id END) as occupied_shelves,
                COUNT(DISTINCT shelf_positions.id) as total_positions,
                COUNT(DISTINCT CASE WHEN position_items.id IS NOT NULL THEN shelf_positions.id END) as occupied_positions
            ')
            ->first();

        // Get expiring items count
        $expiringItems = PositionItem::whereHas('shelfPosition.warehouseShelf', function($q) {
            $q->where('warehouse_id', $this->id);
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
}
