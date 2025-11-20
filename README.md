# SIGaP - Sistem Informasi Gabungan Pelaporan

<p align="center">
  <img src="public/imgs/logo.png" alt="SIGaP Logo" width="200">
</p>

<p align="center">
  <strong>Enterprise ERP & Business Process Management Platform for Food Manufacturing</strong>
</p>

## About SIGaP

SIGaP (Sistem Informasi Gabungan Pelaporan) is a comprehensive **Enterprise Resource Planning (ERP) and Business Process Management** platform designed specifically for **PT. Surya Inti Aneka Pangan**, one of Indonesia's largest fish and prawn manufacturing companies.

Built on Laravel 12 with modern best practices, SIGaP integrates four critical business modules into one unified system:

### 🎯 Four-Pillar Architecture

**1. Forms & Approval Workflows** - Digital transformation of paper-based processes
- Dynamic form builder with 16 field types
- Multi-level approval workflows with SLA management
- Department-based access control and routing
- Complete audit trails and compliance tracking

**2. Manufacturing & Inventory Management** - Smart warehouse operations
- Multi-warehouse management with shelf-based organization
- Bill of Materials (BoM) with approval workflows
- FIFO-based picklist generation
- Real-time inventory tracking and expiry management
- Excel import/export capabilities

**3. Computerized Maintenance Management System (CMMS)** - Equipment lifecycle management
- Complete asset tracking with automatic QR code generation
- Preventive and corrective maintenance scheduling
- Work order management with parts integration
- Maintenance calendar and performance analytics
- Mobile-friendly QR code scanning for field technicians

**4. Facility Management & Cleaning System** - Comprehensive facility operations
- Dynamic cleaning schedules with time-based configuration (hourly/daily/weekly/monthly/yearly)
- Mobile-first cleaner workflow with GPS photo watermarking
- Smart approval system with random quality sampling (10-20%)
- SLA tracking with real-time overdue monitoring
- Guest request handling (public form for cleaning/repair requests)
- Daily and weekly reports with PDF export
- Asset lifecycle management with automatic alerts

**5. Document Management System (DMS)** - Enterprise document lifecycle management
- Document version control with two-tier approval workflows
- Access request system with version tracking and watermarked downloads
- Printed form lifecycle management with QR code tracking
- Document instances for memos and letters
- Comprehensive reporting and SLA dashboards
- OnlyOffice integration for collaborative editing

This isn't just another business application - it's a **sophisticated enterprise platform** that rivals commercial ERP solutions like SAP, Oracle, or Microsoft Dynamics, but purpose-built for the food manufacturing industry with Indonesian business practices in mind.

## Core Features

### 📋 Advanced Form Management
- **Dynamic Form Builder**: Create custom forms with 16 field types including:
  - Text fields: Short text and long text (WYSIWYG)
  - Numeric fields: Number and decimal with validation
  - Date/time fields: Date and datetime pickers
  - Selection fields: Single select, multiple select, radio buttons, checkboxes
  - Boolean fields: Yes/No toggle switches
  - File uploads with image processing and watermarking
  - Digital signature capture with canvas-based pad
  - Live photo capture with HTML5 camera integration
  - Calculated fields with formula engine (SUM, MULTIPLY, DIVIDE, etc.)
  - Hidden fields for system metadata and tracking
- **Form Versioning**: Complete version control with activation management
- **Department-based Access**: Granular form access control by organizational units
- **Form Templates**: Reusable templates for standardized processes
- **API Integration**: Dynamic dropdown options from external data sources
- **Form Prefilling**: Auto-populate forms based on user context and historical data
- **TomSelect Enhancement**: Modern dropdown interface with search and better UX (uses tom-select.base.min.js)

### 🔄 Sophisticated Approval Workflow Engine
- **Sequential Workflows**: Step-by-step approval chains with conditional routing
- **Parallel Workflows**: Simultaneous multi-approver processes
- **Multi-level Approvals**: Unlimited approval steps with complex hierarchies
- **Role-based Assignment**: Assign approvers by user, role, or department
- **SLA Management**: Set time limits with automatic escalation
- **Smart Escalation**: Auto-escalate to managers/supervisors when overdue
- **Conditional Workflows**: Dynamic routing based on form data
- **Complete Audit Trail**: Full logging with metadata and timestamps

### 👥 Enterprise User & Permission Management
- **Role-based Access Control (RBAC)**: Granular permissions using Spatie Laravel Permission 6.20+
  - Custom roles with specific permissions
  - Role inheritance and composition
  - Permission caching for performance
- **Department Organization**: Hierarchical department structure with inheritance
  - Department-based form access
  - Cost center tracking
  - Organizational reporting
- **User Impersonation**: Admin capability for user experience testing (Lab404 Impersonate)
  - Full audit logging
  - Secure session management
  - One-click impersonation from user management
- **Authentication**: Enhanced security with Laravel Fortify via Fortify-UI
  - Email/password authentication
  - Password reset functionality
  - Email verification
  - Session management
