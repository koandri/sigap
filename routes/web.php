<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\KeycloakController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DepartmentController;

use App\Http\Controllers\FormController;
use App\Http\Controllers\FormVersionController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\Api\FormFieldOptionsController;

use App\Http\Controllers\FileController;

// Manufacturing Controllers
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ShelfInventoryController;
use App\Http\Controllers\ShelfManagementController;
use App\Http\Controllers\BulkInventoryController;
use App\Http\Controllers\PicklistController;
use App\Http\Controllers\WarehouseOverviewController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\ProductionPlanStepController;
use App\Http\Controllers\YieldGuidelineController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\PackingMaterialBlueprintController;
use App\Http\Controllers\KerupukPackConfigurationController;

// Maintenance Controllers
use App\Http\Controllers\MaintenanceDashboardController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\MaintenanceCalendarController;
use App\Http\Controllers\AssetManagementReportController;

// Facility Management Routes
use App\Http\Controllers\FacilityDashboardController;
use App\Http\Controllers\CleaningScheduleController;
use App\Http\Controllers\CleaningTaskController;
use App\Http\Controllers\CleaningApprovalController;
use App\Http\Controllers\CleaningRequestController;
use App\Http\Controllers\FacilityManagementReportController;

// Document Management System Routes
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentVersionController;
use App\Http\Controllers\DocumentApprovalController;
use App\Http\Controllers\DocumentAccessController;
use App\Http\Controllers\CorrespondenceController;
use App\Http\Controllers\FormRequestController;
use App\Http\Controllers\PrintedFormController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\DocumentManagementDashboardController;
use App\Http\Controllers\DocumentManagementLocationReportController;
use App\Http\Controllers\DocumentManagementMasterlistReportController;
use App\Http\Controllers\DocumentManagementSlaReportController;

//Basic
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(['auth', 'verified']);
Route::get('/editmyprofile', [UserController::class, 'editmyprofile'])->name('editmyprofile')->middleware('auth');

// ===== KEYCLOAK SSO ROUTES =====
Route::get('/auth/keycloak', [KeycloakController::class, 'redirectToKeycloak'])->name('keycloak.login');

Route::get('/auth/keycloak/callback', [KeycloakController::class, 'handleKeycloakCallback'])->name('keycloak.callback');

Route::post('/auth/keycloak/logout', [KeycloakController::class, 'logout'])->name('keycloak.logout');

//Impersonate
Route::impersonate();

//Users
Route::resource('users', UserController::class)->except(['destroy'])->middleware(['auth', 'role:Super Admin|IT Staff']);

//Role
Route::resource('roles', RoleController::class)->except(['destroy'])->middleware(['auth', 'role:Super Admin|IT Staff']);

//Permissions
Route::resource('permissions', PermissionController::class)->except(['destroy'])->middleware(['auth', 'role:Super Admin|IT Staff']);

//Departments
Route::resource('departments', DepartmentController::class)->except(['destroy'])->middleware(['auth', 'role:Super Admin|IT Staff']);

//Forms
Route::resource('forms', FormController::class)->middleware(['auth', 'role:Super Admin|IT Staff']);

//Form Versions
Route::prefix('forms/{form}/versions')->name('formversions.')->middleware(['auth'])->group(function () {
    Route::get('/', [FormVersionController::class, 'index'])->name('index');
    Route::get('/create', [FormVersionController::class, 'create'])->name('create');
    Route::post('/', [FormVersionController::class, 'store'])->name('store');
    Route::get('/{version}', [FormVersionController::class, 'show'])->name('show');
    Route::put('/{version}/activate', [FormVersionController::class, 'activate'])->name('activate');
    Route::delete('/{version}', [FormVersionController::class, 'destroy'])->name('destroy');
});

