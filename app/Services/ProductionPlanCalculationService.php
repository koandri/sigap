<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\PackingMaterialBlueprint;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanStep1;
use App\Models\ProductionPlanStep2;
use App\Models\ProductionPlanStep3;
use App\Models\ProductionPlanStep4;
use App\Models\YieldGuideline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ProductionPlanCalculationService
{
    /**
     * Calculate Step 2 quantities from Step 1 using yield guidelines.
     *
     * Converts Adonan quantities to Gelondongan quantities.
     */
    public function calculateStep2FromStep1(ProductionPlan $plan): array
    {
        $step1Records = $plan->step1()->with(['doughItem'])->get();
        $calculations = [];

        foreach ($step1Records as $step1) {
            $doughItem = $step1->doughItem;
            
            // Find gelondongan items that match this dough type
            $gelondonganItems = $this->findGelondonganItems($doughItem);

            foreach ($gelondonganItems as $gelondonganItem) {
                // Find yield guideline
                $yieldGuideline = YieldGuideline::forConversion(
                    $doughItem->id,
                    $gelondonganItem->id
                )->first();

                if (!$yieldGuideline) {
                    continue; // Skip if no yield guideline found
                }

                $yield = (float) $yieldGuideline->yield_quantity;

                // Calculate gelondongan quantities from adonan quantities
                $qtyGl1Gelondongan = (float) $step1->qty_gl1 * $yield;
                $qtyGl2Gelondongan = (float) $step1->qty_gl2 * $yield;
                $qtyTaGelondongan = (float) $step1->qty_ta * $yield;
                $qtyBlGelondongan = (float) $step1->qty_bl * $yield;

                $calculations[] = [
                    'production_plan_id' => $plan->id,
                    'adonan_item_id' => $doughItem->id,
                    'gelondongan_item_id' => $gelondonganItem->id,
                    'qty_gl1_adonan' => $step1->qty_gl1,
                    'qty_gl1_gelondongan' => $qtyGl1Gelondongan,
                    'qty_gl2_adonan' => $step1->qty_gl2,
                    'qty_gl2_gelondongan' => $qtyGl2Gelondongan,
                    'qty_ta_adonan' => $step1->qty_ta,
                    'qty_ta_gelondongan' => $qtyTaGelondongan,
                    'qty_bl_adonan' => $step1->qty_bl,
                    'qty_bl_gelondongan' => $qtyBlGelondongan,
                    'yield_used' => $yield,
                ];
            }
        }

        return $calculations;
    }

    /**
     * Calculate Step 3 quantities from Step 2 using yield guidelines.
     *
     * Converts Gelondongan quantities to Kerupuk Kering (Kg) quantities.
     */
    public function calculateStep3FromStep2(ProductionPlan $plan): array
    {
        $step2Records = $plan->step2()->with(['adonanItem', 'gelondonganItem'])->get();
        $calculations = [];

        foreach ($step2Records as $step2) {
            $gelondonganItem = $step2->gelondonganItem;
            
            // Find kerupuk kering items that match this gelondongan type
            $kerupukItems = $this->findKerupukKeringItems($gelondonganItem);

            foreach ($kerupukItems as $kerupukItem) {
                // Find yield guideline
                $yieldGuideline = YieldGuideline::forConversion(
                    $gelondonganItem->id,
                    $kerupukItem->id
                )->first();

                if (!$yieldGuideline) {
                    continue;
                }

                $yield = (float) $yieldGuideline->yield_quantity;

                // Calculate kg quantities from gelondongan quantities
                $qtyGl1Kg = (float) $step2->qty_gl1_gelondongan * $yield;
                $qtyGl2Kg = (float) $step2->qty_gl2_gelondongan * $yield;
                $qtyTaKg = (float) $step2->qty_ta_gelondongan * $yield;
                $qtyBlKg = (float) $step2->qty_bl_gelondongan * $yield;

                $calculations[] = [
                    'production_plan_id' => $plan->id,
                    'gelondongan_item_id' => $gelondonganItem->id,
                    'kerupuk_kering_item_id' => $kerupukItem->id,
                    'qty_gl1_gelondongan' => $step2->qty_gl1_gelondongan,
                    'qty_gl1_kg' => $qtyGl1Kg,
                    'qty_gl2_gelondongan' => $step2->qty_gl2_gelondongan,
                    'qty_gl2_kg' => $qtyGl2Kg,
                    'qty_ta_gelondongan' => $step2->qty_ta_gelondongan,
                    'qty_ta_kg' => $qtyTaKg,
                    'qty_bl_gelondongan' => $step2->qty_bl_gelondongan,
                    'qty_bl_kg' => $qtyBlKg,
                    'yield_used' => $yield,
                ];
            }
        }

        return $calculations;
    }

    /**
     * Calculate Step 4 quantities from Step 3 using weight per unit.
     *
     * Converts Kerupuk Kering (Kg) quantities to Packing quantities.
     */
    public function calculateStep4FromStep3(ProductionPlan $plan): array
    {
        $step3Records = $plan->step3()->with(['gelondonganItem', 'kerupukKeringItem'])->get();
        $calculations = [];

        foreach ($step3Records as $step3) {
            $kerupukItem = $step3->kerupukKeringItem;
            
            // Find packing items that match this kerupuk kering type
            $packingItems = $this->findPackingItems($kerupukItem);

            foreach ($packingItems as $packingItem) {
                // Get weight per unit from item or use default
                $weightPerUnit = $packingItem->qty_kg_per_pack > 0 
                    ? (float) $packingItem->qty_kg_per_pack 
                    : 1.0; // Default to 1 kg per pack if not set

                // Calculate packing quantities from kg quantities
                $qtyGl1Packing = (float) $step3->qty_gl1_kg / $weightPerUnit;
                $qtyGl2Packing = (float) $step3->qty_gl2_kg / $weightPerUnit;
                $qtyTaPacking = (float) $step3->qty_ta_kg / $weightPerUnit;
                $qtyBlPacking = (float) $step3->qty_bl_kg / $weightPerUnit;

                $calculations[] = [
                    'production_plan_id' => $plan->id,
                    'kerupuk_kering_item_id' => $kerupukItem->id,
                    'kerupuk_packing_item_id' => $packingItem->id,
                    'weight_per_unit' => $weightPerUnit,
                    'qty_gl1_kg' => $step3->qty_gl1_kg,
                    'qty_gl1_packing' => $qtyGl1Packing,
                    'qty_gl2_kg' => $step3->qty_gl2_kg,
                    'qty_gl2_packing' => $qtyGl2Packing,
                    'qty_ta_kg' => $step3->qty_ta_kg,
                    'qty_ta_packing' => $qtyTaPacking,
                    'qty_bl_kg' => $step3->qty_bl_kg,
                    'qty_bl_packing' => $qtyBlPacking,
                ];
            }
        }

        return $calculations;
    }

    /**
     * Calculate packing material requirements for Step 4.
     *
     * Based on packing quantities and packing material configuration.
     */
    public function calculatePackingMaterialRequirements(ProductionPlanStep4 $step4): array
    {
        $blueprints = PackingMaterialBlueprint::with('packingMaterialItem')
            ->where('kerupuk_packing_item_id', $step4->kerupuk_packing_item_id)
            ->get();

        $totalPacks = $step4->total_packing;

        if ($totalPacks <= 0 || $blueprints->isEmpty()) {
            return [];
        }

        return $blueprints->map(static function (PackingMaterialBlueprint $blueprint) use ($totalPacks) {
            return [
                'packing_material_item_id' => $blueprint->packing_material_item_id,
                'packing_material_item_name' => $blueprint->packingMaterialItem->name ?? 'N/A',
                'quantity_total' => round((float) $blueprint->quantity_per_pack * $totalPacks, 3),
            ];
        })->all();
    }

    public function syncPackingMaterials(ProductionPlanStep4 $step4, ?Collection $blueprints = null): void
    {
        $step4->materials()->delete();

        $blueprints ??= PackingMaterialBlueprint::where('kerupuk_packing_item_id', $step4->kerupuk_packing_item_id)
            ->get();

        $totalPacks = $step4->total_packing;

        if ($totalPacks <= 0 || $blueprints->isEmpty()) {
            return;
        }

        foreach ($blueprints as $blueprint) {
            $step4->materials()->create([
                'packing_material_item_id' => $blueprint->packing_material_item_id,
                'quantity_total' => $this->roundQuantity((float) $blueprint->quantity_per_pack * $totalPacks),
            ]);
        }
    }

    /**
     * Find gelondongan items that match a given dough item.
     */
    private function findGelondonganItems(Item $doughItem): Collection
    {
        $doughName = strtolower($doughItem->name);
        
        // Get gelondongan category
        $gelondonganCategory = $doughItem->itemCategory()
            ->whereHas('items', function ($query) {
                $category = \App\Models\ItemCategory::where('name', 'Gelondongan')->first();
                if ($category) {
                    $query->where('item_category_id', $category->id);
                }
            })
            ->first();

        if (!$gelondonganCategory) {
            $gelondonganCategory = \App\Models\ItemCategory::where('name', 'Gelondongan')->first();
        }

        if (!$gelondonganCategory) {
            return collect([]);
        }

        // Extract product type from dough name
        $productTypes = ['kancing', 'gondang', 'mentor', 'mini', 'surya bintang'];
        $matchedTypes = [];

        foreach ($productTypes as $type) {
            if (stripos($doughName, $type) !== false) {
                $matchedTypes[] = $type;
            }
        }

        // Find gelondongan items matching the product type
        $query = Item::where('item_category_id', $gelondonganCategory->id)
            ->where('is_active', true);

        if (!empty($matchedTypes)) {
            $query->where(function ($q) use ($matchedTypes) {
                foreach ($matchedTypes as $type) {
                    $q->orWhere('name', 'like', "%{$type}%");
                }
            });
        }

        return $query->get();
    }

    /**
     * Find kerupuk kering items that match a given gelondongan item.
     */
    private function findKerupukKeringItems(Item $gelondonganItem): Collection
    {
        $gelondonganName = strtolower($gelondonganItem->name);
        
        // Get Kerupuk Kg category
        $kerupukKgCategory = \App\Models\ItemCategory::where('name', 'Kerupuk Kg')->first();

        if (!$kerupukKgCategory) {
            return collect([]);
        }

        // Extract product type
        $productTypes = ['kancing', 'gondang', 'mentor', 'mini', 'surya bintang'];
        $matchedTypes = [];

        foreach ($productTypes as $type) {
            if (stripos($gelondonganName, $type) !== false) {
                $matchedTypes[] = $type;
            }
        }

        // Find kerupuk kering items matching the product type
        $query = Item::where('item_category_id', $kerupukKgCategory->id)
            ->where('is_active', true);

        if (!empty($matchedTypes)) {
            $query->where(function ($q) use ($matchedTypes) {
                foreach ($matchedTypes as $type) {
                    $q->orWhere('name', 'like', "%{$type}%");
                }
            });
        }

        return $query->get();
    }

    /**
     * Find packing items that match a given kerupuk kering item.
     */
    private function findPackingItems(Item $kerupukItem): Collection
    {
        $kerupukName = strtolower($kerupukItem->name);
        
        // Get Kerupuk Pack category
        $kerupukPackCategory = \App\Models\ItemCategory::where('name', 'Kerupuk Pack')->first();

        if (!$kerupukPackCategory) {
            return collect([]);
        }

        // Extract product type
        $productTypes = ['kancing', 'gondang', 'mentor', 'mini', 'surya bintang'];
        $matchedTypes = [];

        foreach ($productTypes as $type) {
            if (stripos($kerupukName, $type) !== false) {
                $matchedTypes[] = $type;
            }
        }

        // Find packing items matching the product type
        $query = Item::where('item_category_id', $kerupukPackCategory->id)
            ->where('is_active', true);

        if (!empty($matchedTypes)) {
            $query->where(function ($q) use ($matchedTypes) {
                foreach ($matchedTypes as $type) {
                    $q->orWhere('name', 'like', "%{$type}%");
                }
            });
        }

        return $query->get();
    }

    private function roundQuantity(float $value): float
    {
        return round($value, 3);
    }
}

