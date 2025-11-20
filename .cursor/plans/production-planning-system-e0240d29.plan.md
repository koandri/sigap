<!-- e0240d29-d185-426b-a845-8ba18740eaa0 bc6fb79b-986a-4077-9b2b-b3ef86ec6067 -->
# Production Planning System Implementation Plan

## Overview

Implement a Production Planning System that manages 5-step production planning for cracker manufacturing:

1. **Step 1**: Dough Production Planning (Adonan) with recipe selection
2. **Step 2**: Gelondongan Production Planning from Adonan
3. **Step 3**: Kerupuk Kering Production Planning from Gelondongan  
4. **Step 4**: Packing Planning for finished products
5. **Step 5**: Packing Materials Planning (material requirements for packing)

The system will track planned quantities by distribution channel (GL1, GL2, TA, BL) and support comparing planned vs actual production results.

## Database Schema

### Core Models

**1. ProductionPlan** (new model)

- `id`, `plan_date` (date), `production_start_date` (date), `ready_date` (date)
- `status` (enum: draft, approved, in_production, completed)
- `created_by`, `approved_by`, `approved_at`
- `notes`, `created_at`, `updated_at`, `deleted_at`

**2. ProductionPlanStep1** (new model) - Dough Planning

- `production_plan_id`, `dough_name`, `recipe_id` (references BomTemplate)
- `qty_gl1`, `qty_gl2`, `qty_ta`, `qty_bl`
- Relationships: belongsTo ProductionPlan, belongsTo BomTemplate

**6. ProductionPlanStep2** (new model) - Gelondongan Planning

- `production_plan_id`, `adonan_name`, `gelondongan_name`
- `qty_gl1_adonan`, `qty_gl1_gelondongan`
- `qty_gl2_adonan`, `qty_gl2_gelondongan`
- `qty_ta_adonan`, `qty_ta_gelondongan`
- `qty_bl_adonan`, `qty_bl_gelondongan`
- Relationship: belongsTo ProductionPlan

**4. ProductionPlanStep3** (new model) - Kerupuk Kering Planning

- `production_plan_id`, `gelondongan_name`, `kerupuk_kering_name`
- `qty_gl1_gelondongan`, `qty_gl1_kg`
- `qty_gl2_gelondongan`, `qty_gl2_kg`
- `qty_ta_gelondongan`, `qty_ta_kg`
- `qty_bl_gelondongan`, `qty_bl_kg`
- Relationship: belongsTo ProductionPlan

**5. ProductionPlanStep4** (new model) - Packing Planning

- `production_plan_id`, `kerupuk_kering_item_id`, `kerupuk_packing_item_id`
- `qty_gl1_kg`, `qty_gl1_packing`
- `qty_gl2_kg`, `qty_gl2_packing`
- `qty_ta_kg`, `qty_ta_packing`
- `qty_bl_kg`, `qty_bl_packing`
- Relationship: belongsTo ProductionPlan, belongsTo Item (kerupukKeringItem, kerupukPackingItem)

**6. ProductionPlanStep5** (new model) - Packing Materials Planning

- `production_plan_id`, `pack_sku_id`, `packing_material_item_id`
- `quantity_total` (integer)
- Relationships: belongsTo ProductionPlan, belongsTo Item (packSku, packingMaterialItem)

**7. ProductionPlanStep1RecipeIngredient** (new model) - Recipe ingredients snapshot

- `production_plan_step1_id`, `ingredient_item_id`, `quantity`, `unit`, `sort_order`
- Relationships: belongsTo ProductionPlanStep1, belongsTo Item (ingredientItem)

**8. YieldGuideline** (new model) - Master data for yield calculations

- `product_type` (enum: kancing, gondang, mentor, mini)
- `from_stage` (enum: adonan, gelondongan, kerupuk_kg)
- `to_stage` (enum: gelondongan, kerupuk_kg, packing)
- `yield_quantity` (decimal) - e.g., 3.9 for Kancing Gelondongan→Kg
- `unit` (string)
- `is_active` (boolean)

## Implementation Steps

### Phase 1: Database & Models

1. Create migrations for all 7 tables
2. Create Eloquent models with relationships and casts
3. Add model factories for testing
4. Update ManufacturingPermissionSeeder if needed

### Phase 2: Services

1. **ProductionPlanningService** - Core business logic

- Calculate production dates (plan_date → production_start_date → ready_date)
- Validate step dependencies
- Calculate totals and aggregations
- Yield calculation helpers

2. **ProductionPlanCalculationService** - Calculation logic

- Step 1 to Step 2 conversion (Adonan → Gelondongan using yield)
- Step 2 to Step 3 conversion (Gelondongan → Kg using yield)
- Step 3 to Step 4 conversion (Kg → Packing using weight per unit)
- Material requirement calculations