- **External Authentication**: Laravel Socialite with Asana provider
  - Single Sign-On (SSO) capability
  - OAuth2 integration
  - Automatic user provisioning
- **Permission Matrix**: Comprehensive role and permission management
  - Module-specific permissions (forms, manufacturing, maintenance)
  - Action-based permissions (view, create, edit, delete, approve)
  - Department-level access controls

### 📁 Advanced Document Management
- **File Processing**: Complete upload/download system with security controls
- **Image Optimization**: Automatic image compression and thumbnail generation (Intervention Image 3.11+)
- **Digital Signatures**: Built-in signature capture and verification
- **Live Photo Capture**: Real-time camera integration for field documentation
- **File Watermarking**: Document security with custom watermarks
- **Print-ready Reports**: PDF-optimized submission views
- **Download Controls**: Secure file access with permission checks
- **Multiple File Upload**: Support for multiple files per field

### 📊 Business Intelligence & Analytics
- **Calculation Engine**: Built-in formula processor for complex calculations
- **Real-time Dashboards**: Form submission tracking and analytics
- **Workflow Performance**: Approval time analysis and bottleneck identification
- **Status Monitoring**: Live progress tracking for all submissions
- **Escalation Reports**: Overdue approval monitoring and alerting
- **Department Analytics**: Performance metrics by organizational unit

### 📱 Notification System
- **WhatsApp Integration**: Primary notification channel via WAHA API
  - Text messages with Markdown formatting support
  - Image attachments with captions
  - File attachments (PDFs, documents)
  - Link previews for quick access
  - Group and individual messaging
- **Pushover Fallback**: Automatic high-priority alerts when WhatsApp fails
  - HTML-formatted notifications
  - Priority 2 (requires acknowledgment)
  - Detailed failure information
  - Multi-device delivery
- **Notification Types**:
  - Asset disposal alerts to Engineering team
  - User registration credentials delivery
  - Warehouse inventory updates
  - Approval workflow notifications (requests, completions, rejections, escalations)
- **Testing Tools**: Built-in test command for verification
- **Dual-Channel Reliability**: Never miss critical notifications

### 🔧 Advanced Technical Features
- **Queue Processing**: Background job processing for notifications and escalations
- **RESTful API**: Comprehensive API endpoints for:
  - Form field options (API-sourced dropdowns)
  - Field calculations
  - Testing API configurations
  - Cache management
- **Caching System**: Optimized performance with Redis/Memcached support
  - API response caching with configurable TTL
  - Form field options caching
  - Manual cache clearing endpoints
- **Background Processing**: Asynchronous handling of heavy operations
- **Error Handling**: Comprehensive logging and error tracking
- **Route Organization**: Clean route structure with resource controllers
  - 39 controllers handling different modules
  - Middleware-protected routes with role-based access
  - Route model binding for cleaner code

### 🏭 Manufacturing & Inventory Management
- **Multi-Warehouse Management**: Manage multiple warehouses with shelf-based organization
  - Warehouse categories and locations
  - Shelf and position hierarchy (Warehouse → Shelf → Position)
  - Aisle-based organization
  - Visual warehouse layouts
- **Shelf-Based Inventory**: Organize inventory by warehouse, shelf, and position
  - Three-level hierarchy for precise location tracking
  - Position-level quantity management
  - Batch number tracking per position
  - Expiry date management per batch
  - Movement history and audit trail
- **Item Management**: Comprehensive item catalog with categories and units
  - Item categories for organization
  - Multiple unit types supported
  - Minimum stock level alerts
  - Price tracking (optional)
  - Active/inactive status management
- **Recipe Management**: Recipe and ingredient management (replaces BoM system)
  - Recipe versioning with date tracking
  - Ingredient list with quantities and units
  - Recipe approval workflow
  - Production requirement calculation
  - Cost estimation
- **Production Planning System**: 5-step production planning workflow for cracker manufacturing
  - **Step 1**: Dough Production Planning (Adonan) with recipe selection
  - **Step 2**: Gelondongan Production Planning from Adonan (auto-calculated using yield guidelines)
  - **Step 3**: Kerupuk Kering Production Planning from Gelondongan (auto-calculated using yield guidelines)
  - **Step 4**: Packing Planning for finished products (auto-calculated using weight configurations)
  - **Step 5**: Packing Materials Planning (material requirements calculation)
  - Multi-site quantity tracking (GL1, GL2, TA, BL distribution channels)
  - Recipe integration with ingredient snapshot tracking
  - Yield guideline management (master data for yield calculations)
  - Auto-calculation between steps using yield guidelines
  - Status workflow: Draft → Approved → In Production → Completed
  - Complete CRUD operations with step-by-step planning
  - Production plan approval system
  - Comprehensive plan overview with all steps in tabbed interface
- **Picklist Generation**: FIFO-based picking lists across warehouses
  - Automatic FIFO logic (oldest first, expiry date priority)
  - Cross-warehouse picking
  - Recipe-based picklist generation
  - Manual picklist creation
  - Print-friendly format
- **Expiry Tracking**: Monitor and manage items approaching expiration
  - Dashboard alerts for expiring items
  - Configurable warning thresholds
  - Expiry date filtering in reports
