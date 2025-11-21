# Production Actual Add/Delete Functionality - Implementation Plan

## Overview
This plan documents the implementation of add/delete functionality for Production Actual steps, allowing users to dynamically add and remove items during actual production recording, similar to the production planning functionality.

## Requirements Summary
1. Users can add/delete items in each step during actual production
2. Steps must be completed one-by-one (sequential progression)
3. Role-based access control:
   - Step 1: R&D, Super Admin, Owner
   - Step 2: Production, Super Admin, Owner
   - Steps 3-5: PPIC, QC, Super Admin, Owner

---

## ✅ COMPLETED IMPLEMENTATIONS

### 1. Controller Updates (`app/Http/Controllers/ProductionActualController.php`)

#### ✅ Role-Based Access Control
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Added `canAccessStep(int $stepNumber)` method to check role-based access
  - Step 1: R&D, Super Admin, Owner
  - Step 2: Production, Super Admin, Owner
  - Steps 3-5: PPIC, QC, Super Admin, Owner
  - Super Admin and Owner can access all steps

#### ✅ Step-by-Step Progression Validation
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Added `validateStepProgression(ProductionPlan $productionPlan, int $stepNumber)` method
  - Validates that previous step has actual data before allowing access to next step
  - Step 1 has no dependency
  - Step 2 requires Step 1 to be completed
  - Step 3 requires Step 2 to be completed
  - Step 4 requires Step 3 to be completed
  - Step 5 requires Step 4 to be completed

#### ✅ Updated Execute Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Added role-based access checking for each step
  - Added step progression validation
  - Added data preparation for adding new items:
    - Dough items and ingredient items for Step 1
    - Adonan items and Gelondongan items for Step 2
    - Gelondongan items and Kerupuk Kering items for Step 3
    - Packing items for Step 4
    - Packing material items and blueprints for Step 5
  - Passes `stepAccess` array to view for conditional rendering

#### ✅ Updated Record Methods
- **Status**: ✅ COMPLETED
- **Implementation**:
  - All `recordStep1` through `recordStep5` methods now include:
    - Role-based access validation
    - Step progression validation
    - Error messages for unauthorized access

#### ✅ Delete Methods
- **Status**: ✅ COMPLETED
- **Implementation**:
  - `deleteStep1(ProductionPlan $productionPlan, int $actualStep1Id)`
  - `deleteStep2(ProductionPlan $productionPlan, int $actualStep2Id)`
  - `deleteStep3(ProductionPlan $productionPlan, int $actualStep3Id)`
  - `deleteStep4(ProductionPlan $productionPlan, int $actualStep4Id)`
  - `deleteStep5(ProductionPlan $productionPlan, int $actualStep5Id)`
  - All methods include role-based access validation

### 2. Service Updates (`app/Services/ProductionActualService.php`)

#### ✅ Updated Record Methods to Support New Items
- **Status**: ✅ COMPLETED
- **Implementation**:
  - All `recordStep1` through `recordStep5` methods now:
    - Accept optional `production_plan_stepX_id` (can be null for new items)
    - Create new plan step records when `production_plan_stepX_id` is not provided
    - Support creating plan steps on-the-fly during actual recording

#### ✅ Step 1 Record Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Creates `ProductionPlanStep1` if not exists
  - Handles recipe creation with ingredients
  - Supports both existing and new dough items
  - Creates recipe ingredients if provided

#### ✅ Step 2 Record Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Creates `ProductionPlanStep2` if not exists
  - Requires adonan_item_id and gelondongan_item_id for new records

#### ✅ Step 3 Record Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Creates `ProductionPlanStep3` if not exists
  - Requires gelondongan_item_id and kerupuk_kering_item_id for new records
  - Handles decimal quantities (kg) properly

#### ✅ Step 4 Record Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Creates `ProductionPlanStep4` if not exists
  - Requires kerupuk_kering_item_id and kerupuk_packing_item_id for new records
  - Handles both kg and packing quantities

#### ✅ Step 5 Record Method
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Creates `ProductionPlanStep5` if not exists
  - Requires pack_sku_id and packing_material_item_id for new records