// Form Field Routes
Route::prefix('forms/{form}/versions/{version}/fields')->name('formfields.')->middleware(['auth'])->group(function () {
    Route::get('/create', [FormFieldController::class, 'create'])->name('create');
    Route::post('/', [FormFieldController::class, 'store'])->name('store');
    Route::get('/available-for-calculation', [FormFieldController::class, 'getAvailableFields'])->name('available');
    Route::post('/reorder', [FormFieldController::class, 'reorder'])->name('reorder');
    Route::get('/{field}/edit', [FormFieldController::class, 'edit'])->name('edit');
    Route::put('/{field}', [FormFieldController::class, 'update'])->name('update');
    Route::delete('/{field}', [FormFieldController::class, 'destroy'])->name('destroy');
    Route::get('/{field}/options', [FormFieldController::class, 'options'])->name('options');
    Route::put('/{field}/options', [FormFieldController::class, 'updateOptions'])->name('options.update');
});

// Form Submission Routes
Route::prefix('formsubmissions')->name('formsubmissions.')->middleware('auth')->group(function () {
    Route::get('/', [FormSubmissionController::class, 'index'])->name('index');
    Route::get('/submissions', [FormSubmissionController::class, 'submissions'])->name('submissions');
    Route::get('/pending-approvals', [FormSubmissionController::class, 'pendingApprovals'])->name('pending');
    Route::get('/form/{form}', [FormSubmissionController::class, 'create'])->name('create');
    Route::post('/form/{form}', [FormSubmissionController::class, 'store'])->name('store');
    Route::get('/{submission}', [FormSubmissionController::class, 'show'])->name('show');
    Route::get('/{submission}/edit', [FormSubmissionController::class, 'edit'])->name('edit');
    Route::put('/{submission}', [FormSubmissionController::class, 'update'])->name('update');
    Route::delete('/{submission}', [FormSubmissionController::class, 'destroy'])->name('destroy');
    Route::get('/{submission}/print', [FormSubmissionController::class, 'print'])->name('print');

    // Approval actions
    Route::post('/{submission}/approve', [FormSubmissionController::class, 'processApproval'])->name('approve');
    Route::get('/{submission}/approval-history', [FormSubmissionController::class, 'approvalHistory'])->name('approval.history');
    Route::post('/{submission}/start-workflow', [FormSubmissionController::class, 'startWorkflow'])->name('start-workflow');
});

// File handling routes
Route::prefix('files')->name('files.')->middleware('auth')->group(function () {
    Route::get('/preview/{answer}/{index?}', [FileController::class, 'preview'])->name('preview');
    Route::get('/download/{answer}/{index?}', [FileController::class, 'download'])->name('download');
    Route::get('/download-original/{answer}/{index?}', [FileController::class, 'downloadOriginal'])->name('download.original');
    Route::get('/thumbnail/{answer}/{index?}', [FileController::class, 'thumbnail'])->name('thumbnail');
    Route::get('/stream/{answer}/{index?}', [FileController::class, 'stream'])->name('stream');
    Route::get('/info/{answer}/{index?}', [FileController::class, 'info'])->name('info');
});

// Approval Workflow Routes
Route::prefix('forms/{form}/approval-workflows')->name('approval-workflows.')->middleware('auth')->group(function () {
    Route::get('/', [ApprovalWorkflowController::class, 'index'])->name('index');
    Route::get('/create', [ApprovalWorkflowController::class, 'create'])->name('create');
    Route::post('/', [ApprovalWorkflowController::class, 'store'])->name('store');
    Route::get('/{workflow}', [ApprovalWorkflowController::class, 'show'])->name('show');
    Route::get('/{workflow}/edit', [ApprovalWorkflowController::class, 'edit'])->name('edit');
    Route::put('/{workflow}', [ApprovalWorkflowController::class, 'update'])->name('update');
    Route::put('/{workflow}/toggle', [ApprovalWorkflowController::class, 'toggleActive'])->name('toggle');
    Route::delete('/{workflow}', [ApprovalWorkflowController::class, 'destroy'])->name('destroy');
    Route::get('/{workflow}/test', [ApprovalWorkflowController::class, 'test'])->name('test');
});

