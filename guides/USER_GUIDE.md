# SIGaP User Guide

**Sistem Informasi Gabungan Pelaporan**  
**Version 1.0 - Overview & Quick Start**

---

## Welcome to SIGaP

SIGaP (Sistem Informasi Gabungan Pelaporan) is an enterprise business process automation platform designed for **PT. Surya Inti Aneka Pangan**. This comprehensive system serves as the central hub for:

- **Form Management & Submissions** - Dynamic forms with approval workflows
- **Manufacturing & Inventory** - Warehouse and inventory management
- **Maintenance Management (CMMS)** - Asset tracking and preventive maintenance
- **Facility Management** - Cleaning schedules and facility operations
- **Document Management System (DMS)** - Document lifecycle management

---

## Who is This Guide For?

This guide provides an overview of SIGaP and directs you to detailed documentation for each module.

**User Types:**
- **Operators** - Submit forms, manage inventory, perform maintenance
- **Supervisors** - Review and approve submissions, oversee operations
- **Managers** - Generate reports, manage teams, oversee processes
- **Administrators** - Configure system, manage users and permissions

---

## Getting Started

### Accessing the System

You can log in to SIGaP using two methods:

#### Method 1: Password Login

1. Navigate to your SIGaP URL in your web browser
2. Enter your **username** (email address)
3. Enter your **password**
4. Click **"Login"**

![Login Page](/guides-imgs/user-guide-login.png)

**First Time Login:**
- You'll receive initial credentials from your administrator
- Change your password on first login
- Update your profile information

#### Method 2: Single Sign-On (SSO) with Keycloak

1. Navigate to your SIGaP URL
2. Click **"Login with SSO"** button
3. You'll be redirected to Keycloak login page
4. Enter your **Keycloak credentials**
5. Click **"Sign In"**
6. You'll be automatically logged in to SIGaP

**SSO Benefits:**
- **One-click login** - No need to remember separate SIGaP password
- **Automatic account creation** - First-time SSO users get accounts created automatically
- **Secure authentication** - Uses enterprise-grade Keycloak security
- **Seamless experience** - Same credentials across multiple systems

**SSO Notes:**
- Your account must be active in SIGaP to log in
- If this is your first SSO login, an account will be created automatically
- You can still use password login if SSO is unavailable
- Contact IT if you have issues with SSO login

### Dashboard Overview

After logging in, the dashboard shows:

![Dashboard Overview](/guides-imgs/user-guide-dashboard.png)

