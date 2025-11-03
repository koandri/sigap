<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ProductionPlanningService
{
    /**
     * Calculate production dates based on plan date.
     *
     * production_start_date = plan_date + 1 day
     * ready_date = production_start_date + 2 days
     */
    public function calculateProductionDates(Carbon $planDate): array
    {
        $productionStartDate = $planDate->copy()->addDay();
        $readyDate = $productionStartDate->copy()->addDays(2);

        return [
            'plan_date' => $planDate->format('Y-m-d'),
            'production_start_date' => $productionStartDate->format('Y-m-d'),
            'ready_date' => $readyDate->format('Y-m-d'),
        ];
    }

    /**
     * Create a new production plan.
     */
    public function createProductionPlan(array $data, User $user): ProductionPlan
    {
        return DB::transaction(function () use ($data, $user) {
            // Calculate dates if plan_date is provided
            if (isset($data['plan_date'])) {
                $planDate = Carbon::parse($data['plan_date']);
                $dates = $this->calculateProductionDates($planDate);
                $data = array_merge($data, $dates);
            }

            $data['created_by'] = $user->id;
            $data['status'] = $data['status'] ?? 'draft';

            return ProductionPlan::create($data);
        });
    }

    /**
     * Update a production plan (only if draft).
     */
    public function updateProductionPlan(ProductionPlan $plan, array $data): ProductionPlan
    {
        return DB::transaction(function () use ($plan, $data) {
            if (!$plan->canBeEdited()) {
                throw new \RuntimeException('Cannot edit production plan that is not in draft status.');
            }

            // Recalculate dates if plan_date changed
            if (isset($data['plan_date']) && $data['plan_date'] !== $plan->plan_date->format('Y-m-d')) {
                $planDate = Carbon::parse($data['plan_date']);
                $dates = $this->calculateProductionDates($planDate);
                $data = array_merge($data, $dates);
            }

            $plan->update($data);
            return $plan->fresh();
        });
    }

    /**
     * Approve a production plan.
     */
    public function approveProductionPlan(ProductionPlan $plan, User $approver): ProductionPlan
    {
        return DB::transaction(function () use ($plan, $approver) {
            if ($plan->status !== 'draft') {
                throw new \RuntimeException('Only draft production plans can be approved.');
            }

            $plan->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $plan->fresh();
        });
    }

    /**
     * Validate that Step N-1 exists before Step N can be created.
     */
    public function validateStepDependency(ProductionPlan $plan, int $stepNumber): bool
    {
        return match ($stepNumber) {
            1 => true, // Step 1 has no dependency
            2 => $plan->step1()->exists(),
            3 => $plan->step2()->exists(),
            4 => $plan->step3()->exists(),
            default => false,
        };
    }

    /**
     * Get total quantities for a production plan across all steps.
     */
    public function getTotalQuantities(ProductionPlan $plan): array
    {
        $totals = [
            'step1' => [
                'qty_gl1' => 0.0,
                'qty_gl2' => 0.0,
                'qty_ta' => 0.0,
                'qty_bl' => 0.0,
            ],
            'step2' => [
                'qty_gl1_adonan' => 0.0,
                'qty_gl1_gelondongan' => 0.0,
                'qty_gl2_adonan' => 0.0,
                'qty_gl2_gelondongan' => 0.0,
                'qty_ta_adonan' => 0.0,
                'qty_ta_gelondongan' => 0.0,
                'qty_bl_adonan' => 0.0,
                'qty_bl_gelondongan' => 0.0,
            ],
            'step3' => [
                'qty_gl1_gelondongan' => 0.0,
                'qty_gl1_kg' => 0.0,
                'qty_gl2_gelondongan' => 0.0,
                'qty_gl2_kg' => 0.0,
                'qty_ta_gelondongan' => 0.0,
                'qty_ta_kg' => 0.0,
                'qty_bl_gelondongan' => 0.0,
                'qty_bl_kg' => 0.0,
            ],
            'step4' => [
                'qty_gl1_kg' => 0.0,
                'qty_gl1_packing' => 0.0,
                'qty_gl2_kg' => 0.0,
                'qty_gl2_packing' => 0.0,
                'qty_ta_kg' => 0.0,
                'qty_ta_packing' => 0.0,
                'qty_bl_kg' => 0.0,
                'qty_bl_packing' => 0.0,
            ],
        ];

        // Step 1 totals
        $step1Records = $plan->step1;
        foreach ($step1Records as $step1) {
            $totals['step1']['qty_gl1'] += (float) $step1->qty_gl1;
            $totals['step1']['qty_gl2'] += (float) $step1->qty_gl2;
            $totals['step1']['qty_ta'] += (float) $step1->qty_ta;
            $totals['step1']['qty_bl'] += (float) $step1->qty_bl;
        }

        // Step 2 totals
        $step2Records = $plan->step2;
        foreach ($step2Records as $step2) {
            $totals['step2']['qty_gl1_adonan'] += (float) $step2->qty_gl1_adonan;
            $totals['step2']['qty_gl1_gelondongan'] += (float) $step2->qty_gl1_gelondongan;
            $totals['step2']['qty_gl2_adonan'] += (float) $step2->qty_gl2_adonan;
            $totals['step2']['qty_gl2_gelondongan'] += (float) $step2->qty_gl2_gelondongan;
            $totals['step2']['qty_ta_adonan'] += (float) $step2->qty_ta_adonan;
            $totals['step2']['qty_ta_gelondongan'] += (float) $step2->qty_ta_gelondongan;
            $totals['step2']['qty_bl_adonan'] += (float) $step2->qty_bl_adonan;
            $totals['step2']['qty_bl_gelondongan'] += (float) $step2->qty_bl_gelondongan;
        }

        // Step 3 totals
        $step3Records = $plan->step3;
        foreach ($step3Records as $step3) {
            $totals['step3']['qty_gl1_gelondongan'] += (float) $step3->qty_gl1_gelondongan;
            $totals['step3']['qty_gl1_kg'] += (float) $step3->qty_gl1_kg;
            $totals['step3']['qty_gl2_gelondongan'] += (float) $step3->qty_gl2_gelondongan;
            $totals['step3']['qty_gl2_kg'] += (float) $step3->qty_gl2_kg;
            $totals['step3']['qty_ta_gelondongan'] += (float) $step3->qty_ta_gelondongan;
            $totals['step3']['qty_ta_kg'] += (float) $step3->qty_ta_kg;
            $totals['step3']['qty_bl_gelondongan'] += (float) $step3->qty_bl_gelondongan;
            $totals['step3']['qty_bl_kg'] += (float) $step3->qty_bl_kg;
        }

        // Step 4 totals
        $step4Records = $plan->step4;
        foreach ($step4Records as $step4) {
            $totals['step4']['qty_gl1_kg'] += (float) $step4->qty_gl1_kg;
            $totals['step4']['qty_gl1_packing'] += (float) $step4->qty_gl1_packing;
            $totals['step4']['qty_gl2_kg'] += (float) $step4->qty_gl2_kg;
            $totals['step4']['qty_gl2_packing'] += (float) $step4->qty_gl2_packing;
            $totals['step4']['qty_ta_kg'] += (float) $step4->qty_ta_kg;
            $totals['step4']['qty_ta_packing'] += (float) $step4->qty_ta_packing;
            $totals['step4']['qty_bl_kg'] += (float) $step4->qty_bl_kg;
            $totals['step4']['qty_bl_packing'] += (float) $step4->qty_bl_packing;
        }

        return $totals;
    }

    /**
     * Check if all steps are completed for a production plan.
     */
    public function isComplete(ProductionPlan $plan): bool
    {
        return $plan->step1()->exists()
            && $plan->step2()->exists()
            && $plan->step3()->exists()
            && $plan->step4()->exists();
    }
}

