<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PackingMaterialBlueprint;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanStep2;
use App\Models\ProductionPlanStep3;
use App\Models\ProductionPlanStep4;
use App\Services\ProductionPlanCalculationService;
use App\Services\ProductionPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $productionPlan->step2()->create([
                'adonan_item_id' => $data['adonan_item_id'],
                'gelondongan_item_id' => $data['gelondongan_item_id'],
                'qty_gl1_adonan' => (int) $data['qty_gl1_adonan'],
                'qty_gl1_gelondongan' => (int) $data['qty_gl1_gelondongan'],
                'qty_gl2_adonan' => (int) $data['qty_gl2_adonan'],
                'qty_gl2_gelondongan' => (int) $data['qty_gl2_gelondongan'],
                'qty_ta_adonan' => (int) $data['qty_ta_adonan'],
                'qty_ta_gelondongan' => (int) $data['qty_ta_gelondongan'],
                'qty_bl_adonan' => (int) $data['qty_bl_adonan'],
                'qty_bl_gelondongan' => (int) $data['qty_bl_gelondongan'],
            ]);
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
            $productionPlan->step3()->create([
                'gelondongan_item_id' => $data['gelondongan_item_id'],
                'kerupuk_kering_item_id' => $data['kerupuk_kering_item_id'],
                'qty_gl1_gelondongan' => (int) $data['qty_gl1_gelondongan'],
                'qty_gl1_kg' => round((float) $data['qty_gl1_kg'], 2),
                'qty_gl2_gelondongan' => (int) $data['qty_gl2_gelondongan'],
                'qty_gl2_kg' => round((float) $data['qty_gl2_kg'], 2),
                'qty_ta_gelondongan' => (int) $data['qty_ta_gelondongan'],
                'qty_ta_kg' => round((float) $data['qty_ta_kg'], 2),
                'qty_bl_gelondongan' => (int) $data['qty_bl_gelondongan'],
                'qty_bl_kg' => round((float) $data['qty_bl_kg'], 2),
            ]);
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

        $productionPlan->load([
            'step3.kerupukKeringItem',
            'step4.kerupukKeringItem',
            'step4.kerupukPackingItem',
            'step4.materials.packingMaterialItem',
        ]);

        // Auto-calculate Step 4 from Step 3 if Step 4 is empty
        $calculatedData = [];
        if ($productionPlan->step4()->count() === 0) {
            $calculatedData = $this->calculationService->calculateStep4FromStep3($productionPlan);
        }

        $packingItems = Item::with(['packingMaterialBlueprints.packingMaterialItem'])
            ->whereHas('itemCategory', static function ($query): void {
                $query->where('name', 'like', '%Kerupuk Pack%');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $kerupukKeringOptions = $productionPlan->step3
            ->unique('kerupuk_kering_item_id')
            ->map(static function ($step3) {
                return [
                    'id' => $step3->kerupuk_kering_item_id,
                    'name' => $step3->kerupukKeringItem->name ?? 'N/A',
                ];
            })
            ->values();

        $packingBlueprints = $packingItems
            ->mapWithKeys(static function ($item) {
                return [
                    $item->id => $item->packingMaterialBlueprints->map(static function ($blueprint) {
                        return [
                            'packing_material_item_id' => $blueprint->material_item_id,
                            'packing_material_item_name' => $blueprint->packingMaterialItem->name ?? 'N/A',
                            'quantity_per_pack' => (float) $blueprint->quantity_per_pack,
                        ];
                    }),
                ];
            });

        // Get pack configurations for each kerupuk kg item
        $packConfigurations = \App\Models\KerupukPackConfiguration::with('packItem')
            ->whereIn('kerupuk_kg_item_id', $kerupukKeringOptions->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->groupBy('kerupuk_kg_item_id')
            ->map(static function ($configs) {
                return $configs->pluck('pack_item_id')->toArray();
            });

        return view('manufacturing.production-plans.step4', compact(
            'productionPlan',
            'calculatedData',
            'packingItems',
            'packingBlueprints',
            'kerupukKeringOptions',
            'packConfigurations'
        ));
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

        $request->merge([
            'materials' => collect($request->input('materials', []))
                ->filter(static function ($material) {
                    return !empty($material['packing_material_item_id']) && 
                           $material['packing_material_item_id'] !== 'null' &&
                           is_numeric($material['packing_material_item_id']);
                })
                ->values()
                ->toArray()
        ]);

        $validated = $request->validate([
            'step4' => 'required|array',
            'step4.*.kerupuk_kering_item_id' => 'required|exists:items,id',
            'step4.*.kerupuk_packing_item_id' => 'required|exists:items,id',
            'step4.*.weight_per_unit' => 'nullable|numeric|min:0.001',
            'step4.*.qty_gl1_packing' => 'required|numeric|min:0',
            'step4.*.qty_gl2_packing' => 'required|numeric|min:0',
            'step4.*.qty_ta_packing' => 'required|numeric|min:0',
            'step4.*.qty_bl_packing' => 'required|numeric|min:0',
            'materials' => 'nullable|array',
            'materials.*.production_plan_step4_row_index' => 'required|integer',
            'materials.*.packing_material_item_id' => 'required|exists:items,id',
            'materials.*.quantity_total' => 'required|numeric|min:0',
        ]);

        $packingItemIds = collect($validated['step4'])
            ->pluck('kerupuk_packing_item_id')
            ->unique()
            ->values();

        $blueprintsByPackingId = PackingMaterialBlueprint::whereIn('pack_item_id', $packingItemIds)
            ->get()
            ->groupBy('pack_item_id');

        $missingBlueprintItems = $packingItemIds->filter(static function ($packingItemId) use ($blueprintsByPackingId) {
            return !$blueprintsByPackingId->has($packingItemId) || $blueprintsByPackingId->get($packingItemId)->isEmpty();
        });

        if ($missingBlueprintItems->isNotEmpty()) {
            $missingNames = Item::whereIn('id', $missingBlueprintItems)->pluck('name')->implode(', ');

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'step4' => sprintf(
                        'Packing material blueprint not found for: %s. Please define packing materials before saving.',
                        $missingNames
                    ),
                ]);
        }

        DB::transaction(function () use ($productionPlan, $validated, $blueprintsByPackingId): void {
            $productionPlan->step4()->delete();

            // Group materials by row index
            $materialsByRowIndex = [];
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    $rowIndex = $material['production_plan_step4_row_index'];
                    if (!isset($materialsByRowIndex[$rowIndex])) {
                        $materialsByRowIndex[$rowIndex] = [];
                    }
                    $materialsByRowIndex[$rowIndex][] = [
                        'packing_material_item_id' => $material['packing_material_item_id'],
                        'quantity_total' => (int) $material['quantity_total'],
                    ];
                }
            }

            foreach ($validated['step4'] as $rowIndex => $data) {
                $packingItem = Item::find($data['kerupuk_packing_item_id']);

                if (!$packingItem) {
                    continue;
                }

                $weightPerUnit = $this->resolveWeightPerUnit($packingItem, (float) ($data['weight_per_unit'] ?? 0.0));

                $qtyGl1Packing = (int) $data['qty_gl1_packing'];
                $qtyGl2Packing = (int) $data['qty_gl2_packing'];
                $qtyTaPacking = (int) $data['qty_ta_packing'];
                $qtyBlPacking = (int) $data['qty_bl_packing'];

                $qtyGl1Kg = round($qtyGl1Packing * $weightPerUnit, 2);
                $qtyGl2Kg = round($qtyGl2Packing * $weightPerUnit, 2);
                $qtyTaKg = round($qtyTaPacking * $weightPerUnit, 2);
                $qtyBlKg = round($qtyBlPacking * $weightPerUnit, 2);

                $step4Record = $productionPlan->step4()->create([
                    'kerupuk_kering_item_id' => $data['kerupuk_kering_item_id'],
                    'kerupuk_packing_item_id' => $data['kerupuk_packing_item_id'],
                    'weight_per_unit' => $weightPerUnit,
                    'qty_gl1_kg' => $qtyGl1Kg,
                    'qty_gl1_packing' => $qtyGl1Packing,
                    'qty_gl2_kg' => $qtyGl2Kg,
                    'qty_gl2_packing' => $qtyGl2Packing,
                    'qty_ta_kg' => $qtyTaKg,
                    'qty_ta_packing' => $qtyTaPacking,
                    'qty_bl_kg' => $qtyBlKg,
                    'qty_bl_packing' => $qtyBlPacking,
                ]);

                // Save materials for this specific row
                if (isset($materialsByRowIndex[$rowIndex]) && !empty($materialsByRowIndex[$rowIndex])) {
                    // User provided custom materials for this row
                    foreach ($materialsByRowIndex[$rowIndex] as $material) {
                        $step4Record->materials()->create($material);
                    }
                } else {
                    // Use blueprint to auto-calculate materials
                    $this->calculationService->syncPackingMaterials($step4Record, $blueprintsByPackingId->get($data['kerupuk_packing_item_id']));
                }
            }
        });

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 4 (Packing Planning) saved successfully.');
    }

    private function resolveWeightPerUnit(Item $packingItem, float $requestedWeight): float
    {
        if ($requestedWeight > 0) {
            return $requestedWeight;
        }

        if ($packingItem->qty_kg_per_pack && $packingItem->qty_kg_per_pack > 0) {
            return (float) $packingItem->qty_kg_per_pack;
        }

        return 1.0;
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
















