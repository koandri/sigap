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

        // Also load blueprints for Pack SKUs that are already in Step 4 (in case they're not in the main list)
        $existingPackSkuIds = $productionPlan->step4->pluck('kerupuk_packing_item_id')->unique();
        $additionalPackingItems = Item::with(['packingMaterialBlueprints.packingMaterialItem'])
            ->whereIn('id', $existingPackSkuIds)
            ->whereNotIn('id', $packingItems->pluck('id'))
            ->get();
        
        // Merge both collections
        $allPackingItems = $packingItems->merge($additionalPackingItems)->unique('id');

        $kerupukKeringOptions = $productionPlan->step3
            ->unique('kerupuk_kering_item_id')
            ->map(static function ($step3) {
                return [
                    'id' => $step3->kerupuk_kering_item_id,
                    'name' => $step3->kerupukKeringItem->name ?? 'N/A',
                ];
            })
            ->values();

        // Get Step 3 limits for validation
        $step3Limits = $productionPlan->step3
            ->groupBy('kerupuk_kering_item_id')
            ->map(static function ($group) {
                return [
                    'qty_gl1_kg' => $group->sum('qty_gl1_kg'),
                    'qty_gl2_kg' => $group->sum('qty_gl2_kg'),
                    'qty_ta_kg' => $group->sum('qty_ta_kg'),
                    'qty_bl_kg' => $group->sum('qty_bl_kg'),
                ];
            });

        // Load blueprints for ALL Pack SKUs (from both collections)
        $packingBlueprints = $allPackingItems
            ->mapWithKeys(static function ($item) {
                return [
                    (string) $item->id => $item->packingMaterialBlueprints->map(static function ($blueprint) {
                        return [
                            'material_id' => $blueprint->material_item_id,
                            'material_name' => $blueprint->packingMaterialItem->name ?? 'N/A',
                            'qty_per_pack' => (float) $blueprint->quantity_per_pack,
                            'unit' => $blueprint->packingMaterialItem->unit ?? 'pcs',
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

        // Get weight configurations for quick lookup
        $weightConfigurations = \App\Models\KerupukPackConfiguration::with('packItem')
            ->whereIn('kerupuk_kg_item_id', $kerupukKeringOptions->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(static function ($config) {
                $key = $config->kerupuk_kg_item_id . '_' . $config->pack_item_id;
                return [$key => (float) $config->qty_kg_per_pack];
            });

        return view('manufacturing.production-plans.step4', compact(
            'productionPlan',
            'calculatedData',
            'packingItems',
            'allPackingItems',
            'packingBlueprints',
            'kerupukKeringOptions',
            'packConfigurations',
            'weightConfigurations',
            'step3Limits'
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

        $validated = $request->validate([
            'step4' => 'required|array',
            'step4.*.kerupuk_kering_item_id' => 'required|exists:items,id',
            'step4.*.kerupuk_packing_item_id' => 'required|exists:items,id',
            'step4.*.qty_gl1_packing' => 'required|numeric|min:0',
            'step4.*.qty_gl2_packing' => 'required|numeric|min:0',
            'step4.*.qty_ta_packing' => 'required|numeric|min:0',
            'step4.*.qty_bl_packing' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($productionPlan, $validated): void {
            // Delete existing Step 4 data
            $productionPlan->step4()->delete();

            foreach ($validated['step4'] as $rowIndex => $data) {
                // Get weight from KerupukPackConfiguration
                $config = \App\Models\KerupukPackConfiguration::where('kerupuk_kg_item_id', $data['kerupuk_kering_item_id'])
                    ->where('pack_item_id', $data['kerupuk_packing_item_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$config) {
                    throw new \Exception('Kerupuk Pack Configuration not found for the selected items.');
                }

                $weightPerUnit = (float) $config->qty_kg_per_pack;

                $qtyGl1Packing = (int) $data['qty_gl1_packing'];
                $qtyGl2Packing = (int) $data['qty_gl2_packing'];
                $qtyTaPacking = (int) $data['qty_ta_packing'];
                $qtyBlPacking = (int) $data['qty_bl_packing'];

                $qtyGl1Kg = round($qtyGl1Packing * $weightPerUnit, 2);
                $qtyGl2Kg = round($qtyGl2Packing * $weightPerUnit, 2);
                $qtyTaKg = round($qtyTaPacking * $weightPerUnit, 2);
                $qtyBlKg = round($qtyBlPacking * $weightPerUnit, 2);

                $productionPlan->step4()->create([
                    'kerupuk_kering_item_id' => $data['kerupuk_kering_item_id'],
                    'kerupuk_packing_item_id' => $data['kerupuk_packing_item_id'],
                    'qty_gl1_kg' => $qtyGl1Kg,
                    'qty_gl1_packing' => $qtyGl1Packing,
                    'qty_gl2_kg' => $qtyGl2Kg,
                    'qty_gl2_packing' => $qtyGl2Packing,
                    'qty_ta_kg' => $qtyTaKg,
                    'qty_ta_packing' => $qtyTaPacking,
                    'qty_bl_kg' => $qtyBlKg,
                    'qty_bl_packing' => $qtyBlPacking,
                ]);
            }
        });

        return redirect()
            ->route('manufacturing.production-plans.step5', $productionPlan)
            ->with('success', 'Step 4 (Packing Output Planning) saved successfully. Please continue to Step 5.');
    }


    /**
     * Show Step 5 form.
     */
    public function step5(ProductionPlan $productionPlan): View|RedirectResponse
    {
        // Validate dependency
        if (!$this->planningService->validateStepDependency($productionPlan, 5)) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Please complete Step 4 first.');
        }

        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit production plan that is not in draft status.');
        }

        $productionPlan->load([
            'step4.kerupukKeringItem',
            'step4.kerupukPackingItem',
            'step5.packingMaterialItem',
        ]);

        // Get unique Pack SKUs from Step 4 and their materials from Step 5
        $packSkus = $productionPlan->step4
            ->groupBy('kerupuk_packing_item_id')
            ->map(function ($group) use ($productionPlan) {
                $first = $group->first();
                $packSkuId = $first->kerupuk_packing_item_id;
                
                // Get materials for this Pack SKU from Step 5
                $materials = $productionPlan->step5->where('pack_sku_id', $packSkuId);
                
                return [
                    'pack_sku_id' => $packSkuId,
                    'pack_sku_name' => $first->kerupukPackingItem->name ?? 'N/A',
                    'total_qty' => $group->sum('total_packing'),
                    'materials' => $materials,
                ];
            })
            ->values();

        // Get all packing material items for the dropdown from specific categories
        $packingMaterialItems = Item::whereHas('itemCategory', static function ($query): void {
            $query->whereIn('name', ['Bahan Pembantu Lainnya', 'Plastik', 'Dos']);
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get packing blueprints for all Pack SKUs
        $packingBlueprints = PackingMaterialBlueprint::with('packingMaterialItem')
            ->whereIn('pack_item_id', $packSkus->pluck('pack_sku_id'))
            ->get()
            ->groupBy('pack_item_id')
            ->mapWithKeys(static function ($blueprints, $packItemId) {
                return [
                    (string) $packItemId => $blueprints->map(static function ($blueprint) {
                        return [
                            'packing_material_item_id' => $blueprint->material_item_id,
                            'packing_material_item_name' => $blueprint->packingMaterialItem->name ?? 'N/A',
                            'quantity_per_pack' => (float) $blueprint->quantity_per_pack,
                            'unit' => $blueprint->packingMaterialItem->unit ?? 'pcs',
                        ];
                    }),
                ];
            });

        return view('manufacturing.production-plans.step5', compact(
            'productionPlan',
            'packSkus',
            'packingMaterialItems',
            'packingBlueprints'
        ));
    }

    /**
     * Store Step 5 data.
     */
    public function storeStep5(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit production plan that is not in draft status.');
        }

        $validated = $request->validate([
            'materials' => 'required|array',
            'materials.*.pack_sku_id' => 'required|exists:items,id',
            'materials.*.packing_material_item_id' => 'required|exists:items,id',
            'materials.*.quantity_total' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($productionPlan, $validated): void {
            // Delete all existing materials from Step 5
            $productionPlan->step5()->delete();

            // Save materials to Step 5
            foreach ($validated['materials'] as $material) {
                $productionPlan->step5()->create([
                    'pack_sku_id' => $material['pack_sku_id'],
                    'packing_material_item_id' => $material['packing_material_item_id'],
                    'quantity_total' => (int) $material['quantity_total'],
                ]);
            }
        });

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Step 5 (Packing Materials Planning) saved successfully.');
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
















