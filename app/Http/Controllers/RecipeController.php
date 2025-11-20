<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.recipes.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.recipes.create')->only(['create', 'store', 'duplicate', 'storeDuplicate']);
        $this->middleware('can:manufacturing.recipes.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.recipes.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Recipe::with(['doughItem', 'createdBy']);

        // Filter by dough item if specified
        if ($request->filled('dough_item')) {
            $query->where('dough_item_id', $request->dough_item);
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $recipes = $query->orderBy('recipe_date', 'desc')->orderBy('name')->paginate(20);
        
        // Get dough items for filter dropdown
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        return view('manufacturing.recipes.index', compact('recipes', 'doughItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        // Get ingredient items from specific categories (only active items)
        $ingredientCategories = ItemCategory::whereIn('name', ['Bahan Baku Lainnya', 'Ikan', 'Tepung', 'Udang'])->pluck('id');
        $ingredientItems = Item::whereIn('item_category_id', $ingredientCategories)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('manufacturing.recipes.create', compact('doughItems', 'ingredientItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dough_item_id' => 'required|exists:items,id',
            'name' => 'required|string|max:100',
            'recipe_date' => 'required|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.sort_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $recipe = Recipe::create([
                'dough_item_id' => $validated['dough_item_id'],
                'name' => $validated['name'],
                'recipe_date' => $validated['recipe_date'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);

            // Create ingredients if provided
            if (!empty($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $index => $ingredientData) {
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_item_id' => $ingredientData['ingredient_item_id'],
                        'quantity' => $ingredientData['quantity'],
                        'sort_order' => $ingredientData['sort_order'] ?? $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('manufacturing.recipes.show', $recipe)
                ->with('success', "Recipe '{$recipe->name}' created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create recipe: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe): View
    {
        $recipe->load(['doughItem', 'createdBy', 'ingredients.ingredientItem']);
        
        return view('manufacturing.recipes.show', compact('recipe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe): View
    {
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        // Get ingredient items from specific categories (only active items)
        $ingredientCategories = ItemCategory::whereIn('name', ['Bahan Baku Lainnya', 'Ikan', 'Tepung', 'Udang'])->pluck('id');
        
        // Get active ingredient items
        $ingredientItems = Item::whereIn('item_category_id', $ingredientCategories)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recipe->load('ingredients.ingredientItem');

        return view('manufacturing.recipes.edit', compact('recipe', 'doughItems', 'ingredientItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $validated = $request->validate([
            'dough_item_id' => 'required|exists:items,id',
            'name' => 'required|string|max:100',
            'recipe_date' => 'required|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.sort_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $recipe->update([
                'dough_item_id' => $validated['dough_item_id'],
                'name' => $validated['name'],
                'recipe_date' => $validated['recipe_date'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? $recipe->is_active,
            ]);

            // Delete existing ingredients
            $recipe->ingredients()->delete();

            // Create new ingredients if provided
            if (!empty($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $index => $ingredientData) {
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_item_id' => $ingredientData['ingredient_item_id'],
                        'quantity' => $ingredientData['quantity'],
                        'sort_order' => $ingredientData['sort_order'] ?? $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('manufacturing.recipes.show', $recipe)
                ->with('success', "Recipe '{$recipe->name}' updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update recipe: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe): RedirectResponse
    {
        // Check if recipe is used in production plans
        $usedInPlans = DB::table('production_plan_step1')
            ->where('recipe_id', $recipe->id)
            ->exists();

        if ($usedInPlans) {
            return redirect()
                ->route('manufacturing.recipes.index')
                ->with('error', "Cannot delete recipe '{$recipe->name}' because it is used in production plans.");
        }

        $name = $recipe->name;
        $recipe->delete();

        return redirect()
            ->route('manufacturing.recipes.index')
            ->with('success', "Recipe '{$name}' deleted successfully.");
    }

    /**
     * Show the form for duplicating a recipe.
     */
    public function duplicate(Recipe $recipe): View
    {
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();
        $doughItems = $doughCategory
            ? Item::where('item_category_id', $doughCategory->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect([]);

        // Get ingredient items from specific categories (only active items)
        $ingredientCategories = ItemCategory::whereIn('name', ['Bahan Baku Lainnya', 'Ikan', 'Tepung', 'Udang'])->pluck('id');
        $ingredientItems = Item::whereIn('item_category_id', $ingredientCategories)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recipe->load('ingredients.ingredientItem');

        return view('manufacturing.recipes.duplicate', compact('recipe', 'doughItems', 'ingredientItems'));
    }

    /**
     * Store a duplicated recipe.
     */
    public function storeDuplicate(Request $request, Recipe $sourceRecipe): RedirectResponse
    {
        $validated = $request->validate([
            'dough_item_id' => 'required|exists:items,id',
            'name' => 'required|string|max:100',
            'recipe_date' => 'required|date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.sort_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $recipe = Recipe::create([
                'dough_item_id' => $validated['dough_item_id'],
                'name' => $validated['name'],
                'recipe_date' => $validated['recipe_date'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);

            // Copy ingredients from source recipe if not provided, otherwise use provided ingredients
            if (!empty($validated['ingredients'])) {
                foreach ($validated['ingredients'] as $index => $ingredientData) {
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_item_id' => $ingredientData['ingredient_item_id'],
                        'quantity' => $ingredientData['quantity'],
                        'sort_order' => $ingredientData['sort_order'] ?? $index,
                    ]);
                }
            } else {
                // Copy ingredients from source recipe
                foreach ($sourceRecipe->ingredients as $index => $ingredient) {
                    RecipeIngredient::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_item_id' => $ingredient->ingredient_item_id,
                        'quantity' => $ingredient->quantity,
                        'unit' => $ingredient->unit,
                        'sort_order' => $ingredient->sort_order ?? $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('manufacturing.recipes.show', $recipe)
                ->with('success', "Recipe '{$recipe->name}' created successfully from '{$sourceRecipe->name}'.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to duplicate recipe: ' . $e->getMessage());
        }
    }
}

