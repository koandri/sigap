<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PackingMaterialBlueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PackingMaterialBlueprintController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.packing-blueprints.view')->only(['index', 'manage']);
        $this->middleware('can:manufacturing.packing-blueprints.edit')->only(['update']);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search');

        $packItems = Item::with(['itemCategory'])
            ->withCount('packingMaterialBlueprints')
            ->whereHas('itemCategory', static function ($query): void {
                $query->where('name', 'like', '%Kerupuk Pack%');
            })
            ->where('is_active', true)
            ->when($search, static function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('itemCategory', static function ($categoryQuery) use ($search): void {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('manufacturing.packing-material-blueprints.index', compact('packItems', 'search'));
    }

    public function manage(Item $item): View
    {
        $item->load(['itemCategory', 'packingMaterialBlueprints.materialItem.itemCategory']);

        // Get available material items
        $materialItems = Item::with('itemCategory:id,name')
            ->where('is_active', true)
            ->whereHas('itemCategory', static function ($query): void {
                $query->whereIn('name', ['Bahan Pembantu Lainnya', 'Plastik', 'Dos']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'item_category_id']);

        return view('manufacturing.packing-material-blueprints.manage', compact('item', 'materialItems'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_item_id' => [
                'required',
                'exists:items,id',
                'distinct',
            ],
            'materials.*.quantity_per_pack' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'materials.required' => 'At least one packing material is required.',
            'materials.*.material_item_id.required' => 'Please select a material.',
            'materials.*.material_item_id.distinct' => 'Each material can only be added once.',
            'materials.*.quantity_per_pack.required' => 'Quantity is required.',
            'materials.*.quantity_per_pack.integer' => 'Quantity must be a whole number.',
            'materials.*.quantity_per_pack.min' => 'Quantity must be at least 1.',
        ]);

        DB::transaction(function () use ($item, $validated): void {
            // Delete existing blueprints
            $item->packingMaterialBlueprints()->delete();

            // Create new blueprints
            foreach ($validated['materials'] as $material) {
                $item->packingMaterialBlueprints()->create([
                    'material_item_id' => $material['material_item_id'],
                    'quantity_per_pack' => $material['quantity_per_pack'],
                ]);
            }
        });

        return redirect()
            ->route('manufacturing.packing-material-blueprints.index')
            ->with('success', "Packing materials for {$item->name} updated successfully.");
    }
}

