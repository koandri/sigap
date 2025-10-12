<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WarehouseShelf;
use App\Models\ShelfPosition;
use Illuminate\Database\Seeder;

final class ShelfPositionSeeder extends Seeder
{
    public function run(): void
    {
        $shelves = WarehouseShelf::all();
        
        foreach ($shelves as $shelf) {
            $this->createPositionsForShelf($shelf);
        }
    }

    private function createPositionsForShelf(WarehouseShelf $shelf): void
    {
        // Each shelf gets 3 positions: 01, 02, 03
        $positions = ['01', '02', '03'];
        
        foreach ($positions as $positionCode) {
            $positionName = $this->getPositionName($positionCode);
            
            ShelfPosition::firstOrCreate(
                [
                    'warehouse_shelf_id' => $shelf->id,
                    'position_code' => $positionCode
                ],
                [
                    'position_name' => $positionName,
                    'max_capacity' => 1, // Each position can hold 1 item by default
                    'is_active' => true
                ]
            );
        }
    }

    private function getPositionName(string $positionCode): string
    {
        if ($positionCode === '01') {
            return 'Main Position';
        }
        
        return "Position {$positionCode}";
    }
}