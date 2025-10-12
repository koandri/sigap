<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BomTemplate;
use App\Models\BomType;
use App\Models\BomIngredient;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class BomController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.bom.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.bom.create')->only(['create', 'store', 'copy']);
        $this->middleware('can:manufacturing.bom.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.bom.delete')->only(['destroy']);
    }

    /**
     * Display a listing of BoM templates.
     */
    public function index(Request $request): View
    {
        $query = BomTemplate::with(['bomType', 'outputItem', 'createdBy']);

        // Filter by BoM type
        if ($request->filled('type')) {
            $query->where('bom_type_id', $request->type);
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $bomTemplates = $query->orderBy('created_at', 'desc')->paginate(20);
        $bomTypes = BomType::active()->orderBy('name')->get();

        return view('manufacturing.bom.index', compact('bomTemplates', 'bomTypes'));
    }

    /**
     * Show the form for creating a new BoM template.
     */
    public function create(Request $request): View
    {
        $bomTypes = BomType::active()->orderBy('name')->get();
        $items = Item::active()->with('itemCategory')->orderBy('name')->get();
        
        // If copying from existing template
        $sourceTemplate = null;
        if ($request->filled('copy_from')) {
            $sourceTemplate = BomTemplate::with('ingredients.ingredientItem')->find($request->copy_from);
        }

        return view('manufacturing.bom.create', compact('bomTypes', 'items', 'sourceTemplate'));
    }

    /**
     * Store a newly created BoM template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bom_type_id' => 'required|exists:bom_types,id',
            'code' => 'required|string|max:20|unique:bom_templates',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'output_item_id' => 'required|exists:items,id',
            'output_quantity' => 'required|numeric|min:0.001',
            'output_unit' => 'nullable|string|max:15',
            'is_template' => 'boolean',
            'parent_template_id' => 'nullable|exists:bom_templates,id',
            'ingredients' => 'array',
            'ingredients.*.ingredient_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.001',
            'ingredients.*.unit' => 'nullable|string|max:15',
        ]);

        DB::transaction(function () use ($validated) {
            // Create the BoM template
            $validated['created_by'] = Auth::id();
            $ingredients = $validated['ingredients'] ?? [];
            unset($validated['ingredients']);

            $bomTemplate = BomTemplate::create($validated);

            // Add ingredients
            foreach ($ingredients as $index => $ingredientData) {
                $ingredientData['bom_template_id'] = $bomTemplate->id;
                $ingredientData['sort_order'] = $index + 1;

                BomIngredient::create($ingredientData);
            }
        });

        return redirect()
            ->route('manufacturing.bom.index')
            ->with('success', 'BoM template created successfully.');
    }

    /**
     * Display the specified BoM template.
     */
    public function show(BomTemplate $bom): View
    {
        $bom->load([
            'bomType',
            'outputItem.itemCategory',
            'createdBy',
            'ingredients.ingredientItem.itemCategory',
            'parentTemplate',
            'childTemplates'
        ]);

        return view('manufacturing.bom.show', compact('bom'));
    }

    /**
     * Show the form for editing the specified BoM template.
     */
    public function edit(BomTemplate $bom): View
    {
        $bom->load(['ingredients.ingredientItem']);
        $bomTypes = BomType::active()->orderBy('name')->get();
        $items = Item::active()->with('itemCategory')->orderBy('name')->get();

        return view('manufacturing.bom.edit', compact('bom', 'bomTypes', 'items'));
    }

    /**
     * Update the specified BoM template.
     */
    public function update(Request $request, BomTemplate $bom): RedirectResponse
    {
        $validated = $request->validate([
            'bom_type_id' => 'required|exists:bom_types,id',
            'code' => 'required|string|max:20|unique:bom_templates,code,' . $bom->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'output_item_id' => 'required|exists:items,id',
            'output_quantity' => 'required|numeric|min:0.001',
            'output_unit' => 'nullable|string|max:15',
            'is_template' => 'boolean',
            'ingredients' => 'array',
            'ingredients.*.ingredient_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.001',
            'ingredients.*.unit' => 'nullable|string|max:15',
        ]);

        DB::transaction(function () use ($validated, $bom) {
            // Update the BoM template
            $ingredients = $validated['ingredients'] ?? [];
            unset($validated['ingredients']);

            $bom->update($validated);

            // Remove existing ingredients and recreate
            $bom->ingredients()->delete();

            // Add updated ingredients
            foreach ($ingredients as $index => $ingredientData) {
                $ingredientData['bom_template_id'] = $bom->id;
                $ingredientData['sort_order'] = $index + 1;

                BomIngredient::create($ingredientData);
            }
        });

        return redirect()
            ->route('manufacturing.bom.show', $bom)
            ->with('success', 'BoM template updated successfully.');
    }

    /**
     * Remove the specified BoM template.
     */
    public function destroy(BomTemplate $bom): RedirectResponse
    {
        $name = $bom->name;
        $bom->delete();

        return redirect()
            ->route('manufacturing.bom.index')
            ->with('success', "BoM template '{$name}' deleted successfully.");
    }

    /**
     * Copy/clone an existing BoM template.
     */
    public function copy(BomTemplate $bom): RedirectResponse
    {
        return redirect()
            ->route('manufacturing.bom.create', ['copy_from' => $bom->id]);
    }
}
