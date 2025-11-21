<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\ProductionActual;
use App\Models\ProductionActualStep1;
use App\Models\ProductionActualStep2;
use App\Models\ProductionActualStep3;
use App\Models\ProductionActualStep4;
use App\Models\ProductionActualStep5;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanStep1;
use App\Models\ProductionPlanStep1RecipeIngredient;
use App\Models\ProductionPlanStep2;
use App\Models\ProductionPlanStep3;
use App\Models\ProductionPlanStep4;
use App\Models\ProductionPlanStep5;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ProductionActualService
{
    /**
     * Start production - create ProductionActual record and change plan status to in_production.
     */
    public function startProduction(ProductionPlan $plan, User $user, ?Carbon $productionDate = null): ProductionActual
    {
        return DB::transaction(function () use ($plan, $user, $productionDate) {
            if ($plan->status !== 'approved') {
                throw new \RuntimeException('Only approved production plans can be started.');
            }

            if ($plan->actual()->exists()) {
                throw new \RuntimeException('Production has already been started for this plan.');
            }

            $actual = ProductionActual::create([
                'production_plan_id' => $plan->id,
                'production_date' => $productionDate ?? $plan->production_start_date,
                'recorded_by' => $user->id,
                'recorded_at' => now(),
            ]);

            $plan->update([
                'status' => 'in_production',
            ]);

            return $actual->fresh();
        });
    }

    /**
     * Record Step 1 actual production data.
     */
    public function recordStep1(ProductionActual $actual, array $data): void
    {
        DB::transaction(function () use ($actual, $data) {
            $plan = $actual->productionPlan;

            // Delete existing Step 1 actuals for this actual
            $actual->step1()->delete();

            // Create new Step 1 actuals
            foreach ($data['step1'] as $step1Data) {
                $planStep1Id = $step1Data['production_plan_step1_id'] ?? null;
                $planStep1 = null;

                if ($planStep1Id) {
                    // Use existing plan step
                    $planStep1 = $plan->step1()->find($planStep1Id);
                }

                // If no plan step exists, create one
                if (!$planStep1) {
                    $doughItemId = $step1Data['dough_item_id'] ?? null;
                    $recipeId = $step1Data['recipe_id'] ?? null;

                    if (!$doughItemId) {
                        throw new \RuntimeException('Dough item is required for new Step 1 records.');
                    }

                    $recipe = $recipeId ? Recipe::find($recipeId) : null;

                    $planStep1 = $plan->step1()->create([
                        'dough_item_id' => $doughItemId,
                        'recipe_id' => $recipe?->id,
                        'recipe_name' => $recipe?->name,
                        'recipe_date' => $recipe?->recipe_date,
                        'qty_gl1' => $step1Data['actual_qty_gl1'] ?? 0,
                        'qty_gl2' => $step1Data['actual_qty_gl2'] ?? 0,
                        'qty_ta' => $step1Data['actual_qty_ta'] ?? 0,
                        'qty_bl' => $step1Data['actual_qty_bl'] ?? 0,
                    ]);

                    // Handle recipe ingredients if provided
                    if (isset($step1Data['ingredients']) && is_array($step1Data['ingredients'])) {
                        foreach ($step1Data['ingredients'] as $index => $ingredient) {
                            if (!empty($ingredient['ingredient_item_id'])) {
                                $ingredientItem = Item::find($ingredient['ingredient_item_id']);
                                $unit = $ingredient['unit'] ?? $ingredientItem?->unit ?? null;

                                $planStep1->recipeIngredients()->create([
                                    'ingredient_item_id' => $ingredient['ingredient_item_id'],
                                    'quantity' => $ingredient['quantity'] ?? 0,
                                    'unit' => $unit,
                                    'sort_order' => $index,
                                ]);
                            }
                        }
                    }
                }

                ProductionActualStep1::create([
                    'production_actual_id' => $actual->id,
                    'production_plan_step1_id' => $planStep1->id,
                    'dough_item_id' => $planStep1->dough_item_id,
                    'actual_qty_gl1' => $step1Data['actual_qty_gl1'] ?? 0,
                    'actual_qty_gl2' => $step1Data['actual_qty_gl2'] ?? 0,
                    'actual_qty_ta' => $step1Data['actual_qty_ta'] ?? 0,
                    'actual_qty_bl' => $step1Data['actual_qty_bl'] ?? 0,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * Record Step 2 actual production data.
     */
    public function recordStep2(ProductionActual $actual, array $data): void
    {
        DB::transaction(function () use ($actual, $data) {
            $plan = $actual->productionPlan;

            $actual->step2()->delete();

            foreach ($data['step2'] as $step2Data) {
                $planStep2Id = $step2Data['production_plan_step2_id'] ?? null;
                $planStep2 = null;

                if ($planStep2Id) {
                    $planStep2 = $plan->step2()->find($planStep2Id);
                }

                // If no plan step exists, create one
                if (!$planStep2) {
                    $adonanItemId = $step2Data['adonan_item_id'] ?? null;
                    $gelondonganItemId = $step2Data['gelondongan_item_id'] ?? null;

                    if (!$adonanItemId || !$gelondonganItemId) {
                        throw new \RuntimeException('Adonan item and Gelondongan item are required for new Step 2 records.');
                    }

                    $planStep2 = $plan->step2()->create([
                        'adonan_item_id' => $adonanItemId,
                        'gelondongan_item_id' => $gelondonganItemId,
                        'qty_gl1_adonan' => $step2Data['actual_qty_gl1_adonan'] ?? 0,
                        'qty_gl1_gelondongan' => $step2Data['actual_qty_gl1_gelondongan'] ?? 0,
                        'qty_gl2_adonan' => $step2Data['actual_qty_gl2_adonan'] ?? 0,
                        'qty_gl2_gelondongan' => $step2Data['actual_qty_gl2_gelondongan'] ?? 0,
                        'qty_ta_adonan' => $step2Data['actual_qty_ta_adonan'] ?? 0,
                        'qty_ta_gelondongan' => $step2Data['actual_qty_ta_gelondongan'] ?? 0,
                        'qty_bl_adonan' => $step2Data['actual_qty_bl_adonan'] ?? 0,
                        'qty_bl_gelondongan' => $step2Data['actual_qty_bl_gelondongan'] ?? 0,
                    ]);
                }

                ProductionActualStep2::create([
                    'production_actual_id' => $actual->id,
                    'production_plan_step2_id' => $planStep2->id,
                    'adonan_item_id' => $planStep2->adonan_item_id,
                    'gelondongan_item_id' => $planStep2->gelondongan_item_id,
                    'actual_qty_gl1_adonan' => $step2Data['actual_qty_gl1_adonan'] ?? 0,
                    'actual_qty_gl1_gelondongan' => $step2Data['actual_qty_gl1_gelondongan'] ?? 0,
                    'actual_qty_gl2_adonan' => $step2Data['actual_qty_gl2_adonan'] ?? 0,
                    'actual_qty_gl2_gelondongan' => $step2Data['actual_qty_gl2_gelondongan'] ?? 0,
                    'actual_qty_ta_adonan' => $step2Data['actual_qty_ta_adonan'] ?? 0,
                    'actual_qty_ta_gelondongan' => $step2Data['actual_qty_ta_gelondongan'] ?? 0,
                    'actual_qty_bl_adonan' => $step2Data['actual_qty_bl_adonan'] ?? 0,
                    'actual_qty_bl_gelondongan' => $step2Data['actual_qty_bl_gelondongan'] ?? 0,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * Record Step 3 actual production data.
     */
    public function recordStep3(ProductionActual $actual, array $data): void
    {
        DB::transaction(function () use ($actual, $data) {
            $plan = $actual->productionPlan;

            $actual->step3()->delete();

            foreach ($data['step3'] as $step3Data) {
                $planStep3Id = $step3Data['production_plan_step3_id'] ?? null;
                $planStep3 = null;

                if ($planStep3Id) {
                    $planStep3 = $plan->step3()->find($planStep3Id);
                }

                // If no plan step exists, create one
                if (!$planStep3) {
                    $gelondonganItemId = $step3Data['gelondongan_item_id'] ?? null;
                    $kerupukKeringItemId = $step3Data['kerupuk_kering_item_id'] ?? null;

                    if (!$gelondonganItemId || !$kerupukKeringItemId) {
                        throw new \RuntimeException('Gelondongan item and Kerupuk Kering item are required for new Step 3 records.');
                    }

                    $planStep3 = $plan->step3()->create([
                        'gelondongan_item_id' => $gelondonganItemId,
                        'kerupuk_kering_item_id' => $kerupukKeringItemId,
                        'qty_gl1_gelondongan' => $step3Data['actual_qty_gl1_gelondongan'] ?? 0,
                        'qty_gl1_kg' => round((float) ($step3Data['actual_qty_gl1_kg'] ?? 0), 2),
                        'qty_gl2_gelondongan' => $step3Data['actual_qty_gl2_gelondongan'] ?? 0,
                        'qty_gl2_kg' => round((float) ($step3Data['actual_qty_gl2_kg'] ?? 0), 2),
                        'qty_ta_gelondongan' => $step3Data['actual_qty_ta_gelondongan'] ?? 0,
                        'qty_ta_kg' => round((float) ($step3Data['actual_qty_ta_kg'] ?? 0), 2),
                        'qty_bl_gelondongan' => $step3Data['actual_qty_bl_gelondongan'] ?? 0,
                        'qty_bl_kg' => round((float) ($step3Data['actual_qty_bl_kg'] ?? 0), 2),
                    ]);
                }

                ProductionActualStep3::create([
                    'production_actual_id' => $actual->id,
                    'production_plan_step3_id' => $planStep3->id,
                    'gelondongan_item_id' => $planStep3->gelondongan_item_id,
                    'kerupuk_kering_item_id' => $planStep3->kerupuk_kering_item_id,
                    'actual_qty_gl1_gelondongan' => $step3Data['actual_qty_gl1_gelondongan'] ?? 0,
                    'actual_qty_gl1_kg' => $step3Data['actual_qty_gl1_kg'] ?? 0,
                    'actual_qty_gl2_gelondongan' => $step3Data['actual_qty_gl2_gelondongan'] ?? 0,
                    'actual_qty_gl2_kg' => $step3Data['actual_qty_gl2_kg'] ?? 0,
                    'actual_qty_ta_gelondongan' => $step3Data['actual_qty_ta_gelondongan'] ?? 0,
                    'actual_qty_ta_kg' => $step3Data['actual_qty_ta_kg'] ?? 0,
                    'actual_qty_bl_gelondongan' => $step3Data['actual_qty_bl_gelondongan'] ?? 0,
                    'actual_qty_bl_kg' => $step3Data['actual_qty_bl_kg'] ?? 0,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * Record Step 4 actual production data.
     */
    public function recordStep4(ProductionActual $actual, array $data): void
    {
        DB::transaction(function () use ($actual, $data) {
            $plan = $actual->productionPlan;

            $actual->step4()->delete();

            foreach ($data['step4'] as $step4Data) {
                $planStep4Id = $step4Data['production_plan_step4_id'] ?? null;
                $planStep4 = null;

                if ($planStep4Id) {
                    $planStep4 = $plan->step4()->find($planStep4Id);
                }

                // If no plan step exists, create one
                if (!$planStep4) {
                    $kerupukKeringItemId = $step4Data['kerupuk_kering_item_id'] ?? null;
                    $kerupukPackingItemId = $step4Data['kerupuk_packing_item_id'] ?? null;

                    if (!$kerupukKeringItemId || !$kerupukPackingItemId) {
                        throw new \RuntimeException('Kerupuk Kering item and Packing item are required for new Step 4 records.');
                    }

                    $planStep4 = $plan->step4()->create([
                        'kerupuk_kering_item_id' => $kerupukKeringItemId,
                        'kerupuk_packing_item_id' => $kerupukPackingItemId,
                        'qty_gl1_kg' => round((float) ($step4Data['actual_qty_gl1_kg'] ?? 0), 2),
                        'qty_gl1_packing' => $step4Data['actual_qty_gl1_packing'] ?? 0,
                        'qty_gl2_kg' => round((float) ($step4Data['actual_qty_gl2_kg'] ?? 0), 2),
                        'qty_gl2_packing' => $step4Data['actual_qty_gl2_packing'] ?? 0,
                        'qty_ta_kg' => round((float) ($step4Data['actual_qty_ta_kg'] ?? 0), 2),
                        'qty_ta_packing' => $step4Data['actual_qty_ta_packing'] ?? 0,
                        'qty_bl_kg' => round((float) ($step4Data['actual_qty_bl_kg'] ?? 0), 2),
                        'qty_bl_packing' => $step4Data['actual_qty_bl_packing'] ?? 0,
                    ]);
                }

                ProductionActualStep4::create([
                    'production_actual_id' => $actual->id,
                    'production_plan_step4_id' => $planStep4->id,
                    'kerupuk_kering_item_id' => $planStep4->kerupuk_kering_item_id,
                    'kerupuk_packing_item_id' => $planStep4->kerupuk_packing_item_id,
                    'actual_qty_gl1_kg' => $step4Data['actual_qty_gl1_kg'] ?? 0,
                    'actual_qty_gl1_packing' => $step4Data['actual_qty_gl1_packing'] ?? 0,
                    'actual_qty_gl2_kg' => $step4Data['actual_qty_gl2_kg'] ?? 0,
                    'actual_qty_gl2_packing' => $step4Data['actual_qty_gl2_packing'] ?? 0,
                    'actual_qty_ta_kg' => $step4Data['actual_qty_ta_kg'] ?? 0,
                    'actual_qty_ta_packing' => $step4Data['actual_qty_ta_packing'] ?? 0,
                    'actual_qty_bl_kg' => $step4Data['actual_qty_bl_kg'] ?? 0,
                    'actual_qty_bl_packing' => $step4Data['actual_qty_bl_packing'] ?? 0,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * Record Step 5 actual production data.
     */
    public function recordStep5(ProductionActual $actual, array $data): void
    {
        DB::transaction(function () use ($actual, $data) {
            $plan = $actual->productionPlan;

            $actual->step5()->delete();

            foreach ($data['step5'] as $step5Data) {
                $planStep5Id = $step5Data['production_plan_step5_id'] ?? null;
                $planStep5 = null;

                if ($planStep5Id) {
                    $planStep5 = $plan->step5()->find($planStep5Id);
                }

                // If no plan step exists, create one
                if (!$planStep5) {
                    $packSkuId = $step5Data['pack_sku_id'] ?? null;
                    $packingMaterialItemId = $step5Data['packing_material_item_id'] ?? null;

                    if (!$packSkuId || !$packingMaterialItemId) {
                        throw new \RuntimeException('Pack SKU and Packing Material item are required for new Step 5 records.');
                    }

                    $planStep5 = $plan->step5()->create([
                        'pack_sku_id' => $packSkuId,
                        'packing_material_item_id' => $packingMaterialItemId,
                        'quantity_total' => $step5Data['actual_quantity_total'] ?? 0,
                    ]);
                }

                ProductionActualStep5::create([
                    'production_actual_id' => $actual->id,
                    'production_plan_step5_id' => $planStep5->id,
                    'pack_sku_id' => $planStep5->pack_sku_id,
                    'packing_material_item_id' => $planStep5->packing_material_item_id,
                    'actual_quantity_total' => $step5Data['actual_quantity_total'] ?? 0,
                    'recorded_at' => now(),
                ]);
            }
        });
    }

    /**
     * Delete Step 1 actual record.
     */
    public function deleteStep1(ProductionActual $actual, int $actualStep1Id): void
    {
        $actualStep1 = $actual->step1()->findOrFail($actualStep1Id);
        $actualStep1->delete();
    }

    /**
     * Delete Step 2 actual record.
     */
    public function deleteStep2(ProductionActual $actual, int $actualStep2Id): void
    {
        $actualStep2 = $actual->step2()->findOrFail($actualStep2Id);
        $actualStep2->delete();
    }

    /**
     * Delete Step 3 actual record.
     */
    public function deleteStep3(ProductionActual $actual, int $actualStep3Id): void
    {
        $actualStep3 = $actual->step3()->findOrFail($actualStep3Id);
        $actualStep3->delete();
    }

    /**
     * Delete Step 4 actual record.
     */
    public function deleteStep4(ProductionActual $actual, int $actualStep4Id): void
    {
        $actualStep4 = $actual->step4()->findOrFail($actualStep4Id);
        $actualStep4->delete();
    }

    /**
     * Delete Step 5 actual record.
     */
    public function deleteStep5(ProductionActual $actual, int $actualStep5Id): void
    {
        $actualStep5 = $actual->step5()->findOrFail($actualStep5Id);
        $actualStep5->delete();
    }

    /**
     * Complete production - change plan status to completed.
     */
    public function completeProduction(ProductionActual $actual): void
    {
        DB::transaction(function () use ($actual) {
            if (!$actual->isComplete()) {
                throw new \RuntimeException('Cannot complete production: not all steps have actual data.');
            }

            $actual->productionPlan->update([
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Calculate variances between planned and actual production.
     */
    public function calculateVariances(ProductionPlan $plan): array
    {
        $variances = [
            'step1' => [],
            'step2' => [],
            'step3' => [],
            'step4' => [],
            'step5' => [],
        ];

        $actual = $plan->actual;
        if (!$actual) {
            return $variances;
        }

        // Step 1 variances
        foreach ($plan->step1 as $planned) {
            $actualStep1 = $planned->actualStep1;
            if ($actualStep1) {
                $variances['step1'][] = $this->calculateStep1Variance($planned, $actualStep1);
            }
        }

        // Step 2 variances
        foreach ($plan->step2 as $planned) {
            $actualStep2 = $planned->actualStep2;
            if ($actualStep2) {
                $variances['step2'][] = $this->calculateStep2Variance($planned, $actualStep2);
            }
        }

        // Step 3 variances
        foreach ($plan->step3 as $planned) {
            $actualStep3 = $planned->actualStep3;
            if ($actualStep3) {
                $variances['step3'][] = $this->calculateStep3Variance($planned, $actualStep3);
            }
        }

        // Step 4 variances
        foreach ($plan->step4 as $planned) {
            $actualStep4 = $planned->actualStep4;
            if ($actualStep4) {
                $variances['step4'][] = $this->calculateStep4Variance($planned, $actualStep4);
            }
        }

        // Step 5 variances
        foreach ($plan->step5 as $planned) {
            $actualStep5 = $planned->actualStep5;
            if ($actualStep5) {
                $variances['step5'][] = $this->calculateStep5Variance($planned, $actualStep5);
            }
        }

        return $variances;
    }

    /**
     * Get production progress for a plan.
     */
    public function getProductionProgress(ProductionPlan $plan): array
    {
        $actual = $plan->actual;

        if (!$actual) {
            return [
                'completion_percentage' => 0,
                'steps_complete' => [],
                'steps_incomplete' => [1, 2, 3, 4, 5],
                'overall_status' => 'not_started',
            ];
        }

        $steps = [
            1 => $actual->step1()->exists(),
            2 => $actual->step2()->exists(),
            3 => $actual->step3()->exists(),
            4 => $actual->step4()->exists(),
            5 => $actual->step5()->exists(),
        ];

        $stepsComplete = array_keys(array_filter($steps));
        $stepsIncomplete = array_keys(array_diff_key($steps, array_flip($stepsComplete)));

        return [
            'completion_percentage' => $actual->completion_percentage,
            'steps_complete' => $stepsComplete,
            'steps_incomplete' => $stepsIncomplete,
            'overall_status' => $actual->isComplete() ? 'complete' : 'in_progress',
        ];
    }

    /**
     * Get variance status based on percentage.
     */
    private function getVarianceStatus(float $percent): string
    {
        $absPercent = abs($percent);
        if ($absPercent <= 5) {
            return 'on_target';
        }
        if ($absPercent <= 15) {
            return 'minor_variance';
        }
        return 'major_variance';
    }

    private function calculateStep1Variance($planned, $actual): array
    {
        $channels = ['gl1', 'gl2', 'ta', 'bl'];
        $result = [
            'production_plan_step1_id' => $planned->id,
            'dough_item_id' => $planned->dough_item_id,
            'dough_item_name' => $planned->doughItem->name ?? '',
            'channels' => [],
        ];

        foreach ($channels as $channel) {
            $plannedQty = (float) $planned->{"qty_{$channel}"};
            $actualQty = (float) $actual->{"actual_qty_{$channel}"};
            $variance = $actualQty - $plannedQty;
            $variancePercent = $plannedQty > 0 ? ($variance / $plannedQty) * 100 : 0;

            $result['channels'][$channel] = [
                'planned' => $plannedQty,
                'actual' => $actualQty,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'status' => $this->getVarianceStatus($variancePercent),
            ];
        }

        return $result;
    }

    private function calculateStep2Variance($planned, $actual): array
    {
        $channels = ['gl1', 'gl2', 'ta', 'bl'];
        $result = [
            'production_plan_step2_id' => $planned->id,
            'adonan_item_id' => $planned->adonan_item_id,
            'gelondongan_item_id' => $planned->gelondongan_item_id,
            'adonan_item_name' => $planned->adonanItem->name ?? '',
            'gelondongan_item_name' => $planned->gelondonganItem->name ?? '',
            'channels' => [],
        ];

        foreach ($channels as $channel) {
            // Adonan variance
            $plannedAdonan = (float) $planned->{"qty_{$channel}_adonan"};
            $actualAdonan = (float) $actual->{"actual_qty_{$channel}_adonan"};
            $varianceAdonan = $actualAdonan - $plannedAdonan;
            $variancePercentAdonan = $plannedAdonan > 0 ? ($varianceAdonan / $plannedAdonan) * 100 : 0;

            // Gelondongan variance
            $plannedGelondongan = (float) $planned->{"qty_{$channel}_gelondongan"};
            $actualGelondongan = (float) $actual->{"actual_qty_{$channel}_gelondongan"};
            $varianceGelondongan = $actualGelondongan - $plannedGelondongan;
            $variancePercentGelondongan = $plannedGelondongan > 0 ? ($varianceGelondongan / $plannedGelondongan) * 100 : 0;

            $result['channels'][$channel] = [
                'adonan' => [
                    'planned' => $plannedAdonan,
                    'actual' => $actualAdonan,
                    'variance' => $varianceAdonan,
                    'variance_percent' => $variancePercentAdonan,
                    'status' => $this->getVarianceStatus($variancePercentAdonan),
                ],
                'gelondongan' => [
                    'planned' => $plannedGelondongan,
                    'actual' => $actualGelondongan,
                    'variance' => $varianceGelondongan,
                    'variance_percent' => $variancePercentGelondongan,
                    'status' => $this->getVarianceStatus($variancePercentGelondongan),
                ],
            ];
        }

        return $result;
    }

    private function calculateStep3Variance($planned, $actual): array
    {
        $channels = ['gl1', 'gl2', 'ta', 'bl'];
        $result = [
            'production_plan_step3_id' => $planned->id,
            'gelondongan_item_id' => $planned->gelondongan_item_id,
            'kerupuk_kering_item_id' => $planned->kerupuk_kering_item_id,
            'gelondongan_item_name' => $planned->gelondonganItem->name ?? '',
            'kerupuk_kering_item_name' => $planned->kerupukKeringItem->name ?? '',
            'channels' => [],
        ];

        foreach ($channels as $channel) {
            // Gelondongan variance
            $plannedGelondongan = (float) $planned->{"qty_{$channel}_gelondongan"};
            $actualGelondongan = (float) $actual->{"actual_qty_{$channel}_gelondongan"};
            $varianceGelondongan = $actualGelondongan - $plannedGelondongan;
            $variancePercentGelondongan = $plannedGelondongan > 0 ? ($varianceGelondongan / $plannedGelondongan) * 100 : 0;

            // Kg variance
            $plannedKg = (float) $planned->{"qty_{$channel}_kg"};
            $actualKg = (float) $actual->{"actual_qty_{$channel}_kg"};
            $varianceKg = $actualKg - $plannedKg;
            $variancePercentKg = $plannedKg > 0 ? ($varianceKg / $plannedKg) * 100 : 0;

            $result['channels'][$channel] = [
                'gelondongan' => [
                    'planned' => $plannedGelondongan,
                    'actual' => $actualGelondongan,
                    'variance' => $varianceGelondongan,
                    'variance_percent' => $variancePercentGelondongan,
                    'status' => $this->getVarianceStatus($variancePercentGelondongan),
                ],
                'kg' => [
                    'planned' => $plannedKg,
                    'actual' => $actualKg,
                    'variance' => $varianceKg,
                    'variance_percent' => $variancePercentKg,
                    'status' => $this->getVarianceStatus($variancePercentKg),
                ],
            ];
        }

        return $result;
    }

    private function calculateStep4Variance($planned, $actual): array
    {
        $channels = ['gl1', 'gl2', 'ta', 'bl'];
        $result = [
            'production_plan_step4_id' => $planned->id,
            'kerupuk_kering_item_id' => $planned->kerupuk_kering_item_id,
            'kerupuk_packing_item_id' => $planned->kerupuk_packing_item_id,
            'kerupuk_kering_item_name' => $planned->kerupukKeringItem->name ?? '',
            'kerupuk_packing_item_name' => $planned->kerupukPackingItem->name ?? '',
            'channels' => [],
        ];

        foreach ($channels as $channel) {
            // Kg variance
            $plannedKg = (float) $planned->{"qty_{$channel}_kg"};
            $actualKg = (float) $actual->{"actual_qty_{$channel}_kg"};
            $varianceKg = $actualKg - $plannedKg;
            $variancePercentKg = $plannedKg > 0 ? ($varianceKg / $plannedKg) * 100 : 0;

            // Packing variance
            $plannedPacking = (float) $planned->{"qty_{$channel}_packing"};
            $actualPacking = (float) $actual->{"actual_qty_{$channel}_packing"};
            $variancePacking = $actualPacking - $plannedPacking;
            $variancePercentPacking = $plannedPacking > 0 ? ($variancePacking / $plannedPacking) * 100 : 0;

            $result['channels'][$channel] = [
                'kg' => [
                    'planned' => $plannedKg,
                    'actual' => $actualKg,
                    'variance' => $varianceKg,
                    'variance_percent' => $variancePercentKg,
                    'status' => $this->getVarianceStatus($variancePercentKg),
                ],
                'packing' => [
                    'planned' => $plannedPacking,
                    'actual' => $actualPacking,
                    'variance' => $variancePacking,
                    'variance_percent' => $variancePercentPacking,
                    'status' => $this->getVarianceStatus($variancePercentPacking),
                ],
            ];
        }

        return $result;
    }

    private function calculateStep5Variance($planned, $actual): array
    {
        $plannedQty = (float) $planned->quantity_total;
        $actualQty = (float) $actual->actual_quantity_total;
        $variance = $actualQty - $plannedQty;
        $variancePercent = $plannedQty > 0 ? ($variance / $plannedQty) * 100 : 0;

        return [
            'production_plan_step5_id' => $planned->id,
            'pack_sku_id' => $planned->pack_sku_id,
            'packing_material_item_id' => $planned->packing_material_item_id,
            'pack_sku_name' => $planned->packSku->name ?? '',
            'packing_material_item_name' => $planned->packingMaterialItem->name ?? '',
            'planned' => $plannedQty,
            'actual' => $actualQty,
            'variance' => $variance,
            'variance_percent' => $variancePercent,
            'status' => $this->getVarianceStatus($variancePercent),
        ];
    }
}

