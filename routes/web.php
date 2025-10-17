<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
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
use App\Http\Controllers\BomController;
use App\Http\Controllers\ShelfInventoryController;
use App\Http\Controllers\ShelfManagementController;
use App\Http\Controllers\BulkInventoryController;
use App\Http\Controllers\PicklistController;
use App\Http\Controllers\WarehouseOverviewController;

// Maintenance Controllers
use App\Http\Controllers\MaintenanceDashboardController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\MaintenanceReportController;
use App\Http\Controllers\MaintenanceCalendarController;
use App\Http\Controllers\Reports\AssetReportController;

use App\Models\FormSubmission;

//Basic
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(['auth', 'verified']);
Route::get('/editmyprofile', [UserController::class, 'editmyprofile'])->name('editmyprofile')->middleware('auth');

//Asana
Route::get('/auth/redirect', [LoginController::class, 'redirect']);
Route::get('/auth/callback', [LoginController::class, 'callback']);

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
    Route::delete('item-categories/{itemCategory}', [ItemCategoryController::class, 'destroy'])->name('item-categories.destroy')->middleware('permission:manufacturing.bom');
    
    // Item Import from Excel (MUST be before resource routes)
    Route::get('items/import', [ItemController::class, 'showImport'])->name('items.import');
    Route::post('items/import', [ItemController::class, 'import'])->name('items.import.process');
    
    // Items
    Route::resource('items', ItemController::class)->except(['create', 'store', 'destroy']);
    Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy')->middleware('permission:manufacturing.bom');
    
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
    
    // Bill of Materials (BoM)
    Route::resource('bom', BomController::class)->except(['destroy']);
    Route::delete('bom/{bomTemplate}', [BomController::class, 'destroy'])->name('bom.destroy')->middleware('permission:manufacturing.bom.delete');
    Route::post('bom/{bomTemplate}/submit-approval', [BomController::class, 'submitForApproval'])->name('bom.submit-approval');
    Route::post('bom/{bomTemplate}/approve', [BomController::class, 'approve'])->name('bom.approve');
    Route::post('bom/{bomTemplate}/reject', [BomController::class, 'reject'])->name('bom.reject');
    Route::get('bom/{bomTemplate}/copy', [BomController::class, 'copy'])->name('bom.copy');
});

// Maintenance Routes
Route::prefix('maintenance')->name('maintenance.')->middleware(['auth'])->group(function () {
    Route::get('/', [MaintenanceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('asset-categories', AssetCategoryController::class);
    Route::resource('assets', AssetController::class);
    Route::get('assets/{asset}/qr-code', [AssetController::class, 'generateQR'])->name('assets.qr-code');
    Route::resource('locations', LocationController::class);
    Route::resource('schedules', MaintenanceScheduleController::class);
    Route::post('schedules/{schedule}/trigger', [MaintenanceScheduleController::class, 'trigger'])->name('schedules.trigger');
    Route::resource('work-orders', WorkOrderController::class);
    Route::put('work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.status');
    Route::post('work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete'])->name('work-orders.complete');
    Route::put('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::post('work-orders/{workOrder}/start', [WorkOrderController::class, 'startWork'])->name('work-orders.start');
    Route::post('work-orders/{workOrder}/log-progress', [WorkOrderController::class, 'logProgress'])->name('work-orders.log-progress');
    Route::post('work-orders/{workOrder}/add-action', [WorkOrderController::class, 'addAction'])->name('work-orders.add-action');
    Route::post('work-orders/{workOrder}/upload-photo', [WorkOrderController::class, 'uploadPhoto'])->name('work-orders.upload-photo');
    Route::post('work-orders/{workOrder}/submit-verification', [WorkOrderController::class, 'submitForVerification'])->name('work-orders.submit-verification');
    Route::post('work-orders/{workOrder}/verify', [WorkOrderController::class, 'verify'])->name('work-orders.verify');
    Route::post('work-orders/{workOrder}/close', [WorkOrderController::class, 'close'])->name('work-orders.close');
    Route::get('logs', [MaintenanceLogController::class, 'index'])->name('logs.index');
    Route::get('logs/asset/{asset}', [MaintenanceLogController::class, 'assetHistory'])->name('logs.asset');
    Route::get('reports', [MaintenanceReportController::class, 'index'])->name('reports.index');
    Route::get('reports/assets-by-location', [AssetReportController::class, 'assetsByLocation'])->name('reports.assets-by-location');
    Route::get('reports/assets-by-category', [AssetReportController::class, 'assetsByCategory'])->name('reports.assets-by-category');
    Route::get('reports/assets-by-category-location', [AssetReportController::class, 'assetsByCategoryAndLocation'])->name('reports.assets-by-category-location');
    Route::get('reports/assets-by-department', [AssetReportController::class, 'assetsByDepartment'])->name('reports.assets-by-department');
    Route::get('reports/assets-by-user', [AssetReportController::class, 'assetsByUser'])->name('reports.assets-by-user');
    Route::get('calendar/events', [MaintenanceCalendarController::class, 'events'])->name('calendar.events');
});

// API Routes for Form Field Options
Route::prefix('api')->name('api.')->middleware('auth')->group(function () {
    Route::prefix('forms/{form}/versions/{version}/fields/{field}')->group(function () {
        Route::get('/options', [FormFieldOptionsController::class, 'getOptions'])->name('field.options');
        Route::delete('/cache', [FormFieldOptionsController::class, 'clearCache'])->name('field.clear-cache');
    });
    Route::post('/test-api-config', [FormFieldOptionsController::class, 'testApiConfig'])->name('test-api-config');
    Route::post('/calculate-fields', [FormSubmissionController::class, 'calculateFields'])->name('calculate-fields');
});
