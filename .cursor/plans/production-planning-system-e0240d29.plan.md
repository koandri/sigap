<!-- e0240d29-d185-426b-a845-8ba18740eaa0 bc6fb79b-986a-4077-9b2b-b3ef86ec6067 -->
# Production Planning System Implementation Plan

## Overview

Implement a Production Planning System that manages 4-step production planning for cracker manufacturing:

1. **Step 1**: Dough Production Planning (Adonan) with recipe selection
2. **Step 2**: Gelondongan Production Planning from Adonan
3. **Step 3**: Kerupuk Kering Production Planning from Gelondongan  
4. **Step 4**: Packing Planning for finished products

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

- `production_plan_id`, `kerupuk_kering_name`, `kerupuk_packing_name`
- `qty_gl1_kg`, `qty_gl1_packing`
- `qty_gl2_kg`, `qty_gl2_packing`
- `qty_ta_kg`, `qty_ta_packing`
- `qty_bl_kg`, `qty_bl_packing`
- Relationship: belongsTo ProductionPlan

**6. PackingMaterialRequirement** (new model) - For Step 4 packing materials

- `production_plan_step4_id`, `packing_material_name` (references Item)
- `quantity_per_unit` (decimal)
- Relationships: belongsTo ProductionPlanStep4, belongsTo Item

**7. YieldGuideline** (new model) - Master data for yield calculations

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

#### Phase 7: Custom Recipe Functionality Completion

- [ ] Task 7.1: Add validation for custom recipes in Form Requests
- [ ] Task 7.2: Fix edit view recipe loading bugs
- [ ] Task 7.3: Implement recipe ingredients handling (controller and views)
- [ ] Task 7.4: Enhance create view for custom recipes with ingredients UI
- [ ] Task 7.5: Update controller to load recipe ingredients
- [ ] Task 7.6: Add recipe ingredients AJAX endpoint
- [ ] Task 7.7: Update show view to display ingredients

## ✅ IMPLEMENTATION STATUS: MOSTLY COMPLETE

**Core Production Planning System features have been successfully implemented:**

- ✅ Complete database schema with all 7 migrations
- ✅ All 7 models with full relationships, casts, and scopes
- ✅ All 2 service classes implemented (ProductionPlanningService, ProductionPlanCalculationService)
- ✅ All 2 controllers with full CRUD and step management
- ✅ Complete view set for all production planning modules (index, create, edit, show, step2, step3, step4)
- ✅ Yield Guidelines management (controller, views, routes)
- ✅ Permissions fully configured in ManufacturingPermissionSeeder
- ✅ Routes and navigation integrated
- ✅ Form Request validation classes
- ✅ BoM system completely removed and replaced with Recipe system

**⚠️ INCOMPLETE: Custom Recipe Functionality** (See Phase 7 below)

---

## Phase 7: Custom Recipe Functionality Completion

### Overview

Custom Recipe functionality is partially implemented but requires completion to be fully functional. Currently, users can create custom recipes with name and date, but recipe ingredients are not handled, validation is missing, and the edit view has bugs.

### Current Status

**✅ What Works:**

- Database fields exist (`is_custom_recipe`, `recipe_name`, `recipe_date`)
- Basic save functionality in controller
- UI elements (checkbox, fields) in create/edit views
- Display in show view with "Custom" badge

**❌ What's Missing:**

- Validation for custom recipe fields
- Recipe ingredients handling (copying from standard recipes, entering for custom recipes)
- Edit view bugs (recipe loading, custom recipe state restoration)
- Recipe ingredients display in views
- UI for managing custom recipe ingredients

### Implementation Tasks

#### Task 7.1: Add Validation for Custom Recipes

**File:** `app/Http/Requests/StoreProductionPlanRequest.php`

**File:** `app/Http/Requests/UpdateProductionPlanRequest.php`

**Requirements:**

1. Add validation rules for `step1` array:

   - `step1.*.dough_item_id` - required|exists:items,id
   - `step1.*.recipe_id` - nullable|required_without:step1.*.is_custom_recipe|exists:recipes,id
   - `step1.*.is_custom_recipe` - nullable|boolean
   - `step1.*.recipe_name` - required_if:step1.*.is_custom_recipe,1|string|max:100
   - `step1.*.recipe_date` - required_if:step1.*.is_custom_recipe,1|date
   - `step1.*.qty_gl1` - required|numeric|min:0
   - `step1.*.qty_gl2` - required|numeric|min:0
   - `step1.*.qty_ta` - required|numeric|min:0
   - `step1.*.qty_bl` - required|numeric|min:0

2. Add custom validation logic:

   - Ensure either `recipe_id` OR `is_custom_recipe` is provided (not both, not neither)
   - Validate that if `is_custom_recipe` is true, `recipe_name` and `recipe_date` are provided

