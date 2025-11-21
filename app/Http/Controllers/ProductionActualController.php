<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RecordProductionActualStep1Request;
use App\Http\Requests\RecordProductionActualStep2Request;
use App\Http\Requests\RecordProductionActualStep3Request;
use App\Http\Requests\RecordProductionActualStep4Request;
use App\Http\Requests\RecordProductionActualStep5Request;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PackingMaterialBlueprint;
use App\Models\ProductionPlan;
use App\Services\ProductionActualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ProductionActualController extends Controller
{
    public function __construct(
        private readonly ProductionActualService $actualService
    ) {
        $this->middleware('can:manufacturing.production-plans.view-actuals')->only(['show']);
        $this->middleware('can:manufacturing.production-plans.start')->only(['start']);
        $this->middleware('can:manufacturing.production-plans.record-actuals')->only(['execute', 'recordStep1', 'recordStep2', 'recordStep3', 'recordStep4', 'recordStep5']);
        $this->middleware('can:manufacturing.production-plans.complete')->only(['complete']);
    }

    /**
     * Check if user has access to a specific step based on role.
     */
    private function canAccessStep(int $stepNumber): bool
    {
        $user = Auth::user();

        // Super Admin and Owner can access all steps
        if ($user->hasRole('Super Admin') || $user->hasRole('Owner')) {
            return true;
        }

        return match ($stepNumber) {
            1 => $user->hasRole('R&D'),
            2 => $user->hasRole('Production'),
            3, 4, 5 => $user->hasRole('PPIC') || $user->hasRole('QC'),
            default => false,
        };
    }

    /**
     * Validate step-by-step progression.
     */
    private function validateStepProgression(ProductionPlan $productionPlan, int $stepNumber): bool
    {
        $actual = $productionPlan->actual;
        if (!$actual) {
            return false;
        }

        // Step 1 has no dependency
        if ($stepNumber === 1) {
            return true;
        }

        // Check if previous step has actual data
        return match ($stepNumber) {
            2 => $actual->step1()->exists(),
            3 => $actual->step2()->exists(),
            4 => $actual->step3()->exists(),
            5 => $actual->step4()->exists(),
            default => false,
        };
    }

    /**
     * Start production for an approved plan.
     */
    public function start(ProductionPlan $productionPlan): RedirectResponse
    {
        try {
            $this->actualService->startProduction($productionPlan, Auth::user());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Production started successfully. You can now record actual production data.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show production execution form.
     */
    public function execute(ProductionPlan $productionPlan): View|RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Production has not been started yet.');
        }

        // Load all steps with relationships
        $productionPlan->load([
            'step1.doughItem',
            'step1.recipe',
            'step1.recipeIngredients.ingredientItem',
            'step1.actualStep1',
            'step2.adonanItem',
            'step2.gelondonganItem',
            'step2.actualStep2',
            'step3.gelondonganItem',
            'step3.kerupukKeringItem',
            'step3.actualStep3',
            'step4.kerupukKeringItem',
            'step4.kerupukPackingItem',
            'step4.actualStep4',
            'step5.packSku',
            'step5.packingMaterialItem',
            'step5.actualStep5',
        ]);

        $progress = $this->actualService->getProductionProgress($productionPlan);

        // Check role-based access for each step
        $stepAccess = [];
        for ($i = 1; $i <= 5; $i++) {
            $stepAccess[$i] = $this->canAccessStep($i) && $this->validateStepProgression($productionPlan, $i);
        }

        // Get data for adding new items (Step 1)
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->whereHas('recipes', function ($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('name')
                ->get()
            : collect([]);

        $ingredientCategories = ItemCategory::whereIn('name', ['Bahan Baku Lainnya', 'Ikan', 'Tepung', 'Udang'])->pluck('id');
        $ingredientItems = Item::whereIn('item_category_id', $ingredientCategories)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get data for Step 2
        $adonanCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $adonanItems = $adonanCategory
            ? Item::where('item_category_id', $adonanCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        $gelondonganCategory = ItemCategory::where('name', 'like', '%Gelondongan%')->first();
        $gelondonganItems = $gelondonganCategory
            ? Item::where('item_category_id', $gelondonganCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        // Get data for Step 3
        $kerupukKeringCategory = ItemCategory::where('name', 'like', '%Kerupuk Kering%')->first();
        $kerupukKeringItems = $kerupukKeringCategory
            ? Item::where('item_category_id', $kerupukKeringCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        // Get data for Step 4
        $packingItems = Item::with(['packingMaterialBlueprints.packingMaterialItem'])
            ->whereHas('itemCategory', static function ($query): void {
                $query->where('name', 'like', '%Kerupuk Pack%');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get data for Step 5
        $packingMaterialItems = Item::whereHas('itemCategory', static function ($query): void {
            $query->whereIn('name', ['Bahan Pembantu Lainnya', 'Plastik', 'Dos']);
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $packingBlueprints = PackingMaterialBlueprint::with('packingMaterialItem')
            ->whereIn('pack_item_id', $productionPlan->step4->pluck('kerupuk_packing_item_id')->unique())
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

        return view('manufacturing.production-plans.execute', compact(
            'productionPlan',
            'actual',
            'progress',
            'stepAccess',
            'doughItems',
            'ingredientItems',
            'adonanItems',
            'gelondonganItems',
            'kerupukKeringItems',
            'packingItems',
            'packingMaterialItems',
            'packingBlueprints'
        ));
    }

    /**
     * Show actual production comparison view.
     */
    public function show(ProductionPlan $productionPlan): View
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Production has not been started yet.');
        }

        // Load all steps with relationships
        $productionPlan->load([
            'step1.doughItem',
            'step1.actualStep1',
            'step2.adonanItem',
            'step2.gelondonganItem',
            'step2.actualStep2',
            'step3.gelondonganItem',
            'step3.kerupukKeringItem',
            'step3.actualStep3',
            'step4.kerupukKeringItem',
            'step4.kerupukPackingItem',
            'step4.actualStep4',
            'step5.packSku',
            'step5.packingMaterialItem',
            'step5.actualStep5',
        ]);

        $variances = $this->actualService->calculateVariances($productionPlan);
        $progress = $this->actualService->getProductionProgress($productionPlan);

        return view('manufacturing.production-plans.actuals', compact('productionPlan', 'actual', 'variances', 'progress'));
    }

    /**
     * Record Step 1 actual production data.
     */
    public function recordStep1(ProductionPlan $productionPlan, RecordProductionActualStep1Request $request): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        if (!$this->canAccessStep(1)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to access Step 1.');
        }

        if (!$this->validateStepProgression($productionPlan, 1)) {
            return redirect()
                ->back()
                ->with('error', 'Cannot access Step 1.');
        }

        try {
            $this->actualService->recordStep1($actual, $request->all());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 1 actual data recorded successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record Step 2 actual production data.
     */
    public function recordStep2(ProductionPlan $productionPlan, RecordProductionActualStep2Request $request): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        if (!$this->canAccessStep(2)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to access Step 2.');
        }

        if (!$this->validateStepProgression($productionPlan, 2)) {
            return redirect()
                ->back()
                ->with('error', 'Please complete Step 1 first.');
        }

        try {
            $this->actualService->recordStep2($actual, $request->all());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 2 actual data recorded successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record Step 3 actual production data.
     */
    public function recordStep3(ProductionPlan $productionPlan, RecordProductionActualStep3Request $request): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        if (!$this->canAccessStep(3)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to access Step 3.');
        }

        if (!$this->validateStepProgression($productionPlan, 3)) {
            return redirect()
                ->back()
                ->with('error', 'Please complete Step 2 first.');
        }

        try {
            $this->actualService->recordStep3($actual, $request->all());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 3 actual data recorded successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record Step 4 actual production data.
     */
    public function recordStep4(ProductionPlan $productionPlan, RecordProductionActualStep4Request $request): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        if (!$this->canAccessStep(4)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to access Step 4.');
        }

        if (!$this->validateStepProgression($productionPlan, 4)) {
            return redirect()
                ->back()
                ->with('error', 'Please complete Step 3 first.');
        }

        try {
            $this->actualService->recordStep4($actual, $request->all());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 4 actual data recorded successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Record Step 5 actual production data.
     */
    public function recordStep5(ProductionPlan $productionPlan, RecordProductionActualStep5Request $request): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        if (!$this->canAccessStep(5)) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to access Step 5.');
        }

        if (!$this->validateStepProgression($productionPlan, 5)) {
            return redirect()
                ->back()
                ->with('error', 'Please complete Step 4 first.');
        }

        try {
            $this->actualService->recordStep5($actual, $request->all());

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 5 actual data recorded successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Step 1 actual record.
     */
    public function deleteStep1(ProductionPlan $productionPlan, int $actualStep1Id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            $message = 'Production has not been started yet.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        if (!$this->canAccessStep(1)) {
            $message = 'You do not have permission to delete Step 1 records.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $this->actualService->deleteStep1($actual, $actualStep1Id);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Step 1 record deleted successfully.']);
            }

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 1 record deleted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Step 2 actual record.
     */
    public function deleteStep2(ProductionPlan $productionPlan, int $actualStep2Id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            $message = 'Production has not been started yet.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        if (!$this->canAccessStep(2)) {
            $message = 'You do not have permission to delete Step 2 records.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $this->actualService->deleteStep2($actual, $actualStep2Id);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Step 2 record deleted successfully.']);
            }

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 2 record deleted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Step 3 actual record.
     */
    public function deleteStep3(ProductionPlan $productionPlan, int $actualStep3Id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            $message = 'Production has not been started yet.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        if (!$this->canAccessStep(3)) {
            $message = 'You do not have permission to delete Step 3 records.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $this->actualService->deleteStep3($actual, $actualStep3Id);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Step 3 record deleted successfully.']);
            }

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 3 record deleted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Step 4 actual record.
     */
    public function deleteStep4(ProductionPlan $productionPlan, int $actualStep4Id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            $message = 'Production has not been started yet.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        if (!$this->canAccessStep(4)) {
            $message = 'You do not have permission to delete Step 4 records.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $this->actualService->deleteStep4($actual, $actualStep4Id);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Step 4 record deleted successfully.']);
            }

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 4 record deleted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Step 5 actual record.
     */
    public function deleteStep5(ProductionPlan $productionPlan, int $actualStep5Id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            $message = 'Production has not been started yet.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        if (!$this->canAccessStep(5)) {
            $message = 'You do not have permission to delete Step 5 records.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $this->actualService->deleteStep5($actual, $actualStep5Id);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Step 5 record deleted successfully.']);
            }

            return redirect()
                ->route('manufacturing.production-plans.execute', $productionPlan)
                ->with('success', 'Step 5 record deleted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete production.
     */
    public function complete(ProductionPlan $productionPlan): RedirectResponse
    {
        $actual = $productionPlan->actual;

        if (!$actual) {
            return redirect()
                ->back()
                ->with('error', 'Production has not been started yet.');
        }

        try {
            $this->actualService->completeProduction($actual);

            return redirect()
                ->route('manufacturing.production-plans.actuals', $productionPlan)
                ->with('success', 'Production marked as completed successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}

