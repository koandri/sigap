<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
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

    /**
     * Display assets by department report.
     */
    public function assetsByDepartment(Request $request): View
    {
        $departmentId = $request->get('department_id');
        
        $departments = Department::orderBy('name')->get();
        
        $selectedDepartment = null;
        $activeAssets = collect();
        $inactiveAssets = collect();
        
        if ($departmentId) {
            $selectedDepartment = Department::findOrFail($departmentId);
            
            $activeAssets = Asset::with(['assetCategory', 'location', 'user'])
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $inactiveAssets = Asset::with(['assetCategory', 'location', 'user'])
                ->where('department_id', $departmentId)
                ->where('is_active', false)
                ->orderBy('name')
                ->get();
        }
        
        return view('maintenance.reports.assets-by-department', compact(
            'departments',
            'selectedDepartment',
            'activeAssets',
            'inactiveAssets'
        ));
    }

    /**
     * Display assets by assigned user report.
     */
    public function assetsByUser(Request $request): View
    {
        $userId = $request->get('user_id');
        
        $users = User::where('active', true)->orderBy('name')->get();
        
        $selectedUser = null;
        $activeAssets = collect();
        $inactiveAssets = collect();
        
        if ($userId) {
            $selectedUser = User::findOrFail($userId);
            
            $activeAssets = Asset::with(['assetCategory', 'location', 'department'])
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $inactiveAssets = Asset::with(['assetCategory', 'location', 'department'])
                ->where('user_id', $userId)
                ->where('is_active', false)
                ->orderBy('name')
                ->get();
        }
        
        return view('maintenance.reports.assets-by-user', compact(
            'users',
            'selectedUser',
            'activeAssets',
            'inactiveAssets'
        ));
    }
}
