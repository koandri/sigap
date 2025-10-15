# CMMS Module Implementation Plan

## Current State Analysis

The CMMS foundation is already in place:
- Database schema: All 9 migrations created (`asset_categories`, `assets`, `maintenance_types`, `maintenance_schedules`, `work_orders`, `work_order_parts`, `maintenance_logs`, `asset_documents`)
- Models: All 8 models complete with relationships and scopes
- Service layer: `MaintenanceService.php` fully implemented with work order generation, scheduling, inventory consumption, and reporting
- Routes: All CMMS routes registered in `routes/web.php` (lines 205-219)
- Partial views: Dashboard template, work order views, asset category views, asset index

## Implementation Tasks

### 1. Complete Controller Implementation

**AssetCategoryController** (`app/Http/Controllers/AssetCategoryController.php`)
- Implement CRUD operations for asset categories
- Add filtering by status and search
- Include asset count in listings

**AssetController** (`app/Http/Controllers/AssetController.php`)
- Implement full CRUD for assets with image upload
- QR code generation for asset tracking
- Filter by category, status, location, department
- Asset detail view with maintenance history
- Document attachment management

**MaintenanceScheduleController** (`app/Http/Controllers/MaintenanceScheduleController.php`)
- CRUD operations for preventive maintenance schedules
- Manual trigger functionality to generate work orders
- Calendar view integration
- Filter by asset, status, due date

**MaintenanceDashboardController** (`app/Http/Controllers/MaintenanceDashboardController.php`)
- Statistics: total assets, active work orders, overdue schedules, upcoming maintenance
- Recent work orders list
- Upcoming maintenance schedule
- Asset status distribution chart
- Work order priority distribution chart

**MaintenanceLogController** (`app/Http/Controllers/MaintenanceLogController.php`)
- Display all maintenance logs with filters
- Asset-specific maintenance history view
- Export capabilities for reports

**MaintenanceReportController** (`app/Http/Controllers/MaintenanceReportController.php`)
- Date range-based maintenance reports
- Cost analysis per asset/category
- Downtime tracking
- Parts consumption reports
- Preventive vs corrective maintenance analysis

**MaintenanceCalendarController** (`app/Http/Controllers/MaintenanceCalendarController.php`)
- Calendar view of scheduled maintenance
- Work order scheduling
- FullCalendar integration for responsive mobile access

### 2. Complete View Implementation

**Maintenance Schedules** (`resources/views/maintenance/schedules/`)
- `index.blade.php`: List with filters (asset, type, status, overdue)
- `create.blade.php`: Form with asset, type, frequency, checklist
- `edit.blade.php`: Edit existing schedules
- `show.blade.php`: Schedule details with generation history

**Assets** (`resources/views/maintenance/assets/`)
- Complete `create.blade.php`: Full form with image upload
- `edit.blade.php`: Edit asset details and documents
- `show.blade.php`: Asset details, QR code, maintenance history, documents

**Maintenance Reports** (`resources/views/maintenance/reports/`)
- `index.blade.php`: Report parameter selection (date range, asset, category)
- Report output with charts using ApexCharts (Tabler includes it)

**Calendar** (`resources/views/maintenance/calendar.blade.php`)
- FullCalendar implementation showing schedules and work orders
- Color-coded by priority/type

### 3. Seed Data & Permissions

**MaintenanceTypeSeeder** (`database/seeders/MaintenanceTypeSeeder.php`)
- Preventive Maintenance (green)
- Corrective Maintenance (orange)
- Emergency Repair (red)
- Inspection (blue)
- Calibration (purple)

**MaintenancePermissionSeeder** (`database/seeders/MaintenancePermissionSeeder.php`)
- `maintenance.dashboard.view`
- `maintenance.assets.view`, `maintenance.assets.manage`
- `maintenance.schedules.view`, `maintenance.schedules.manage`
- `maintenance.work-orders.view`, `maintenance.work-orders.create`, `maintenance.work-orders.complete`
- `maintenance.reports.view`