### Phase 3: Controllers & Routes

1. **ProductionPlanController** - CRUD operations

- `index()` - List all production plans
- `create()` - Create new plan (Step 1 form)
- `store()` - Save Step 1 data
- `show()` - View complete plan (all 4 steps)
- `edit()` - Edit plan (if status = draft)
- `update()` - Update plan
- `destroy()` - Delete draft plans
- `approve()` - Approve plan

2. **ProductionPlanStepController** - Step management

- `step2()` - Create/edit Step 2
- `step3()` - Create/edit Step 3  
- `step4()` - Create/edit Step 4
- Save each step independently

3. Add routes under `/manufacturing/production-plans`

### Phase 4: Views

1. **Index View** - List production plans with filters (date, status)
2. **Create/Edit Step 1 View** - Dough planning form

- Select dough types, recipes (from BomTemplate)
- Enter quantities per channel (GL1, GL2, TA, BL)
- Show recipe details

3. **Step 2 View** - Gelondongan planning

- Auto-calculate from Step 1 using yield guidelines
- Allow manual adjustments
- Show Adonan and Gelondongan quantities

4. **Step 3 View** - Kerupuk Kering planning

- Auto-calculate from Step 2 using yield guidelines
- Allow manual adjustments
- Show Gelondongan and Kg quantities

5. **Step 4 View** - Packing planning

- Select packing types per Kerupuk Kering type
- Enter packing quantities per channel
- Show packing material requirements
- Weight per unit configuration

6. **Show View** - Complete plan overview

- Display all 4 steps in tabbed interface
- Show totals and summaries
- Print/export functionality

### Phase 5: Validation & Business Rules

1. Form Request validation classes
2. Validate that Step N-1 exists before Step N
3. Validate recipe selection matches dough type
4. Validate yield calculations
5. Prevent editing after approval

### Phase 6: Yield Guidelines Management (Master Data)

1. **YieldGuidelineController** - CRUD for yield guidelines
2. Views for managing yield guidelines
3. Default seeders with yield data from images

## Key Features

- **Sequential Step Planning**: Each step builds on previous step
- **Multi-Site Support**: Track quantities per production site (GL1, GL2, TA, BL) - configurable master data
- **Recipe Integration**: Link to BomTemplate with recipe date tracking
- **Item-Based Yield Guidelines**: Yield calculations per specific Item (not just product type)
- **Date Management**: Automatically calculate production_start_date (plan_date + 1 day) and ready_date (production_start_date + 2 days)
- **Status Management**: Draft → Approved → In Production → Completed
- **Production Site Master Data**: Configurable production sites with main site designation
- **Planning vs Actual**: Structure supports future actual production tracking

## BoM System Removal

Since the new Recipe system replaces BoM functionality, we need to remove:

- Models: `BomTemplate`, `BomIngredient`, `BomType`
- Controller: `BomController`
- Views: `resources/views/manufacturing/bom/*.blade.php` (index, create, edit, show)
- Routes: All `manufacturing.bom.*` routes in `routes/web.php`
- Migrations: Drop `bom_templates`, `bom_ingredients`, `bom_types` tables
- Seeder: Remove `BomTypeSeeder` from `DatabaseSeeder`
- Permissions: Remove `manufacturing.bom.*` permissions from `ManufacturingPermissionSeeder`
- Navbar: Remove BoM link from `resources/views/layouts/navbar.blade.php`
- Dashboard: Remove BoM quick action from `resources/views/manufacturing/dashboard.blade.php`

**Note:** This removal is safe because:

- Items table has no foreign keys pointing TO BoM tables
- BoM tables reference Items, so dropping BoM won't affect Items
- Inventory management (Warehouses, Items, ItemCategory) remains completely intact

## Files to Create/Modify

### New Files

- `app/Models/ProductionPlan.php`
- `app/Models/ProductionPlanStep1.php`
- `app/Models/ProductionPlanStep2.php`
- `app/Models/ProductionPlanStep3.php`
- `app/Models/ProductionPlanStep4.php`
- `app/Models/PackingMaterialRequirement.php`
- `app/Models/YieldGuideline.php`
- `app/Http/Controllers/ProductionPlanController.php`
- `app/Http/Controllers/ProductionPlanStepController.php`
- `app/Services/ProductionPlanningService.php`
- `app/Services/ProductionPlanCalculationService.php`
- `app/Http/Requests/StoreProductionPlanRequest.php`
- `app/Http/Requests/UpdateProductionPlanRequest.php`
- Migrations (7 files)
- Views (10+ blade files)

### Modified Files

- `routes/web.php` - Add production planning routes
- `resources/views/layouts/navbar.blade.php` - Enable Production Planning link

## Technical Notes

