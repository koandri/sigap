<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\KerupukPackConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class KerupukPackConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.kerupuk-pack-config.view')->only(['index', 'manage']);
        $this->middleware('can:manufacturing.kerupuk-pack-config.edit')->only(['update']);
    }

    public function index(Request $request): View
    {
        $search = $request->input('search');

        $kerupukKgItems = Item::with(['itemCategory'])
            ->withCount('kerupukPackConfigurations')
            ->whereHas('itemCategory', static function ($query): void {
                $query->where('name', 'like', '%Kerupuk Kg%');
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

        return view('manufacturing.kerupuk-pack-configurations.index', compact('kerupukKgItems', 'search'));
    }

    public function manage(Item $item): View
    {
        $item->load(['itemCategory', 'kerupukPackConfigurations.packItem.itemCategory']);

        // Get available pack items
        $packItems = Item::with('itemCategory:id,name')
            ->where('is_active', true)
            ->whereHas('itemCategory', static function ($query): void {
                $query->where('name', 'like', '%Kerupuk Pack%');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'item_category_id']);

        return view('manufacturing.kerupuk-pack-configurations.manage', compact('item', 'packItems'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'configurations' => ['required', 'array', 'min:1'],
            'configurations.*.pack_item_id' => [
                'required',
                'exists:items,id',
                'distinct',
            ],
            'configurations.*.qty_kg_per_pack' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
        ], [
            'configurations.required' => 'At least one pack SKU is required.',
            'configurations.*.pack_item_id.required' => 'Please select a pack SKU.',
            'configurations.*.pack_item_id.distinct' => 'Each pack SKU can only be added once.',
            'configurations.*.qty_kg_per_pack.required' => 'Kg per pack quantity is required.',
            'configurations.*.qty_kg_per_pack.numeric' => 'Kg per pack must be a number.',
            'configurations.*.qty_kg_per_pack.min' => 'Kg per pack must be at least 0.01.',
        ]);

        DB::transaction(function () use ($item, $validated): void {
            // Delete existing configurations
            $item->kerupukPackConfigurations()->delete();

            // Create new configurations
            foreach ($validated['configurations'] as $config) {
                $item->kerupukPackConfigurations()->create([
                    'pack_item_id' => $config['pack_item_id'],
                    'qty_kg_per_pack' => $config['qty_kg_per_pack'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()
            ->route('manufacturing.kerupuk-pack-configurations.index')
            ->with('success', "Pack configurations for {$item->name} updated successfully.");
    }
}