- **Inventory Reports**: Overview reports with filtering and export capabilities
  - Warehouse overview report
  - Shelf-level reports
  - Item location reports
  - Expiry reports
  - Excel and PDF export
- **Bulk Operations**: Bulk inventory updates and movements
  - Bulk quantity adjustments
  - Mass item movements
  - Aisle-based bulk operations
  - Excel-based bulk updates
- **Excel Import/Export**: Import items from Excel spreadsheets
  - Template-based import
  - Item master data import
  - Validation and error reporting
  - Inventory data export
- **Temperature Sensor Monitoring**: Real-time temperature monitoring via HomeAssistant integration
  - Live temperature data visualization with Chart.js
  - Configurable time range selection (default: last 8 hours)
  - Adjustable data sampling intervals (5, 15, 30, 60 minutes)
  - Interactive datetime range picker
  - Smooth line charts with historical data
  - HomeAssistant API integration with Bearer token authentication

### 🧹 Facility Management & Cleaning System
- **Cleaning Schedules**: Flexible scheduling system with 5 frequency types
  - Hourly: Generate tasks every X hours within time range (e.g., every 2 hours from 8am-6pm)
  - Daily: Tasks at specific time daily
  - Weekly: Tasks on specific weekdays at specific time
  - Monthly: Tasks on specific dates at specific time
  - Yearly: Annual tasks on specific date/month
  - Time-based configuration with duplicate prevention
- **Mobile Cleaner Workflow**: Complete task management with mobile support
  - Today's task view with assignment priority
  - Task locking system (2-hour timeout)
  - Before/after photo capture with GPS watermarking
  - Automatic task status tracking
  - Real-time progress monitoring
- **Smart Approval System**: Intelligent quality control
  - Random flagging (10-20% of tasks) for detailed review
  - Enforcement: Must review flagged tasks before mass approval
  - SLA tracking with 9am next-day deadline
  - Color-coded status: green (on-time), yellow (<24hrs overdue), red (>24hrs)
  - Hours overdue calculation and monitoring
- **Guest Request System**: Public form for facility requests
  - Anonymous submission (name + phone, no login required)
  - Request types: cleaning or repair
  - Photo upload support
  - Turnstile CAPTCHA protection
  - Staff handling: create cleaning task or maintenance work order
  - Automatic assignment notifications
- **Asset Lifecycle Management**: Proactive maintenance
  - Detects inactive/disposed assets in schedules
  - Creates alerts for schedule maintenance
  - Resolution actions: replace asset, convert to general item, dismiss
  - Dashboard widget for unresolved alerts
- **Dashboard & Analytics**: Real-time performance monitoring
  - Cleaner ranking by completion percentage
  - Overall completion vs pending rates
  - Average approval time tracking
  - SLA compliance monitoring
  - Tasks by location breakdown
  - Unresolved alerts widget
- **Reporting System**: Comprehensive reports with PDF export
  - Daily Report: All tasks for location on specific date
  - Weekly Report: 7-day grid with ✓/⚠/✗ indicators
  - Cell details modal (click for task breakdown)
  - PDF export (A4 landscape for weekly)
  - Filter by location, date range, status
- **Notifications**: Multi-channel notification system
  - WhatsApp integration (primary channel)
  - Pushover fallback for critical alerts
  - Task assignment notifications
  - Reminder notifications (configurable hours before)
  - Flagged submission alerts
  - Missed task notifications
- **Automation Commands**: Optional automated operations
  - Auto-generation: Daily task creation from schedules (disabled by default)
  - Automatic reminders: Send notifications X hours before tasks
  - Missed task marking: Auto-mark uncompleted tasks
  - Lock release: Free tasks locked >2 hours
  - Random flagging: Select tasks for quality review

### 🔧 Maintenance Management (CMMS)
- **Asset Management**: Track all equipment with comprehensive features
  - Asset categories with custom codes
  - Custom locations (database-driven, not hardcoded)
  - **Automatic QR code generation** for mobile access (Endroid QR Code 6.0+)
  - QR codes with optional embedded company logo
  - QR codes gallery with filtering and bulk download
  - Asset specifications in JSON format
  - Department and user assignment
  - Purchase information and warranty tracking
- **Maintenance Types**: Six pre-configured types (customizable)
  - Preventive Maintenance (scheduled prevention)
  - Corrective Maintenance (repairs and fixes)
  - Emergency Repair (critical failures)
  - Inspection (condition assessment)
  - Calibration (instrument calibration)
  - Enhancement (modifications and improvements)
- **Preventive Maintenance Scheduling**: Flexible frequency options
  - Hourly: Every X hours
  - Daily: Every X days
  - Weekly: Specific days of the week
  - Monthly: Specific dates, last day, or weekday patterns
  - Yearly: Annual maintenance on specific dates
- **Work Order Management**: Complete lifecycle tracking
  - Status workflow: Submitted → Assigned → In Progress → Pending Verification → Verified → Completed
  - Priority levels: Low, Medium, High, Critical
  - Time tracking: Estimated vs. actual hours
  - Parts consumption tracking from inventory
  - Photo documentation with multiple uploads
  - Progress logging with timestamps
  - Action tracking for all work performed
  - Work order policies for authorization
