<!-- da14c247-8863-40e1-be87-a42233b74a79 72eecaf5-3a78-45e1-9ef7-7de325416db7 -->
# Refactor Plan: Item Dropdowns & TomSelect

### 1. Discovery: Identify all Item dropdown usages

- **Scan Blade views for Item bindings**
- Search in `resources/views` for common Item-related field names and bindings: `item_id`, `items[`, `dough_item_id`, `pack_item_id`, `kerupuk_kg_item_id`, `positionItems`, `Item::`, etc.
- Manually confirm which `<select>` elements actually represent `Item` records (vs other models) by checking their `name`/`id` attributes and the variables used in `@foreach` loops (e.g. `$items as $item`).
- **Catalogue each Item dropdown**
- For every confirmed Item dropdown, note:
- View file path (e.g. [`resources/views/manufacturing/recipes/create.blade.php`](resources/views/manufacturing/recipes/create.blade.php)).
- Field name (`item_id`, `pack_item_id`, etc.) and context (manufacturing, warehouse, forms, etc.).
- Whether it already uses TomSelect (CSS class, JS initialisation) or is a plain `<select>`.
- Any existing filters in the controller/service (e.g. `Item::active()->byCategory(...)`).

### 2. Design a single label format & model accessor

- **Confirm global label format**
- Standardize on a single human-facing label for Items, e.g. `"{{ $item->name }} - {{ $item->accurate_id }}"` so users can search by name or barcode/accurate_id.
- **Add an accessor on `Item`**
- In [`app/Models/Item.php`](app/Models/Item.php), introduce a `getLabelAttribute()` accessor returning the unified label string.
- Update any existing ad-hoc concatenations in views/controllers to use `$item->label` instead, ensuring future changes are centralized.

### 3. Standardize Item filtering via scopes/services

- **Extend Eloquent scopes on `Item`**
- In [`app/Models/Item.php`](app/Models/Item.php), keep `scopeActive()` and `scopeByCategory()` and consider adding more precise scopes (e.g. `scopeByCategoryIds(array $ids)`, `scopeForWarehouse(...)`, `scopeForProductionMaterials()`) based on patterns observed in controllers.
- **Introduce an Item dropdown provider service/helper**
- Create a small service class (e.g. `App\Services\ItemDropdownService`) that exposes methods like `forSelect(array $filters = []): Collection`.
- Implement filter options for:
- Active vs all items.
- Specific `item_category_id` or sets of categories (e.g. raw materials, packing materials, finished goods).
- Domain-specific needs (e.g. "only dough items", "only pack items", etc.), implemented using the standardized scopes.
- Ensure it always returns a consistent `id => label` collection using `$item->label`.
- **Document available filters**
- Based on the catalogue in step 1, list all distinct filter combinations required by current dropdowns and map them to service method/parameter names.

### 4. Refactor controllers/services to use the provider

- **Locate all controllers/services populating Item dropdowns**
- Use the catalogue from step 1 to find corresponding controllers (e.g. `RecipeController`, `ProductionPlanController`, `BulkInventoryController`, `FormRequestService`, etc.).
- **Replace inline Item queries**
- Where you currently have `Item::...->get()` in controllers, replace with calls to `ItemDropdownService` (method injection or via constructor in services) to retrieve `id => label` pairs.
- Keep domain-specific filters by translating them into explicit parameters (e.g. `->forSelect(['category_ids' => [...], 'active' => true])`).
- **Maintain backwards compatibility**
- Ensure each refactored controller still passes the same variable names to the views (`$items`, `$packItems`, etc.), but now as keyed collections (`id => label`) instead of Eloquent collections, or adjust the view loops accordingly.

### 5. Refactor Blade `<select>` elements (no components)