- Follow Laravel best practices: final classes, type declarations, repository pattern where appropriate
- Use database transactions for multi-step saves
- Implement soft deletes for ProductionPlan
- Use enum classes for status and distribution channels
- Store yield guidelines as master data for flexibility
- Plan structure designed to support future "actual production" comparison

#### Phase 1: Database & Models

- [x] Create database migrations for ProductionPlan, ProductionPlanStep1-4, PackingMaterialRequirement, and YieldGuideline tables
- [x] Create Eloquent models with relationships, casts, and scopes for all production planning entities
- [x] Create YieldGuidelineSeeder with default yield values from the planning images (Kancing, Gondang, Mentor, Mini yields)
- [x] Update ManufacturingPermissionSeeder with production planning permissions

#### Phase 2: Services

- [x] Create ProductionPlanningService and ProductionPlanCalculationService with business logic for calculations and validations

#### Phase 3: Controllers & Routes

- [x] Create ProductionPlanController and ProductionPlanStepController with CRUD operations and step management
- [x] Add production planning routes to web.php under /manufacturing/production-plans prefix

#### Phase 4: Views

- [x] Create Blade views for index, create, edit, show, and all 4 step forms with proper form handling and calculations

#### Phase 5: Validation & Business Rules

- [x] Create Form Request validation classes for storing and updating production plans

#### Phase 6: Yield Guidelines Management

- [x] Create YieldGuidelineController with CRUD operations
- [x] Create views for managing yield guidelines
- [x] Add routes for yield guidelines management

#### Navigation & Integration

- [x] Update navbar.blade.php to link to production planning index page
- [x] Add yield guidelines link to navbar

#### BoM System Removal