3. Add validation messages for all new rules

**Implementation Notes:**

- Use `required_without` to ensure either recipe_id or custom recipe is provided
- Use `required_if` for custom recipe fields
- Add custom validation method if needed for complex logic

#### Task 7.2: Fix Edit View Recipe Loading

**File:** `resources/views/manufacturing/production-plans/edit.blade.php`

**Issues to Fix:**

1. **Bug:** Line 282 uses `$productionPlan->step1->first()->recipe_id` which only works for first row
2. **Bug:** Doesn't handle custom recipes (when `is_custom_recipe` is true, `recipe_id` is null)
3. **Bug:** Custom recipe checkbox state and fields not properly restored

**Requirements:**

1. Pass recipe data to JavaScript properly:
   ```php
   const step1Data = @json($productionPlan->step1->map(function($step) {
       return [
           'recipe_id' => $step->recipe_id,
           'is_custom_recipe' => $step->is_custom_recipe,
           'recipe_name' => $step->recipe_name,
           'recipe_date' => $step->recipe_date->format('Y-m-d'),
       ];
   }));
   ```

2. Update `loadRecipes()` function:

   - Accept row index and step1 data
   - After loading recipes, check if current row has existing recipe_id
   - Set recipe select value if recipe_id exists
   - If is_custom_recipe is true, show custom fields and set values

3. Update `toggleCustomRecipe()` function:

   - Properly restore custom recipe state on page load
   - Ensure recipe select is cleared when custom recipe is checked

4. Add initialization function:

   - On page load, restore all custom recipe states
   - Load recipes for all existing rows
   - Set custom recipe fields visibility and values

#### Task 7.3: Implement Recipe Ingredients Handling

**Files to Modify:**

- `app/Http/Controllers/ProductionPlanController.php`
- `resources/views/manufacturing/production-plans/create.blade.php`
- `resources/views/manufacturing/production-plans/edit.blade.php`
- `resources/views/manufacturing/production-plans/show.blade.php`

**Requirements:**

1. **Controller - Store/Update Step 1:**

   - When standard recipe is selected:
     - Copy ingredients from Recipe to ProductionPlanStep1RecipeIngredient
     - Store ingredient_item_id, quantity, unit, sort_order
   - When custom recipe is selected:
     - Allow ingredients to be entered via form
     - Store ingredients in ProductionPlanStep1RecipeIngredient
   - Update `storeStep1()` method to handle ingredients:
     ```php
     // After creating step1 record
     if ($recipe && !$isCustomRecipe) {
         // Copy ingredients from standard recipe
         foreach ($recipe->ingredients as $ingredient) {
             $step1->recipeIngredients()->create([
                 'ingredient_item_id' => $ingredient->ingredient_item_id,
                 'quantity' => $ingredient->quantity,
                 'unit' => $ingredient->unit,
                 'sort_order' => $ingredient->sort_order,
             ]);
         }
     } elseif ($isCustomRecipe && !empty($data['ingredients'])) {
         // Store custom recipe ingredients
         foreach ($data['ingredients'] as $index => $ingredient) {
             $step1->recipeIngredients()->create([
                 'ingredient_item_id' => $ingredient['ingredient_item_id'],
                 'quantity' => $ingredient['quantity'],
                 'unit' => $ingredient['unit'],
                 'sort_order' => $index,
             ]);
         }
     }
     ```


2. **Create/Edit Views - Add Ingredients UI:**

   - Add collapsible section for recipe ingredients
   - For standard recipes: Show ingredients in read-only mode (from Recipe)
   - For custom recipes: Allow adding/editing ingredients
   - Ingredients table with columns:
     - Ingredient Item (select dropdown)
     - Quantity (number input)
     - Unit (text input)
     - Actions (add/remove buttons)
   - Use JavaScript to:
     - Show/hide ingredients section based on recipe selection
     - Dynamically add/remove ingredient rows
     - Load ingredients when standard recipe is selected
     - Clear ingredients when switching recipes

3. **Show View - Display Ingredients:**

   - Add expandable section to show recipe ingredients
   - Display ingredients table with:
     - Ingredient Item name
     - Quantity
     - Unit
   - Show for both standard and custom recipes
   - Use accordion or collapsible card for clean UI

4. **Validation:**

   - Add validation for ingredients array:
     - `step1.*.ingredients` - array (required if custom recipe)
     - `step1.*.ingredients.*.ingredient_item_id` - required|exists:items,id
     - `step1.*.ingredients.*.quantity` - required|numeric|min:0.001
     - `step1.*.ingredients.*.unit` - nullable|string|max:15