// Manufacturing Routes
Route::prefix('manufacturing')->name('manufacturing.')->middleware(['auth'])->group(function () {
    // Manufacturing Dashboard
    Route::get('/', [ManufacturingController::class, 'index'])->name('dashboard');
    
    // Item Categories
    Route::resource('item-categories', ItemCategoryController::class)->except(['destroy']);
    Route::delete('item-categories/{itemCategory}', [ItemCategoryController::class, 'destroy'])->name('item-categories.destroy')->middleware('permission:manufacturing.categories.delete');
    
    // Item Import from Excel (MUST be before resource routes)
    Route::get('items/import', [ItemController::class, 'showImport'])->name('items.import');
    Route::post('items/import', [ItemController::class, 'import'])->name('items.import.process');
    
    // Items
    Route::resource('items', ItemController::class)->except(['create', 'store', 'destroy']);
    Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy')->middleware('permission:manufacturing.items.delete');
    
    // Global Warehouse Routes (must come before resource routes)
    Route::get('warehouses/picklist', [PicklistController::class, 'index'])->name('warehouses.picklist');
    Route::post('warehouses/picklist', [PicklistController::class, 'generate'])->name('warehouses.picklist.generate');
    Route::get('warehouses/overview-report', [WarehouseOverviewController::class, 'index'])->name('warehouses.overview-report');
    Route::get('warehouses/overview-report/print', [WarehouseOverviewController::class, 'print'])->name('warehouses.overview-report-print');
    
    // Warehouses
    Route::resource('warehouses', WarehouseController::class)->except(['destroy']);
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy')->middleware('permission:manufacturing.inventory');
    
    // Shelf-Based Inventory Management
    Route::get('warehouses/{warehouse}/shelf-inventory', [ShelfInventoryController::class, 'index'])->name('warehouses.shelf-inventory');
    Route::get('warehouses/{warehouse}/shelves/{shelf}', [ShelfInventoryController::class, 'showShelf'])->name('warehouses.shelf-detail');
    Route::post('warehouses/{warehouse}/positions/{position}/add-item', [ShelfInventoryController::class, 'addItemToPosition'])->name('warehouses.position.add-item');
    Route::put('warehouses/{warehouse}/position-items/{positionItem}', [ShelfInventoryController::class, 'updatePositionItem'])->name('warehouses.position-item.update');
    Route::delete('warehouses/{warehouse}/position-items/{positionItem}', [ShelfInventoryController::class, 'removeFromPosition'])->name('warehouses.position-item.remove');
    Route::post('warehouses/{warehouse}/position-items/{positionItem}/move', [ShelfInventoryController::class, 'moveItem'])->name('warehouses.position-item.move');
    Route::get('warehouses/{warehouse}/position-items/{positionItem}/available-positions', [ShelfInventoryController::class, 'getAvailablePositions'])->name('warehouses.position-item.available-positions');
    Route::post('warehouses/{warehouse}/shelf-bulk-update', [ShelfInventoryController::class, 'bulkUpdate'])->name('warehouses.shelf-bulk-update');
    Route::get('warehouses/{warehouse}/shelf-report', [ShelfInventoryController::class, 'report'])->name('warehouses.shelf-report');
    
    // Shelf and Position Management
    Route::get('warehouses/{warehouse}/shelf-management', [ShelfManagementController::class, 'index'])->name('warehouses.shelf-management');
    Route::get('warehouses/{warehouse}/shelves/create', [ShelfManagementController::class, 'createShelf'])->name('warehouses.shelf.create');
    Route::post('warehouses/{warehouse}/shelves', [ShelfManagementController::class, 'storeShelf'])->name('warehouses.shelf.store');
    Route::get('warehouses/{warehouse}/shelves/{shelf}/edit', [ShelfManagementController::class, 'editShelf'])->name('warehouses.shelf.edit');
    Route::put('warehouses/{warehouse}/shelves/{shelf}', [ShelfManagementController::class, 'updateShelf'])->name('warehouses.shelf.update');
    Route::delete('warehouses/{warehouse}/shelves/{shelf}', [ShelfManagementController::class, 'destroyShelf'])->name('warehouses.shelf.destroy');
    Route::get('warehouses/{warehouse}/shelves/{shelf}/positions', [ShelfManagementController::class, 'showShelf'])->name('warehouses.shelf-positions');
    Route::get('warehouses/{warehouse}/shelves/{shelf}/positions/create', [ShelfManagementController::class, 'createPosition'])->name('warehouses.position.create');
    Route::post('warehouses/{warehouse}/shelves/{shelf}/positions', [ShelfManagementController::class, 'storePosition'])->name('warehouses.position.store');
    Route::get('warehouses/{warehouse}/shelves/{shelf}/positions/{position}/edit', [ShelfManagementController::class, 'editPosition'])->name('warehouses.position.edit');
    Route::put('warehouses/{warehouse}/shelves/{shelf}/positions/{position}', [ShelfManagementController::class, 'updatePosition'])->name('warehouses.position.update');
    Route::delete('warehouses/{warehouse}/shelves/{shelf}/positions/{position}', [ShelfManagementController::class, 'destroyPosition'])->name('warehouses.position.destroy');
    
    // Bulk Inventory Management
    Route::get('warehouses/{warehouse}/bulk-edit', [BulkInventoryController::class, 'index'])->name('warehouses.bulk-edit');
    Route::get('warehouses/{warehouse}/aisle-positions/{aisle}', [BulkInventoryController::class, 'getAislePositions'])->name('warehouses.aisle-positions');
    Route::post('warehouses/{warehouse}/bulk-update', [BulkInventoryController::class, 'bulkUpdate'])->name('warehouses.bulk-update');
    Route::post('warehouses/{warehouse}/bulk-operations', [BulkInventoryController::class, 'bulkUpdate'])->name('warehouses.bulk-operations');
    Route::get('warehouses/{warehouse}/export', [BulkInventoryController::class, 'export'])->name('warehouses.export');
    
    // Production Planning
    Route::get('production-plans/recipes', [ProductionPlanController::class, 'getRecipes'])->name('production-plans.recipes');
    Route::get('production-plans/recipe-ingredients', [ProductionPlanController::class, 'getRecipeIngredients'])->name('production-plans.recipe-ingredients');
    Route::resource('production-plans', ProductionPlanController::class);
    Route::post('production-plans/{productionPlan}/approve', [ProductionPlanController::class, 'approve'])->name('production-plans.approve');
    
    // Production Plan Documents (Work Orders and JC/RO Reports)
    Route::get('production-plans/{productionPlan}/work-order/wet', [ProductionPlanController::class, 'showWetProductionWorkOrder'])->name('production-plans.work-order.wet');
    Route::get('production-plans/{productionPlan}/work-order/dry', [ProductionPlanController::class, 'showDryProductionWorkOrder'])->name('production-plans.work-order.dry');
    
    // Combined JC/RO Reports
    Route::get('production-plans/{productionPlan}/jc-ro/adonan', [ProductionPlanController::class, 'showJcRoAdonan'])->name('production-plans.jc-ro.adonan');
    Route::get('production-plans/{productionPlan}/jc-ro/gelondongan', [ProductionPlanController::class, 'showJcRoGelondongan'])->name('production-plans.jc-ro.gelondongan');
    Route::get('production-plans/{productionPlan}/jc-ro/kerupuk-kg', [ProductionPlanController::class, 'showJcRoKerupukKg'])->name('production-plans.jc-ro.kerupuk-kg');
    Route::get('production-plans/{productionPlan}/jc-ro/kerupuk-pack', [ProductionPlanController::class, 'showJcRoKerupukPack'])->name('production-plans.jc-ro.kerupuk-pack');
    
    // Yield Guidelines Management
    Route::resource('yield-guidelines', YieldGuidelineController::class);
    Route::get('yield-guidelines/items', [YieldGuidelineController::class, 'getItemsForStage'])->name('yield-guidelines.items');
    
    // Packing Material Blueprints
    Route::get('packing-material-blueprints', [PackingMaterialBlueprintController::class, 'index'])->name('packing-material-blueprints.index');
    Route::get('packing-material-blueprints/{item}', [PackingMaterialBlueprintController::class, 'manage'])->name('packing-material-blueprints.manage');
    Route::put('packing-material-blueprints/{item}', [PackingMaterialBlueprintController::class, 'update'])->name('packing-material-blueprints.update');
    
    // Kerupuk Pack Configurations
    Route::get('kerupuk-pack-configurations', [KerupukPackConfigurationController::class, 'index'])->name('kerupuk-pack-configurations.index');
    Route::get('kerupuk-pack-configurations/{item}', [KerupukPackConfigurationController::class, 'manage'])->name('kerupuk-pack-configurations.manage');
    Route::put('kerupuk-pack-configurations/{item}', [KerupukPackConfigurationController::class, 'update'])->name('kerupuk-pack-configurations.update');
    
    // Production Plan Steps
    Route::get('production-plans/{productionPlan}/step2', [ProductionPlanStepController::class, 'step2'])->name('production-plans.step2');
    Route::post('production-plans/{productionPlan}/step2', [ProductionPlanStepController::class, 'storeStep2'])->name('production-plans.step2.store');
    Route::delete('production-plans/{productionPlan}/step2', [ProductionPlanStepController::class, 'deleteStep2'])->name('production-plans.step2.delete');
    Route::get('production-plans/{productionPlan}/step3', [ProductionPlanStepController::class, 'step3'])->name('production-plans.step3');
    Route::post('production-plans/{productionPlan}/step3', [ProductionPlanStepController::class, 'storeStep3'])->name('production-plans.step3.store');
    Route::delete('production-plans/{productionPlan}/step3', [ProductionPlanStepController::class, 'deleteStep3'])->name('production-plans.step3.delete');
    Route::get('production-plans/{productionPlan}/step4', [ProductionPlanStepController::class, 'step4'])->name('production-plans.step4');
    Route::post('production-plans/{productionPlan}/step4', [ProductionPlanStepController::class, 'storeStep4'])->name('production-plans.step4.store');
    Route::delete('production-plans/{productionPlan}/step4', [ProductionPlanStepController::class, 'deleteStep4'])->name('production-plans.step4.delete');
    Route::get('production-plans/{productionPlan}/step5', [ProductionPlanStepController::class, 'step5'])->name('production-plans.step5');
    Route::post('production-plans/{productionPlan}/step5', [ProductionPlanStepController::class, 'storeStep5'])->name('production-plans.step5.store');
    
    // Recipes
    Route::resource('recipes', RecipeController::class);
    Route::get('recipes/{recipe}/duplicate', [RecipeController::class, 'duplicate'])->name('recipes.duplicate');
    Route::post('recipes/{recipe}/duplicate', [RecipeController::class, 'storeDuplicate'])->name('recipes.duplicate.store');
    
    // Production Actuals (Actual Production Tracking)
    Route::post('production-plans/{productionPlan}/start', [ProductionActualController::class, 'start'])->name('production-plans.start');
    Route::get('production-plans/{productionPlan}/execute', [ProductionActualController::class, 'execute'])->name('production-plans.execute');
    Route::get('production-plans/{productionPlan}/actuals', [ProductionActualController::class, 'show'])->name('production-plans.actuals');
    Route::post('production-plans/{productionPlan}/actuals/step1', [ProductionActualController::class, 'recordStep1'])->name('production-plans.actuals.step1');
    Route::post('production-plans/{productionPlan}/actuals/step2', [ProductionActualController::class, 'recordStep2'])->name('production-plans.actuals.step2');
    Route::post('production-plans/{productionPlan}/actuals/step3', [ProductionActualController::class, 'recordStep3'])->name('production-plans.actuals.step3');
    Route::post('production-plans/{productionPlan}/actuals/step4', [ProductionActualController::class, 'recordStep4'])->name('production-plans.actuals.step4');
    Route::post('production-plans/{productionPlan}/actuals/step5', [ProductionActualController::class, 'recordStep5'])->name('production-plans.actuals.step5');
    Route::post('production-plans/{productionPlan}/complete', [ProductionActualController::class, 'complete'])->name('production-plans.complete');
    
});

