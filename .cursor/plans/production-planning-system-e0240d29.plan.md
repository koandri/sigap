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

### To-dos

- [x] Create database migrations for ProductionPlan, ProductionPlanStep1-4, PackingMaterialRequirement, and YieldGuideline tables
- [x] Create Eloquent models with relationships, casts, and scopes for all production planning entities
- [x] Create YieldGuidelineSeeder with default yield values from the planning images (Kancing, Gondang, Mentor, Mini yields)
- [x] Create ProductionPlanningService and ProductionPlanCalculationService with business logic for calculations and validations
- [x] Create Form Request validation classes for storing and updating production plans
- [ ] Create ProductionPlanController and ProductionPlanStepController with CRUD operations and step management
- [ ] Add production planning routes to web.php under /manufacturing/production-plans prefix
- [ ] Create Blade views for index, create, edit, show, and all 4 step forms with proper form handling and calculations
- [ ] Update navbar.blade.php to link to production planning index page