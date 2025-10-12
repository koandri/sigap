<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use Illuminate\Database\Seeder;

final class WarehouseShelfSeeder extends Seeder
{
    public function run(): void
    {
        // Create the 4 Finished Goods Warehouses
        $warehouses = [
            [
                'code' => 'FG-WH-01',
                'name' => 'Finished Goods Warehouse 1',
                'description' => 'Main finished goods warehouse - Aisles A, B, C, D',
                'is_active' => true,
            ],
            [
                'code' => 'FG-WH-02',
                'name' => 'Finished Goods Warehouse 2',
                'description' => 'Secondary finished goods warehouse - Aisles E, F, G, H',
                'is_active' => true,
            ],
            [
                'code' => 'FG-WH-03',
                'name' => 'Finished Goods Warehouse 3',
                'description' => 'Tertiary finished goods warehouse - Aisles I, J, K, L',
                'is_active' => true,
            ],
            [
                'code' => 'FG-WH-OUT',
                'name' => 'Finished Goods Warehouse Outside',
                'description' => 'Outdoor finished goods warehouse - Aisles M, N, O, P, Q, R',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            $warehouse = Warehouse::firstOrCreate(
                ['code' => $warehouseData['code']],
                $warehouseData
            );

            $this->createShelvesForWarehouse($warehouse);
        }

        $this->call(ShelfPositionSeeder::class);
    }

    private function createShelvesForWarehouse(Warehouse $warehouse): void
    {
        // Define exact shelf codes for each warehouse
        $shelfCodes = $this->getShelfCodesForWarehouse($warehouse->code);
        
        foreach ($shelfCodes as $shelfCode) {
            $shelfName = "Shelf {$shelfCode}";
            $description = "Storage location at {$shelfCode}";
            
            WarehouseShelf::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'shelf_code' => $shelfCode
                ],
                [
                    'shelf_name' => $shelfName,
                    'description' => $description,
                    'max_capacity' => 3, // Each shelf has 3 positions (01, 02, 03)
                    'is_active' => true
                ]
            );
        }
    }

    private function getShelfCodesForWarehouse(string $warehouseCode): array
    {
        switch ($warehouseCode) {
            case 'FG-WH-01':
                return $this->getWarehouse1Shelves();
            case 'FG-WH-02':
                return $this->getWarehouse2Shelves();
            case 'FG-WH-03':
                return $this->getWarehouse3Shelves();
            case 'FG-WH-OUT':
                return $this->getWarehouseOutsideShelves();
            default:
                return [];
        }
    }

    private function getWarehouse1Shelves(): array
    {
        return [
            // Aisle A (50 shelves)
            'A-03-00', 'A-04-00', 'A-05-00', 'A-06-00', 'A-07-00', 'A-08-00', 'A-09-00', 'A-10-00',
            'A-01-01', 'A-02-01', 'A-03-01', 'A-04-01', 'A-05-01', 'A-06-01', 'A-07-01', 'A-08-01', 'A-09-01', 'A-10-01',
            'A-01-02', 'A-02-02', 'A-03-02', 'A-04-02', 'A-05-02', 'A-06-02', 'A-07-02', 'A-08-02', 'A-09-02', 'A-10-02',
            'A-01-03', 'A-02-03', 'A-03-03', 'A-04-03', 'A-05-03', 'A-06-03', 'A-07-03', 'A-08-03', 'A-09-03', 'A-10-03',
            'A-01-04', 'A-02-04', 'A-03-04', 'A-04-04', 'A-05-04', 'A-06-04', 'A-07-04', 'A-08-04', 'A-09-04', 'A-10-04',
            // Aisle B (32 shelves)
            'B-01-01', 'B-02-01', 'B-03-01', 'B-04-01', 'B-05-01', 'B-06-01', 'B-07-01', 'B-07-02', 'B-08-01',
            'B-01-02', 'B-02-02', 'B-03-02', 'B-04-02', 'B-05-02', 'B-06-02', 'B-08-02',
            'B-01-03', 'B-02-03', 'B-03-03', 'B-04-03', 'B-05-03', 'B-06-03', 'B-07-03', 'B-08-03',
            'B-01-04', 'B-02-04', 'B-03-04', 'B-04-04', 'B-05-04', 'B-06-04', 'B-07-04', 'B-08-04',
            // Aisle C (32 shelves)
            'C-01-01', 'C-02-01', 'C-03-01', 'C-04-01', 'C-05-01', 'C-06-01', 'C-07-01', 'C-08-01',
            'C-01-02', 'C-02-02', 'C-03-02', 'C-04-02', 'C-05-02', 'C-06-02', 'C-07-02', 'C-08-02',
            'C-01-03', 'C-02-03', 'C-03-03', 'C-04-03', 'C-05-03', 'C-06-03', 'C-07-03', 'C-08-03',
            'C-01-04', 'C-02-04', 'C-03-04', 'C-04-04', 'C-05-04', 'C-06-04', 'C-07-04', 'C-08-04',
            // Aisle D (50 shelves)
            'D-03-00', 'D-04-00', 'D-05-00', 'D-06-00', 'D-07-00', 'D-08-00', 'D-09-00', 'D-10-00',
            'D-01-01', 'D-02-01', 'D-03-01', 'D-04-01', 'D-05-01', 'D-06-01', 'D-07-01', 'D-08-01', 'D-09-01', 'D-10-01',
            'D-01-02', 'D-02-02', 'D-03-02', 'D-04-02', 'D-05-02', 'D-06-02', 'D-07-02', 'D-08-02', 'D-09-02', 'D-10-02',
            'D-01-03', 'D-02-03', 'D-03-03', 'D-04-03', 'D-05-03', 'D-06-03', 'D-07-03', 'D-08-03', 'D-09-03', 'D-10-03',
            'D-01-04', 'D-02-04', 'D-03-04', 'D-04-04', 'D-05-04', 'D-06-04', 'D-07-04', 'D-08-04', 'D-09-04', 'D-10-04',
        ];
    }

    private function getWarehouse2Shelves(): array
    {
        return [
            // Aisle E (50 shelves)
            'E-03-00', 'E-04-00', 'E-05-00', 'E-06-00', 'E-07-00', 'E-08-00', 'E-09-00', 'E-10-00',
            'E-01-01', 'E-02-01', 'E-03-01', 'E-04-01', 'E-05-01', 'E-06-01', 'E-07-01', 'E-08-01', 'E-09-01', 'E-10-01',
            'E-01-02', 'E-02-02', 'E-03-02', 'E-04-02', 'E-05-02', 'E-06-02', 'E-07-02', 'E-08-02', 'E-09-02', 'E-10-02',
            'E-01-03', 'E-02-03', 'E-03-03', 'E-04-03', 'E-05-03', 'E-06-03', 'E-07-03', 'E-08-03', 'E-09-03', 'E-10-03',
            'E-01-04', 'E-02-04', 'E-03-04', 'E-04-04', 'E-05-04', 'E-06-04', 'E-07-04', 'E-08-04', 'E-09-04', 'E-10-04',
            // Aisle F (32 shelves)
            'F-01-01', 'F-02-01', 'F-03-01', 'F-04-01', 'F-05-01', 'F-06-01', 'F-07-01', 'F-08-01',
            'F-01-02', 'F-02-02', 'F-03-02', 'F-04-02', 'F-05-02', 'F-06-02', 'F-07-02', 'F-08-02',
            'F-01-03', 'F-02-03', 'F-03-03', 'F-04-03', 'F-05-03', 'F-06-03', 'F-07-03', 'F-08-03',
            'F-01-04', 'F-02-04', 'F-03-04', 'F-04-04', 'F-05-04', 'F-06-04', 'F-07-04', 'F-08-04',
            // Aisle G (32 shelves)
            'G-01-01', 'G-02-01', 'G-03-01', 'G-04-01', 'G-05-01', 'G-06-01', 'G-07-01', 'G-08-01',
            'G-01-02', 'G-02-02', 'G-03-02', 'G-04-02', 'G-05-02', 'G-06-02', 'G-07-02', 'G-08-02',
            'G-01-03', 'G-02-03', 'G-03-03', 'G-04-03', 'G-05-03', 'G-06-03', 'G-07-03', 'G-08-03',
            'G-01-04', 'G-02-04', 'G-03-04', 'G-04-04', 'G-05-04', 'G-06-04', 'G-07-04', 'G-08-04',
            // Aisle H (50 shelves)
            'H-03-00', 'H-04-00', 'H-05-00', 'H-06-00', 'H-07-00', 'H-08-00', 'H-09-00', 'H-10-00',
            'H-01-01', 'H-02-01', 'H-03-01', 'H-04-01', 'H-05-01', 'H-06-01', 'H-07-01', 'H-08-01', 'H-09-01', 'H-10-01',
            'H-01-02', 'H-02-02', 'H-03-02', 'H-04-02', 'H-05-02', 'H-06-02', 'H-07-02', 'H-08-02', 'H-09-02', 'H-10-02',
            'H-01-03', 'H-02-03', 'H-03-03', 'H-04-03', 'H-05-03', 'H-06-03', 'H-07-03', 'H-08-03', 'H-09-03', 'H-10-03',
            'H-01-04', 'H-02-04', 'H-03-04', 'H-04-04', 'H-05-04', 'H-06-04', 'H-07-04', 'H-08-04', 'H-09-04', 'H-10-04',
        ];
    }

    private function getWarehouse3Shelves(): array
    {
        return [
            // Aisle I (50 shelves)
            'I-03-00', 'I-04-00', 'I-05-00', 'I-06-00', 'I-07-00', 'I-08-00', 'I-09-00', 'I-10-00',
            'I-01-01', 'I-02-01', 'I-03-01', 'I-04-01', 'I-05-01', 'I-06-01', 'I-07-01', 'I-08-01', 'I-09-01', 'I-10-01',
            'I-01-02', 'I-02-02', 'I-03-02', 'I-04-02', 'I-05-02', 'I-06-02', 'I-07-02', 'I-08-02', 'I-09-02', 'I-10-02',
            'I-01-03', 'I-02-03', 'I-03-03', 'I-04-03', 'I-05-03', 'I-06-03', 'I-07-03', 'I-08-03', 'I-09-03', 'I-10-03',
            'I-01-04', 'I-02-04', 'I-03-04', 'I-04-04', 'I-05-04', 'I-06-04', 'I-07-04', 'I-08-04', 'I-09-04', 'I-10-04',
            // Aisle J (32 shelves)
            'J-01-01', 'J-02-01', 'J-03-01', 'J-04-01', 'J-05-01', 'J-06-01', 'J-07-01', 'J-08-01',
            'J-01-02', 'J-02-02', 'J-03-02', 'J-04-02', 'J-05-02', 'J-06-02', 'J-07-02', 'J-08-02',
            'J-01-03', 'J-02-03', 'J-03-03', 'J-04-03', 'J-05-03', 'J-06-03', 'J-07-03', 'J-08-03',
            'J-01-04', 'J-02-04', 'J-03-04', 'J-04-04', 'J-05-04', 'J-06-04', 'J-07-04', 'J-08-04',
            // Aisle K (32 shelves)
            'K-01-01', 'K-02-01', 'K-03-01', 'K-04-01', 'K-05-01', 'K-06-01', 'K-07-01', 'K-08-01',
            'K-01-02', 'K-02-02', 'K-03-02', 'K-04-02', 'K-05-02', 'K-06-02', 'K-07-02', 'K-08-02',
            'K-01-03', 'K-02-03', 'K-03-03', 'K-04-03', 'K-05-03', 'K-06-03', 'K-07-03', 'K-08-03',
            'K-01-04', 'K-02-04', 'K-03-04', 'K-04-04', 'K-05-04', 'K-06-04', 'K-07-04', 'K-08-04',
            // Aisle L (50 shelves)
            'L-03-00', 'L-04-00', 'L-05-00', 'L-06-00', 'L-07-00', 'L-08-00', 'L-09-00', 'L-10-00',
            'L-01-01', 'L-02-01', 'L-03-01', 'L-04-01', 'L-05-01', 'L-06-01', 'L-07-01', 'L-08-01', 'L-09-01', 'L-10-01',
            'L-01-02', 'L-02-02', 'L-03-02', 'L-04-02', 'L-05-02', 'L-06-02', 'L-07-02', 'L-08-02', 'L-09-02', 'L-10-02',
            'L-01-03', 'L-02-03', 'L-03-03', 'L-04-03', 'L-05-03', 'L-06-03', 'L-07-03', 'L-08-03', 'L-09-03', 'L-10-03',
            'L-01-04', 'L-02-04', 'L-03-04', 'L-04-04', 'L-05-04', 'L-06-04', 'L-07-04', 'L-08-04', 'L-09-04', 'L-10-04',
        ];
    }

    private function getWarehouseOutsideShelves(): array
    {
        return [
            // Aisle M (6 shelves)
            'M-01-00', 'M-02-00', 'M-03-00', 'M-04-00', 'M-05-00', 'M-06-00',
            // Aisle N (8 shelves)
            'N-01-00', 'N-02-00', 'N-03-00', 'N-04-00', 'N-05-00', 'N-06-00', 'N-07-00', 'N-08-00',
            // Aisle O (3 shelves)
            'O-01-00', 'O-02-00', 'O-03-00',
            // Aisle P (5 shelves)
            'P-01-00', 'P-02-00', 'P-03-00', 'P-04-00', 'P-05-00',
            // Aisle Q (6 shelves)
            'Q-01-00', 'Q-02-00', 'Q-03-00', 'Q-04-00', 'Q-05-00', 'Q-06-00',
            // Aisle R (4 shelves)
            'R-01-00', 'R-02-00', 'R-03-00', 'R-04-00',
        ];
    }
}