#### ✅ Delete Methods
- **Status**: ✅ COMPLETED
- **Implementation**:
  - `deleteStep1(ProductionActual $actual, int $actualStep1Id)`
  - `deleteStep2(ProductionActual $actual, int $actualStep2Id)`
  - `deleteStep3(ProductionActual $actual, int $actualStep3Id)`
  - `deleteStep4(ProductionActual $actual, int $actualStep4Id)`
  - `deleteStep5(ProductionActual $actual, int $actualStep5Id)`
  - All methods delete only the actual record (plan step remains for reference)

### 3. Routes (`routes/web.php`)

#### ✅ Delete Routes
- **Status**: ✅ COMPLETED
- **Implementation**:
  - `DELETE production-plans/{productionPlan}/actuals/step1/{actualStep1}`
  - `DELETE production-plans/{productionPlan}/actuals/step2/{actualStep2}`
  - `DELETE production-plans/{productionPlan}/actuals/step3/{actualStep3}`
  - `DELETE production-plans/{productionPlan}/actuals/step4/{actualStep4}`
  - `DELETE production-plans/{productionPlan}/actuals/step5/{actualStep5}`
  - All routes properly named and grouped

### 4. Model Imports
- **Status**: ✅ COMPLETED
- **Implementation**:
  - Added necessary model imports to controller and service
  - `Item`, `ItemCategory`, `PackingMaterialBlueprint`, `Recipe`
  - All plan step models (`ProductionPlanStep1` through `ProductionPlanStep5`)
  - Recipe ingredient model (`ProductionPlanStep1RecipeIngredient`)

---

## ❌ PENDING IMPLEMENTATIONS

### 1. View Updates (`resources/views/manufacturing/production-plans/execute.blade.php`)

#### ❌ Step 1 Tab Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Table Structure**:
     - Add "Actions" column header
     - Add delete button column for each row
     - Make dough item selectable (dropdown) instead of read-only text
     - Add recipe selection dropdown
     - Add ingredients table (collapsible/expandable) for each row
     - Show planned quantities as read-only (grayed out)
     - Allow editing actual quantities

  2. **Add Row Functionality**:
     - Add "Add Row" button (only visible if user has Step 1 access)
     - JavaScript function to clone row template
     - Initialize TomSelect for dough item dropdown
     - Initialize TomSelect for recipe dropdown
     - Handle recipe change to load ingredients
     - Reset form values for new row

  3. **Delete Row Functionality**:
     - Add delete button for each row (only if actual record exists)
     - Confirmation dialog before deletion
     - AJAX call to delete route OR form submission with delete flag
     - Remove row from DOM after successful deletion
     - Handle case where deleting last row (prevent or show warning)

  4. **Ingredients Management**:
     - Add/remove ingredient rows within each dough item row
     - Ingredient item dropdown
     - Quantity and unit inputs
     - Load ingredients from recipe when recipe is selected
     - Allow manual editing of ingredients

  5. **Form Submission**:
     - Handle both existing plan steps (with `production_plan_step1_id`) and new items (without)
     - Include `dough_item_id` and `recipe_id` for new items
     - Include ingredients array for new items
     - Validate that at least one row exists

  6. **Role-Based Visibility**:
     - Show add/delete buttons only if `$stepAccess[1]` is true
     - Disable form if user doesn't have access
     - Show appropriate error message if access denied

#### ❌ Step 2 Tab Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Table Structure**:
     - Add "Actions" column header
     - Add delete button column
     - Make adonan item selectable (dropdown)
     - Make gelondongan item selectable (dropdown)
     - Show planned quantities as read-only
     - Allow editing actual quantities

  2. **Add Row Functionality**:
     - Add "Add Row" button (only visible if user has Step 2 access)
     - JavaScript function to clone row template
     - Initialize TomSelect for adonan and gelondongan dropdowns
     - Reset form values for new row

  3. **Delete Row Functionality**:
     - Add delete button for each row
     - Confirmation dialog
     - AJAX/form submission to delete route
     - Remove row from DOM

  4. **Form Submission**:
     - Handle both existing and new items
     - Include `adonan_item_id` and `gelondongan_item_id` for new items
     - Validate that at least one row exists

  5. **Role-Based Visibility**:
     - Show add/delete buttons only if `$stepAccess[2]` is true