- **Upcoming Maintenance**: 14-day forecast visibility
  - See upcoming schedules before they're overdue
  - One-click work order generation from upcoming schedules
  - Status indicators: Scheduled, WO Exists, Overdue
  - Prevents duplicate work orders
- **Automatic Work Order Generation**: Optional automation (disabled by default)
  - Runs daily via Laravel scheduler
  - Auto-creates work orders from overdue schedules
  - See MAINTENANCE_SCHEDULING_GUIDE.md for setup
- **Maintenance Dashboard**: Real-time KPI overview
  - Total assets by status
  - Active and pending work orders
  - Overdue schedules with alerts
  - Recent work order activity
  - Asset status distribution (pie charts)
  - Upcoming maintenance grid (14-day forecast)
- **Asset Reports**: Comprehensive reporting suite
  - Assets by Location (with active/inactive filter)
  - Assets by Category (grouped statistics)
  - Assets by Category and Location (cross-matrix view)
  - Assets by Department (organizational view)
  - Assets by Assigned User (responsibility tracking)
- **Work Order Reports**: Performance analytics
  - Work order completion metrics
  - Total and average hours worked
  - Maintenance type breakdown
  - Asset-level work order history
  - Monthly trend analysis
  - Technician performance tracking
- **Maintenance Calendar**: Visual scheduling interface
  - Calendar view of all maintenance schedules
  - Color-coded by maintenance type
  - Interactive event details
  - Export and print capabilities
- **Maintenance Logs**: Complete audit trail
  - Asset-level maintenance history
  - Parts usage tracking
  - Technician activity logs
  - Before/after photo comparison
- **Parts Integration**: Seamless inventory connection
  - Direct parts consumption from warehouse positions
  - Automatic inventory deduction
  - Parts usage history per asset
  - Cost tracking per maintenance activity

### 📄 Document Management System (DMS)
- **Document Version Control**: Complete versioning system with two-tier approval workflow
  - Manager and management representative approval tiers
  - Draft → Pending Manager Approval → Pending Mgmt Approval → Active → Superseded
  - Version history and revision tracking
  - OnlyOffice integration for document editing (DOCX/XLSX)
  - PDF conversion and watermarked viewing
- **Document Types**: Support for 8 document types
  - SOP (Standard Operating Procedures)
  - Work Instructions
  - Forms
  - Job Descriptions
  - Internal Memos (template-based with instances)
  - Incoming Letters
  - Outgoing Letters (template-based with instances)
  - Other
- **Access Control**: Comprehensive access request system
  - One-time or multiple access requests
  - Access tied to specific document versions
  - Approval workflow with expiry dates
  - Access logging and audit trail
  - Watermarked PDF downloads for security
- **Form Request Management**: Complete printed form lifecycle
  - Request printed forms with quantities
  - Document Control acknowledgment and processing
  - QR code label generation for tracking
  - Form numbering system (PF-YYMMDD-XXXX)
  - Physical location tracking (room, shelf, folder)
  - Status tracking: Issued → Circulating → Returned → Received → Scanned
  - Bulk operations (return, receive, upload scans, update location)
  - Scanned form storage and access control
- **Document Instances**: Template-based memo and letter management
  - Create instances from template versions
  - Instance numbering system
  - Approval workflow for instances
  - Final PDF generation and storage
- **Reports & Analytics**: Comprehensive reporting suite
  - Documents Masterlist (grouped by department/type, exportable)
  - Location-based reports (group by physical location)
  - SLA Dashboard with performance metrics
  - Form circulation reports
  - Access request history
- **Dashboard**: Real-time DMS overview
  - Document statistics by type and status
  - Pending approvals by tier
  - Recent access requests
  - Overdue approvals
  - Active form requests
  - SLA compliance metrics

### 🗺️ System Integration
- **Cross-Module Integration**: Seamless data flow between modules
  - Facility requests create maintenance work orders
  - Maintenance uses manufacturing inventory for parts
  - Form workflows integrate with all modules
  - Shared asset and location databases
  - Unified notification system
  - Consistent permission model across modules
  - DMS documents can be attached to assets

### 🛡️ Security Officer Logs
- Security incident reporting and tracking
- Daily security logs and patrol checklists
- Security officer shift management
- Incident escalation and resolution workflows
- Integration with surveillance systems

### 👥 Visitors Log
- Comprehensive visitor registration system
- Digital visitor badge printing
- Visit purpose tracking and approval
- Security clearance management
- Integration with access control systems

### 🏭 Production and QC Reporting Tool
- Real-time production line reporting
- Quality control checklists and inspections
- Batch tracking and product traceability
- Compliance reporting for food safety standards
- Integration with manufacturing systems

## Technology Stack

