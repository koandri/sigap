<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RecordProductionActualStep1Request;
use App\Http\Requests\RecordProductionActualStep2Request;
use App\Http\Requests\RecordProductionActualStep3Request;
use App\Http\Requests\RecordProductionActualStep4Request;
use App\Http\Requests\RecordProductionActualStep5Request;
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
    public function execute(ProductionPlan $productionPlan): View
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

        $progress = $this->actualService->getProductionProgress($productionPlan);

        return view('manufacturing.production-plans.execute', compact('productionPlan', 'actual', 'progress'));
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