**AssetCategorySeeder** (`database/seeders/AssetCategorySeeder.php`)
- Production Equipment (PROD)
- Processing Machinery (PROC)
- Refrigeration Systems (REFR)
- Packaging Equipment (PACK)
- Facility Infrastructure (FACL)

### 4. Integration & Features

**Spare Parts Integration**
- Already integrated via `WorkOrderPart` model
- Uses existing warehouse/inventory system (`PositionItem`, `Item`, `Warehouse`)
- Inventory consumption on work order completion (implemented in `MaintenanceService`)

**Mobile Responsiveness**
- Tabler admin template is mobile-responsive by default
- Calendar uses FullCalendar responsive mode
- Forms use Bootstrap 5 responsive grid

**QR Code Generation**
- Asset QR codes for quick mobile scanning (route already exists)
- Links to asset detail page

### 5. UI/UX Enhancements

- Use FontAwesome icons for actions
- Status badges with appropriate colors
- Priority indicators (urgent=red, high=orange, medium=blue, low=gray)
- Empty states with helpful messages
- Toast notifications for actions
- Confirmation modals for deletions

## Key Files to Implement

**Controllers (7 files)**
- `app/Http/Controllers/AssetCategoryController.php` - Full CRUD
- `app/Http/Controllers/AssetController.php` - Full CRUD + QR + Documents
- `app/Http/Controllers/MaintenanceScheduleController.php` - Full CRUD + Trigger
- `app/Http/Controllers/MaintenanceDashboardController.php` - Dashboard data
- `app/Http/Controllers/MaintenanceLogController.php` - History viewing
- `app/Http/Controllers/MaintenanceReportController.php` - Reports generation
- `app/Http/Controllers/MaintenanceCalendarController.php` - Calendar data

**Views (15+ files)**
- Schedules: index, create, edit, show
- Assets: complete create, add edit & show
- Reports: index with charts
- Calendar: calendar view
- Complete work-orders edit view

**Seeders (3 files)**
- `database/seeders/MaintenanceTypeSeeder.php`
- `database/seeders/MaintenancePermissionSeeder.php`
- `database/seeders/AssetCategorySeeder.php`

## Dependencies Already Available

- Spatie Laravel Permission (for RBAC)
- Intervention Image (for asset images)
- Tabler Admin Template with Bootstrap 5
- ApexCharts (included in Tabler for reports)
- FullCalendar (included in Tabler for calendar)
- SimpleSoftwareIO/simple-qrcode or Bacon/BaconQrCode (for QR codes)

## Mobile Access Strategy

Since responsive design for mobile browsers was chosen:
- All views use Tabler responsive components
- Mobile-optimized tables with horizontal scroll
- Touch-friendly buttons and forms
- Calendar view works on mobile devices
- QR code scanning links to asset details

## Testing Checklist

1. Create asset categories and assets
2. Set up preventive maintenance schedules
3. Generate work orders automatically from schedules
4. Create manual work orders
5. Complete work orders with parts consumption
6. View maintenance history per asset
7. Generate maintenance reports
8. Test calendar view
9. Test QR code generation and scanning
10. Verify mobile responsiveness

### To-dos

- [ ] Implement all 7 CMMS controllers with full CRUD operations, filtering, and business logic
- [ ] Create all missing views for schedules, assets (edit/show), reports, and calendar
- [ ] Populate MaintenanceTypeSeeder, MaintenancePermissionSeeder, and AssetCategorySeeder with seed data
- [ ] Implement QR code generation for assets using available QR library
- [ ] Implement FullCalendar in MaintenanceCalendarController and view for schedule visualization
- [ ] Build maintenance reports with ApexCharts for cost analysis and downtime tracking
- [ ] Verify and optimize all views for mobile responsiveness using Tabler components
- [ ] Test complete CMMS workflow from asset creation to work order completion and reporting