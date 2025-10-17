<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AssetReportController extends Controller
{
    /**
     * Display assets by location report.
     */
    public function assetsByLocation(Request $request): View
    {
        $locationId = $request->get('location_id');
        
        $locations = Location::active()->orderBy('name')->get();
        
        $selectedLocation = null;
        $activeAssets = collect();
        $inactiveAssets = collect();
        
        if ($locationId) {
            $selectedLocation = Location::findOrFail($locationId);
            
            $activeAssets = Asset::with(['assetCategory', 'department', 'user'])
                ->where('location_id', $locationId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $inactiveAssets = Asset::with(['assetCategory', 'department', 'user'])
                ->where('location_id', $locationId)
                ->where('is_active', false)
                ->orderBy('name')
                ->get();
        }
        
        return view('maintenance.reports.assets-by-location', compact(
            'locations',
            'selectedLocation',
            'activeAssets',
            'inactiveAssets'
        ));
    }

    /**
     * Display assets by category report.
     */
    public function assetsByCategory(Request $request): View
    {
        $categoryId = $request->get('category_id');
        
        $categories = AssetCategory::orderBy('name')->get();
        
        $selectedCategory = null;
        $assets = collect();
        
        if ($categoryId) {
            $selectedCategory = AssetCategory::findOrFail($categoryId);
            
            $assets = Asset::with(['location', 'department', 'user'])
                ->where('asset_category_id', $categoryId)
                ->orderBy('name')
                ->get()
                ->groupBy('is_active');
        }
        
        return view('maintenance.reports.assets-by-category', compact(
            'categories',
            'selectedCategory',
            'assets'
        ));
    }

    /**
     * Display assets by category and location report.
     */
    public function assetsByCategoryAndLocation(Request $request): View
    {
        $categoryId = $request->get('category_id');
        $locationIds = $request->get('location_ids', []);
        
        $categories = AssetCategory::orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        
        $selectedCategory = null;
        $selectedLocations = collect();
        $assetsByLocation = collect();
        
        if ($categoryId && !empty($locationIds)) {
            $selectedCategory = AssetCategory::findOrFail($categoryId);
            $selectedLocations = Location::whereIn('id', $locationIds)->orderBy('name')->get();
            
            // Get assets grouped by location
            foreach ($selectedLocations as $location) {
                $locationAssets = Asset::with(['location', 'department', 'user'])
                    ->where('asset_category_id', $categoryId)
                    ->where('location_id', $location->id)
                    ->orderBy('name')
                    ->get();
                
                if ($locationAssets->isNotEmpty()) {
                    $assetsByLocation[$location->name] = [
                        'location' => $location,
                        'active' => $locationAssets->where('is_active', true),
                        'inactive' => $locationAssets->where('is_active', false),
                        'total' => $locationAssets->count(),
                    ];
                }
            }
        }
        
        return view('maintenance.reports.assets-by-category-location', compact(
            'categories',
            'locations',
            'selectedCategory',
            'selectedLocations',
            'assetsByLocation'
        ));
    }
}