- **Update options to use the standard label**
- For each Item dropdown `<select>`, ensure options are rendered using the unified label:
- Change from `{{ $item->name }}` or `{{ $item->accurate_id }}` to `{{ $item->label }}` or the equivalent `id => label` pair.
- Preserve validation, `old()` handling, and selected state logic in place (no shared Blade components, as requested).
- **Normalize HTML structure and classes**
- Ensure each Item `<select>` has consistent classes (e.g. `form-select tom-select-item`) and clear `id` attributes that will be referenced by TomSelect initialisation.
- Keep other per-view attributes (e.g. `multiple`, `required`, `data-*` attributes) intact.

### 6. TomSelect: ensure all Item dropdowns use it

- **Identify non-TomSelect Item selects**
- From the catalogue, mark all Item dropdowns that are plain `<select>` (no TomSelect JS initialisation and no TomSelect-specific class).
- **Standardize TomSelect initialisation patterns**
- Reuse existing patterns from [`resources/views/warehouses/warehouses/bulk-edit.blade.php`](resources/views/warehouses/warehouses/bulk-edit.blade.php) and report views, but keep initialisation scripts local to each view (no shared Blade component).
- For single-select Item dropdowns, initialize `TomSelect` with:
- `placeholder: 'Select Item'` (or a context-specific placeholder).
- `allowEmptyOption: true` when the field is optional.
- For multi-select Item dropdowns, configure `maxItems: null`, `hideSelected: true`, and appropriate placeholders.
- **Wire TomSelect to all remaining Item selects**
- For each non-TomSelect Item select:
- Add a unique `id` if missing and a TomSelect CSS hook class (e.g. `class="form-select tom-select-item"`).
- Add or extend the view's `<script>` block to `new TomSelect('#id', { ... })`, ensuring required JS assets (Tabler/TomSelect bundle) are already loaded on the page.

### 7. Review filters coverage and edge cases

- **Verify filter completeness**
- Cross-check every Item dropdown against the `ItemDropdownService` capabilities to ensure each required filter (category, active state, domain-specific constraints) is supported.
- If new patterns emerge (e.g. "items with available stock", "items assigned to warehouse X"), add corresponding scopes and service parameters.
- **Handle large datasets & performance**
- For views where the Item list is very large (e.g. warehouse bulk-edit or production planning), consider switching to AJAX-backed TomSelect (endpoint using `ItemDropdownService`) in a later iteration, but keep this out of the initial refactor scope unless already implemented.

### 8. Testing & validation

- **Functional verification per view**
- For each refactored view, test:
- The dropdown shows the correct items, with label `Item Name - accurate_id`.
- Filters behave as before (no extra/missing items).
- Validation works and selected values persist via `old()` on validation errors.
- TomSelect renders correctly and is keyboard/scan-friendly (barcode scanner input selects the correct item via accurate_id).
- **Regression checks**
- Run existing feature tests touching manufacturing, warehouse, and forms flows involving Item selection.
- Perform a quick manual smoke test across key flows (e.g. recipe creation/editing, production planning, warehouse bulk edit, any form-request flows referencing items).

### 9. Documentation

- **Document the convention**
- Add a short section to an internal guide (e.g. [`guides/COMMON_TASKS.md`](guides/COMMON_TASKS.md) or a new `ITEMS_GUIDE.md`) describing:
- The standard Item label format (`$item->label`).
- How to use `ItemDropdownService` and its filters.
- How to initialise TomSelect for a new Item dropdown.
- Encourage future work to follow this pattern instead of introducing ad-hoc Item dropdown implementations.

### To-dos

- [ ] Scan all Blade views to identify every Item-related <select>, catalogue them with file paths, context, current filters, and TomSelect usage.
- [ ] Define and implement a single Item label accessor on the Item model and update references to use it for dropdowns.
- [ ] Create an ItemDropdownService (or similar helper) that centralizes Item filtering and returns id => label collections for dropdowns.
- [ ] Update controllers and services to use the Item dropdown provider instead of ad-hoc Item queries for dropdowns.
- [ ] Convert all remaining Item dropdowns to use TomSelect, update Blade markup to use the standard label, and validate behavior across all affected flows.