#### ❌ Step 3 Tab Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Table Structure**:
     - Add "Actions" column header
     - Add delete button column
     - Make gelondongan item selectable (dropdown)
     - Make kerupuk kering item selectable (dropdown)
     - Show planned quantities as read-only
     - Allow editing actual quantities (both gelondongan and kg)

  2. **Add Row Functionality**:
     - Add "Add Row" button (only visible if user has Step 3 access)
     - JavaScript function to clone row template
     - Initialize TomSelect for dropdowns
     - Reset form values

  3. **Delete Row Functionality**:
     - Add delete button for each row
     - Confirmation dialog
     - AJAX/form submission
     - Remove row from DOM

  4. **Form Submission**:
     - Handle both existing and new items
     - Include `gelondongan_item_id` and `kerupuk_kering_item_id` for new items

  5. **Role-Based Visibility**:
     - Show add/delete buttons only if `$stepAccess[3]` is true

#### ❌ Step 4 Tab Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Table Structure**:
     - Add "Actions" column header
     - Add delete button column
     - Make kerupuk kering item selectable (dropdown)
     - Make packing item selectable (dropdown)
     - Show planned quantities as read-only
     - Allow editing actual quantities (both kg and packing)

  2. **Add Row Functionality**:
     - Add "Add Row" button (only visible if user has Step 4 access)
     - JavaScript function to clone row template
     - Initialize TomSelect for dropdowns
     - Reset form values

  3. **Delete Row Functionality**:
     - Add delete button for each row
     - Confirmation dialog
     - AJAX/form submission
     - Remove row from DOM

  4. **Form Submission**:
     - Handle both existing and new items
     - Include `kerupuk_kering_item_id` and `kerupuk_packing_item_id` for new items

  5. **Role-Based Visibility**:
     - Show add/delete buttons only if `$stepAccess[4]` is true

#### ❌ Step 5 Tab Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Table Structure**:
     - Add "Actions" column header
     - Add delete button column
     - Make pack SKU selectable (dropdown)
     - Make packing material item selectable (dropdown)
     - Show planned quantity as read-only
     - Allow editing actual quantity

  2. **Add Row Functionality**:
     - Add "Add Row" button (only visible if user has Step 5 access)
     - JavaScript function to clone row template
     - Initialize TomSelect for dropdowns
     - Reset form values

  3. **Delete Row Functionality**:
     - Add delete button for each row
     - Confirmation dialog
     - AJAX/form submission
     - Remove row from DOM

  4. **Form Submission**:
     - Handle both existing and new items
     - Include `pack_sku_id` and `packing_material_item_id` for new items

  5. **Role-Based Visibility**:
     - Show add/delete buttons only if `$stepAccess[5]` is true

#### ❌ JavaScript Functions
- **Status**: ❌ NOT STARTED
- **Required Functions**:
  1. **Step 1 Functions**:
     - `addStep1Row()` - Clone row template, initialize TomSelect, reset values
     - `removeStep1Row(button)` - Remove row, handle TomSelect cleanup
     - `loadRecipes(doughItemId, rowIndex)` - Load recipes for selected dough item
     - `loadRecipeIngredients(recipeId, rowIndex)` - Load ingredients from recipe
     - `addIngredientRow(rowIndex, ingredientData)` - Add ingredient row
     - `removeIngredientRow(button)` - Remove ingredient row
     - `initializeTomSelectsForRow(rowIndex)` - Initialize all TomSelect instances

  2. **Step 2 Functions**:
     - `addStep2Row()` - Clone row template, initialize TomSelect
     - `removeStep2Row(button)` - Remove row, cleanup
     - `deleteStep2Record(actualStep2Id, rowElement)` - AJAX delete

  3. **Step 3 Functions**:
     - `addStep3Row()` - Clone row template, initialize TomSelect
     - `removeStep3Row(button)` - Remove row, cleanup
     - `deleteStep3Record(actualStep3Id, rowElement)` - AJAX delete

  4. **Step 4 Functions**:
     - `addStep4Row()` - Clone row template, initialize TomSelect
     - `removeStep4Row(button)` - Remove row, cleanup
     - `deleteStep4Record(actualStep4Id, rowElement)` - AJAX delete

  5. **Step 5 Functions**:
     - `addStep5Row()` - Clone row template, initialize TomSelect
     - `removeStep5Row(button)` - Remove row, cleanup
     - `deleteStep5Record(actualStep5Id, rowElement)` - AJAX delete

  6. **Common Functions**:
     - `deleteActualRecord(stepNumber, recordId, rowElement, routeName)` - Generic AJAX delete
     - `confirmDelete(message)` - Confirmation dialog wrapper

