# SIGaP - Sistem Informasi Gabungan Pelaporan

<p align="center">
  <img src="public/imgs/logo.png" alt="SIGaP Logo" width="200">
</p>

<p align="center">
  <strong>Enterprise-grade form management and approval workflow system for PT. Surya Inti Aneka Pangan</strong>
</p>

## About SIGaP

SIGaP (Sistem Informasi Gabungan Pelaporan) is a comprehensive enterprise business process automation platform designed to serve as the central system for all reports and document control at **PT. Surya Inti Aneka Pangan**, one of Indonesia's largest fish and prawn manufacturing companies.

This isn't just a form builder - it's a sophisticated workflow management system that rivals commercial enterprise solutions, providing dynamic form creation, multi-level approval workflows, department-based access control, and comprehensive audit trails.

## Core Features

### 📋 Advanced Form Management
- **Dynamic Form Builder**: Create custom forms with 10+ field types including:
  - Text, number, email, and date inputs
  - Single/multiple select with API integration
  - File uploads with image processing
  - Digital signature capture
  - Live photo capture with camera integration
  - Calculated fields with formula engine
  - Hidden fields with conditional logic
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
- **Role-based Access Control**: Granular permissions using Spatie Laravel Permission
- **Department Organization**: Hierarchical department structure with inheritance
- **User Impersonation**: Admin capability for user experience testing
- **Multi-factor Authentication**: Enhanced security with Laravel Fortify
- **External Authentication**: Asana integration for SSO
- **Permission Matrix**: Comprehensive role and permission management

### 📁 Advanced Document Management
- **File Processing**: Complete upload/download system with security controls
- **Image Optimization**: Automatic image compression and thumbnail generation
- **Digital Signatures**: Built-in signature capture and verification
- **Live Photo Capture**: Real-time camera integration for field documentation
- **File Watermarking**: Document security with custom watermarks
- **Print-ready Reports**: PDF-optimized submission views
- **Download Controls**: Secure file access with permission checks

### 📊 Business Intelligence & Analytics
- **Calculation Engine**: Built-in formula processor for complex calculations
- **Real-time Dashboards**: Form submission tracking and analytics
- **Workflow Performance**: Approval time analysis and bottleneck identification
- **Status Monitoring**: Live progress tracking for all submissions
- **Escalation Reports**: Overdue approval monitoring and alerting
- **Department Analytics**: Performance metrics by organizational unit

### 🔧 Advanced Technical Features
- **Queue Processing**: Background job processing for notifications and escalations
- **Email Notifications**: Automated workflow notifications with customizable templates
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
- **Shelf-Based Inventory**: Organize inventory by warehouse, shelf, and position
- **Item Management**: Comprehensive item catalog with categories and units
- **Bill of Materials (BoM)**: Recipe and ingredient management with approval workflows
- **Picklist Generation**: FIFO-based picking lists across warehouses
- **Expiry Tracking**: Monitor and manage items approaching expiration
- **Inventory Reports**: Overview reports with filtering and export capabilities
- **Bulk Operations**: Bulk inventory updates and movements
- **Excel Import**: Import items from Excel spreadsheets

### 🔧 Maintenance Management (CMMS)
- **Asset Management**: Track all equipment with categories, locations, and QR codes
- **Preventive Maintenance**: Schedule regular maintenance with multiple frequency types:
  - Hourly, Daily, Weekly, Monthly, Yearly schedules
  - Flexible configuration for complex patterns
- **Work Order Management**: Complete lifecycle from creation to verification
  - Status workflow: Submitted → Assigned → In Progress → Pending Verification → Verified → Completed
  - Time tracking and estimated hours
  - Parts consumption from inventory
  - Photo documentation with upload support
  - Progress logging and action tracking
  - Work order policies for authorization
- **Upcoming Maintenance Visibility**: 14-day forecast of scheduled maintenance
  - See upcoming schedules before they're overdue
  - Manual work order generation from upcoming schedules
  - Status indicators (Scheduled, WO Exists, Overdue)
- **Automatic Work Order Generation**: Auto-generate from overdue schedules (disabled by default)
- **Maintenance Dashboard**: Real-time overview
  - Total assets and active work orders
  - Overdue schedules alerts
  - Recent work order activity
  - Asset status distribution charts
- **Maintenance Calendar**: Visual calendar view of all scheduled maintenance
- **Maintenance Logs**: Complete history of all maintenance activities
- **Reports & Analytics**: Performance metrics and cost tracking
- **Integration**: Parts inventory integrated with manufacturing module

## Future Modules (Planned)