**Backend:**
- **Framework**: Laravel 12.x with PHP 8.2+
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Authentication**: Laravel Fortify with enhanced UI components (zacksmash/fortify-ui 2.0+)
- **Authorization**: Spatie Laravel Permission 6.20+ for RBAC
- **Image Processing**: Intervention Image 3.11+ for file handling
- **QR Code Generation**: Endroid QR Code 6.0+ for asset QR codes with logo support
- **Excel Processing**: Maatwebsite Excel 3.1+ for imports/exports
- **User Impersonation**: Lab404 Laravel Impersonate 1.7+ for admin support
- **Options Management**: Spatie Laravel Options 1.2+ for configuration
- **Testing**: Pest PHP 3.8+ with comprehensive test coverage
- **Queue System**: Redis/Database queues for background processing
- **Caching**: Redis/Memcached for performance optimization
- **Task Scheduling**: Laravel scheduler for automated tasks
- **External Auth**: Laravel Socialite 5.21+ with Asana provider support

**Frontend:**
- **Template Engine**: Laravel Blade
- **CSS Framework**: Bootstrap 5
- **Admin Template**: Tabler.io Admin Template (tabler/tabler)
- **JavaScript Framework**: Minimal vanilla JavaScript/jQuery
- **Build Tools**: Vite for modern asset compilation and hot reloading
- **Icon Library**: Font Awesome for web icons
- **Enhanced Selects**: Tom Select (tom-select.base.min.js) for dropdowns
- **Date Picker**: Litepicker (CDN delivery)
- **Image Handling**: Lightbox for image viewing
- **File Uploads**: Native HTML5 with drag-and-drop support
- **Signature Capture**: Canvas-based signature pad
- **Photo Capture**: HTML5 Media API for camera access

**Frontend Philosophy:**
- Server-side form validation (Laravel)
- Client-side JavaScript only for UI enhancement
- Progressive enhancement approach
- Mobile-responsive design
- Minimal JavaScript dependencies

## System Requirements

### Server Requirements
- **PHP**: 8.2 or higher (PHP 8.3+ recommended for latest features)
- **Database**: MySQL 8.0+ or PostgreSQL 13+ (with JSON support)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 2GB RAM (4GB+ recommended for production with multiple modules)
- **Storage**: 20GB+ available space (more for file uploads, images, and backups)
- **Redis**: For caching and queue management (highly recommended for production)
- **Node.js**: 18+ and NPM for asset compilation

### PHP Extensions
Required:
- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- GD or ImageMagick for image processing with Intervention Image 3.11+
- GD extension for QR code generation (Endroid QR Code 6.0+)
- Redis extension for caching and queue management (optional but highly recommended)
- Zip extension for Excel imports/exports (PHPSpreadsheet)
- cURL for external API integrations (API-sourced form fields)

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+ or PostgreSQL 13+
- Redis (recommended)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd sigap
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   Edit `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sigap
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Configure file storage**
   ```bash
   php artisan storage:link
   
   # Create QR code directory
   mkdir -p public/storage/assets_qr
   ```

8. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

9. **Start the application**
   ```bash
   php artisan serve
   ```

### Development Environment
For local development with hot module replacement:
```bash
composer dev
# This runs: server, queue worker, logs (pail), and vite dev server concurrently
```

### Testing
```bash
composer test
# or directly
php artisan test
```

## API Integration

### External API Configuration
Forms can integrate with external APIs for dynamic options:

```php
// Example API configuration for form fields
'api_source' => [
    'url' => 'https://api.example.com/data',
    'method' => 'GET',
    'headers' => ['Authorization' => 'Bearer token'],
    'value_field' => 'id',
    'label_field' => 'name',
    'cache_ttl' => 300
]
```

### Calculation Engine
Use built-in formulas in calculated fields:

```javascript
// Example calculations
SUM(field1, field2, field3)
MULTIPLY(quantity, price)
IF(field1 > 100, field1 * 0.1, 0)
AVERAGE(field1, field2, field3)
SUBTRACT(total, discount)
DIVIDE(amount, quantity)
```

## Project Structure

```
app/
├── Models/              # Eloquent models
│   ├── Form.php            # Form management
│   ├── FormSubmission.php  # Submission handling
│   ├── ApprovalWorkflow.php # Workflow engine
│   ├── Asset.php           # Asset management
│   ├── WorkOrder.php       # Work order tracking
│   ├── MaintenanceSchedule.php # Maintenance scheduling
│   ├── Warehouse.php       # Warehouse management
│   ├── Item.php            # Inventory items
│   └── BomTemplate.php     # Bill of materials
├── Services/            # Business logic services
│   ├── ApprovalService.php    # Workflow processing and SLA management
│   ├── CalculationService.php # Formula engine for calculated fields
│   ├── FormPrefillService.php # Auto-population based on user context
│   ├── HiddenFieldService.php # Hidden field value resolution
│   ├── MaintenanceService.php # Maintenance scheduling and work orders
│   ├── ApiOptionsService.php  # External API integration and caching
│   ├── WhatsAppService.php    # WhatsApp notification integration (WAHA API)
│   ├── PushoverService.php    # Pushover fallback notifications
│   └── AssetDisposalService.php # Asset disposal and deactivation
├── Http/Controllers/    # Web controllers
│   ├── FormController.php     # Forms
│   ├── WorkOrderController.php # Maintenance
│   ├── WarehouseController.php # Inventory
│   └── ... (39 controllers)
├── Helpers/            # Utility classes
│   ├── AuthHelper.php        # Authentication helpers and user context
│   ├── FormPrefillHelper.php # Form auto-population logic
│   └── helpers.php           # Global helper functions
├── Enums/             # Application enums
│   ├── FrequencyType.php  # Maintenance schedule frequencies
│   └── Location.php       # Asset location options
├── Policies/          # Authorization policies
│   └── WorkOrderPolicy.php   # Work order authorization
└── Console/Commands/  # Artisan commands
    ├── GenerateMaintenanceWorkOrders.php
    ├── TestWhatsAppNotification.php
    └── (other commands...)

