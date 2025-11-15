<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanStep2;
use App\Models\ProductionPlanStep3;
use App\Models\ProductionPlanStep4;
use App\Services\ProductionPlanCalculationService;
use App\Services\ProductionPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionPlanStepController extends Controller
{
    public function __construct(
        private readonly ProductionPlanningService $planningService,
        private readonly ProductionPlanCalculationService $calculationService
    ) {
        $this->middleware('can:manufacturing.production-plans.view')->only(['step2', 'step3', 'step4']);
        $this->middleware('can:manufacturing.production-plans.edit')->only(['storeStep2', 'storeStep3', 'storeStep4', 'deleteStep2', 'deleteStep3', 'deleteStep4']);
    }

    /**
     * Show Step 2 form (Gelondongan planning).
     */
    public function step2(ProductionPlan $productionPlan): View|RedirectResponse
    {
        // Validate dependency
        if (!$this->planningService->validateStepDependency($productionPlan, 2)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Please complete Step 1 first.');
        }

        if (!$productionPlan->canEditStep(2)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit Step 2. Please delete Step 3 first.');
        }

        $productionPlan->load(['step1.doughItem', 'step2.adonanItem', 'step2.gelondonganItem']);

        // Auto-calculate Step 2 from Step 1 if Step 2 is empty
        $calculatedData = [];
        if ($productionPlan->step2()->count() === 0) {
            $calculatedData = $this->calculationService->calculateStep2FromStep1($productionPlan);
        }

        return view('manufacturing.production-plans.step2', compact('productionPlan', 'calculatedData'));
    }

    /**
     * Store Step 2 data.
     */
    public function storeStep2(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canEditStep(2)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit Step 2. Please delete Step 3 first.');
        }

        $validated = $request->validate([
            'step2' => 'required|array',
            'step2.*.adonan_item_id' => 'required|exists:items,id',
            'step2.*.gelondongan_item_id' => 'required|exists:items,id',
            'step2.*.qty_gl1_adonan' => 'required|numeric|min:0',
            'step2.*.qty_gl1_gelondongan' => 'required|numeric|min:0',
            'step2.*.qty_gl2_adonan' => 'required|numeric|min:0',
            'step2.*.qty_gl2_gelondongan' => 'required|numeric|min:0',
            'step2.*.qty_ta_adonan' => 'required|numeric|min:0',
            'step2.*.qty_ta_gelondongan' => 'required|numeric|min:0',
            'step2.*.qty_bl_adonan' => 'required|numeric|min:0',
            'step2.*.qty_bl_gelondongan' => 'required|numeric|min:0',
        ]);

        // Delete existing Step 2 records
        $productionPlan->step2()->delete();

        // Create new Step 2 records
        foreach ($validated['step2'] as $data) {
            $productionPlan->step2()->create($data);
        }

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 2 (Gelondongan Planning) saved successfully.');
    }

    /**
     * Show Step 3 form (Kerupuk Kering planning).
     */
    public function step3(ProductionPlan $productionPlan): View|RedirectResponse
    {
        // Validate dependency
        if (!$this->planningService->validateStepDependency($productionPlan, 3)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Please complete Step 2 first.');
        }

        if (!$productionPlan->canEditStep(3)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit Step 3. Please delete Step 4 first.');
        }

        $productionPlan->load(['step2.gelondonganItem', 'step3.gelondonganItem', 'step3.kerupukKeringItem']);

        // Auto-calculate Step 3 from Step 2 if Step 3 is empty
        $calculatedData = [];
        if ($productionPlan->step3()->count() === 0) {
            $calculatedData = $this->calculationService->calculateStep3FromStep2($productionPlan);
        }

        return view('manufacturing.production-plans.step3', compact('productionPlan', 'calculatedData'));
    }

    /**
     * Store Step 3 data.
     */
    public function storeStep3(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canEditStep(3)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit Step 3. Please delete Step 4 first.');
        }

        $validated = $request->validate([
            'step3' => 'required|array',
            'step3.*.gelondongan_item_id' => 'required|exists:items,id',
            'step3.*.kerupuk_kering_item_id' => 'required|exists:items,id',
            'step3.*.qty_gl1_gelondongan' => 'required|numeric|min:0',
            'step3.*.qty_gl1_kg' => 'required|numeric|min:0',
            'step3.*.qty_gl2_gelondongan' => 'required|numeric|min:0',
            'step3.*.qty_gl2_kg' => 'required|numeric|min:0',
            'step3.*.qty_ta_gelondongan' => 'required|numeric|min:0',
            'step3.*.qty_ta_kg' => 'required|numeric|min:0',
            'step3.*.qty_bl_gelondongan' => 'required|numeric|min:0',
            'step3.*.qty_bl_kg' => 'required|numeric|min:0',
        ]);

        // Delete existing Step 3 records
        $productionPlan->step3()->delete();

        // Create new Step 3 records
        foreach ($validated['step3'] as $data) {
            $productionPlan->step3()->create($data);
        }

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 3 (Kerupuk Kering Planning) saved successfully.');
    }

    /**
     * Show Step 4 form (Packing planning).
     */
    public function step4(ProductionPlan $productionPlan): View|RedirectResponse
    {
        // Validate dependency
        if (!$this->planningService->validateStepDependency($productionPlan, 4)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Please complete Step 3 first.');
        }

        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit production plan that is not in draft status.');
        }

        $productionPlan->load(['step3.kerupukKeringItem', 'step4.kerupukKeringItem', 'step4.kerupukPackingItem']);

        // Auto-calculate Step 4 from Step 3 if Step 4 is empty
        $calculatedData = [];
        if ($productionPlan->step4()->count() === 0) {
            $calculatedData = $this->calculationService->calculateStep4FromStep3($productionPlan);
        }

        return view('manufacturing.production-plans.step4', compact('productionPlan', 'calculatedData'));
    }

    /**
     * Store Step 4 data.
     */
    public function storeStep4(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit production plan that is not in draft status.');
        }

        $validated = $request->validate([
            'step4' => 'required|array',
            'step4.*.kerupuk_kering_item_id' => 'required|exists:items,id',
            'step4.*.kerupuk_packing_item_id' => 'required|exists:items,id',
            'step4.*.weight_per_unit' => 'required|numeric|min:0.001',
            'step4.*.qty_gl1_kg' => 'required|numeric|min:0',
            'step4.*.qty_gl1_packing' => 'required|numeric|min:0',
            'step4.*.qty_gl2_kg' => 'required|numeric|min:0',
            'step4.*.qty_gl2_packing' => 'required|numeric|min:0',
            'step4.*.qty_ta_kg' => 'required|numeric|min:0',
            'step4.*.qty_ta_packing' => 'required|numeric|min:0',
            'step4.*.qty_bl_kg' => 'required|numeric|min:0',
            'step4.*.qty_bl_packing' => 'required|numeric|min:0',
        ]);

        // Delete existing Step 4 records
        $productionPlan->step4()->delete();

        // Create new Step 4 records
        foreach ($validated['step4'] as $data) {
            $productionPlan->step4()->create($data);
        }

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 4 (Packing Planning) saved successfully.');
    }

    /**
     * Delete Step 2 and all subsequent steps.
     */
    public function deleteStep2(ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot delete steps. Production plan is not in draft status.');
        }

        if ($productionPlan->step3()->exists() || $productionPlan->step4()->exists()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot delete Step 2 while later steps exist. Please delete Step 3 (and Step 4) first.');
        }

        $productionPlan->step2()->delete();

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 2 has been deleted. You can now edit Step 1.');
    }

    /**
     * Delete Step 3 and all subsequent steps.
     */
    public function deleteStep3(ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot delete steps. Production plan is not in draft status.');
        }

        if ($productionPlan->step4()->exists()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot delete Step 3 while Step 4 exists. Please delete Step 4 first.');
        }

        $productionPlan->step3()->delete();

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 3 has been deleted. You can now edit Step 2.');
    }

    /**
     * Delete Step 4.
     */
    public function deleteStep4(ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot delete steps. Production plan is not in draft status.');
        }

        $productionPlan->step4()->delete();

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 4 has been deleted. You can now edit Step 3.');
    }
}
