**Quick Stats:**
- Recent form submissions
- Pending approvals (if you're an approver)
- Upcoming maintenance schedules
- Inventory alerts

**Navigation Menu:**
- **Home** - Main dashboard
- **Forms** - Form management (admin only)
- **Form Submissions** - Submit and view forms
- **Manufacturing** - Inventory management
- **Maintenance** - CMMS module
- **Facility Management** - Cleaning schedules and operations
- **DMS** - Document Management System
- **Reports** - Asset, facility, and document reports
- **Options** - Assets, locations, categories
- **Users & Roles** - User management (admin only)
- **Departments** - Organization structure (admin only)

---

## System Modules

SIGaP consists of six main modules. Click on the guide links below for detailed information:

### 📋 Form Management

Create and manage dynamic forms with approval workflows.

**Key Features:**
- 16 field types (text, number, date, file, signature, live photo, calculated, hidden, etc.)
- API-integrated dropdowns with caching
- Calculated fields with formula engine
- Hidden fields for system metadata
- Form versioning with activation control
- Department-based access control

**Learn More:**
- **[Forms Guide](./FORMS_GUIDE.md)** - Complete form management guide
- **[API Options Guide](./API_OPTIONS_GUIDE.md)** - API-sourced dropdown configuration

**Quick Start:**
- **Users**: [Submit a Form](./FORMS_GUIDE.md#for-users-submitting-forms)
- **Admins**: [Create a Form](./FORMS_GUIDE.md#for-administrators-creating-forms)

---

### ✅ Approval Workflows

Multi-level approval processes with SLA management.

**Key Features:**
- Sequential and parallel workflows
- Role-based approver assignment
- SLA tracking with auto-escalation
- Complete audit trails
- Email notifications

**Learn More:**
- **[Workflows Guide](./WORKFLOWS_GUIDE.md)** - Complete approval workflow guide

**Quick Start:**
- **Approvers**: [Process Approvals](./WORKFLOWS_GUIDE.md#for-approvers-processing-approvals)
- **Admins**: [Create Workflows](./WORKFLOWS_GUIDE.md#for-administrators-creating-workflows)

---

### 🏭 Manufacturing & Inventory

Comprehensive warehouse and inventory management system with production planning capabilities.

**Key Features:**
- Multi-warehouse management
- Shelf-based organization
- Recipe management (replaces BoM)
- Production Planning System (5-step planning workflow)
- FIFO picklist generation
- Expiry tracking
- Excel import/export

**Production Planning:**
- **5-Step Planning Workflow**: Dough → Gelondongan → Kerupuk Kering → Packing → Packing Materials
- Multi-site quantity tracking (GL1, GL2, TA, BL)
- Recipe integration with ingredient tracking
- Yield guideline management
- Auto-calculation between steps
- Status workflow: Draft → Approved → In Production → Completed

**Learn More:**
- **[Manufacturing Guide](./MANUFACTURING_GUIDE.md)** - Complete manufacturing and inventory guide

**Quick Start:**
- **Warehouse Staff**: [Manage Inventory](./MANUFACTURING_GUIDE.md#shelf-based-inventory)
- **Production Planners**: Create production plans → Plan Step 1 (Dough) → Step 2 (Gelondongan) → Step 3 (Kerupuk Kering) → Step 4 (Packing) → Step 5 (Materials) → Approve plan
- **Managers**: [View Reports](./MANUFACTURING_GUIDE.md#reports-and-analytics)

---

### 🔧 Maintenance Management (CMMS)

Computerized Maintenance Management System for asset tracking.

**Key Features:**
- Asset management with QR codes and custom locations
- **Mobile Asset Creation** with camera integration and AI-powered analysis
  - Capture multiple photos directly from mobile device camera
  - AI image analysis to automatically extract asset information (name, category, manufacturer, model, serial number)
  - GPS location tracking with automatic geotagging
  - AI-powered specification fetching from web sources
- **Multiple Photo Management** for comprehensive asset documentation
  - Upload and manage up to 10 photos per asset
  - Set primary photo for quick identification
  - Photo gallery with thumbnail previews
  - Automatic EXIF data extraction (capture time, GPS coordinates)
- Flexible maintenance scheduling (hourly, daily, weekly, monthly, yearly)
- Complete work order lifecycle with status tracking
- 14-day upcoming maintenance forecast
- Automatic work order generation (optional, disabled by default)
- Manual work order creation from upcoming schedules
- Parts inventory integration with manufacturing module
- Comprehensive asset reports (by location, category, department, user)
- Maintenance calendar with visual scheduling
- Work order reports with performance metrics

![Asset Index Page](/guides-imgs/asset-index.png)

![Mobile Asset Creation](/guides-imgs/asset-create-mobile.png)

![Asset Detail with Photos](/guides-imgs/asset-detail-photos.png)

**Learn More:**
- **[Maintenance Guide](./MAINTENANCE_GUIDE.md)** - Complete CMMS guide
- **[Maintenance Scheduling Guide](./MAINTENANCE_SCHEDULING_GUIDE.md)** - Automatic work order generation

**Quick Start:**
- **Technicians**: [Complete Work Orders](./MAINTENANCE_GUIDE.md#for-technicians-completing-work)
- **Supervisors**: [Verify Work](./MAINTENANCE_GUIDE.md#for-supervisors-verifying-work)
- **Admins**: [Create Assets](./MAINTENANCE_GUIDE.md#creating-an-asset) - Use mobile creation for quick asset registration with AI assistance

---

### 🧹 Facility Management

Comprehensive facility cleaning and operations management system.

**Key Features:**
- Dynamic cleaning schedules with 5 frequency types (hourly, daily, weekly, monthly, yearly)
- Mobile-first cleaner workflow with GPS photo watermarking
- Smart approval system with random quality sampling (10-20%)
- SLA tracking with real-time overdue monitoring
- Guest request system (public form for cleaning/repair requests)
- Daily and weekly reports with PDF export
- Asset lifecycle management with automatic alerts
- Dashboard with cleaner performance ranking and statistics
- Multi-channel notifications (WhatsApp/Pushover)
- Optional automation (task generation, reminders)

**Learn More:**
- **[Cleaning Notifications Guide](./CLEANING_NOTIFICATIONS_GUIDE.md)** - Complete facility management guide

**Quick Start:**
- **Cleaners**: View assigned tasks → Start task → Submit before/after photos → Automatic watermarking
- **GA Staff**: Review submissions → Approve/reject → Mass approve (after reviewing flagged tasks)
- **Admins**: Create schedules → Configure frequency → Assign cleaners → Generate reports
- **Guests**: Submit requests via public form → Staff creates tasks or work orders

**Roles:**
- **Cleaner** - Can view and complete assigned cleaning tasks
- **General Affairs (GA)** - Can manage schedules, review submissions, approve tasks, handle requests
- **Super Admin/Owner** - Full access to all facility features

---

### 📄 Document Management System (DMS)

Enterprise document lifecycle management with version control and access management.

**Key Features:**
- Document version control with two-tier approval (Manager → Management)
- Access request system with watermarked PDF downloads
- Printed form lifecycle management with QR code tracking
- Document instances for memos and letters (template-based)
- OnlyOffice integration for collaborative editing (DOCX/XLSX)
- Physical location tracking (room, shelf, folder)
- Comprehensive reports (masterlist, location, SLA dashboard)

**Learn More:**
- **[DMS Guide](./DMS_GUIDE.md)** - Complete Document Management System guide

**Quick Start:**
- **Users**: Request document access → View/download watermarked documents
- **Document Creators**: Create document → Create version → Submit for approval
- **Approvers**: Review pending approvals → Approve/reject versions
- **Document Control**: Process form requests → Generate QR labels → Track printed forms

---

## Administration

### User & Permission Management

Manage users, roles, and permissions across the system.

**Key Features:**
- Role-based access control (RBAC)
- User impersonation for support
- Department-based organization
- Granular permissions
- User activity tracking

**Learn More:**
- **[Admin Guide](./ADMIN_GUIDE.md)** - Complete administration guide

**Quick Start:**
- [Create Users](./ADMIN_GUIDE.md#creating-users)
- [Manage Roles](./ADMIN_GUIDE.md#roles-and-permissions)
- [Manage Departments](./ADMIN_GUIDE.md#department-management)

---

## Common Tasks

### Frequently Performed Tasks

Need help with day-to-day operations? Check our quick reference:

**[Common Tasks & Troubleshooting Guide](./COMMON_TASKS.md)**

**Includes:**
- Changing your password
- Updating your profile
- Searching and filtering
- Exporting data and printing
- Uploading files and photos
- Common troubleshooting steps
- Keyboard shortcuts
- Getting help

---

## Documentation Index

### Complete Guide Library

| Guide | Description | For |
|-------|-------------|-----|
| **[User Guide](./USER_GUIDE.md)** | Overview (this document) | Everyone |
| **[Forms Guide](./FORMS_GUIDE.md)** | Form creation and submission | Users & Admins |
| **[Workflows Guide](./WORKFLOWS_GUIDE.md)** | Approval processes | Approvers & Admins |
| **[Manufacturing Guide](./MANUFACTURING_GUIDE.md)** | Inventory management | Warehouse Staff & Managers |
| **[Maintenance Guide](./MAINTENANCE_GUIDE.md)** | CMMS operations | Technicians & Supervisors |
| **[Cleaning Notifications Guide](./CLEANING_NOTIFICATIONS_GUIDE.md)** | Facility management | Cleaners & GA Staff |
| **[DMS Guide](./DMS_GUIDE.md)** | Document Management System | Document Control & Users |
| **[Admin Guide](./ADMIN_GUIDE.md)** | System administration | Administrators |
| **[Common Tasks](./COMMON_TASKS.md)** | Quick reference | Everyone |
| **[API Options Guide](./API_OPTIONS_GUIDE.md)** | API integration | Admins (Technical) |
| **[Maintenance Scheduling Guide](./MAINTENANCE_SCHEDULING_GUIDE.md)** | Automatic work order generation | Maintenance Managers |

---

## Quick Tips

### Navigation Tips

**Finding Features:**
- Use the left sidebar menu
- Dashboard shows recent items
- Use global search (top right)
- Breadcrumbs show current location

**Module Shortcuts:**
- Dashboard widgets link to modules
- Notification badges show pending items
- Recent items show frequently used pages

### Data Entry Tips

**Forms and Fields:**
- Required fields have red asterisk (*)
- Help text appears below fields
- Validation shows in real-time
- Save drafts frequently

**Efficiency:**
- Use Tab key to move between fields
- Autocomplete helps with repeated data
- Templates save time
- Keyboard shortcuts available

### Best Practices

**For All Users:**
- Keep your profile updated
- Use descriptive names
- Add notes and comments
- Review before submitting
- Check notifications regularly

**For Data Entry:**
- Verify data accuracy
- Use consistent formatting
- Double-check calculations
- Attach relevant files
- Document your work

**For Approvers:**
- Review thoroughly
- Provide clear feedback
- Respond within SLA
- Document decisions
- Communicate with submitters

---

## Getting Help

### Support Resources

**Documentation:**
1. Start with this User Guide overview
2. Read the module-specific guide
3. Check Common Tasks for quick help
4. Review troubleshooting sections

**In-System Help:**
- Field help text (below inputs)
- Tooltips (hover over icons)
- Contextual help buttons

### Contact Support

**System Administrator:**
- Contact your IT department
- Submit support ticket
- Email administrator
- Check internal resources

**When Reporting Issues:**
Include:
- What you were doing
- What happened (error messages)
- Screenshots if helpful
- Your username
- Browser and version
- Date and time

### Training

**Available Training:**
- New user orientation
- Module-specific training
- Role-based workshops
- Administrator certification

**Request Training:**
- Contact your supervisor
- Check training schedule
- Sign up for sessions

---

## System Information

### Technology

**Browser Requirements:**
- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari (Mac)

**Recommended:**
- Keep browser updated
- Enable cookies
- Allow JavaScript
- Enable pop-ups for SIGaP

### Security

**Account Security:**
- Change password regularly
- Never share credentials
- Log out when finished
- Report suspicious activity

**Data Security:**
- All actions are logged
- Audit trails maintained
- Access is role-based
- Data is backed up regularly

### Company Information

**PT. Surya Inti Aneka Pangan** is one of Indonesia's largest fish and prawn manufacturing companies, specializing in high-quality seafood products for both domestic and international markets.

SIGaP was built specifically to support our operations and streamline our business processes.

---

## Next Steps

### New Users

1. ✅ Log in to SIGaP
2. ✅ Update your profile
3. ✅ Change your password
4. ✅ Explore the dashboard
5. ✅ Read relevant module guides
6. ✅ Try basic tasks
7. ✅ Ask questions if needed

### Administrators

1. ✅ Review all documentation
2. ✅ Read [Admin Guide](./ADMIN_GUIDE.md)
3. ✅ Configure system settings
4. ✅ Create users and roles
5. ✅ Set up departments
6. ✅ Train end users
7. ✅ Monitor system usage

### Module-Specific Users

**Forms Users:**
- Read [Forms Guide](./FORMS_GUIDE.md)
- Practice submitting forms
- Learn approval process

**Inventory Staff:**
- Read [Manufacturing Guide](./MANUFACTURING_GUIDE.md)
- Understand warehouse organization
- Learn inventory procedures

**Maintenance Staff:**
- Read [Maintenance Guide](./MAINTENANCE_GUIDE.md)
- Understand work order flow
- Learn scheduling system

**Facility Staff:**
- Read [Cleaning Notifications Guide](./CLEANING_NOTIFICATIONS_GUIDE.md)
- Understand cleaner workflow
- Learn approval and reporting system

**Document Management Staff:**
- Read [DMS Guide](./DMS_GUIDE.md)
- Understand document version control
- Learn access request and form request workflows

---

## Feedback & Suggestions

We continuously improve SIGaP based on user feedback.

**Share Your Ideas:**
- Suggest new features
- Report usability issues
- Recommend improvements
- Share success stories

Contact your supervisor or IT department with feedback.

---

## Document Information

**Version**: 1.1  
**Last Updated**: October 18, 2025  
**System Version**: SIGaP Laravel 12.x (PHP 8.2+)  
**Platform**: Web-based application  
**Database**: MySQL 8.0+ / PostgreSQL 13+

---

**Thank you for using SIGaP!**

*Built with ❤️ for PT. Surya Inti Aneka Pangan*