resources/views/
├── forms/              # Form management
├── formsubmissions/    # Submission views
├── approval-workflows/ # Workflow management
├── manufacturing/      # Inventory & warehouses
├── maintenance/        # CMMS module
└── layouts/           # Application layouts

database/
├── migrations/        # Database schema (54 migrations)
├── seeders/           # Database seeders (10 seeders)
│   ├── DatabaseSeeder.php
│   ├── UserSeeder.php
│   ├── LocationSeeder.php
│   ├── AssetCategorySeeder.php
│   ├── MaintenanceTypeSeeder.php
│   ├── MaintenancePermissionSeeder.php
│   ├── ManufacturingPermissionSeeder.php
│   ├── BomTypeSeeder.php
│   ├── ShelfPositionSeeder.php
│   └── WarehouseShelfSeeder.php
└── factories/         # Model factories for testing

config/                # Application configuration (13 files)
├── app.php            # Core application settings
├── auth.php           # Authentication configuration
├── cache.php          # Cache driver configuration
├── database.php       # Database connections
├── filesystems.php    # Storage configuration
├── fortify.php        # Laravel Fortify authentication
├── image.php          # Intervention Image settings
├── logging.php        # Log configuration
├── mail.php           # Email settings
├── options.php        # Spatie Laravel Options
├── permission.php     # Spatie Permission RBAC
├── queue.php          # Queue driver settings
├── services.php       # Third-party service credentials
├── session.php        # Session configuration
└── watermark.php      # Custom watermark settings

guides/                # User documentation (11+ comprehensive guides)
├── USER_GUIDE.md              # System overview and quick start
├── FORMS_GUIDE.md             # Form management (admins and users)
├── WORKFLOWS_GUIDE.md         # Approval workflow configuration
├── MANUFACTURING_GUIDE.md     # Warehouse and inventory management
├── MAINTENANCE_GUIDE.md       # CMMS operations and asset management
├── CLEANING_NOTIFICATIONS_GUIDE.md # Facility management and cleaning operations
├── DMS_GUIDE.md              # Document Management System guide
├── NOTIFICATIONS_GUIDE.md     # WhatsApp and Pushover notification system
├── ADMIN_GUIDE.md             # User management and permissions
├── COMMON_TASKS.md            # Quick reference and troubleshooting
└── API_OPTIONS_GUIDE.md       # API integration for form fields

