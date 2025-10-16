# SIGaP User Guide
**Sistem Informasi Gabungan Pelaporan**

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Authentication & Login](#authentication--login)
3. [Dashboard Overview](#dashboard-overview)
4. [Form Management](#form-management)
5. [Form Submissions](#form-submissions)
6. [Approval Workflows](#approval-workflows)
7. [Manufacturing Module](#manufacturing-module)
8. [User Management](#user-management)
9. [File Management](#file-management)
10. [Troubleshooting](#troubleshooting)
11. [Frequently Asked Questions](#frequently-asked-questions)

---

## Getting Started

### What is SIGaP?

SIGaP (Sistem Informasi Gabungan Pelaporan) is an enterprise-grade form management and approval workflow system designed for **PT. Surya Inti Aneka Pangan**. It serves as the central platform for all reports, document control, and business process automation.

### Key Features

- **Dynamic Form Builder** - Create custom forms with various field types
- **Multi-level Approval Workflows** - Sophisticated approval chains
- **Manufacturing Management** - Inventory, warehouse, and BOM management
- **Role-based Access Control** - Secure permissions system
- **File Management** - Upload, preview, and manage documents
- **Real-time Notifications** - Email alerts and status updates

### System Requirements

- **Web Browser**: Chrome, Firefox, Safari, or Edge (latest versions)
- **Internet Connection**: Required for full functionality
- **Screen Resolution**: 1024x768 or higher recommended

---

## Authentication & Login

### Logging In

1. **Navigate to the login page**
2. **Enter your credentials**:
   - Email address
   - Password
3. **Click "Sign In"**

### External Authentication (Asana SSO)

If your organization uses Asana for authentication:

1. Click **"Sign in with Asana"**
2. You'll be redirected to Asana's login page
3. Enter your Asana credentials
4. You'll be automatically redirected back to SIGaP

### Password Reset

1. Click **"Forgot your password?"** on the login page
2. Enter your email address
3. Check your email for reset instructions
4. Follow the link to create a new password

### Two-Factor Authentication

For enhanced security, enable two-factor authentication:

1. Go to **Profile** → **Edit Profile**
2. Navigate to **Two-Factor Authentication**
3. Follow the setup instructions
4. Use your authenticator app to scan the QR code

---

## Dashboard Overview

### Home Dashboard

The home dashboard provides:
- **Welcome message** with your name
- **Quick access** to recent forms and submissions
- **System status** and notifications
- **Navigation menu** to all modules

### Navigation Menu

The left sidebar contains:

- **🏠 Home** - Main dashboard
- **📋 Forms** - Form management and submissions
- **🏭 Manufacturing** - Inventory and production management
- **👑 Admin** - User and system administration
- **🚪 Logout** - Sign out of the system

---

## Form Management

### Creating Forms

**Note**: Only Super Admin and IT Staff can create forms.

1. Navigate to **Forms** → **Form Templates**
2. Click **"Create New Form"**
3. Fill in form details:
   - **Form Name**: Descriptive title
   - **Description**: Purpose and instructions
   - **Department Access**: Select which departments can access
4. Click **"Create Form"**

### Form Versions

Forms use versioning for better control:

1. **Create Version**: Add a new version of your form
2. **Activate Version**: Set which version is currently active
3. **Version History**: View all previous versions

### Adding Form Fields

1. Go to your form → **Versions** → **Select Version**
2. Click **"Add Field"**
3. Choose field type:
   - **Text Input** - Single line text
   - **Textarea** - Multi-line text
   - **Number** - Numeric input
   - **Email** - Email validation
   - **Date** - Date picker
   - **Select** - Dropdown menu
   - **Checkbox** - Multiple selections
   - **File Upload** - Document uploads
   - **Signature** - Digital signature
   - **Live Photo** - Camera capture
   - **Calculated Field** - Auto-calculated values

4. Configure field properties:
   - **Field Label**: Display name
   - **Required**: Make field mandatory
   - **Validation**: Set input rules
   - **Options**: For select/checkbox fields
   - **API Integration**: For dynamic options

### Field Types Explained

#### Text Fields
- **Text Input**: Single line text entry
- **Textarea**: Multi-line text with character limits
- **Number**: Numeric input with validation
- **Email**: Email format validation

#### Selection Fields
- **Select**: Single choice dropdown
- **Checkbox**: Multiple choice selection
- **Radio**: Single choice from options

#### File Fields
- **File Upload**: Document and image uploads
- **Signature**: Digital signature capture
- **Live Photo**: Real-time camera capture

#### Special Fields
- **Calculated Field**: Auto-calculated using formulas
- **Hidden Field**: Not visible to users
- **Date Field**: Date picker with validation

### API Integration

Connect form fields to external data sources:

1. Select field type that supports API integration
2. Configure API settings:
   - **URL**: API endpoint
   - **Method**: GET/POST
   - **Headers**: Authentication and content type
   - **Value Field**: Field containing the value
   - **Label Field**: Field containing the display text
3. Test the connection
4. Save configuration

### Approval Workflows

Set up approval processes for form submissions:

1. Go to **Forms** → **Form Templates** → **Select Form**
2. Click **"Approval Workflows"**
3. Click **"Create Workflow"**
4. Configure workflow:
   - **Workflow Name**: Descriptive title
   - **Steps**: Add approval steps
   - **Assignees**: Set who approves at each step
   - **Conditions**: Set routing rules
   - **SLA**: Set time limits
5. **Activate** the workflow

---

## Form Submissions

### Filling Out Forms

1. Navigate to **Forms** → **Fill Form**
2. Select the form you want to fill
3. Complete all required fields
4. Upload any necessary files
5. Add digital signature if required
6. Click **"Submit Form"**

### Form Submission Status

Your submissions can have these statuses:
- **Draft** - Not yet submitted
- **Submitted** - Awaiting approval
- **In Progress** - Under review
- **Approved** - Approved by all required approvers
- **Rejected** - Rejected by an approver
- **Completed** - Final approval received

### Viewing Submissions

1. Go to **Forms** → **Form Submissions**
2. View all your submissions
3. Click on any submission to see details
4. Use filters to find specific submissions

### Pending Approvals

If you have approval authority:

1. Navigate to **Forms** → **Pending Approvals**
2. Review submission details
3. Add comments if needed
4. Click **"Approve"** or **"Reject"**
5. Provide reason for rejection if applicable

### Approval History

Track the approval process:

1. Open any submission
2. Click **"Approval History"**
3. View all approval steps and comments
4. See who approved/rejected and when

---

## Approval Workflows

### Understanding Workflows

Approval workflows define who must approve form submissions and in what order.

### Workflow Types

#### Sequential Workflows
- Approvals happen one after another
- Each approver must complete their step before the next
- Most common type

#### Parallel Workflows
- Multiple approvers can review simultaneously
- Faster processing for non-dependent approvals
- All approvers must complete their review

### Creating Workflows

1. **Access Workflow Builder**
   - Go to Forms → Form Templates
   - Select your form
   - Click "Approval Workflows"

2. **Add Workflow Steps**
   - Click "Add Step"
   - Set step name and description
   - Assign approvers (by role, department, or specific users)
   - Set time limits (SLA)

3. **Configure Routing**
   - Set conditions for different paths
   - Define escalation rules
   - Configure notifications

4. **Test Workflow**
   - Use the test feature to validate
   - Check all routing scenarios
   - Verify notifications

### Workflow Management

#### Activating Workflows
- Only one workflow can be active per form
- Deactivate old workflows before activating new ones

#### Modifying Workflows
- Changes affect new submissions only
- Existing submissions continue with original workflow

#### Escalation Rules
- Set automatic escalation for overdue approvals
- Escalate to supervisors or managers
- Send additional notifications

---

## Manufacturing Module

### Manufacturing Dashboard

Access the manufacturing overview:
1. Navigate to **Manufacturing** → **Dashboard**
2. View key metrics and recent activities
3. Quick access to inventory and production tools

### Item Management

#### Item Categories
1. Go to **Manufacturing** → **Item Categories**
2. **Create Category**:
   - Category name
   - Description
   - Parent category (for hierarchy)
3. **Edit/Delete** existing categories

#### Items
1. Navigate to **Manufacturing** → **Items**
2. **View Items**: Browse all items with filters
3. **Import Items**: Bulk import from Excel
4. **Item Details**: View specifications and inventory

### Warehouse Management

#### Warehouse Setup
1. Go to **Manufacturing** → **Warehouses**
2. **Create Warehouse**:
   - Warehouse name and code
   - Location details
   - Capacity information
3. **Configure Shelves**: Set up storage locations

#### Shelf Management
1. **Create Shelves**:
   - Shelf name and identifier
   - Aisle and position
   - Capacity limits
2. **Position Management**:
   - Define specific storage positions
   - Set item restrictions
   - Configure access levels

#### Inventory Management
1. **Shelf Inventory**:
   - View items by shelf and position
   - Add items to specific positions
   - Move items between positions
   - Update quantities

2. **Bulk Operations**:
   - Bulk update multiple items
   - Export inventory reports
   - Generate pick lists

### Bill of Materials (BOM)

#### Creating BOM Templates
1. Navigate to **Manufacturing** → **Bill of Materials**
2. **Create Template**:
   - Product name and description
   - Add ingredients and quantities
   - Set production ratios
3. **Submit for Approval**: Send to approval workflow

#### BOM Management
- **View Templates**: Browse all BOM templates
- **Copy Templates**: Duplicate existing BOMs
- **Approval Process**: Review and approve BOMs
- **Version Control**: Track BOM changes

### Pick Lists

Generate pick lists for production:
1. Go to **Manufacturing** → **Pick Lists**
2. **Select Items**: Choose items to include
3. **Generate List**: Create organized pick list
4. **Print/Export**: Get physical or digital copy

### Warehouse Overview Reports

View comprehensive warehouse status:
1. Navigate to **Manufacturing** → **Overview Report**
2. **Select Warehouse**: Choose warehouse to analyze
3. **Generate Report**: Create detailed inventory report
4. **Print Report**: Get printable version

---

## User Management

### User Administration

**Note**: Only Super Admin and IT Staff can manage users.

#### Creating Users
1. Go to **Admin** → **Users**
2. Click **"Create User"**
3. Fill in user details:
   - Name and email
   - Department assignment
   - Role assignment
   - Password (or send invitation)
4. Click **"Create User"**

#### Managing Users
- **Edit User**: Update user information
- **Reset Password**: Send password reset
- **Deactivate User**: Disable user account
- **Impersonate User**: Test user experience (admin only)

### Role Management

#### Creating Roles
1. Navigate to **Admin** → **Roles**
2. Click **"Create Role"**
3. Set role details:
   - Role name
   - Description
   - Permissions assignment
4. Save role

#### Permission Management
1. Go to **Admin** → **Permissions**
2. **View Permissions**: See all available permissions
3. **Create Permission**: Add new permission
4. **Assign to Roles**: Grant permissions to roles

### Department Management

#### Creating Departments
1. Navigate to **Admin** → **Departments**
2. Click **"Create Department"**
3. Set department details:
   - Department name
   - Description
   - Parent department (for hierarchy)
   - Manager assignment
4. Save department

---

## File Management

### Uploading Files

When filling forms with file fields:
1. Click **"Choose File"** or **"Browse"**
2. Select files from your computer
3. Wait for upload to complete
4. Preview files if needed
5. Submit form

### File Types Supported

- **Images**: JPG, PNG, GIF, WebP
- **Documents**: PDF, DOC, DOCX, XLS, XLSX
- **Other**: TXT, CSV, ZIP

### File Operations

#### Previewing Files
1. Click on file name in submission
2. View file in browser
3. Use zoom and navigation controls

#### Downloading Files
1. Click **"Download"** button
2. Choose original or processed version
3. File downloads to your computer

#### File Security
- Files are stored securely
- Access is controlled by permissions
- Watermarks applied to sensitive documents

---

## Troubleshooting

### Common Issues

#### Login Problems
**Issue**: Cannot log in
**Solutions**:
- Check email and password
- Try password reset
- Contact IT support
- Check if account is active

#### Form Submission Issues
**Issue**: Cannot submit form
**Solutions**:
- Check all required fields are filled
- Verify file uploads completed
- Check internet connection
- Try refreshing page

#### File Upload Problems
**Issue**: Files not uploading
**Solutions**:
- Check file size limits
- Verify file type is supported
- Check internet connection
- Try smaller files

#### Approval Notifications
**Issue**: Not receiving notifications
**Solutions**:
- Check email spam folder
- Verify email address in profile
- Check notification settings
- Contact IT support

### Browser Issues

#### Page Not Loading
1. Clear browser cache
2. Disable browser extensions
3. Try different browser
4. Check internet connection

#### JavaScript Errors
1. Enable JavaScript in browser
2. Update browser to latest version
3. Clear browser data
4. Contact IT support

### Performance Issues

#### Slow Loading
1. Check internet connection
2. Close unnecessary browser tabs
3. Clear browser cache
4. Try during off-peak hours

#### Timeout Errors
1. Check internet stability
2. Try smaller file uploads
3. Contact IT support
4. Check system status

---

## Frequently Asked Questions

### General Questions

**Q: What is SIGaP used for?**
A: SIGaP is used for form management, approval workflows, and manufacturing operations at PT. Surya Inti Aneka Pangan.

**Q: Who can access the system?**
A: All employees with valid company email addresses can access the system. Specific features depend on your role and permissions.

**Q: Is my data secure?**
A: Yes, all data is encrypted and stored securely with proper access controls and audit trails.

### Form Questions

**Q: Can I save a form and complete it later?**
A: Yes, you can save forms as drafts and return to complete them later.

**Q: How do I know if my form was approved?**
A: You'll receive email notifications when your form is approved, rejected, or needs changes.

**Q: Can I edit a submitted form?**
A: No, once submitted, forms cannot be edited. You can create a new submission if needed.

### Technical Questions

**Q: What browsers are supported?**
A: Chrome, Firefox, Safari, and Edge (latest versions) are all supported.

**Q: Can I use the system on mobile devices?**
A: Yes, the system is responsive and works on tablets and smartphones.

**Q: How do I report a bug or issue?**
A: Contact your IT support team or use the system's feedback feature.

### Manufacturing Questions

**Q: How do I add items to inventory?**
A: Use the Manufacturing → Items section to add new items, or import them from Excel.

**Q: Can I track item locations in warehouses?**
A: Yes, the system tracks exact shelf and position locations for all items.

**Q: How do I generate inventory reports?**
A: Use the Warehouse Overview Report feature in the Manufacturing module.

---

## Support and Contact

### Getting Help

- **IT Support**: Contact your internal IT team
- **System Administrator**: For account and permission issues
- **Department Manager**: For workflow and process questions

### System Information

- **Version**: SIGaP v1.0
- **Last Updated**: 2024
- **Developer**: Andri Halim Gunawan

---

*This user guide is regularly updated. Please check for the latest version on the system.*

**© 2024 PT. Surya Inti Aneka Pangan. All rights reserved.**