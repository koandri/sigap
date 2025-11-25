<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\Recipe;
use App\Models\YieldGuideline;
use Illuminate\View\View;

final class ManufacturingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manufacturing.dashboard.view')->only(['index']);
    }

    /**
     * Display the manufacturing dashboard.
     */
    public function index(): View
    {
        // Get production plan statistics by status
        $stats = [
            'total_plans' => ProductionPlan::count(),
            'draft_plans' => ProductionPlan::draft()->count(),
            'approved_plans' => ProductionPlan::approved()->count(),
            'in_production_plans' => ProductionPlan::inProduction()->count(),
            'completed_plans' => ProductionPlan::completed()->count(),
            'total_recipes' => Recipe::where('is_active', true)->count(),
            'total_yield_guidelines' => YieldGuideline::count(),
        ];

        // Get active production plans
        $activeProductionPlans = ProductionPlan::inProduction()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('production_start_date', 'desc')
            ->limit(10)
            ->get();

        // Get recent production plans
        $recentProductionPlans = ProductionPlan::with(['createdBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent recipes
        $recentRecipes = Recipe::where('is_active', true)
            ->with(['doughItem', 'createdBy'])
            ->orderBy('recipe_date', 'desc')
            ->limit(10)
            ->get();

        return view('manufacturing.dashboard', compact(
            'stats',
            'activeProductionPlans',
            'recentProductionPlans',
            'recentRecipes'
        ));
    }
}