### 📄 Document Management System
- Centralized document storage with version control
- Document approval workflows with digital signatures
- Advanced categorization and tagging system
- Full-text search and retrieval capabilities
- Integration with existing form workflows

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

- **Backend**: Laravel 12.x with PHP 8.2+
- **Frontend**: Blade templates with Bootstrap 5 and Tabler Admin Template
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Authentication**: Laravel Fortify with enhanced UI components (via zacksmash/fortify-ui)
- **Permissions**: Spatie Laravel Permission 6.20+ for RBAC
- **Image Processing**: Intervention Image 3.11+ for file handling
- **Excel Processing**: Maatwebsite Excel 3.1+ for imports/exports
- **User Impersonation**: Lab404 Laravel Impersonate for admin support
- **Testing**: Pest PHP with comprehensive test coverage
- **Build Tools**: Vite for modern asset compilation and hot reloading
- **Queue System**: Redis/Database queues for background processing
- **Caching**: Redis/Memcached for performance optimization
- **Task Scheduling**: Laravel scheduler for automated maintenance tasks
- **External Auth**: Laravel Socialite with Asana provider support

## System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 2GB RAM (4GB+ recommended for production)
- **Storage**: 10GB+ available space (more for file uploads and backups)
- **Redis**: For caching and queue management (highly recommended)

### PHP Extensions
Required:
- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- GD or ImageMagick for image processing with Intervention Image
- Redis extension for caching (optional but highly recommended)
- Zip extension for Excel imports/exports

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
│   ├── ApprovalService.php    # Workflow processing
│   ├── CalculationService.php # Formula engine
│   ├── FormPrefillService.php # Auto-population
│   ├── MaintenanceService.php # Maintenance logic
│   └── ApiOptionsService.php  # API integration
├── Http/Controllers/    # Web controllers
│   ├── FormController.php     # Forms
│   ├── WorkOrderController.php # Maintenance
│   ├── WarehouseController.php # Inventory
│   └── ... (39 controllers)
├── Helpers/            # Utility classes
│   ├── AuthHelper.php        # Authentication helpers
│   └── FormPrefillHelper.php # Form auto-population
├── Enums/             # Application enums
│   ├── FrequencyType.php  # Maintenance schedule frequencies
│   └── Location.php       # Asset location options
├── Policies/          # Authorization policies
│   └── WorkOrderPolicy.php   # Work order authorization
└── Console/Commands/  # Artisan commands
    ├── GenerateMaintenanceWorkOrders.php
    └── (other commands...)

resources/views/
├── forms/              # Form management
├── formsubmissions/    # Submission views
├── approval-workflows/ # Workflow management
├── manufacturing/      # Inventory & warehouses
├── maintenance/        # CMMS module
└── layouts/           # Application layouts

database/
├── migrations/        # Database schema (50 migrations)
├── seeders/           # Database seeders
│   ├── UserSeeder.php
│   ├── AssetCategorySeeder.php
│   ├── MaintenanceTypeSeeder.php
│   ├── BomTypeSeeder.php
│   └── ... (8 seeders)
└── factories/         # Model factories for testing

config/                # Application configuration
├── fortify.php        # Authentication config
├── permission.php     # RBAC settings
├── options.php        # Spatie options
├── watermark.php      # Image watermark settings
└── ... (12 config files)

guides/                # User documentation (8 guides)
```

## Database Schema

### Core Models (33 models)

**Form Management:**
- `Form`, `FormVersion`, `FormField`, `FormFieldOption`
- `FormSubmission`, `FormAnswer`
- `ApprovalWorkflow`, `ApprovalFlowStep`, `ApprovalLog`

**Manufacturing & Inventory:**
- `Warehouse`, `WarehouseShelf`, `ShelfPosition`, `PositionItem`
- `Item`, `ItemCategory`
- `BomTemplate`, `BomIngredient`, `BomType`

**Maintenance (CMMS):**
- `Asset`, `AssetCategory`, `AssetDocument`
- `MaintenanceSchedule`, `MaintenanceType`, `MaintenanceLog`
- `WorkOrder`, `WorkOrderAction`, `WorkOrderPart`, `WorkOrderPhoto`, `WorkOrderProgressLog`

**User Management:**
- `User`, `Role`, `Permission`, `Department`

All models follow Laravel best practices:
- Final classes to prevent inheritance
- Explicit type declarations
- Eloquent relationships properly defined
- Mass assignment protection
- Proper timestamp handling

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

## Support

For technical support or questions:
- Create an issue in the repository
- Contact the development team
- Review the comprehensive documentation in the `guides/` folder

## Author

**Andri Halim Gunawan**

---

*Built with ❤️ using Laravel for PT. Surya Inti Aneka Pangan*
