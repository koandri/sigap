# SIGaP User Guide

**Sistem Informasi Gabungan Pelaporan**  
**Version 1.0 - Overview & Quick Start**

---

## Welcome to SIGaP

SIGaP (Sistem Informasi Gabungan Pelaporan) is an enterprise business process automation platform designed for **PT. Surya Inti Aneka Pangan**. This comprehensive system serves as the central hub for:

- **Form Management & Submissions** - Dynamic forms with approval workflows
- **Manufacturing & Inventory** - Warehouse and inventory management
- **Maintenance Management (CMMS)** - Asset tracking and preventive maintenance

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

1. Navigate to your SIGaP URL in your web browser
2. Enter your **username** (email address)
3. Enter your **password**
4. Click **"Login"**

**First Time Login:**
- You'll receive initial credentials from your administrator
- Change your password on first login
- Update your profile information

### Dashboard Overview

After logging in, the dashboard shows:

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
- **Users & Roles** - User management (admin only)
- **Departments** - Organization structure (admin only)

---

## System Modules

SIGaP consists of four main modules. Click on the guide links below for detailed information:

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

Comprehensive warehouse and inventory management system.

**Key Features:**
- Multi-warehouse management
- Shelf-based organization
- Bill of Materials (BoM)
- FIFO picklist generation
- Expiry tracking
- Excel import/export

**Learn More:**
- **[Manufacturing Guide](./MANUFACTURING_GUIDE.md)** - Complete manufacturing and inventory guide

**Quick Start:**
- **Warehouse Staff**: [Manage Inventory](./MANUFACTURING_GUIDE.md#shelf-based-inventory)
- **Planners**: [Create BoMs](./MANUFACTURING_GUIDE.md#bill-of-materials-bom)
- **Managers**: [View Reports](./MANUFACTURING_GUIDE.md#reports-and-analytics)

---

### 🔧 Maintenance Management (CMMS)

Computerized Maintenance Management System for asset tracking.

**Key Features:**
- Asset management with QR codes and custom locations
- Flexible maintenance scheduling (hourly, daily, weekly, monthly, yearly)
- Complete work order lifecycle with status tracking
- 14-day upcoming maintenance forecast
- Automatic work order generation (optional, disabled by default)
- Manual work order creation from upcoming schedules
- Parts inventory integration with manufacturing module
- Comprehensive asset reports (by location, category, department, user)
- Maintenance calendar with visual scheduling
- Work order reports with performance metrics

**Learn More:**
- **[Maintenance Guide](./MAINTENANCE_GUIDE.md)** - Complete CMMS guide
- **[Maintenance Scheduling Guide](./MAINTENANCE_SCHEDULING_GUIDE.md)** - Automatic work order generation

**Quick Start:**
- **Technicians**: [Complete Work Orders](./MAINTENANCE_GUIDE.md#for-technicians-completing-work)
- **Supervisors**: [Verify Work](./MAINTENANCE_GUIDE.md#for-supervisors-verifying-work)
- **Admins**: [Create Schedules](./MAINTENANCE_GUIDE.md#creating-a-schedule)

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

**Version**: 1.0  
**Last Updated**: October 17, 2025  
**System Version**: SIGaP Laravel 12.x (PHP 8.2+)  
**Platform**: Web-based application  
**Database**: MySQL 8.0+ / PostgreSQL 13+

---

**Thank you for using SIGaP!**

*Built with ❤️ for PT. Surya Inti Aneka Pangan*