#### Task 7.4: Enhance Create View for Custom Recipes

**File:** `resources/views/manufacturing/production-plans/create.blade.php`

**Requirements:**

1. Add ingredients section similar to edit view
2. Ensure JavaScript properly handles:

   - Loading ingredients when standard recipe is selected
   - Showing/hiding ingredients section
   - Adding/removing ingredient rows for custom recipes

3. Use TomSelect for ingredient item selection (consistent with other selects)

#### Task 7.5: Update Controller to Load Recipe Ingredients

**File:** `app/Http/Controllers/ProductionPlanController.php`

**Requirements:**

1. Update `show()` method to eager load recipe ingredients:
   ```php
   $productionPlan->load([
       // ... existing relationships
       'step1.recipeIngredients.ingredientItem',
       'step1.recipe.ingredients.ingredientItem',
   ]);
   ```

2. Update `edit()` method to load recipe ingredients:
   ```php
   $productionPlan->load([
       'step1.doughItem',
       'step1.recipe',
       'step1.recipeIngredients.ingredientItem',
   ]);
   ```

3. Add new method `getRecipeIngredients()` for AJAX endpoint:

   - Route: `GET /manufacturing/production-plans/recipe-ingredients?recipe_id={id}`
   - Returns JSON with recipe ingredients
   - Used by JavaScript to load ingredients when recipe is selected

#### Task 7.6: Add Recipe Ingredients AJAX Endpoint

**File:** `app/Http/Controllers/ProductionPlanController.php`

**File:** `routes/web.php`

**Requirements:**

1. Add new method in ProductionPlanController:
   ```php
   public function getRecipeIngredients(Request $request): \Illuminate\Http\JsonResponse
   {
       $recipeId = $request->input('recipe_id');
       
       if (!$recipeId) {
           return response()->json([]);
       }
       
       $recipe = Recipe::with('ingredients.ingredientItem')->findOrFail($recipeId);
       
       return response()->json(
           $recipe->ingredients->map(function ($ingredient) {
               return [
                   'ingredient_item_id' => $ingredient->ingredient_item_id,
                   'ingredient_item_name' => $ingredient->ingredientItem->name,
                   'quantity' => $ingredient->quantity,
                   'unit' => $ingredient->unit,
                   'sort_order' => $ingredient->sort_order,
               ];
           })
       );
   }
   ```

2. Add route in `routes/web.php`:
   ```php
   Route::get('production-plans/recipe-ingredients', [ProductionPlanController::class, 'getRecipeIngredients'])
       ->name('production-plans.recipe-ingredients');
   ```


#### Task 7.7: Update Show View to Display Ingredients

**File:** `resources/views/manufacturing/production-plans/show.blade.php`

**Requirements:**

1. Add expandable ingredients section in Step 1 tab
2. Display ingredients in a table:

   - Ingredient Item
   - Quantity
   - Unit

3. Show for each step1 row
4. Use Bootstrap collapse or similar for clean UI
5. Show "No ingredients" message if none exist

### Testing Checklist

- [ ] Create production plan with standard recipe - ingredients should be copied
- [ ] Create production plan with custom recipe - should allow entering ingredients
- [ ] Edit production plan - custom recipe state should be restored correctly
- [ ] Edit production plan - recipe ingredients should load correctly
- [ ] Switch from standard to custom recipe - ingredients should clear
- [ ] Switch from custom to standard recipe - ingredients should load from recipe
- [ ] Validation errors show correctly for missing custom recipe fields
- [ ] Validation errors show correctly for invalid ingredients
- [ ] Show view displays ingredients correctly for both types
- [ ] All JavaScript functions work correctly in both create and edit views

### Files to Create/Modify

**Modified Files:**

- `app/Http/Requests/StoreProductionPlanRequest.php` - Add validation
- `app/Http/Requests/UpdateProductionPlanRequest.php` - Add validation
- `app/Http/Controllers/ProductionPlanController.php` - Add ingredients handling, AJAX endpoint
- `resources/views/manufacturing/production-plans/create.blade.php` - Add ingredients UI
- `resources/views/manufacturing/production-plans/edit.blade.php` - Fix bugs, add ingredients UI
- `resources/views/manufacturing/production-plans/show.blade.php` - Display ingredients
- `routes/web.php` - Add recipe ingredients route

**No New Files Required** (all models and migrations already exist)

### Technical Notes

- Recipe ingredients are stored in `production_plan_step1_recipe_ingredients` table
- Ingredients should be copied when standard recipe is selected (snapshot at time of planning)
- Custom recipe ingredients are entered manually by user
- Ingredients are displayed in show view for reference
- Use JavaScript to dynamically manage ingredient rows
- Consider using a reusable JavaScript component for ingredient management