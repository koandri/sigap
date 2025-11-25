<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\YieldGuideline;
use Illuminate\Database\Seeder;

final class YieldGuidelineSeeder extends Seeder
{
    /**
     * Seed yield guidelines based on item categories and name patterns.
     *
     * This seeder creates yield guidelines for production conversions:
     * - Adonan → Gelondongan
     * - Gelondongan → Kerupuk Kering (Kg)
     * - Kerupuk Kering (Kg) → Packing
     *
     * Yield values should be updated based on actual production data from planning images.
     */
    public function run(): void
    {
        // Get or create item categories
        $adonanCategory = ItemCategory::where('name', 'Adonan')->first();
        $gelondonganCategory = ItemCategory::where('name', 'Gelondongan')->first();
        
        // Create missing categories if they don't exist
        $kerupukKgCategory = ItemCategory::firstOrCreate(
            ['name' => 'Kerupuk Kg'],
            ['description' => 'Kerupuk kering dalam satuan kilogram (finished product)']
        );
        
        $kerupukPackCategory = ItemCategory::firstOrCreate(
            ['name' => 'Kerupuk Pack'],
            ['description' => 'Kerupuk dalam kemasan/packing (finished product)']
        );

        if (!$adonanCategory || !$gelondonganCategory) {
            $this->command->warn('Required item categories (Adonan, Gelondongan) not found. Please run migrations first.');
            return;
        }

        // Get items by category
        $adonanItems = Item::where('item_category_id', $adonanCategory->id)->where('is_active', true)->get();
        $gelondonganItems = Item::where('item_category_id', $gelondonganCategory->id)->where('is_active', true)->get();
        $kerupukKgItems = Item::where('item_category_id', $kerupukKgCategory->id)->where('is_active', true)->get();
        $kerupukPackItems = Item::where('item_category_id', $kerupukPackCategory->id)->where('is_active', true)->get();

        if ($adonanItems->isEmpty() || $gelondonganItems->isEmpty() || $kerupukKgItems->isEmpty() || $kerupukPackItems->isEmpty()) {
            $this->command->warn('Items not found. Please create items first before seeding yield guidelines.');
            return;
        }

        $created = 0;
        $skipped = 0;

        // Step 1: Create Adonan → Gelondongan yield guidelines
        // Match items by name pattern (e.g., "Adonan Kancing" → "Gelondongan Kancing")
        foreach ($adonanItems as $adonanItem) {
            $adonanName = $adonanItem->name;
            
            // Extract product type from adonan name (Kancing, Gondang, Mentor, Mini, etc.)
            $productTypes = ['Kancing', 'Gondang', 'Mentor', 'Mini', 'Surya Bintang'];
            
            foreach ($productTypes as $type) {
                if (stripos($adonanName, $type) !== false) {
                    // Find matching gelondongan item
                    $gelondonganItem = $gelondonganItems->first(function ($item) use ($type) {
                        return stripos($item->name, $type) !== false;
                    });

                    if ($gelondonganItem) {
                        $result = $this->createYieldGuideline(
                            $adonanItem->id,
                            $gelondonganItem->id,
                            'adonan',
                            'gelondongan',
                            $this->getAdonanToGelondonganYield($type)
                        );

                        if ($result) {
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                    break; // Only match first product type found
                }
            }
        }

        // Step 2: Create Gelondongan → Kerupuk Kering (Kg) yield guidelines
        foreach ($gelondonganItems as $gelondonganItem) {
            $gelondonganName = $gelondonganItem->name;
            
            $productTypes = ['Kancing', 'Gondang', 'Mentor', 'Mini', 'Surya Bintang'];
            
            foreach ($productTypes as $type) {
                if (stripos($gelondonganName, $type) !== false) {
                    // Find matching kerupuk kering item from Kerupuk Kg category
                    $kerupukItem = $kerupukKgItems->first(function ($item) use ($type) {
                        $name = strtolower($item->name);
                        return stripos($name, $type) !== false;
                    });

                    if ($kerupukItem) {
                        $result = $this->createYieldGuideline(
                            $gelondonganItem->id,
                            $kerupukItem->id,
                            'gelondongan',
                            'kerupuk_kg',
                            $this->getGelondonganToKgYield($type)
                        );

                        if ($result) {
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                    break;
                }
            }
        }

        // Step 3: Create Kerupuk Kering (Kg) → Packing yield guidelines
        // This conversion uses weight_per_unit from items (qty_kg_per_pack)
        foreach ($kerupukKgItems as $kerupukItem) {
            $kerupukName = strtolower($kerupukItem->name);
            
            // Extract product type
            $productTypes = ['kancing', 'gondang', 'mentor', 'mini', 'surya bintang'];
            $matchedType = null;
            
            foreach ($productTypes as $type) {
                if (stripos($kerupukName, $type) !== false) {
                    $matchedType = $type;
                    break;
                }
            }

            if (!$matchedType) {
                continue;
            }

            // Find matching packed product from Kerupuk Pack category
            $packedItem = $kerupukPackItems->first(function ($item) use ($matchedType) {
                $itemName = strtolower($item->name);
                return stripos($itemName, $matchedType) !== false;
            });

            if ($packedItem && $packedItem->qty_kg_per_pack > 0) {
                // Conversion: 1 packing = qty_kg_per_pack kg
                // So 1 kg = 1 / qty_kg_per_pack packing
                $yieldPerKg = 1 / $packedItem->qty_kg_per_pack;

                $result = $this->createYieldGuideline(
                    $kerupukItem->id,
                    $packedItem->id,
                    'kerupuk_kg',
                    'packing',
                    $yieldPerKg
                );

                if ($result) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->command->info("Yield guidelines seeded: {$created} created, {$skipped} skipped (already exist).");
    }

    /**
     * Create a yield guideline if it doesn't exist.
     */
    private function createYieldGuideline(
        int $fromItemId,
        int $toItemId,
        string $fromStage,
        string $toStage,
        float $yieldQuantity
    ): bool {
        $exists = YieldGuideline::where('from_item_id', $fromItemId)
            ->where('to_item_id', $toItemId)
            ->exists();

        if ($exists) {
            return false;
        }

        YieldGuideline::create([
            'from_item_id' => $fromItemId,
            'to_item_id' => $toItemId,
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'yield_quantity' => $yieldQuantity,
        ]);

        return true;
    }

    /**
     * Get yield quantity for Adonan → Gelondongan conversion.
     *
     * Default values - should be updated based on actual production data.
     */
    private function getAdonanToGelondonganYield(string $productType): float
    {
        // Default yield values (these should be updated from actual planning images)
        // Typically, yield is close to 1:1 for Adonan → Gelondongan, with slight losses
        return match (strtolower($productType)) {
            'kancing' => 0.95,  // 95% yield
            'gondang' => 0.95,
            'mentor' => 0.95,
            'mini' => 0.95,
            default => 0.95,
        };
    }

    /**
     * Get yield quantity for Gelondongan → Kerupuk Kering (Kg) conversion.
     *
     * Default values based on typical yields mentioned in plan (e.g., 3.9 for Kancing).
     */
    private function getGelondonganToKgYield(string $productType): float
    {
        // Default yield values - UPDATE THESE based on actual planning images
        // These represent how many kg of kerupuk kering you get from 1 unit of gelondongan
        return match (strtolower($productType)) {
            'kancing' => 3.9,   // As mentioned in plan documentation
            'gondang' => 3.5,    // Example value - update from actual data
            'mentor' => 3.8,     // Example value - update from actual data
            'mini' => 4.0,       // Example value - update from actual data
            default => 3.9,
        };
    }
}