// Options Routes
Route::prefix('options')->name('options.')->middleware(['auth'])->group(function () {
    Route::resource('asset-categories', AssetCategoryController::class);
    Route::resource('assets', AssetController::class);
    Route::get('assets/qr-codes/all', [AssetController::class, 'qrIndex'])->name('assets.qr-index');
    Route::get('assets/{asset}/qr-code', [AssetController::class, 'generateQR'])->name('assets.qr-code');
    Route::resource('locations', LocationController::class);
});

// Maintenance Routes
Route::prefix('maintenance')->name('maintenance.')->middleware(['auth'])->group(function () {
    Route::get('/', [MaintenanceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('schedules', MaintenanceScheduleController::class);
    Route::post('schedules/{schedule}/trigger', [MaintenanceScheduleController::class, 'trigger'])->name('schedules.trigger');
    Route::resource('work-orders', WorkOrderController::class);
    Route::put('work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.status');
    Route::post('work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete'])->name('work-orders.complete');
    Route::put('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'startWork'])->name('work-orders.start');
    Route::post('work-orders/{workOrder}/log-progress', [WorkOrderController::class, 'logProgress'])->name('work-orders.log-progress');
    Route::post('work-orders/{workOrder}/add-action', [WorkOrderController::class, 'addAction'])->name('work-orders.add-action');
    Route::post('work-orders/{workOrder}/dispose-asset', [WorkOrderController::class, 'handleDisposal'])->name('work-orders.dispose-asset');
    Route::post('work-orders/{workOrder}/upload-photo', [WorkOrderController::class, 'uploadPhoto'])->name('work-orders.upload-photo');
    Route::post('work-orders/{workOrder}/submit-verification', [WorkOrderController::class, 'submitForVerification'])->name('work-orders.submit-verification');
    Route::post('work-orders/{workOrder}/verify', [WorkOrderController::class, 'verify'])->name('work-orders.verify');
    Route::post('work-orders/{workOrder}/close', [WorkOrderController::class, 'close'])->name('work-orders.close');
    Route::get('logs', [MaintenanceLogController::class, 'index'])->name('logs.index');
    Route::get('logs/asset/{asset}', [MaintenanceLogController::class, 'assetHistory'])->name('logs.asset');
    Route::get('calendar/events', [MaintenanceCalendarController::class, 'events'])->name('calendar.events');
});

// Guest request form (public)
Route::get('facility/request', [CleaningRequestController::class, 'guestForm'])->name('facility.requests.guest-form');
Route::post('facility/request', [CleaningRequestController::class, 'store'])->name('facility.requests.store');

Route::prefix('facility')->name('facility.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [FacilityDashboardController::class, 'index'])->name('dashboard');
    
    // Cleaning Schedules
    Route::resource('schedules', CleaningScheduleController::class);
    
    // Cleaning Tasks
    Route::get('tasks', [CleaningTaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/my-tasks', [CleaningTaskController::class, 'myTasks'])->name('tasks.my-tasks');
    Route::get('tasks/{task}', [CleaningTaskController::class, 'show'])->name('tasks.show');
    Route::post('tasks/{task}/start', [CleaningTaskController::class, 'startTask'])->name('tasks.start');
    Route::get('tasks/{task}/submit', [CleaningTaskController::class, 'submitForm'])->name('tasks.submit');
    Route::post('tasks/{task}/submit', [CleaningTaskController::class, 'submitTask'])->name('tasks.submit.post');
    Route::post('tasks/bulk-assign', [CleaningTaskController::class, 'bulkAssign'])->name('tasks.bulk-assign');
    
    // Approvals
    Route::get('approvals', [CleaningApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{approval}/review', [CleaningApprovalController::class, 'review'])->name('approvals.review');
    Route::post('approvals/{approval}/approve', [CleaningApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [CleaningApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('approvals/mass-approve', [CleaningApprovalController::class, 'massApprove'])->name('approvals.mass-approve');
    
    // Requests (staff view)
    Route::get('requests', [CleaningRequestController::class, 'index'])->name('requests.index');
    Route::get('requests/{cleaningRequest}/handle', [CleaningRequestController::class, 'handleForm'])->name('requests.handle-form');
    Route::post('requests/{cleaningRequest}/handle', [CleaningRequestController::class, 'handle'])->name('requests.handle');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->middleware(['auth'])->group(function () {
    // Asset Reports
    Route::get('assets/by-location', [AssetManagementReportController::class, 'assetsByLocation'])->name('assets.by-location');
    Route::get('assets/by-category', [AssetManagementReportController::class, 'assetsByCategory'])->name('assets.by-category');
    Route::get('assets/by-category-location', [AssetManagementReportController::class, 'assetsByCategoryAndLocation'])->name('assets.by-category-location');
    Route::get('assets/by-department', [AssetManagementReportController::class, 'assetsByDepartment'])->name('assets.by-department');
    Route::get('assets/by-user', [AssetManagementReportController::class, 'assetsByUser'])->name('assets.by-user');
    
    // Facility Management Reports
    Route::get('facility/daily', [FacilityManagementReportController::class, 'dailyReport'])->name('facility.daily');
    Route::get('facility/daily/pdf', [FacilityManagementReportController::class, 'dailyReportPdf'])->name('facility.daily-pdf');
    Route::get('facility/weekly', [FacilityManagementReportController::class, 'weeklyReport'])->name('facility.weekly');
    Route::get('facility/weekly/pdf', [FacilityManagementReportController::class, 'weeklyReportPdf'])->name('facility.weekly-pdf');
    Route::get('facility/cell-details', [FacilityManagementReportController::class, 'cellDetails'])->name('facility.cell-details');
    
    // Document Management Reports
    Route::prefix('document-management')->name('document-management.')->group(function () {
        Route::get('locations', [DocumentManagementLocationReportController::class, 'index'])->name('locations.index');
        Route::get('locations/group-by-location', [DocumentManagementLocationReportController::class, 'groupByLocation'])->name('locations.group-by-location');
        Route::get('masterlist', [DocumentManagementMasterlistReportController::class, 'index'])->name('masterlist');
        Route::get('masterlist/print', [DocumentManagementMasterlistReportController::class, 'print'])->name('masterlist.print');
        Route::get('sla', [DocumentManagementSlaReportController::class, 'index'])->name('sla');
    });
});

// OnlyOffice Callback (no auth/CSRF required - uses JWT verification)
Route::post('document-versions/{version}/onlyoffice-callback', [DocumentVersionController::class, 'onlyofficeCallback'])->name('document-versions.onlyoffice-callback');

Route::middleware(['auth'])->group(function () {
    // Documents
    Route::resource('documents', DocumentController::class);
    
    // Versions
    Route::resource('documents.versions', DocumentVersionController::class);
    Route::post('document-versions/{version}/submit', [DocumentVersionController::class, 'submitForApproval'])->name('document-versions.submit');
    Route::get('document-versions/{version}/editor', [DocumentVersionController::class, 'edit'])->name('document-versions.editor');
    Route::get('document-versions/{version}/view', [DocumentVersionController::class, 'viewPDF'])->name('document-versions.view');
    
    // Approvals
    Route::get('document-approvals', [DocumentApprovalController::class, 'index'])->name('document-approvals.index');
    Route::post('document-approvals/{approval}/approve', [DocumentApprovalController::class, 'approve'])->name('document-approvals.approve');
    Route::post('document-approvals/{approval}/reject', [DocumentApprovalController::class, 'reject'])->name('document-approvals.reject');
    
    // Access Requests
    Route::get('my-document-access', [DocumentAccessController::class, 'myAccess'])->name('my-document-access');
    Route::get('documents/{document}/request-access', [DocumentAccessController::class, 'requestAccess'])->name('documents.request-access');
    Route::post('documents/{document}/request-access', [DocumentAccessController::class, 'storeAccessRequest'])->name('documents.request-access.store');
    Route::get('document-access-requests', [DocumentAccessController::class, 'pendingRequests'])->name('document-access-requests.pending');
    Route::post('document-access-requests/{request}/approve', [DocumentAccessController::class, 'approve'])->name('document-access-requests.approve');
    Route::post('document-access-requests/{request}/reject', [DocumentAccessController::class, 'reject'])->name('document-access-requests.reject');
    
    // Correspondences
    Route::get('correspondences', [CorrespondenceController::class, 'index'])->name('correspondences.index');
    Route::get('correspondences/create', [CorrespondenceController::class, 'create'])->name('correspondences.create');
    Route::post('documents/{document}/correspondences', [CorrespondenceController::class, 'store'])->name('correspondences.store');
    Route::get('correspondences/{instance}', [CorrespondenceController::class, 'show'])->name('correspondences.show');
    Route::get('correspondences/{instance}/edit', [CorrespondenceController::class, 'edit'])->name('correspondences.edit');
    Route::put('correspondences/{instance}', [CorrespondenceController::class, 'update'])->name('correspondences.update');
    Route::post('correspondences/{instance}/submit', [CorrespondenceController::class, 'submitForApproval'])->name('correspondences.submit');
    Route::post('correspondences/{instance}/approve', [CorrespondenceController::class, 'approve'])->name('correspondences.approve');
    Route::post('correspondences/{instance}/reject', [CorrespondenceController::class, 'reject'])->name('correspondences.reject');
    Route::get('correspondences/{instance}/download-pdf', [CorrespondenceController::class, 'downloadPdf'])->name('correspondences.download-pdf');
    
    // Form Requests
    Route::resource('form-requests', FormRequestController::class);
    Route::post('form-requests/{form_request}/acknowledge', [FormRequestController::class, 'acknowledge'])->name('form-requests.acknowledge');
    Route::post('form-requests/{form_request}/process', [FormRequestController::class, 'process'])->name('form-requests.process');
    Route::post('form-requests/{form_request}/ready', [FormRequestController::class, 'markReady'])->name('form-requests.ready');
    Route::get('form-requests/{form_request}/labels', [FormRequestController::class, 'printLabels'])->name('form-requests.labels');
    Route::post('form-requests/{form_request}/collect', [FormRequestController::class, 'collect'])->name('form-requests.collect');
    
    // Printed Forms
    Route::get('printed-forms', [PrintedFormController::class, 'index'])->name('printed-forms.index');
    Route::get('printed-forms/{printedForm}', [PrintedFormController::class, 'show'])->name('printed-forms.show');
    Route::post('printed-forms/{printedForm}/return', [PrintedFormController::class, 'returnForm'])->name('printed-forms.return');
    Route::post('printed-forms/bulk-return', [PrintedFormController::class, 'bulkReturn'])->name('printed-forms.bulk-return');
    Route::post('printed-forms/bulk-receive', [PrintedFormController::class, 'bulkReceive'])->name('printed-forms.bulk-receive');
    Route::post('printed-forms/bulk-upload-scans', [PrintedFormController::class, 'bulkUploadScans'])->name('printed-forms.bulk-upload-scans');
    Route::post('printed-forms/bulk-update-location', [PrintedFormController::class, 'bulkUpdatePhysicalLocation'])->name('printed-forms.bulk-update-location');
    Route::post('printed-forms/{printedForm}/receive', [PrintedFormController::class, 'receive'])->name('printed-forms.receive');
    Route::post('printed-forms/{printedForm}/upload-scan', [PrintedFormController::class, 'uploadScans'])->name('printed-forms.upload-scan');
    Route::post('printed-forms/{printedForm}/update-location', [PrintedFormController::class, 'updatePhysicalLocation'])->name('printed-forms.update-location');
    Route::get('printed-forms/{printedForm}/view-scanned', [PrintedFormController::class, 'viewScanned'])->name('printed-forms.view-scanned');
    
    // Document Management Dashboard
    Route::get('dms-dashboard', [DocumentManagementDashboardController::class, 'index'])->name('dms-dashboard');
    
    // User Guides
    Route::prefix('guides')->name('guides.')->group(function () {
        Route::get('/', [GuideController::class, 'index'])->name('index');
        Route::get('download/combined-handbook', [GuideController::class, 'downloadCombinedPdf'])->name('download-combined');
        Route::get('{filename}/pdf', [GuideController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('{filename}', [GuideController::class, 'show'])->name('show');
    });
});

// API Routes for Form Field Options
Route::prefix('api')->name('api.')->middleware('auth')->group(function () {
    Route::prefix('forms/{form}/versions/{version}/fields/{field}')->group(function () {
        Route::get('/options', [FormFieldOptionsController::class, 'getOptions'])->name('field.options');
        Route::delete('/cache', [FormFieldOptionsController::class, 'clearCache'])->name('field.clear-cache');
    });
    Route::post('/test-api-config', [FormFieldOptionsController::class, 'testApiConfig'])->name('test-api-config');
    Route::post('/calculate-fields', [FormSubmissionController::class, 'calculateFields'])->name('calculate-fields');
    
    // Manufacturing API Routes
    Route::prefix('manufacturing')->name('manufacturing.')->group(function () {
        Route::get('/temperature-data', [ManufacturingController::class, 'getTemperatureData'])->name('temperature-data');
    });
});
