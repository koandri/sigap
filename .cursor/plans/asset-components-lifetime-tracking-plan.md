# Asset Components and Lifetime Tracking Implementation Plan (Simplified)

## Overview

Enhance the asset management system to support:
1. **Component Relationships**: Track assets that are part of other assets (e.g., tyres on a car, harddisks in a computer).
2. **Lifetime Tracking**: Calculate and track average lifetime of assets by category.
3. **Installation/Disposal Tracking**: Track when assets/components are installed (first used) and disposed, with optional usage values (e.g., Start KM and End KM for tyres).

## Key Design Decisions

- **No usage logging**: Only track installation and disposal dates/times. For usage-based assets (like tyres), track Start KM (installed_usage_value) when installed and End KM (disposed_usage_value) when disposed.
- **Lifetime calculation**:
  - **Usage-based** (e.g., kilometers): `disposed_usage_value - installed_usage_value`
  - **Date-based** (e.g., days): `disposed_date - installed_date` (or `purchase_date` for regular assets)
- **Lifetime unit**: Set at asset level. Each asset can use either date-based or usage-based lifetime.
- **Real-time Reporting**: Calculate averages on-the-fly when generating reports. No background jobs or complex caching.
- **Foreign key constraint**: Use `onDelete('restrict')` to prevent deletion of parent assets with components.

## Database Changes

### 1. Create ComponentType Enum

- **File**: `app/Enums/ComponentType.php`
- Cases: `Consumable`, `Replaceable`, `Integral`
- Methods: `label()`, `description()`

### 2. Create UsageUnit Enum

- **File**: `app/Enums/UsageUnit.php`
- Cases: `Days`, `Kilometers`, `MachineHours`, `Cycles`
- Methods: `label()`, `description()`

### 3. Add Component Relationships and Lifetime Tracking to Assets Table

- **File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_component_relationships_and_lifetime_to_assets.php`
- Add columns to `assets` table:
  - `parent_asset_id` (foreign key to assets, nullable, restrict on delete)
  - `component_type` (string, nullable) - Store ComponentType enum value
  - `installed_date` (date, nullable) - When asset/component was first installed/used
  - `disposed_date` (date, nullable) - When asset/component was disposed
  - `installed_usage_value` (decimal 15,2, nullable) - Start usage value (e.g., Start KM)
  - `disposed_usage_value` (decimal 15,2, nullable) - End usage value (e.g., End KM)
  - `lifetime_unit` (string, nullable) - Store UsageUnit enum value
  - `expected_lifetime_value` (decimal 15,2, nullable) - Expected lifetime
  - `actual_lifetime_value` (decimal 15,2, nullable) - Actual lifetime when disposed (calculated)
  - `installation_notes` (text, nullable)

## Models

### 4. Update Asset Model

- **File**: `app/Models/Asset.php`
- Add to `$fillable`:
  - `parent_asset_id`, `component_type`, `installed_date`, `installed_usage_value`, `disposed_usage_value`, `installation_notes`
  - `lifetime_unit`, `expected_lifetime_value`, `actual_lifetime_value`
- Add to `$casts`:
  - `installed_date` as 'date'
  - `installed_usage_value`, `disposed_usage_value`, `expected_lifetime_value`, `actual_lifetime_value` as 'decimal:2'
  - `component_type` as ComponentType enum
  - `lifetime_unit` as UsageUnit enum
- Add relationships:
  - `parentAsset(): BelongsTo`
  - `childAssets(): HasMany`
- Add methods:
  - `isComponent(): bool`
  - `hasComponents(): bool`
  - `getLifetimePercentage(): ?float`
  - `getRemainingLifetime(): ?float`

### 5. Update AssetCategory Model

- **File**: `app/Models/AssetCategory.php`
- Add methods:
  - `getAverageLifetime(?UsageUnit $unit = null): ?float` - Calculate average on-the-fly

## Services

### 6. AssetComponentService

- **File**: `app/Services/AssetComponentService.php`
- Methods:
  - `attachComponent(...)`
  - `detachComponent(...)`
  - `getComponents(...)`
  - `getComponentTree(...)`

### 7. AssetLifetimeService

- **File**: `app/Services/AssetLifetimeService.php`
- Methods:
  - `calculateAverageLifetime(int $categoryId, ?UsageUnit $unit = null): ?float`
  - `calculateActualLifetime(Asset $asset): ?float`
  - `updateAssetLifetimeOnDisposal(Asset $asset): void`

## Controllers

### 8. Update AssetController

- **File**: `app/Http/Controllers/AssetController.php`
- Update `store`, `update` to handle component/lifetime fields (remove usage_type).
- Add `attachComponent`, `detachComponent`, `showComponents`.

### 9. AssetLifetimeReportController

- **File**: `app/Http/Controllers/AssetLifetimeReportController.php`
- Methods:
  - `index(Request $request): View` - Simple dashboard showing categories and their average lifetimes.
  - `show(AssetCategory $category): View` - Detailed list of disposed assets in a category with their lifetimes.

## Views

### 10. Component Management Views

- `resources/views/assets/components/index.blade.php`
- `resources/views/assets/components/attach.blade.php`
- `resources/views/assets/components/detach.blade.php`

### 11. Lifetime Reporting Views

- `resources/views/reports/asset-lifetime/index.blade.php` - Summary table.
- `resources/views/reports/asset-lifetime/category.blade.php` - Detail list.

## Implementation Steps

1. **Cleanup**: Remove unused UsageType and Metrics tables/models/controllers.
2. **Database**: Update migrations to remove complex tables.
3. **Code**: Simplify Models and Controllers.
4. **Reporting**: Build the simple real-time report views.

