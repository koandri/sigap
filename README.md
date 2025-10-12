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
- **Authentication**: Laravel Fortify with enhanced UI components
- **Permissions**: Spatie Laravel Permission for RBAC
- **Image Processing**: Intervention Image 3.11 for file handling
- **Testing**: Pest PHP with comprehensive test coverage
- **Build Tools**: Vite for modern asset compilation and hot reloading
- **Queue System**: Redis/Database queues for background processing
- **Caching**: Redis/Memcached for performance optimization

## System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
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
│   └── ApprovalLog.php     # Audit trails
├── Services/            # Business logic services
│   ├── ApprovalService.php # Workflow processing
│   ├── CalculationService.php # Formula engine
│   └── FormPrefillService.php # Auto-population
├── Http/Controllers/    # Web controllers
├── Helpers/            # Utility classes
└── Enums/             # Application enums

resources/views/
├── forms/              # Form management
├── formsubmissions/    # Submission views
├── approval-workflows/ # Workflow management
└── layouts/           # Application layouts

database/migrations/    # Database schema
config/                # Application configuration
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

### Backup Strategy
- Database backups (daily recommended)
- File storage backups
- Configuration backups
- Disaster recovery procedures

## Company Information

**PT. Surya Inti Aneka Pangan** is one of Indonesia's largest fish and prawn manufacturing companies, specializing in high-quality seafood products for both domestic and international markets.

## Support

For technical support or questions:
- Create an issue in the repository
- Contact the development team
- Review the documentation and API guides

## Author

**Andri Halim Gunawan**

---

*Built with ❤️ using Laravel for PT. Surya Inti Aneka Pangan*