MAINTENANCE_SCHEDULING_GUIDE.md # Automatic work order generation (root level)
```

## Database Schema

### Core Models (33 models)

**Form Management (9 models):**
- `Form`, `FormVersion`, `FormField`, `FormFieldOption`
- `FormSubmission`, `FormAnswer`
- `ApprovalWorkflow`, `ApprovalFlowStep`, `ApprovalLog`

**Manufacturing & Inventory (15+ models):**
- `Warehouse`, `WarehouseShelf`, `ShelfPosition`, `PositionItem`
- `Item`, `ItemCategory`
- `Recipe`, `RecipeIngredient` (replaces BoM system)
- `ProductionPlan`, `ProductionPlanStep1`, `ProductionPlanStep2`, `ProductionPlanStep3`, `ProductionPlanStep4`, `ProductionPlanStep5`
- `ProductionPlanStep1RecipeIngredient`, `YieldGuideline`

**Facility Management (7 models):**
- `CleaningSchedule`, `CleaningScheduleItem`, `CleaningScheduleAlert`
- `CleaningTask`, `CleaningSubmission`, `CleaningApproval`
- `CleaningRequest`

**Document Management (8 models):**
- `Document`, `DocumentVersion`, `DocumentVersionApproval`
- `DocumentAccessRequest`, `DocumentAccessLog`, `DocumentInstance`
- `FormRequest`, `FormRequestItem`, `PrintedForm`, `PrintedFormLabel`

**Maintenance (CMMS - 12 models):**
- `Asset`, `AssetCategory`, `AssetDocument`, `Location`
- `MaintenanceSchedule`, `MaintenanceType`, `MaintenanceLog`
- `WorkOrder`, `WorkOrderAction`, `WorkOrderPart`, `WorkOrderPhoto`, `WorkOrderProgressLog`

**User Management (4 models):**
- `User`, `Role`, `Permission`, `Department`

**Total: 49+ Eloquent models**

All models follow Laravel best practices:
- Final classes to prevent inheritance
- Explicit type declarations
- Eloquent relationships properly defined
- Mass assignment protection
- Proper timestamp handling

### Service Layer Architecture

The application follows a service-oriented architecture with business logic separated from controllers:

**ApprovalService**: Manages workflow processing
- Step execution and routing
- SLA tracking and escalation
- Approval/rejection handling
- Audit trail logging

**CalculationService**: Handles field calculations
- Formula parsing and evaluation
- Support for SUM, MULTIPLY, DIVIDE, SUBTRACT, AVERAGE, IF
- Real-time field dependency resolution
- Error handling and validation

**FormPrefillService**: Auto-populates form fields
- User context-based prefilling
- Historical data retrieval
- Department-based defaults
- Custom prefill rules

**MaintenanceService**: Manages maintenance operations
- Next due date calculation for all frequency types
- Work order generation from schedules
- Duplicate prevention logic
- Parts inventory integration

**DocumentService**: Manages document operations
- Document creation and metadata management
- Cross-department access assignment
- Masterlist generation
- Physical location formatting

**DocumentVersionService**: Manages version control
- Version creation (scratch/upload/copy)
- Two-tier approval workflow
- Version activation and superseding
- OnlyOffice integration

**DocumentAccessService**: Manages access control
- Access request processing
- Version-specific access tracking
- Access expiry management
- Watermarked PDF generation

**FormRequestService**: Manages printed form lifecycle
- Form request creation with version tracking
- QR code label generation
- Form numbering system
- Physical location tracking
- Bulk operations

**CleaningService**: Manages facility cleaning operations
- Daily and hourly task generation with time-based scheduling
- Due date calculation for 5 frequency types
- Random task flagging for quality control (10-20%)
- SLA tracking and overdue monitoring
- Asset lifecycle detection and alert creation
- Notification integration (WhatsApp/Pushover)
- Missed task marking and lock release

**ApiOptionsService**: External API integration
- HTTP client configuration
- Authentication handling (Bearer, Basic, API Key)
- Response caching with TTL
- Error handling and fallback
- Support for combined label templates

**HiddenFieldService**: Resolves hidden field values
- Dynamic value calculation
- System metadata injection
- User context data
- Timestamp and tracking information

## Security Considerations

### File Upload Security
- File type validation and sanitization
- Virus scanning integration ready
- Secure file storage with access controls
- Automatic image optimization and watermarking

### Data Protection
- Encrypted sensitive data storage
- Comprehensive audit logging
- Role-based access control
- Session security with CSRF protection

### Performance Optimization
- Redis caching for improved response times
- Background job processing for heavy operations
- Optimized database queries with eager loading
- CDN-ready asset compilation

## Production Deployment

### Server Configuration
1. Configure web server (Apache/Nginx) for Laravel
2. Set up SSL certificates for HTTPS
3. Configure file permissions (storage and bootstrap/cache directories)
4. Set up database with proper user permissions
5. Configure Redis for caching and queues
6. Set up mail server for notifications

### Monitoring
- Set up application monitoring (logs, performance)
- Monitor queue processing and failures
- Track approval workflow performance
- Monitor file storage usage
- Monitor scheduled task execution (maintenance work orders)

### Backup Strategy
- Database backups (daily recommended)
- File storage backups
- Configuration backups
- Disaster recovery procedures

### Scheduled Tasks & Artisan Commands

The system uses Laravel's task scheduler for automated operations:

#### Available Artisan Commands

**Maintenance Management:**
```bash
# Generate work orders from overdue maintenance schedules
php artisan maintenance:generate-work-orders
```

**Facility Management:**
```bash
# Generate cleaning tasks from schedules (daily/hourly)
php artisan cleaning:generate-tasks

# Send task reminders (X hours before scheduled time)
php artisan cleaning:send-reminders --hours=2
```

**Notification Testing:**
```bash
# Send test WhatsApp notification
php artisan whatsapp:test [chatId]

