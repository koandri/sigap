# Administrator Guide

**SIGaP System Administration**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [User Management](#user-management)
3. [Roles and Permissions](#roles-and-permissions)
4. [Department Management](#department-management)
5. [System Configuration](#system-configuration)

---

## Overview

This guide is for system administrators responsible for managing users, roles, permissions, and system configuration.

### Administrator Responsibilities

- User account management
- Role and permission assignment
- Department structure maintenance
- System configuration
- Security and access control
- User support and troubleshooting

---

## User Management

### Creating Users

1. Navigate to **Users** in the sidebar

![Users List](/guides-imgs/admin-users-list.png)

2. Click **"Create User"**
3. Fill in user details:

**Basic Information:**
- **Name**: Full name
- **Email**: Email address (used for login)
- **Password**: Initial password
  - User should change on first login
  - Must meet security requirements
- **Active**: Enable account immediately

**Organizational:**
- **Department**: User's department
- **Manager**: Direct supervisor (optional)
  - Used for approval workflows
  - Used for escalations

4. Click **"Create User"**

![Create User Form](/guides-imgs/admin-create-user.png)

### Assigning Roles

After creating user:

1. Click **"Edit"** on user
2. Go to **"Roles"** section
3. Select appropriate roles
4. Users can have multiple roles
5. Click **"Update"**

**Common Role Combinations:**
- Operator + Department Manager
- IT Staff + Super Admin
- Supervisor + Approver

### Editing Users

1. Find user in list
2. Click **"Edit"**
3. Modify any fields:
   - Name
   - Email
   - Department
   - Manager
   - Active status
   - Roles
4. Click **"Update"**

### Deactivating Users

Instead of deleting:

1. Edit user
2. Uncheck **"Active"**
3. Save changes

**Effects:**
- User cannot log in
- Existing data preserved
- Audit trail maintained
- Can be reactivated later

### User Impersonation

For troubleshooting and support:

1. Open user details
2. Click **"Impersonate"**
3. Experience system as that user
4. See their permissions and access
5. Click **"Leave Impersonation"** when done

**Use Cases:**
- Troubleshoot user issues
- Verify permissions
- Test workflows
- Train administrators
- Reproduce reported problems

**Security:**
- All impersonation actions logged
- Only Super Admin can impersonate
- Original admin identity preserved
- Can't perform sensitive actions while impersonating

### Resetting Passwords

1. Edit user
2. Set new password
3. Check **"Force Password Change"** (if available)
4. Notify user of new password
5. User changes password on next login

### Single Sign-On (SSO) with Keycloak

SIGaP supports **Keycloak Single Sign-On** for seamless authentication.

**How It Works:**
- Users can log in using their Keycloak credentials
- **Automatic User Provisioning**: If a user logs in via SSO for the first time, SIGaP automatically creates their account
- **Existing Users**: SSO links to existing accounts by email address
- **Account Status**: Only active users can log in via SSO

**Automatic User Creation:**
When a user logs in via Keycloak for the first time:
1. System checks if email exists in database
2. If not found, creates new user account automatically
3. Sets name from Keycloak profile
4. Generates random password (user won't need it for SSO login)
5. Sets account as active
6. Links Keycloak ID to user account
7. User is logged in automatically

**Managing SSO Users:**
- SSO-created users appear in the Users list like any other user
- Administrators can assign roles and permissions normally
- Can deactivate accounts if needed (prevents SSO login)
- Users can still use password login if password is set

**Important Notes:**
- Inactive users cannot log in via SSO
- System logs all SSO login attempts
- Failed SSO logins trigger notifications to IT
- Users must exist in Keycloak to use SSO

---

## Roles and Permissions

### Understanding RBAC

SIGaP uses Role-Based Access Control (RBAC):

**Roles**: Collections of permissions
**Permissions**: Specific actions users can perform
**Users**: Assigned one or more roles

### Default Roles

#### Super Admin
- Full system access
- Manage all modules
- User management
- System configuration
- Impersonation rights

#### Owner
- Executive dashboard access
- View all reports
- View all submissions
- Limited editing capabilities
- No user management

#### IT Staff
- System configuration
- User management
- Form management
- Technical support access
- No executive functions

#### Department Manager
- Department-level access
- Approve submissions
- View team reports
- Manage team members
- Create submissions

#### Operator
- Basic user access
- Submit forms
- View own submissions
- Perform assigned work
- Access assigned modules

### Creating Custom Roles

1. Navigate to **Roles**

![Roles List](/guides-imgs/admin-roles-list.png)

2. Click **"Create Role"**
3. Enter role name (e.g., "Quality Inspector")
4. Select permissions

**Important Notes:**
- **Only Super Admin and Owner can create roles**
- IT Staff can view and edit existing roles but cannot create new ones
- Permissions are **grouped by module** for easier management
- Each group shows related permissions together

**Permission Groups (Organized by Module):**

The permissions interface displays permissions grouped by their module prefix:

**Forms Permissions:**
- `forms.view` - View forms list
- `forms.create` - Create new forms
- `forms.edit` - Edit forms
- `forms.delete` - Delete forms

**Form Submissions Permissions:**
- `formsubmissions.view` - View submissions
- `formsubmissions.create` - Create submissions
- `formsubmissions.edit` - Edit submissions
- `formsubmissions.delete` - Delete submissions
- `formsubmissions.approve` - Approve submissions

**Manufacturing Permissions:**
- `manufacturing.dashboard.view` - View dashboard
- `manufacturing.production-plans.view` - View production plans
- `manufacturing.production-plans.create` - Create production plans
- `manufacturing.production-plans.edit` - Edit production plans
- `manufacturing.production-plans.approve` - Approve production plans
- `manufacturing.production-plans.start` - Start production
- `manufacturing.production-plans.record-actuals` - Record production actuals
- `manufacturing.production-plans.complete` - Complete production plans
- `manufacturing.recipes.view` - View recipes
- `manufacturing.recipes.create` - Create recipes
- `manufacturing.recipes.edit` - Edit recipes
- `manufacturing.yield-guidelines.view` - View yield guidelines
- `manufacturing.yield-guidelines.create` - Create yield guidelines
- `manufacturing.yield-guidelines.edit` - Edit yield guidelines

**Warehouses Permissions:**
- `warehouses.dashboard.view` - View warehouse dashboard
- `warehouses.view` - View warehouses
- `warehouses.create` - Create warehouses
- `warehouses.edit` - Edit warehouses
- `warehouses.delete` - Delete warehouses
- `warehouses.inventory.view` - View inventory
- `warehouses.inventory.create` - Add inventory
- `warehouses.inventory.edit` - Update inventory
- `warehouses.inventory.delete` - Delete inventory

**Options Permissions (Master Data):**
- `options.items.view` - View items
- `options.items.edit` - Edit items
- `options.items.delete` - Delete items
- `options.items.import` - Import items
- `options.item-categories.view` - View item categories
- `options.item-categories.create` - Create item categories
- `options.item-categories.edit` - Edit item categories
- `options.item-categories.delete` - Delete item categories
- `options.assets.view` - View assets
- `options.assets.create` - Create assets
- `options.assets.update` - Update assets
- `options.assets.delete` - Delete assets
- `options.asset-categories.view` - View asset categories
- `options.asset-categories.update` - Update asset categories
- `options.users.view` - View users
- `options.users.create` - Create users
- `options.users.edit` - Edit users
- `options.users.delete` - Delete users
- `options.roles.view` - View roles
- `options.roles.create` - Create roles
- `options.roles.edit` - Edit roles
- `options.roles.delete` - Delete roles
- `options.permissions.view` - View permissions
- `options.permissions.create` - Create permissions
- `options.permissions.edit` - Edit permissions
- `options.permissions.delete` - Delete permissions
- `options.departments.view` - View departments
- `options.departments.create` - Create departments
- `options.departments.edit` - Edit departments
- `options.departments.delete` - Delete departments

**Maintenance Permissions:**
- `maintenance.dashboard.view` - View dashboard
- `maintenance.assets.view` - View assets
- `maintenance.assets.create` - Create assets
- `maintenance.assets.edit` - Edit assets
- `maintenance.schedules.manage` - Manage schedules
- `maintenance.workorders.view` - View work orders
- `maintenance.workorders.create` - Create work orders
- `maintenance.workorders.assign` - Assign work orders
- `maintenance.workorders.verify` - Verify completed work
- `maintenance.reports.view` - View reports

**DMS Permissions:**
- `dms.*` - Document Management System permissions (various operations)

**Facility Permissions:**
- `facility.*` - Facility management permissions (cleaning, schedules, etc.)

5. Click **"Create Role"**

### Editing Roles

1. Navigate to **Roles**
2. Click role name
3. Click **"Edit"**
4. Add or remove permissions
5. Changes affect all users with this role
6. Click **"Update"**

**Access Restrictions:**
- **Super Admin role**: Only Super Admin can edit this role
- **Owner role**: Only Super Admin can edit this role
- **Other roles**: Super Admin, Owner, and IT Staff (with `options.roles.edit` permission) can edit
- Permissions are displayed **grouped by module** for easier selection
- Use checkboxes to select/deselect multiple permissions at once

### Best Practices

**Role Design:**
- Create roles based on job functions
- Use principle of least privilege
- Don't duplicate existing roles unnecessarily
- Document role purposes
- Review roles periodically

**Permission Assignment:**
- Grant only needed permissions
- Test roles thoroughly
- Consider approval workflows
- Plan for growth
- Document special cases

**User Assignment:**
- Assign minimum roles needed
- Regular access reviews
- Remove unused roles
- Document exceptions
- Train users on their access

---

## Department Management

### Creating Departments

1. Navigate to **Departments**

![Departments List](/guides-imgs/admin-departments-list.png)

2. Click **"Create Department"**
3. Fill in details:
   - **Code**: Short code (e.g., `PROD`, `QC`, `HR`)
   - **Name**: Full name (e.g., "Production", "Quality Control")
   - **Short Name**: Abbreviated name
   - **Description**: Department details
   - **Active**: Enable department
4. Click **"Create"**

### Department Structure

**Organizational Hierarchy:**
```
Company
├── Production
│   ├── Processing
│   ├── Packaging
│   └── Quality Control
├── Operations
│   ├── Warehouse
│   └── Logistics
├── Administration
│   ├── Finance
│   ├── HR
│   └── IT
└── Sales & Marketing
```

### Department-Based Access

**Forms:**
- Forms assigned to departments
- Only department members see forms
- Submissions filtered by department
- Approvals route through department hierarchy

**Inventory:**
- Departments can have warehouse access
- Usage tracked by department
- Cost allocation by department

**Maintenance:**
- Assets assigned to departments
- Work orders filtered by department
- Maintenance costs tracked per department

### Editing Departments

1. Navigate to **Departments**
2. Click department name
3. Click **"Edit"**
4. Modify details
5. Click **"Update"**

**Impact of Changes:**
- User access may change
- Form visibility affected
- Report filters updated
- Historical data preserved

---

## System Configuration

### Form Settings

**Default Configurations:**
- Form number format
- Approval timeouts
- Notification settings
- File upload limits
- Image compression quality

**API Integration:**
- External API connections
- Authentication tokens
- Timeout settings
- Cache durations

### Maintenance Settings

**Schedule Configuration:**
- Default SLA times
- Escalation rules
- Auto-generation settings
- Notification recipients

**Work Order Settings:**
- Status workflow
- Required fields
- Approval requirements
- Parts tracking

### Notification Settings

**Email Configuration:**
- SMTP settings
- From address
- Email templates
- Notification timing

**Events:**
- Form approvals
- Work order assignments
- Schedule overdue
- System alerts

### Security Settings

**Password Policy:**
- Minimum length
- Complexity requirements
- Expiration period
- Reset procedures

**Session Management:**
- Session timeout
- Concurrent sessions
- Remember me duration

**Audit Logging:**
- Actions logged
- Log retention
- Access to logs

### Backup and Maintenance

**Database Backups:**
- Backup schedule
- Backup location
- Retention policy
- Restore procedures

**File Storage:**
- Storage location
- Backup strategy
- Cleanup policies
- Archive procedures

---

## Best Practices

### User Management

**Account Creation:**
- Verify user information
- Assign appropriate roles
- Set strong initial passwords
- Document access level
- Communicate credentials securely

**Access Reviews:**
- Quarterly access reviews
- Remove unused accounts
- Update changed roles
- Verify department assignments
- Document exceptions

**Offboarding:**
- Deactivate accounts immediately
- Transfer ownership of data
- Document account status
- Archive user information
- Review for compliance

### Security

**Password Management:**
- Enforce strong passwords
- Regular password changes
- No password sharing
- Secure password reset
- Monitor failed logins

**Access Control:**
- Principle of least privilege
- Regular permission audits
- Monitor administrative actions
- Review role assignments
- Investigate anomalies

**Monitoring:**
- Review audit logs regularly
- Monitor system usage
- Track failed access attempts
- Investigate suspicious activity
- Document security incidents

### Documentation

**System Documentation:**
- Maintain current documentation
- Document custom configurations
- Record system changes
- Keep procedure manuals updated
- Version control documentation

**User Training:**
- Provide role-specific training
- Create training materials
- Conduct regular sessions
- Document training completion
- Update materials regularly

---

## Troubleshooting

### User Can't Access Feature

**Problem**: User reports missing functionality

**Solutions:**
1. Verify user has required role
2. Check role has required permissions
3. Verify user account is active
4. Check department assignment
5. Review recent permission changes
6. Clear user's browser cache
7. Test with administrator account

### Role Permission Issues

**Problem**: Role doesn't provide expected access

**Solutions:**
1. Review role permissions
2. Check for conflicting roles
3. Verify permission names
4. Test with test user
5. Check permission inheritance
6. Review recent changes
7. Consult permission documentation

### Department Access Problems

**Problem**: Users can't see department forms

**Solutions:**
1. Verify user's department assignment
2. Check form's department assignments
3. Verify department is active
4. Check for multi-department access
5. Review user roles
6. Test with different user

---

## Related Documentation

- **[User Guide](./USER_GUIDE.md)** - Main system guide
- **[Forms Guide](./FORMS_GUIDE.md)** - Form administration
- **[Workflows Guide](./WORKFLOWS_GUIDE.md)** - Approval workflow management

---

**Last Updated**: October 17, 2025  
**Version**: 1.0

