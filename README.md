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
- **API Endpoints**: RESTful APIs for external system integration
- **Caching System**: Optimized performance with Redis/Memcached support
- **Background Processing**: Asynchronous handling of heavy operations
- **Error Handling**: Comprehensive logging and error tracking

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
  - Photo documentation
  - Progress logging
- **Automatic Work Order Generation**: Auto-generate from overdue schedules
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

- **Backend**: Laravel 12.x with PHP 8.3+
- **Frontend**: Blade templates with Bootstrap 5 and Tabler Admin Template
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Authentication**: Laravel Fortify with enhanced UI components
- **Permissions**: Spatie Laravel Permission for RBAC
- **Image Processing**: Intervention Image 3.11 for file handling
- **Excel Processing**: Maatwebsite Excel 3.1 for imports/exports
- **Testing**: Pest PHP with comprehensive test coverage
- **Build Tools**: Vite for modern asset compilation and hot reloading
- **Queue System**: Redis/Database queues for background processing
- **Caching**: Redis/Memcached for performance optimization
- **Task Scheduling**: Laravel scheduler for automated maintenance tasks

## System Requirements

### Server Requirements
- **PHP**: 8.3 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 2GB RAM (4GB+ recommended)
- **Storage**: 10GB+ available space
- **Redis**: For caching and queue management (recommended)

### PHP Extensions
- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- GD or ImageMagick for image processing
- Redis extension for caching (optional but recommended)

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
    'label_field' => 'name'
]
```

### Calculation Engine
Use built-in formulas in calculated fields:

```javascript
// Example calculations
SUM(field1, field2, field3)
MULTIPLY(quantity, price)
IF(field1 > 100, field1 * 0.1, 0)
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
├── Enums/             # Application enums
│   ├── FrequencyType.php  # Maintenance frequencies
│   └── Location.php       # Asset locations
├── Policies/          # Authorization policies
└── Console/Commands/  # Artisan commands

resources/views/
├── forms/              # Form management
├── formsubmissions/    # Submission views
├── approval-workflows/ # Workflow management
├── manufacturing/      # Inventory & warehouses
├── maintenance/        # CMMS module
└── layouts/           # Application layouts

database/migrations/    # Database schema (50 migrations)
config/                # Application configuration
guides/                # User documentation
```

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

### Scheduled Tasks

The system uses Laravel's task scheduler for automated operations:

1. **Automatic Work Order Generation**: Runs daily at midnight (Asia/Jakarta)
   - Checks overdue maintenance schedules
   - Creates work orders automatically
   - Currently disabled by default (see `routes/console.php`)

To enable scheduled tasks, ensure this cron entry exists:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

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
- **[Enhanced Scheduling Guide](guides/ENHANCED_SCHEDULING_GUIDE.md)** - Detailed maintenance scheduling

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