# Example: Test with specific chat ID
php artisan whatsapp:test 62811337678@c.us
```

**System Management:**
```bash
# Inspiration quote (built-in Laravel command)
php artisan inspire
```

#### Automatic Work Order Generation

Runs daily at midnight (Asia/Jakarta) when enabled:
- Checks overdue maintenance schedules
- Creates work orders automatically
- Prevents duplicate work orders
- Logs all operations
- Currently **disabled by default** for safety (see `routes/console.php`)

#### Automatic Cleaning Task Generation

Runs daily at midnight (Asia/Jakarta) when enabled:
- Generates cleaning tasks from active schedules
- Supports all frequency types (hourly/daily/weekly/monthly/yearly)
- Marks yesterday's uncompleted tasks as missed
- Flags random 10-20% of submissions for review
- Releases inactive locked tasks (>2 hours)
- Detects asset issues and creates alerts
- Currently **disabled by default** for safety (see `routes/console.php`)

#### Automatic Cleaning Reminders

Runs twice daily (8am, 2pm Asia/Jakarta) when enabled:
- Sends reminders for upcoming tasks (default: 2 hours before)
- WhatsApp notifications with Pushover fallback
- Only reminds assigned cleaners
- Prevents duplicate reminders
- Currently **disabled by default** (see `routes/console.php`)

**To enable automatic generation:**
1. Uncomment the schedule block in `routes/console.php`
2. Ensure this cron entry exists on your server:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

See [Maintenance Scheduling Guide](MAINTENANCE_SCHEDULING_GUIDE.md) for detailed information.

## Company Information

**PT. Surya Inti Aneka Pangan** is one of Indonesia's largest fish and prawn manufacturing companies, specializing in high-quality seafood products for both domestic and international markets.

## Documentation

### Complete Guide Library

All documentation is located in the `guides/` directory:

**Start Here:**
- **[User Guide](guides/USER_GUIDE.md)** - System overview and quick start guide for all users

**Module-Specific Guides:**
- **[Forms Guide](guides/FORMS_GUIDE.md)** - Complete form management guide for admins and users
- **[Workflows Guide](guides/WORKFLOWS_GUIDE.md)** - Approval workflow configuration and processing
- **[Manufacturing Guide](guides/MANUFACTURING_GUIDE.md)** - Warehouse and inventory management
- **[Maintenance Guide](guides/MAINTENANCE_GUIDE.md)** - CMMS operations and asset management
- **[Facility Cleaning Guide](guides/CLEANING_NOTIFICATIONS_GUIDE.md)** - Facility management and cleaning operations
- **[Document Management Guide](guides/DMS_GUIDE.md)** - Document Management System operations
- **[Notifications Guide](guides/NOTIFICATIONS_GUIDE.md)** - WhatsApp and Pushover notification system

**Administration:**
- **[Admin Guide](guides/ADMIN_GUIDE.md)** - User management, roles, and permissions

**Reference:**
- **[Common Tasks](guides/COMMON_TASKS.md)** - Quick reference and troubleshooting guide
- **[API Options Guide](guides/API_OPTIONS_GUIDE.md)** - Configure API-sourced dropdown fields
- **[Maintenance Scheduling Guide](MAINTENANCE_SCHEDULING_GUIDE.md)** - Automatic work order generation

### Quick Start

1. **For End Users**: Start with the [User Guide](guides/USER_GUIDE.md) overview, then read the module-specific guide for your role
2. **For Administrators**: Read the [User Guide](guides/USER_GUIDE.md) and [Admin Guide](guides/ADMIN_GUIDE.md)
3. **For Quick Help**: Check [Common Tasks](guides/COMMON_TASKS.md) for frequently performed actions
4. **For Developers**: See project structure above and review code documentation

## Development Guidelines

### Code Standards

**PHP Standards:**
- Follow PSR-12 coding standards
- Use strict typing: `declare(strict_types=1);`
- All classes should be final unless designed for inheritance
- Use explicit return type declarations
- Follow SOLID principles

**Laravel Best Practices:**
- Controllers should be final and read-only (no property mutations)
- Use dependency injection in methods, not constructors
- Keep controllers thin, use services for business logic
- Use Form Requests for validation
- Use Eloquent ORM and Query Builder over raw SQL
- Implement proper error handling and logging

**Frontend Standards:**
- Server-side validation is primary
- JavaScript only for UI enhancement
- Use Bootstrap 5 classes for styling
- Follow Tabler template conventions
- Minimize JavaScript dependencies

### File Organization

**Naming Conventions:**
- Models: Singular, PascalCase (e.g., `User.php`, `WorkOrder.php`)
- Controllers: Plural, PascalCase with Controller suffix (e.g., `UsersController.php`)
- Views: snake_case (e.g., `work_orders/show.blade.php`)
- Database columns: snake_case
- Methods: camelCase
- Constants: UPPER_SNAKE_CASE

**Directory Structure:**
- Models in `app/Models/`
- Controllers in `app/Http/Controllers/`
- Services in `app/Services/`
- Helpers in `app/Helpers/`
- Views in `resources/views/`
- Migrations in `database/migrations/`

### Testing

Run tests using Pest PHP:
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/FormTest.php

# Run with coverage
php artisan test --coverage
```

### Contributing

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes** following the code standards
4. **Write tests** for new functionality
5. **Run tests** to ensure nothing breaks
6. **Commit your changes**: `git commit -m "feat: add your feature"`
7. **Push to your fork**: `git push origin feature/your-feature-name`
8. **Create a Pull Request**

**Commit Message Format:**
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

## Support

For technical support or questions:
- Create an issue in the repository
- Contact the development team
- Review the comprehensive documentation in the `guides/` folder
- Check the troubleshooting sections in each guide

## Author

**Andri Halim Gunawan**

## Acknowledgments

- **PT. Surya Inti Aneka Pangan** for project sponsorship
- Laravel community for excellent documentation and packages
- Tabler.io for the beautiful admin template
- All contributors and testers

---

*Built with ❤️ using Laravel for PT. Surya Inti Aneka Pangan*
