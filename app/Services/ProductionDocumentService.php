<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductionActual;
use App\Models\ProductionPlan;
use Illuminate\Support\Collection;

final class ProductionDocumentService
{
    /**
     * Get data for Surat Perintah Kerja Produksi Basah.
     */
    public function getWetProductionWorkOrderData(ProductionPlan $plan): array
    {
        $plan->load([
            'step1.doughItem',
            'step1.recipeIngredients.ingredientItem',
            'step2.gelondonganItem',
        ]);

        return [
            'plan' => $plan,
            'adonan' => $this->aggregateAdonanProduced($plan),
            'rawMaterials' => $this->aggregateRawMaterialsForAdonan($plan),
            'gelondongan' => $this->aggregateGelondonganByLocation($plan),
        ];
    }

    /**
     * Get data for Surat Perintah Kerja Produksi Kering.
     */
    public function getDryProductionWorkOrderData(ProductionPlan $plan): array
    {
        $plan->load([
            'step1.doughItem',
            'step4.kerupukPackingItem',
            'step4.materials.packingMaterialItem',
            'step5.packingMaterialItem',
        ]);

        return [
            'plan' => $plan,
            'adonan' => $this->aggregateAdonanProduced($plan),
            'kerupukPack' => $this->aggregateKerupukPackProduced($plan),
            'packingMaterials' => $this->aggregatePackingMaterials($plan),
        ];
    }

    /**
     * Get data for Job Costing Adonan.
     * Uses actual production data for Step 1 if completed, otherwise uses plan data.
     */
    public function getJobCostingAdonanData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 1 is completed in actual production
        $isStep1Completed = $actual !== null && $actual->step1()->exists();
        
