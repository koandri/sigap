<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ProductionPlan;
use App\Models\Recipe;
use App\Services\ProductionPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ProductionPlanController extends Controller
{
    public function __construct(
        private readonly ProductionPlanningService $planningService
    ) {
        $this->middleware('can:manufacturing.production-plans.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.production-plans.create')->only(['create', 'store']);
        $this->middleware('can:manufacturing.production-plans.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.production-plans.delete')->only(['destroy']);
        $this->middleware('can:manufacturing.production-plans.approve')->only(['approve']);
    }

    /**
     * Display a listing of production plans.
     */
    public function index(Request $request): View
    {
        $query = ProductionPlan::with(['createdBy', 'approvedBy'])
            ->orderBy('plan_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('plan_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('plan_date', '<=', $request->date_to);
        }

        $plans = $query->paginate(15);

        return view('manufacturing.production-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new production plan.
     */
    public function create(): View
    {
        // Get dough items (Adonan) from ItemCategory
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        return view('manufacturing.production-plans.create', compact('doughItems'));
    }

    /**
     * Store a newly created production plan (Step 1).
     */
    public function store(StoreProductionPlanRequest $request): RedirectResponse
    {
        $plan = $this->planningService->createProductionPlan($request->validated(), Auth::user());

        // Handle Step 1 data if provided
        if ($request->has('step1')) {
            $this->storeStep1($plan, $request->input('step1', []));
        }

        return redirect()
            ->route('manufacturing.production-plans.show', $plan)
            ->with('success', 'Production plan created successfully. You can now add steps 2, 3, and 4.');
    }

    /**
     * Display the specified production plan.
     */
    public function show(ProductionPlan $productionPlan): View
    {
        $productionPlan->load([
            'createdBy',
            'approvedBy',
            'step1.doughItem',
            'step1.recipe',
            'step2.adonanItem',
            'step2.gelondonganItem',
            'step3.gelondonganItem',
            'step3.kerupukKeringItem',
            'step4.kerupukKeringItem',
            'step4.kerupukPackingItem',
            'step4.packingMaterialRequirements',
        ]);

        $totals = $this->planningService->getTotalQuantities($productionPlan);
        $isComplete = $this->planningService->isComplete($productionPlan);

        return view('manufacturing.production-plans.show', compact(
            'productionPlan',
            'totals',
            'isComplete'
        ));
    }

    /**
     * Show the form for editing the specified production plan.
     */
    public function edit(ProductionPlan $productionPlan): View
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', 'Cannot edit production plan that is not in draft status.');
        }

        $productionPlan->load(['step1.doughItem', 'step1.recipe']);

        // Get dough items
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        return view('manufacturing.production-plans.edit', compact('productionPlan', 'doughItems'));
    }

    /**
     * Update the specified production plan.
     */
    public function update(UpdateProductionPlanRequest $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $this->planningService->updateProductionPlan($productionPlan, $request->validated());

        // Update Step 1 data if provided
        if ($request->has('step1')) {
            $this->updateStep1($productionPlan, $request->input('step1', []));
        }

        return redirect()
            ->route('manufacturing.production-plans.show', $productionPlan)
            ->with('success', 'Production plan updated successfully.');
    }

    /**
     * Remove the specified production plan.
     */
    public function destroy(ProductionPlan $productionPlan): RedirectResponse
    {
        if (!$productionPlan->canBeEdited()) {
            return redirect()
                ->route('manufacturing.production-plans.index')
                ->with('error', 'Cannot delete production plan that is not in draft status.');
        }

        $productionPlan->delete();

        return redirect()
            ->route('manufacturing.production-plans.index')
            ->with('success', 'Production plan deleted successfully.');
    }

    /**
     * Approve the specified production plan.
     */
    public function approve(ProductionPlan $productionPlan): RedirectResponse
    {
        try {
            $this->planningService->approveProductionPlan($productionPlan, Auth::user());

            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('success', 'Production plan approved successfully.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('manufacturing.production-plans.show', $productionPlan)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Store Step 1 data for a production plan.
     */
    private function storeStep1(ProductionPlan $plan, array $step1Data): void
    {
        foreach ($step1Data as $data) {
            if (empty($data['dough_item_id'])) {
                continue;
            }

            $doughItem = Item::findOrFail($data['dough_item_id']);
            $recipe = null;
            $recipeName = null;
            $recipeDate = null;
            $isCustomRecipe = false;

            // Handle recipe selection
            if (!empty($data['recipe_id'])) {
                $recipe = Recipe::findOrFail($data['recipe_id']);
                $recipeName = $recipe->name;
                $recipeDate = $recipe->recipe_date;
            } elseif (!empty($data['is_custom_recipe'])) {
                $isCustomRecipe = true;
                $recipeName = $data['recipe_name'] ?? $doughItem->name;
                $recipeDate = $data['recipe_date'] ?? now()->toDateString();
            }

            $plan->step1()->create([
                'dough_item_id' => $doughItem->id,
                'recipe_id' => $recipe?->id,
                'recipe_name' => $recipeName,
                'recipe_date' => $recipeDate,
                'qty_gl1' => $data['qty_gl1'] ?? 0,
                'qty_gl2' => $data['qty_gl2'] ?? 0,
                'qty_ta' => $data['qty_ta'] ?? 0,
                'qty_bl' => $data['qty_bl'] ?? 0,
                'is_custom_recipe' => $isCustomRecipe,
            ]);
        }
    }

    /**
     * Update Step 1 data for a production plan.
     */
    private function updateStep1(ProductionPlan $plan, array $step1Data): void
    {
        // Delete existing Step 1 records
        $plan->step1()->delete();

        // Create new Step 1 records
        $this->storeStep1($plan, $step1Data);
    }

    /**
     * Get recipes for a dough item (AJAX).
     */
    public function getRecipes(Request $request, ?ProductionPlan $productionPlan = null): \Illuminate\Http\JsonResponse
    {
        $doughItemId = $request->input('dough_item_id');

        if (!$doughItemId) {
            return response()->json([]);
        }

        $recipes = Recipe::forDough((int) $doughItemId)
            ->active()
            ->orderBy('recipe_date', 'desc')
            ->get(['id', 'name', 'recipe_date']);

        return response()->json($recipes);
    }
}