- [x] Remove BomTemplate, BomIngredient, BomType models
- [x] Remove BomController
- [x] Remove BoM views (resources/views/manufacturing/bom/*.blade.php)
- [x] Remove manufacturing.bom.* routes from web.php
- [x] Create migration to drop bom_templates, bom_ingredients, bom_types tables
- [x] Remove BomTypeSeeder from DatabaseSeeder
- [x] Remove manufacturing.bom.* permissions from ManufacturingPermissionSeeder
- [x] Remove BoM link from navbar.blade.php
- [x] Remove BoM quick action from manufacturing dashboard

#### Phase 7: Recipe Functionality Completion

- [x] Task 7.5: Update controller to load recipe ingredients (Complete - show() and edit() load ingredients)
- [x] Task 7.6: Add recipe ingredients AJAX endpoint (Complete - getRecipeIngredients() method exists)
- [x] Task 7.7: Update show view to display ingredients (Complete - ingredients displayed in show view)
- [x] Task 7.8: Remove is_custom_recipe field (Complete - removed from migration, model, and controller)
- [x] Task 7.9: Add recipe copy/duplicate functionality (Complete - duplicate method, routes, and views implemented)

**Status:** Complete. Recipe ingredients are fully functional. Custom recipes are not supported - users must manage recipes before creating production plans. Recipe copy functionality allows users to easily duplicate existing recipes for the same or different Adonan.

## ✅ IMPLEMENTATION STATUS: MOSTLY COMPLETE

**Core Production Planning System features have been successfully implemented:**

- ✅ Complete database schema with all 8+ migrations (ProductionPlan, Step1-5, Step1RecipeIngredient, YieldGuideline)
- ✅ All 8+ models with full relationships, casts, and scopes
- ✅ All 2 service classes implemented (ProductionPlanningService, ProductionPlanCalculationService)
- ✅ All 2 controllers with full CRUD and step management
- ✅ Complete view set for all production planning modules (index, create, edit, show, step2, step3, step4, step5)
- ✅ Yield Guidelines management (controller, views, routes)
- ✅ Permissions fully configured in ManufacturingPermissionSeeder
- ✅ Routes and navigation integrated
- ✅ Form Request validation classes
- ✅ BoM system completely removed and replaced with Recipe system

**✅ COMPLETE: Recipe Functionality** (See Phase 7 below)

- Recipe ingredients are stored, loaded, and displayed correctly
- Recipe copy/duplicate functionality implemented
- Custom recipes are not supported - recipes must be managed before creating production plans

**✅ COMPLETE: Step 5 (Packing Materials Planning)**

- Step 5 model, controller, views, and routes fully implemented
- Packing materials planning integrated into workflow

---

## Phase 7: Recipe Functionality Completion

### Overview

Recipe functionality is now complete. The system supports standard recipes only - custom recipes are not supported during production planning. Users must manage recipes before creating production plans. Recipe copy functionality has been added to make it easy to duplicate existing recipes.

### Current Status

**✅ What Works:**

- Recipe ingredients are stored and loaded correctly
- Recipe ingredients are displayed in production plan show view
- Recipe copy/duplicate functionality allows users to easily copy recipes
- Recipes can be copied for the same or different Adonan (dough item)
- All recipe ingredients are copied when duplicating a recipe

**❌ What's Not Supported:**

- Custom recipes during production planning (intentionally removed)
- Creating recipes on-the-fly during production plan creation

### Implementation Tasks

#### Task 7.5: Update Controller to Load Recipe Ingredients

**File:** `app/Http/Controllers/ProductionPlanController.php`

**Status:** ✅ Complete

- `show()` method eager loads recipe ingredients
- `edit()` method loads recipe ingredients
- Ingredients are properly displayed in views

#### Task 7.6: Add Recipe Ingredients AJAX Endpoint

**File:** `app/Http/Controllers/ProductionPlanController.php`

**Status:** ✅ Complete

- `getRecipeIngredients()` method exists and returns JSON
- Route: `GET /manufacturing/production-plans/recipe-ingredients?recipe_id={id}`
- Used by JavaScript to load ingredients when recipe is selected

#### Task 7.7: Update Show View to Display Ingredients

**File:** `resources/views/manufacturing/production-plans/show.blade.php`

**Status:** ✅ Complete

- Ingredients are displayed in expandable sections
- Shows per-batch and total required quantities
- Displays ingredient item name, quantity, and unit

#### Task 7.8: Remove is_custom_recipe Field

**Status:** ✅ Complete

- Removed from migration file
- Removed from ProductionPlanStep1 model
- Removed from ProductionPlanController
- Database column removed using tinker

#### Task 7.9: Add Recipe Copy/Duplicate Functionality

**Status:** ✅ Complete

**Files Created/Modified:**

- `app/Http/Controllers/RecipeController.php` - Added `duplicate()` and `storeDuplicate()` methods
- `resources/views/manufacturing/recipes/duplicate.blade.php` - New view for copying recipes
- `resources/views/manufacturing/recipes/index.blade.php` - Added copy button
- `resources/views/manufacturing/recipes/show.blade.php` - Added copy button
- `routes/web.php` - Added duplicate routes

**Features:**

- Users can copy any existing recipe
- Can change dough item (Adonan) when copying
- All ingredients are pre-filled and can be modified
- Recipe name is pre-filled with "(Copy)" suffix
- Supports copying for same or different Adonan

### Testing Checklist

- [x] Create production plan with standard recipe - ingredients are copied
- [x] Edit production plan - recipe ingredients load correctly
- [x] Show view displays ingredients correctly
- [x] Recipe copy functionality works for same Adonan
- [x] Recipe copy functionality works for different Adonan
- [x] All ingredients are copied when duplicating recipe
- [x] Recipe ingredients can be modified when copying

### Files Created/Modified

**Modified Files:**

- `database/migrations/2025_11_03_120629_create_production_plan_step1_table.php` - Removed is_custom_recipe field
- `app/Models/ProductionPlanStep1.php` - Removed is_custom_recipe from fillable and casts
- `app/Http/Controllers/ProductionPlanController.php` - Removed is_custom_recipe assignment
- `app/Http/Controllers/RecipeController.php` - Added duplicate() and storeDuplicate() methods
- `resources/views/manufacturing/recipes/index.blade.php` - Added copy button
- `resources/views/manufacturing/recipes/show.blade.php` - Added copy button
- `routes/web.php` - Added recipe duplicate routes

**New Files:**

- `resources/views/manufacturing/recipes/duplicate.blade.php` - Recipe copy form

### Technical Notes

- Recipe ingredients are stored in `production_plan_step1_recipe_ingredients` table
- Ingredients are copied when standard recipe is selected (snapshot at time of planning)
- Custom recipes are not supported - users must manage recipes before creating production plans
- Recipe copy functionality allows users to easily duplicate recipes for same or different Adonan
- All recipe ingredients are pre-filled when copying and can be modified before saving

---

## Phase 8: Actual Production Tracking & Comparison System

### Overview

Implement actual production tracking to record real production quantities and compare them against planned quantities. This phase enables the full production workflow from planning through execution to completion, with variance analysis and reporting capabilities.

### Current Status

**✅ What Exists:**

- Production plan structure with all 5 steps (Step 1-5)
- Status workflow defined: `draft` → `approved` → `in_production` → `completed`
- Database schema supports status transitions
- Plan structure designed to support actual production comparison

**❌ What's Missing:**

- Database tables for actual production data
- Models for actual production tracking
- Service layer for recording actuals and calculating variances
- Controllers for production execution workflow
- Views for recording actual production and viewing comparisons
- Status transition logic (approved → in_production → completed)
- Variance calculation and reporting

### Database Schema Design

#### New Tables

**1. ProductionActual** (new model)

- `id`
- `production_plan_id` (FK, unique - one actual per plan)
- `production_date` (date) - When production actually happened
- `recorded_by` (FK to users)
- `recorded_at` (timestamp)
- `notes` (text, nullable)
- `created_at`, `updated_at`

**2. ProductionActualStep1** (new model) - Actual Dough Production

- `id`
- `production_actual_id` (FK)
- `production_plan_step1_id` (FK - reference to planned step)
- `dough_item_id` (FK to items)
- `actual_qty_gl1`, `actual_qty_gl2`, `actual_qty_ta`, `actual_qty_bl` (integers)
- `recorded_at` (timestamp)
- `created_at`, `updated_at`

**3. ProductionActualStep2** (new model) - Actual Gelondongan Production

- `id`
- `production_actual_id` (FK)
- `production_plan_step2_id` (FK - reference to planned step)
- `adonan_item_id`, `gelondongan_item_id` (FKs to items)
- `actual_qty_gl1_adonan`, `actual_qty_gl1_gelondongan`
- `actual_qty_gl2_adonan`, `actual_qty_gl2_gelondongan`
- `actual_qty_ta_adonan`, `actual_qty_ta_gelondongan`
- `actual_qty_bl_adonan`, `actual_qty_bl_gelondongan`
- `recorded_at` (timestamp)
- `created_at`, `updated_at`

**4. ProductionActualStep3** (new model) - Actual Kerupuk Kering Production

- `id`
- `production_actual_id` (FK)
- `production_plan_step3_id` (FK - reference to planned step)
- `gelondongan_item_id`, `kerupuk_kering_item_id` (FKs to items)
- `actual_qty_gl1_gelondongan`, `actual_qty_gl1_kg`
- `actual_qty_gl2_gelondongan`, `actual_qty_gl2_kg`
- `actual_qty_ta_gelondongan`, `actual_qty_ta_kg`
- `actual_qty_bl_gelondongan`, `actual_qty_bl_kg`
- `recorded_at` (timestamp)
- `created_at`, `updated_at`

**5. ProductionActualStep4** (new model) - Actual Packing Production

- `id`
- `production_actual_id` (FK)
- `production_plan_step4_id` (FK - reference to planned step)
- `kerupuk_kering_item_id`, `kerupuk_packing_item_id` (FKs to items)
- `actual_qty_gl1_kg`, `actual_qty_gl1_packing`
- `actual_qty_gl2_kg`, `actual_qty_gl2_packing`
- `actual_qty_ta_kg`, `actual_qty_ta_packing`
- `actual_qty_bl_kg`, `actual_qty_bl_packing`
- `recorded_at` (timestamp)
- `created_at`, `updated_at`

**6. ProductionActualStep5** (new model) - Actual Packing Materials Usage

- `id`
- `production_actual_id` (FK)
- `production_plan_step5_id` (FK - reference to planned step)
- `pack_sku_id`, `packing_material_item_id` (FKs to items)
- `actual_quantity_total` (integer)
- `recorded_at` (timestamp)
- `created_at`, `updated_at`

**Design Decision: Separate "Actual" Tables**

- Clear separation of planned vs actual data
- Maintains data integrity and audit trail
- Easier to query and compare
- Supports future enhancements (multiple actual entries, revisions)

### Workflow Design

#### Status Transitions

```
approved → in_production → completed
```

**Transition Rules:**

- `approved` → `in_production`: Automatically triggered when first actual data is recorded
- `in_production` → `completed`: When all steps have actual data OR manually marked complete

#### User Flow

1. **View Approved Plan**: User sees "Start Production" button on approved plans
2. **Start Production**: Creates ProductionActual record, changes status to `in_production`
3. **Record Actuals Step-by-Step**: User records actual quantities for Steps 1-5
4. **View Comparison**: System displays planned vs actual with variance calculations
5. **Complete Production**: Mark as complete when all steps are recorded

### Implementation Tasks

#### Task 8.1: Database & Models

**Files to Create:**

- Migration: `create_production_actuals_table.php`
- Migration: `create_production_actual_step1_table.php`
- Migration: `create_production_actual_step2_table.php`
- Migration: `create_production_actual_step3_table.php`
- Migration: `create_production_actual_step4_table.php`
- Migration: `create_production_actual_step5_table.php`
- Model: `app/Models/ProductionActual.php`
- Model: `app/Models/ProductionActualStep1.php`
- Model: `app/Models/ProductionActualStep2.php`
- Model: `app/Models/ProductionActualStep3.php`
- Model: `app/Models/ProductionActualStep4.php`
- Model: `app/Models/ProductionActualStep5.php`

**Requirements:**

1. Create all 6 migrations with proper foreign keys and indexes
2. Create all 6 models with:

   - Proper relationships (belongsTo ProductionActual, belongsTo ProductionPlan, belongsTo Items)
   - Casts for numeric fields
   - Accessor methods for totals

3. Update `ProductionPlan` model:

   - Add `hasOne(ProductionActual::class)` relationship
   - Add `scopeInProduction()` and `scopeCompleted()` scopes
   - Add `isInProduction()` and `isCompleted()` helper methods

4. Add relationships from planned steps to actual steps (for easy comparison)

#### Task 8.2: Service Layer

**File:** `app/Services/ProductionActualService.php` (new)

**Methods to Implement:**

1. **`startProduction(ProductionPlan $plan, User $user, ?Carbon $productionDate = null): ProductionActual`**

   - Create ProductionActual record
   - Change plan status from `approved` to `in_production`
   - Set production_date (defaults to plan's production_start_date)
   - Record who started production

2. **`recordStep1(ProductionActual $actual, array $data): void`**

   - Validate data matches planned Step 1 structure
   - Create ProductionActualStep1 records
   - Link to corresponding ProductionPlanStep1 records

3. **`recordStep2(ProductionActual $actual, array $data): void`**

   - Similar to recordStep1 but for Step 2

4. **`recordStep3(ProductionActual $actual, array $data): void`**

   - Similar to recordStep1 but for Step 3

5. **`recordStep4(ProductionActual $actual, array $data): void`**

   - Similar to recordStep1 but for Step 4

6. **`recordStep5(ProductionActual $actual, array $data): void`**

   - Similar to recordStep1 but for Step 5

7. **`completeProduction(ProductionActual $actual): void`**

   - Validate all steps have actual data
   - Change plan status from `in_production` to `completed`
   - Record completion timestamp

8. **`calculateVariances(ProductionPlan $plan): array`**

   - Compare planned vs actual for each step
   - Calculate absolute variance and percentage variance
   - Return structured array with variance status (on_target, minor_variance, major_variance)
   - Variance thresholds:
     - On target: ≤ 5% variance
     - Minor variance: 5-15% variance
     - Major variance: > 15% variance

9. **`getProductionProgress(ProductionPlan $plan): array`**

   - Calculate completion percentage
   - Return which steps are complete/incomplete
   - Return overall status

**File:** Update `app/Services/ProductionPlanningService.php`

**Methods to Add:**

- `startProduction(ProductionPlan $plan, User $user): ProductionPlan`
- `markAsCompleted(ProductionPlan $plan): ProductionPlan`

#### Task 8.3: Controllers

**File:** `app/Http/Controllers/ProductionActualController.php` (new)

**Methods to Implement:**

1. **`start(ProductionPlan $productionPlan): RedirectResponse`**

   - Validate plan is approved
   - Call service to start production
   - Redirect to execution view

2. **`show(ProductionPlan $productionPlan): View`**

   - Load plan with actual data
   - Calculate variances
   - Display comparison view

3. **`execute(ProductionPlan $productionPlan): View`**

   - Show production execution form
   - Display planned quantities (read-only)
   - Input fields for actual quantities
   - Progress indicator

4. **`recordStep1(ProductionPlan $productionPlan, Request $request): RedirectResponse`**

   - Validate actual Step 1 data
   - Call service to record Step 1 actuals
   - Redirect back to execution view

5. **`recordStep2(ProductionPlan $productionPlan, Request $request): RedirectResponse`**

   - Similar to recordStep1

6. **`recordStep3(ProductionPlan $productionPlan, Request $request): RedirectResponse`**

   - Similar to recordStep1

7. **`recordStep4(ProductionPlan $productionPlan, Request $request): RedirectResponse`**

   - Similar to recordStep1

8. **`recordStep5(ProductionPlan $productionPlan, Request $request): RedirectResponse`**

   - Similar to recordStep1

9. **`complete(ProductionPlan $productionPlan): RedirectResponse`**

   - Validate all steps complete
   - Call service to mark as completed
   - Redirect to comparison view

**Routes to Add:**

```php
Route::post('production-plans/{productionPlan}/start', [ProductionActualController::class, 'start'])
    ->name('production-plans.start');
Route::get('production-plans/{productionPlan}/execute', [ProductionActualController::class, 'execute'])
    ->name('production-plans.execute');
Route::get('production-plans/{productionPlan}/actuals', [ProductionActualController::class, 'show'])
    ->name('production-plans.actuals');
Route::post('production-plans/{productionPlan}/actuals/step1', [ProductionActualController::class, 'recordStep1'])
    ->name('production-plans.actuals.step1');
Route::post('production-plans/{productionPlan}/actuals/step2', [ProductionActualController::class, 'recordStep2'])
    ->name('production-plans.actuals.step2');
Route::post('production-plans/{productionPlan}/actuals/step3', [ProductionActualController::class, 'recordStep3'])
    ->name('production-plans.actuals.step3');
Route::post('production-plans/{productionPlan}/actuals/step4', [ProductionActualController::class, 'recordStep4'])
    ->name('production-plans.actuals.step4');
Route::post('production-plans/{productionPlan}/actuals/step5', [ProductionActualController::class, 'recordStep5'])
    ->name('production-plans.actuals.step5');
Route::post('production-plans/{productionPlan}/complete', [ProductionActualController::class, 'complete'])
    ->name('production-plans.complete');
```

#### Task 8.4: Views

**Files to Create:**

1. **`resources/views/manufacturing/production-plans/execute.blade.php`**

   - Production execution form
   - Tabbed interface (similar to show view)
   - Each tab shows:
     - Planned quantities (read-only, grayed out)
     - Input fields for actual quantities
     - Real-time variance calculation (color-coded)
   - Progress indicator showing completion status
   - "Save Step X" buttons for each step
   - "Mark as Complete" button (when all steps done)

2. **`resources/views/manufacturing/production-plans/actuals.blade.php`**

   - Comparison report view
   - Side-by-side comparison tables
   - Planned | Actual | Variance columns
   - Color-coded variance cells:
     - Green: On target (≤5%)
     - Yellow: Minor variance (5-15%)
     - Red: Major variance (>15%)
   - Summary cards:
     - Overall completion percentage
     - Total variance by step
     - Items with major variances
   - Export to Excel/PDF buttons

**Files to Modify:**

1. **`resources/views/manufacturing/production-plans/show.blade.php`**

   - Add "Start Production" button (when status is `approved`)
   - Add "View Actuals" button (when status is `in_production` or `completed`)
   - Add "Continue Production" button (when status is `in_production`)

2. **`resources/views/manufacturing/production-plans/index.blade.php`**

   - Add filter for `in_production` and `completed` statuses
   - Show status badges with appropriate colors

#### Task 8.5: Form Request Validation

**Files to Create:**

- `app/Http/Requests/RecordProductionActualStep1Request.php`
- `app/Http/Requests/RecordProductionActualStep2Request.php`
- `app/Http/Requests/RecordProductionActualStep3Request.php`
- `app/Http/Requests/RecordProductionActualStep4Request.php`
- `app/Http/Requests/RecordProductionActualStep5Request.php`

**Validation Rules:**

- Validate actual quantities match planned structure
- Validate quantities are numeric and non-negative
- Validate items match planned items
- Validate all required fields are present

#### Task 8.6: Permissions

**Update:** `database/seeders/ManufacturingPermissionSeeder.php`

**Add Permissions:**

- `manufacturing.production-plans.start` - Start production
- `manufacturing.production-plans.record-actuals` - Record actual production
- `manufacturing.production-plans.complete` - Complete production
- `manufacturing.production-plans.view-actuals` - View actual production data

### Variance Calculation Logic

**Implementation in ProductionActualService:**

```php
public function calculateVariances(ProductionPlan $plan): array
{
    $variances = [];
    
    // For each step, compare planned vs actual
    foreach ($plan->step1 as $planned) {
        $actual = $planned->actualStep1; // Relationship
        
        if ($actual) {
            $variance = $actual->qty_gl1 - $planned->qty_gl1;
            $variancePercent = ($planned->qty_gl1 > 0) 
                ? ($variance / $planned->qty_gl1) * 100 
                : 0;
            
            $variances['step1'][] = [
                'planned' => $planned->qty_gl1,
                'actual' => $actual->qty_gl1,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'status' => $this->getVarianceStatus($variancePercent),
            ];
        }
    }
    
    // Similar for steps 2-5
    
    return $variances;
}

private function getVarianceStatus(float $percent): string
{
    $absPercent = abs($percent);
    if ($absPercent <= 5) return 'on_target';      // Green
    if ($absPercent <= 15) return 'minor_variance'; // Yellow
    return 'major_variance';                        // Red
}
```

### UI/UX Features

#### Production Execution Page:

- **Progress Bar**: Visual indicator showing completion status (0-100%)
- **Step Indicators**: 
  - ✅ Step completed
  - ⏳ Step in progress (has partial data)
  - ⏸️ Step not started
- **Real-time Variance**: As user enters actual quantities, show variance calculation
- **Color Coding**: 
  - Green background: On target
  - Yellow background: Minor variance
  - Red background: Major variance
- **Navigation**: Easy navigation between steps
- **Save Indicators**: Show last saved timestamp for each step

#### Comparison View:

- **Side-by-side Tables**: Planned | Actual | Variance | Status
- **Summary Dashboard**: 
  - Overall completion percentage
  - Total variance by step
  - Top items with major variances
  - Production efficiency metrics
- **Export Options**: Excel, PDF, CSV
- **Filtering**: Filter by step, item, variance status

### Implementation Priority

**Phase 8.1 (Core Functionality):**

- [ ] Task 8.1: Database schema and models
- [ ] Task 8.2: Service layer (start, record, complete, calculate variances)
- [ ] Task 8.3: Controllers (start, execute, record steps, complete)
- [ ] Task 8.4: Basic execution view
- [ ] Task 8.5: Form request validation
- [ ] Task 8.6: Permissions

**Phase 8.2 (Enhanced Features):**

- [ ] Comparison view with variance analysis
- [ ] Status auto-transitions
- [ ] Progress tracking UI
- [ ] Real-time variance calculations

**Phase 8.3 (Advanced Features):**

- [ ] Production efficiency reports
- [ ] Variance analysis by step/item/date range
- [ ] Yield analysis (actual vs expected)
- [ ] Export functionality (Excel/PDF)
- [ ] Notifications (production started, completed, overdue)
- [ ] Integration with inventory system (material consumption)

### Data Model Relationships

```
ProductionPlan
  ├── hasOne ProductionActual
  │     ├── hasMany ProductionActualStep1
  │     │     └── belongsTo ProductionPlanStep1 (for reference)
  │     ├── hasMany ProductionActualStep2
  │     │     └── belongsTo ProductionPlanStep2 (for reference)
  │     ├── hasMany ProductionActualStep3
  │     │     └── belongsTo ProductionPlanStep3 (for reference)
  │     ├── hasMany ProductionActualStep4
  │     │     └── belongsTo ProductionPlanStep4 (for reference)
  │     └── hasMany ProductionActualStep5
  │           └── belongsTo ProductionPlanStep5 (for reference)
  │
  ├── hasMany ProductionPlanStep1
  │     └── hasOne ProductionActualStep1
  ├── hasMany ProductionPlanStep2
  │     └── hasOne ProductionActualStep2
  ├── hasMany ProductionPlanStep3
  │     └── hasOne ProductionActualStep3
  ├── hasMany ProductionPlanStep4
  │     └── hasOne ProductionActualStep4
  └── hasMany ProductionPlanStep5
        └── hasOne ProductionActualStep5
```

### Example Comparison View Structure

```
Step 1: Dough Production - Planned vs Actual
┌─────────────────────────────────────────────────────────────────┐
│ Item      │ Channel │ Planned │ Actual │ Variance │ Status     │
├───────────┼─────────┼─────────┼────────┼──────────┼────────────┤
│ Adonan A  │ GL1     │ 1,000   │ 980    │ -20 (-2%)│ ✅ On Target│
│ Adonan A  │ GL2     │ 1,500   │ 1,600  │ +100 (+7%)│ ⚠️ Minor   │
│ Adonan B  │ GL1     │ 800     │ 750    │ -50 (-6%)│ ⚠️ Minor   │
│ Adonan C  │ GL1     │ 500     │ 400    │ -100(-20%)│ ❌ Major   │
└─────────────────────────────────────────────────────────────────┘

Summary:
- Overall Completion: 95%
- On Target: 8 items
- Minor Variance: 3 items  
- Major Variance: 1 item
- Average Variance: -2.5%
```

### Testing Checklist

- [ ] Start production from approved plan - status changes to in_production
- [ ] Record Step 1 actuals - data saved correctly
- [ ] Record all steps - progress tracked correctly
- [ ] Complete production - status changes to completed
- [ ] Variance calculations are accurate
- [ ] Comparison view displays correctly
- [ ] Cannot start production on non-approved plans
- [ ] Cannot record actuals without starting production
- [ ] Cannot complete without all steps recorded
- [ ] Progress indicators update correctly
- [ ] Color coding works for variance status
- [ ] Export functionality works

### Files to Create/Modify

**New Files:**

- 6 migrations (production_actuals, production_actual_step1-5)
- 6 models (ProductionActual, ProductionActualStep1-5)
- 1 service (ProductionActualService)
- 1 controller (ProductionActualController)
- 5 form requests (RecordProductionActualStep1-5Request)
- 2 views (execute.blade.php, actuals.blade.php)

**Modified Files:**

- `app/Models/ProductionPlan.php` - Add relationships and scopes
- `app/Services/ProductionPlanningService.php` - Add status transition methods
- `resources/views/manufacturing/production-plans/show.blade.php` - Add action buttons
- `resources/views/manufacturing/production-plans/index.blade.php` - Add status filters
- `routes/web.php` - Add actual production routes
- `database/seeders/ManufacturingPermissionSeeder.php` - Add permissions

### Technical Notes

- Use database transactions for all actual recording operations
- Maintain referential integrity between planned and actual steps
- Variance calculations should handle division by zero (when planned is 0)
- Status transitions should be atomic (use transactions)
- Consider adding audit logging for actual production changes
- Actual data should be immutable once production is completed (or require special permissions to edit)
- Support for partial recording (can record Step 1, then Step 2 later)
- Progress calculation: (steps with actual data / total steps) * 100