#### ❌ Tab Navigation Updates
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Disable Tabs Based on Access**:
     - Disable tab if `$stepAccess[$i]` is false
     - Show tooltip explaining why tab is disabled
     - Prevent tab switching if previous step not completed

  2. **Visual Indicators**:
     - Show lock icon on disabled tabs
     - Show warning message if trying to access locked step
     - Update tab badges based on completion status

#### ❌ Data Preparation for JavaScript
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  1. **Pass Data to JavaScript**:
     - Convert PHP collections to JSON
     - Include dough items, recipes, ingredients, etc.
     - Include existing actual data for editing
     - Include route URLs for AJAX calls

  2. **Script Section**:
     - Add `<script>` section at end of view
     - Initialize global variables with data
     - Define all JavaScript functions
     - Initialize TomSelect on page load

### 2. Request Validation Updates

#### ❌ Update RecordProductionActualStep1Request
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  - Make `production_plan_step1_id` optional
  - Add validation for `dough_item_id` when `production_plan_step1_id` is null
  - Add validation for `recipe_id` when creating new item
  - Add validation for `ingredients` array when creating new item
  - Update validation rules to handle both existing and new items

#### ❌ Update RecordProductionActualStep2Request
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  - Make `production_plan_step2_id` optional
  - Add validation for `adonan_item_id` and `gelondongan_item_id` when creating new item

#### ❌ Update RecordProductionActualStep3Request
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  - Make `production_plan_step3_id` optional
  - Add validation for `gelondongan_item_id` and `kerupuk_kering_item_id` when creating new item

#### ❌ Update RecordProductionActualStep4Request
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  - Make `production_plan_step4_id` optional
  - Add validation for `kerupuk_kering_item_id` and `kerupuk_packing_item_id` when creating new item

#### ❌ Update RecordProductionActualStep5Request
- **Status**: ❌ NOT STARTED
- **Required Changes**:
  - Make `production_plan_step5_id` optional
  - Add validation for `pack_sku_id` and `packing_material_item_id` when creating new item

### 3. Testing & Validation

#### ❌ Unit Tests
- **Status**: ❌ NOT STARTED
- **Required Tests**:
  1. Test role-based access control for each step
  2. Test step-by-step progression validation
  3. Test adding new items (creates plan steps)
  4. Test deleting actual records
  5. Test form validation for new vs existing items

#### ❌ Integration Tests
- **Status**: ❌ NOT STARTED
- **Required Tests**:
  1. Test complete workflow: add items → record actuals → delete items
  2. Test role restrictions across different user roles
  3. Test step progression enforcement
  4. Test AJAX delete functionality

#### ❌ Manual Testing Checklist
- **Status**: ❌ NOT STARTED
- **Required Tests**:
  1. ✅ R&D user can access Step 1
  2. ✅ R&D user cannot access Steps 2-5
  3. ✅ Production user can access Step 2
  4. ✅ Production user cannot access Steps 1, 3-5
  5. ✅ PPIC/QC users can access Steps 3-5
  6. ✅ PPIC/QC users cannot access Steps 1-2
  7. ✅ Super Admin/Owner can access all steps
  8. ✅ Cannot access Step 2 without completing Step 1
  9. ✅ Cannot access Step 3 without completing Step 2
  10. ✅ Cannot access Step 4 without completing Step 3
  11. ✅ Cannot access Step 5 without completing Step 4
  12. ✅ Can add new items in each step
  13. ✅ Can delete items in each step
  14. ✅ New items create plan steps automatically
  15. ✅ Deleted actual records don't delete plan steps

