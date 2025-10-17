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
2. Click **"Create Role"**
3. Enter role name (e.g., "Quality Inspector")
4. Select permissions

**Permission Categories:**

**Forms:**
- `forms.view` - View forms list
- `forms.create` - Create new forms
- `forms.edit` - Edit forms
- `forms.delete` - Delete forms

**Form Submissions:**
- `formsubmissions.view` - View submissions
- `formsubmissions.create` - Create submissions
- `formsubmissions.edit` - Edit submissions
- `formsubmissions.delete` - Delete submissions
- `formsubmissions.approve` - Approve submissions

**Manufacturing:**
- `manufacturing.dashboard.view` - View dashboard
- `manufacturing.warehouses.view` - View warehouses
- `manufacturing.warehouses.create` - Create warehouses
- `manufacturing.warehouses.edit` - Edit warehouses
- `manufacturing.inventory.view` - View inventory
- `manufacturing.inventory.create` - Add inventory
- `manufacturing.inventory.edit` - Update inventory
- `manufacturing.bom.view` - View BoM
- `manufacturing.bom.create` - Create BoM
- `manufacturing.bom.approve` - Approve BoM

**Maintenance:**
- `maintenance.dashboard.view` - View dashboard
- `maintenance.assets.view` - View assets
- `options.assets.create` - Create assets
- `options.assets.edit` - Edit assets
- `maintenance.schedules.manage` - Manage schedules
- `maintenance.workorders.view` - View work orders
- `maintenance.workorders.create` - Create work orders
- `maintenance.workorders.assign` - Assign work orders
- `maintenance.workorders.verify` - Verify completed work
- `maintenance.reports.view` - View reports

**Administration:**
- `users.view` - View users
- `users.create` - Create users
- `users.edit` - Edit users
- `roles.manage` - Manage roles
- `permissions.manage` - Manage permissions
- `departments.manage` - Manage departments

5. Click **"Create Role"**

### Editing Roles

1. Navigate to **Roles**
2. Click role name
3. Click **"Edit"**
4. Add or remove permissions
5. Changes affect all users with this role
6. Click **"Update"**

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

