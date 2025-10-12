<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.items.view')->only(['index', 'show']);
        $this->middleware('can:manufacturing.items.edit')->only(['edit', 'update']);
        $this->middleware('can:manufacturing.items.delete')->only(['destroy']);
        $this->middleware('can:manufacturing.items.import')->only(['showImport', 'import']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Item::with('itemCategory');

        // Filter by category if specified
        if ($request->filled('category')) {
            $query->where('item_category_id', $request->category);
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or accurate_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('accurate_id', 'like', "%{$search}%")
                  ->orWhere('shortname', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(20);
        $categories = ItemCategory::orderBy('name')->get();

        return view('manufacturing.items.index', compact('items', 'categories'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Item $item): View
    {
        $item->load(['itemCategory', 'positionItems.shelfPosition.warehouseShelf.warehouse']);
        
        return view('manufacturing.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item): View
    {
        $categories = ItemCategory::orderBy('name')->get();
        
        return view('manufacturing.items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'accurate_id' => 'required|string|max:15|unique:items,accurate_id,' . $item->id,
            'shortname' => 'nullable|string|max:10',
            'name' => 'required|string|max:100|unique:items,name,' . $item->id,
            'item_category_id' => 'required|exists:item_categories,id',
            'unit' => 'nullable|string|max:15',
            'merk' => 'nullable|string|max:15',
            'qty_kg_per_pack' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()
            ->route('manufacturing.items.index')
            ->with('success', "Item '{$item->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item): RedirectResponse
    {
        // Check if item has positions with stock
        if ($item->positionItems()->where('quantity', '>', 0)->exists()) {
            return redirect()
                ->route('manufacturing.items.index')
                ->with('error', "Cannot delete item '{$item->name}' because it has current stock in warehouses.");
        }

        $name = $item->name;
        $item->delete();

        return redirect()
            ->route('manufacturing.items.index')
            ->with('success', "Item '{$name}' deleted successfully.");
    }

    /**
     * Show the import form.
     */
    public function showImport(): View
    {
        return view('manufacturing.items.import');
    }

    /**
     * Process the Excel import.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $dataRows = array_slice($rows, 1);
            
            $importStats = [
                'categories_created' => 0,
                'categories_skipped' => 0,
                'items_created' => 0,
                'items_skipped' => 0,
                'errors' => []
            ];

            foreach ($dataRows as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + 2; // +2 because we skip header and array is 0-indexed
                
                try {
                    // Extract data from Excel columns
                    $categoryName = trim($row[1] ?? ''); // Column B: "Kategori Barang"
                    $accurateId = trim($row[2] ?? ''); // Column C: "Kode Barang"
                    $itemName = trim($row[3] ?? ''); // Column D: "Nama Barang"
                    $merk = trim($row[56] ?? ''); // Column BE: "Merk/Brand"
                    $unit = trim($row[5] ?? ''); // Column F: "Satuan"
                    $shortname = trim($row[57] ?? ''); // Column BF: "Nama Singkat" (0-indexed, so 57)
                    
                    // Skip empty rows
                    if (empty($categoryName) && empty($accurateId) && empty($itemName)) {
                        continue;
                    }
                    
                    // Process Item Category
                    $category = null;
                    if (!empty($categoryName)) {
                        $category = ItemCategory::where('name', $categoryName)->first();
                        
                        if (!$category) {
                            $category = ItemCategory::create([
                                'name' => $categoryName,
                                'description' => "Imported from Excel"
                            ]);
                            $importStats['categories_created']++;
                        } else {
                            $importStats['categories_skipped']++;
                        }
                    }
                    
                    // Process Item (only if we have required data)
                    if (!empty($accurateId) && !empty($itemName) && $category) {
                        $existingItem = Item::where('accurate_id', $accurateId)->first();
                        
                        $itemData = [
                            'accurate_id' => $accurateId,
                            'shortname' => !empty($shortname) ? $shortname : null,
                            'name' => $itemName,
                            'item_category_id' => $category->id,
                            'unit' => !empty($unit) ? $unit : null,
                            'merk' => !empty($merk) ? $merk : null,
                            'qty_kg_per_pack' => 1, // Default value
                            'is_active' => true, // Default value
                        ];
                        
                        if (!$existingItem) {
                            // Create new item
                            Item::create($itemData);
                            $importStats['items_created']++;
                        } else {
                            // Update existing item (exclude accurate_id from update)
                            $updateData = $itemData;
                            unset($updateData['accurate_id']); // Don't update the reference field
                            $existingItem->update($updateData);
                            $importStats['items_updated'] = ($importStats['items_updated'] ?? 0) + 1;
                        }
                    }
                    
                } catch (\Exception $e) {
                    $importStats['errors'][] = "Row {$actualRowNumber}: " . $e->getMessage();
                }
            }

            // Prepare success message
            $updatedCount = $importStats['items_updated'] ?? 0;
            $message = sprintf(
                'Import completed! Categories: %d created, %d skipped. Items: %d created, %d updated.',
                $importStats['categories_created'],
                $importStats['categories_skipped'],
                $importStats['items_created'],
                $updatedCount
            );
            
            if (!empty($importStats['errors'])) {
                $message .= ' Errors encountered: ' . implode(', ', array_slice($importStats['errors'], 0, 3));
                if (count($importStats['errors']) > 3) {
                    $message .= ' and ' . (count($importStats['errors']) - 3) . ' more.';
                }
            }

            return redirect()
                ->route('manufacturing.items.import')
                ->with('success', $message)
                ->with('import_stats', $importStats);

        } catch (\Exception $e) {
            return redirect()
                ->route('manufacturing.items.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