### 4. Documentation

#### ❌ User Guide Updates
- **Status**: ❌ NOT STARTED
- **Required Updates**:
  1. Document add/delete functionality
  2. Document role-based access
  3. Document step-by-step progression
  4. Add screenshots of new UI

#### ❌ Code Comments
- **Status**: ❌ PARTIALLY COMPLETE
- **Required Updates**:
  1. Add PHPDoc comments to all new methods
  2. Add inline comments for complex logic
  3. Document JavaScript functions

---

## Implementation Priority

### Phase 1: Core Functionality (HIGH PRIORITY)
1. ✅ Controller role-based access and validation
2. ✅ Service methods for add/delete
3. ✅ Routes for delete operations
4. ❌ View updates for Step 1 (most complex)
5. ❌ Request validation updates

### Phase 2: Remaining Steps (MEDIUM PRIORITY)
1. ❌ View updates for Steps 2-5
2. ❌ JavaScript functions for all steps
3. ❌ Tab navigation and access control in UI

### Phase 3: Polish & Testing (LOW PRIORITY)
1. ❌ Unit tests
2. ❌ Integration tests
3. ❌ Manual testing
4. ❌ Documentation updates

---

## Technical Considerations

### 1. Data Flow
- When adding new item: Form submits without `production_plan_stepX_id` → Service creates plan step → Service creates actual step
- When editing existing item: Form submits with `production_plan_stepX_id` → Service updates actual step
- When deleting: AJAX call → Controller validates access → Service deletes actual step (plan step remains)

### 2. Form Structure
- Each step form needs to handle mixed data (existing plan steps + new items)
- Use array indexing: `step1[0][production_plan_step1_id]` vs `step1[1][dough_item_id]`
- Hidden input to distinguish: existing items have `production_plan_stepX_id`, new items have item IDs

### 3. JavaScript Considerations
- TomSelect initialization for all dropdowns
- Row cloning and index management
- AJAX delete with CSRF token
- Form validation before submission
- Handle recipe loading and ingredient population

### 4. Security
- Role-based access enforced at controller level
- Step progression enforced at controller level
- CSRF protection on all forms
- Validation on all inputs
- Authorization checks before delete operations

---

## Dependencies

### External Libraries
- TomSelect (already included in project)
- Font Awesome (already included)
- Bootstrap 5 (already included)
- jQuery (if needed for AJAX, already included)

### Internal Dependencies
- Production planning views (for reference on add/delete patterns)
- Recipe system (for Step 1 ingredients)
- Item management system
- Role and permission system

---

## Estimated Effort

- **Backend (Controller/Service)**: ✅ COMPLETED (4-6 hours)
- **Routes**: ✅ COMPLETED (30 minutes)
- **View Updates**: ❌ NOT STARTED (8-12 hours)
  - Step 1: 3-4 hours (most complex with ingredients)
  - Steps 2-5: 1-2 hours each
  - JavaScript: 2-3 hours
- **Request Validation**: ❌ NOT STARTED (1-2 hours)
- **Testing**: ❌ NOT STARTED (4-6 hours)
- **Documentation**: ❌ NOT STARTED (1-2 hours)

**Total Remaining**: ~18-24 hours

---

## Notes

1. **R&D Role**: May need to be created if it doesn't exist in the system
2. **Recipe Loading**: Step 1 requires recipe selection and ingredient loading - this is the most complex part
3. **Plan Step Preservation**: When deleting actual records, plan steps are preserved for historical reference
4. **Form Validation**: Need to handle both client-side (JavaScript) and server-side (Laravel) validation
5. **AJAX vs Form Submit**: Consider using AJAX for delete operations for better UX, but form submit is also acceptable

---

## Next Steps

1. **Immediate**: Update request validation classes to support optional plan step IDs
2. **Next**: Implement Step 1 view updates (most complex)
3. **Then**: Implement Steps 2-5 view updates
4. **Finally**: Add JavaScript functions and test thoroughly

---

**Last Updated**: 2025-01-20
**Status**: Backend Complete, Frontend Pending