        // For raw materials, we always use plan recipe ingredients
        // as the raw materials are the same (they were used to produce the actual adonan)
        $plan->load([
            'step1.recipeIngredients.ingredientItem',
        ]);
        $rawMaterials = $this->aggregateRawMaterialsForAdonan($plan);

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep1Completed,
            'rawMaterials' => $rawMaterials,
        ];
    }

    /**
     * Get data for Roll Over Adonan.
     * Uses actual production data for Step 1 if completed, otherwise uses plan data.
     */
    public function getRollOverAdonanData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 1 is completed in actual production
        $isStep1Completed = $actual !== null && $actual->step1()->exists();

        if ($isStep1Completed) {
            $adonan = $this->aggregateActualAdonanProduced($actual);
        } else {
            $plan->load([
                'step1.doughItem',
            ]);
            $adonan = $this->aggregateAdonanProduced($plan);
        }

        $adonanWithPercentages = $this->calculateRollOverPercentages($adonan);

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep1Completed,
            'adonan' => $adonanWithPercentages,
        ];
    }

    /**
     * Get data for Job Costing Gelondongan.
     * Uses actual production data for Step 2 if completed, otherwise uses plan data.
     */
    public function getJobCostingGelondonganData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 2 is completed in actual production
        $isStep2Completed = $actual !== null && $actual->step2()->exists();

        if ($isStep2Completed) {
            $adonan = $this->aggregateActualAdonanForGelondongan($actual);
        } else {
            $plan->load([
                'step2.adonanItem',
            ]);
            $adonan = $plan->step2()
                ->with('adonanItem')
                ->get()
                ->groupBy('adonan_item_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'item' => $first->adonanItem,
                        'quantity' => $group->sum(function ($item) {
                            return (float) ($item->qty_gl1_adonan + $item->qty_gl2_adonan + $item->qty_ta_adonan + $item->qty_bl_adonan);
                        }),
                    ];
                })
                ->values();
        }

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep2Completed,
            'adonan' => $adonan,
        ];
    }

    /**
     * Get data for Roll Over Gelondongan.
     * Uses actual production data for Step 2 if completed, otherwise uses plan data.
     */
    public function getRollOverGelondonganData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 2 is completed in actual production
        $isStep2Completed = $actual !== null && $actual->step2()->exists();

        if ($isStep2Completed) {
            $gelondongan = $this->aggregateActualGelondonganProduced($actual);
        } else {
            $plan->load([
                'step2.gelondonganItem',
            ]);
            $gelondongan = $plan->step2()
                ->with('gelondonganItem')
                ->get()
                ->groupBy('gelondongan_item_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'item' => $first->gelondonganItem,
                        'quantity' => $group->sum(function ($item) {
                            return (float) ($item->qty_gl1_gelondongan + $item->qty_gl2_gelondongan + $item->qty_ta_gelondongan + $item->qty_bl_gelondongan);
                        }),
                    ];
                })
                ->values();
        }

        $gelondonganWithPercentages = $this->calculateRollOverPercentages($gelondongan);

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep2Completed,
            'gelondongan' => $gelondonganWithPercentages,
        ];
    }

    /**
     * Get data for Job Costing Kerupuk Kg.
     * Uses actual production data for Step 3 if completed, otherwise uses plan data.
     */
    public function getJobCostingKerupukKgData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 3 is completed in actual production
        $isStep3Completed = $actual !== null && $actual->step3()->exists();

        if ($isStep3Completed) {
            $gelondongan = $this->aggregateActualGelondonganForKerupukKg($actual);
        } else {
            $plan->load([
                'step3.gelondonganItem',
            ]);
            $gelondongan = $plan->step3()
                ->with('gelondonganItem')
                ->get()
                ->groupBy('gelondongan_item_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'item' => $first->gelondonganItem,
                        'quantity' => $group->sum(function ($item) {
                            return (float) ($item->qty_gl1_gelondongan + $item->qty_gl2_gelondongan + $item->qty_ta_gelondongan + $item->qty_bl_gelondongan);
                        }),
                    ];
                })
                ->values();
        }

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep3Completed,
            'gelondongan' => $gelondongan,
        ];
    }

    /**
     * Get data for Roll Over Kerupuk Kg.
     * Uses actual production data for Step 3 if completed, otherwise uses plan data.
     */
    public function getRollOverKerupukKgData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 3 is completed in actual production
        $isStep3Completed = $actual !== null && $actual->step3()->exists();

        if ($isStep3Completed) {
            $kerupukKg = $this->aggregateActualKerupukKgProduced($actual);
        } else {
            $plan->load([
                'step3.kerupukKeringItem',
            ]);
            $kerupukKg = $this->aggregateKerupukKgProduced($plan);
        }

        $kerupukKgWithPercentages = $this->calculateRollOverPercentages($kerupukKg);

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep3Completed,
            'kerupukKg' => $kerupukKgWithPercentages,
        ];
    }

    /**
     * Get data for Job Costing Kerupuk Pack.
     * Uses actual production data for Step 4 if completed, otherwise uses plan data.
     */
    public function getJobCostingKerupukPackData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 4 is completed in actual production
        $isStep4Completed = $actual !== null && $actual->step4()->exists();

        if ($isStep4Completed) {
            $kerupukKg = $this->aggregateActualKerupukKgForPack($actual);
            // For packing materials, check if Step 5 is also completed
            $isStep5Completed = $actual->step5()->exists();
            if ($isStep5Completed) {
                $packingMaterials = $this->aggregateActualPackingMaterials($actual);
            } else {
                $plan->load([
                    'step4.materials.packingMaterialItem',
                    'step5.packingMaterialItem',
                ]);
                $packingMaterials = $this->aggregatePackingMaterials($plan);
            }
        } else {
            $plan->load([
                'step4.kerupukKeringItem',
                'step4.materials.packingMaterialItem',
                'step5.packingMaterialItem',
            ]);
            $kerupukKg = $plan->step4()
                ->with('kerupukKeringItem')
                ->get()
                ->groupBy('kerupuk_kering_item_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'item' => $first->kerupukKeringItem,
                        'quantity' => $group->sum(function ($item) {
                            return (float) ($item->qty_gl1_kg + $item->qty_gl2_kg + $item->qty_ta_kg + $item->qty_bl_kg);
                        }),
                    ];
                })
                ->values();
            $packingMaterials = $this->aggregatePackingMaterials($plan);
        }

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep4Completed,
            'kerupukKg' => $kerupukKg,
            'packingMaterials' => $packingMaterials,
        ];
    }

    /**
     * Get data for Roll Over Kerupuk Pack.
     * Uses actual production data for Step 4 if completed, otherwise uses plan data.
     */
    public function getRollOverKerupukPackData(ProductionPlan $plan): array
    {
        $plan->load('actual');
        $actual = $plan->actual;
        
        // Check if Step 4 is completed in actual production
        $isStep4Completed = $actual !== null && $actual->step4()->exists();

        if ($isStep4Completed) {
            $kerupukPack = $this->aggregateActualKerupukPackProduced($actual);
        } else {
            $plan->load([
                'step4.kerupukPackingItem',
            ]);
            $kerupukPack = $this->aggregateKerupukPackProduced($plan);
        }

        $kerupukPackWithPercentages = $this->calculateRollOverPercentages($kerupukPack);

        return [
            'plan' => $plan,
            'actual' => $actual,
            'isBasedOnActual' => $isStep4Completed,
            'kerupukPack' => $kerupukPackWithPercentages,
        ];
    }

    /**
     * Aggregate raw materials from Step 1 recipe ingredients, grouped by ingredient_item.
     */
    public function aggregateRawMaterialsForAdonan(ProductionPlan $plan): Collection
    {
        $rawMaterials = collect();

        foreach ($plan->step1 as $step1) {
            foreach ($step1->recipeIngredients as $ingredient) {
                $itemId = $ingredient->ingredient_item_id;
                $quantity = (float) $ingredient->quantity;

                if ($rawMaterials->has($itemId)) {
                    $rawMaterials[$itemId]['quantity'] += $quantity;
                } else {
                    $rawMaterials[$itemId] = [
                        'item' => $ingredient->ingredientItem,
                        'quantity' => $quantity,
                        'unit' => $ingredient->unit,
                    ];
                }
            }
        }

        return $rawMaterials->values();
    }

    /**
     * Aggregate Adonan from Step 1, grouped by dough_item.
     */
    public function aggregateAdonanProduced(ProductionPlan $plan): Collection
    {
        return $plan->step1()
            ->with('doughItem')
            ->get()
            ->groupBy('dough_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->doughItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->qty_gl1 + $item->qty_gl2 + $item->qty_ta + $item->qty_bl);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate Gelondongan from Step 2, grouped by location and gelondongan_item.
     */
    public function aggregateGelondonganByLocation(ProductionPlan $plan): Collection
    {
        $gelondongan = collect();

        foreach ($plan->step2 as $step2) {
            $itemId = $step2->gelondongan_item_id;
            $item = $step2->gelondonganItem;

            $locations = [
                'GL1' => (int) $step2->qty_gl1_gelondongan,
                'GL2' => (int) $step2->qty_gl2_gelondongan,
                'TA' => (int) $step2->qty_ta_gelondongan,
                'BL' => (int) $step2->qty_bl_gelondongan,
            ];

            $key = $itemId;
            if ($gelondongan->has($key)) {
                $gelondongan[$key]['locations']['GL1'] += $locations['GL1'];
                $gelondongan[$key]['locations']['GL2'] += $locations['GL2'];
                $gelondongan[$key]['locations']['TA'] += $locations['TA'];
                $gelondongan[$key]['locations']['BL'] += $locations['BL'];
                $gelondongan[$key]['total'] += array_sum($locations);
            } else {
                $gelondongan[$key] = [
                    'item' => $item,
                    'locations' => $locations,
                    'total' => array_sum($locations),
                ];
            }
        }

        return $gelondongan->values();
    }

    /**
     * Aggregate Kerupuk Kg from Step 3, grouped by kerupuk_kering_item.
     */
    public function aggregateKerupukKgProduced(ProductionPlan $plan): Collection
    {
        return $plan->step3()
            ->with('kerupukKeringItem')
            ->get()
            ->groupBy('kerupuk_kering_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->kerupukKeringItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->qty_gl1_kg + $item->qty_gl2_kg + $item->qty_ta_kg + $item->qty_bl_kg);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate Kerupuk Pack from Step 4, grouped by kerupuk_packing_item.
     */
    public function aggregateKerupukPackProduced(ProductionPlan $plan): Collection
    {
        return $plan->step4()
            ->with('kerupukPackingItem')
            ->get()
            ->groupBy('kerupuk_packing_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->kerupukPackingItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->qty_gl1_packing + $item->qty_gl2_packing + $item->qty_ta_packing + $item->qty_bl_packing);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate packing materials from Step 4 materials and Step 5, grouped by packing_material_item.
     */
    public function aggregatePackingMaterials(ProductionPlan $plan): Collection
    {
        $materials = collect();

        // From Step 4 materials
        foreach ($plan->step4 as $step4) {
            foreach ($step4->materials as $material) {
                $itemId = $material->packing_material_item_id;
                $quantity = (int) $material->quantity_total;

                if ($materials->has($itemId)) {
                    $materials[$itemId]['quantity'] += $quantity;
                } else {
                    $materials[$itemId] = [
                        'item' => $material->packingMaterialItem,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        // From Step 5
        foreach ($plan->step5 as $step5) {
            $itemId = $step5->packing_material_item_id;
            $quantity = (int) $step5->quantity_total;

            if ($materials->has($itemId)) {
                $materials[$itemId]['quantity'] += $quantity;
            } else {
                $materials[$itemId] = [
                    'item' => $step5->packingMaterialItem,
                    'quantity' => $quantity,
                ];
            }
        }

        return $materials->values();
    }

    /**
     * Calculate percentage for each item in Roll Over.
     */
    public function calculateRollOverPercentages(Collection $items): Collection
    {
        $totalQty = $items->sum('quantity');

        if ($totalQty == 0) {
            return $items->map(function ($item) {
                $item['percentage'] = 0.0;
                return $item;
            });
        }

        return $items->map(function ($item) use ($totalQty) {
            $item['percentage'] = round(($item['quantity'] / $totalQty) * 100, 2);
            return $item;
        });
    }

    /**
     * Aggregate actual Adonan produced from ProductionActual Step 1.
     */
    private function aggregateActualAdonanProduced(ProductionActual $actual): Collection
    {
        return $actual->step1()
            ->with('doughItem')
            ->get()
            ->groupBy('dough_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->doughItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1 + $item->actual_qty_gl2 + $item->actual_qty_ta + $item->actual_qty_bl);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Adonan for Gelondongan from ProductionActual Step 2.
     */
    private function aggregateActualAdonanForGelondongan(ProductionActual $actual): Collection
    {
        return $actual->step2()
            ->with('adonanItem')
            ->get()
            ->groupBy('adonan_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->adonanItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_adonan + $item->actual_qty_gl2_adonan + $item->actual_qty_ta_adonan + $item->actual_qty_bl_adonan);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Gelondongan produced from ProductionActual Step 2.
     */
    private function aggregateActualGelondonganProduced(ProductionActual $actual): Collection
    {
        return $actual->step2()
            ->with('gelondonganItem')
            ->get()
            ->groupBy('gelondongan_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->gelondonganItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_gelondongan + $item->actual_qty_gl2_gelondongan + $item->actual_qty_ta_gelondongan + $item->actual_qty_bl_gelondongan);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Gelondongan for Kerupuk Kg from ProductionActual Step 3.
     */
    private function aggregateActualGelondonganForKerupukKg(ProductionActual $actual): Collection
    {
        return $actual->step3()
            ->with('gelondonganItem')
            ->get()
            ->groupBy('gelondongan_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->gelondonganItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_gelondongan + $item->actual_qty_gl2_gelondongan + $item->actual_qty_ta_gelondongan + $item->actual_qty_bl_gelondongan);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Kerupuk Kg produced from ProductionActual Step 3.
     */
    private function aggregateActualKerupukKgProduced(ProductionActual $actual): Collection
    {
        return $actual->step3()
            ->with('kerupukKeringItem')
            ->get()
            ->groupBy('kerupuk_kering_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->kerupukKeringItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_kg + $item->actual_qty_gl2_kg + $item->actual_qty_ta_kg + $item->actual_qty_bl_kg);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Kerupuk Kg for Pack from ProductionActual Step 4.
     */
    private function aggregateActualKerupukKgForPack(ProductionActual $actual): Collection
    {
        return $actual->step4()
            ->with('kerupukKeringItem')
            ->get()
            ->groupBy('kerupuk_kering_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->kerupukKeringItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_kg + $item->actual_qty_gl2_kg + $item->actual_qty_ta_kg + $item->actual_qty_bl_kg);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual Kerupuk Pack produced from ProductionActual Step 4.
     */
    private function aggregateActualKerupukPackProduced(ProductionActual $actual): Collection
    {
        return $actual->step4()
            ->with('kerupukPackingItem')
            ->get()
            ->groupBy('kerupuk_packing_item_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'item' => $first->kerupukPackingItem,
                    'quantity' => $group->sum(function ($item) {
                        return (float) ($item->actual_qty_gl1_packing + $item->actual_qty_gl2_packing + $item->actual_qty_ta_packing + $item->actual_qty_bl_packing);
                    }),
                ];
            })
            ->values();
    }

    /**
     * Aggregate actual packing materials from ProductionActual Step 5.
     */
    private function aggregateActualPackingMaterials(ProductionActual $actual): Collection
    {
        $materials = collect();

        foreach ($actual->step5 as $step5) {
            $itemId = $step5->packing_material_item_id;
            $quantity = (int) $step5->actual_quantity_total;

            if ($materials->has($itemId)) {
                $materials[$itemId]['quantity'] += $quantity;
            } else {
                $materials[$itemId] = [
                    'item' => $step5->packingMaterialItem,
                    'quantity' => $quantity,
                ];
            }
        }

        return $materials->values();
    }
}


