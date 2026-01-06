# Admin Production UAT Runbook

**SIGaP Admin Module (Users, Roles & Permissions)**
**Production UAT Manual Test Runbook**
**Version 1.0**

---

## Table of Contents

1. [Purpose & Rules](#purpose--rules)
2. [UAT Accounts & Roles](#uat-accounts--roles)
3. [Authentication & Authorization](#authentication--authorization)
4. [Navigation Smoke](#navigation-smoke)
5. [Users Management](#users-management)
6. [User Assignment (Roles, Permissions, Departments)](#user-assignment-roles-permissions-departments)
7. [User Notifications (WhatsApp)](#user-notifications-whatsapp)
8. [Roles Management](#roles-management)
9. [Permissions Management](#permissions-management)
10. [Authorization & Access Control](#authorization--access-control)
11. [User Profile Management](#user-profile-management)
12. [Failure Reporting Template](#failure-reporting-template)

---

## Purpose & Rules

This runbook is used to validate the full Admin module via the live production UI (UAT phase). The database will be refreshed after UAT, so test data can be created/destructive.

**Important UAT rules:**

1. **All test-created data must be clearly tagged** using the naming convention in this guide.
2. **Do not use real personal information.** Use dummy names, emails with UAT prefix.
3. **Notifications are mandatory pass/fail.** If the expected WhatsApp notification is not received, mark the test as FAIL.
4. **Authorization is critical.** Verify that users cannot perform actions they don't have permission for.
5. **Role hierarchy must be enforced.** Only Super Admin can assign Super Admin role, etc.

---

## UAT Accounts & Roles

Use these roles during testing:

- **Super Admin**: Full access to all admin functions; can create/edit users, roles, permissions; can assign Super Admin and Owner roles; can impersonate any user.
- **Owner**: Can create/edit users, roles, permissions; cannot assign Super Admin role; can assign Owner role; can impersonate all users except Super Admin.
- **IT Staff** (with options.users.edit permission): Can view and edit existing users but cannot create new users; cannot edit Super Admin or Owner users.
- **Regular User** (no admin permissions): Cannot access any admin pages; should get 403 errors.

All UAT users must have valid `mobilephone_no` starting with `628`.

---

## Authentication & Authorization

### Test 1 — Login smoke test (each role)

**Role:** Super Admin, Owner, IT Staff, Regular User

1. Open `https://sigap.suryagroup.app/login`.
2. Login with your test account credentials.
3. Confirm you land inside SIGaP and can see the authenticated UI (navbar, your profile name).

**Pass criteria:**
- Login completes successfully for all role types.

**Fail criteria:**
- Login error, redirect loop, or forbidden error.

### Test 2 — Unauthorized access redirects

**Role:** Regular User (no admin permissions)

1. While logged in as a Regular User, try to access `https://sigap.suryagroup.app/users`.
2. Confirm you receive a 403 Forbidden error.

**Expected error message:**
- `403 | You do not have permission to view users.`

**Pass criteria:**
- Access is denied with 403 error.

**Fail criteria:**
- Page loads successfully (security breach).

---

## Navigation Smoke

### Test 3 — Admin pages load

**Role:** Super Admin

Open each page and confirm it loads (no 500/403):

- `https://sigap.suryagroup.app/users`
- `https://sigap.suryagroup.app/users/create`
- `https://sigap.suryagroup.app/roles`
- `https://sigap.suryagroup.app/roles/create`
- `https://sigap.suryagroup.app/permissions`
- `https://sigap.suryagroup.app/permissions/create`

**Pass criteria:**
- All pages load and show content or valid empty-state.

**Fail criteria:**
- Any 500 error, 403 error, or blank page.

---

## Users Management

### Test 4 — Create new user (Super Admin)

**Role:** Super Admin

1. Go to `https://sigap.suryagroup.app/users`.
2. Click **Create User** button.
3. Fill in the form:
   - **Name**: `UAT Admin User 20251228-01`
   - **Email**: `uat-admin-20251228-01@test.com`
   - **Mobile Phone**: `6281234567801` (use valid format: starts with 628)
   - **Manager**: Select any existing user
   - **Locations**: Select one or more locations
   - **Departments**: Select at least one department
   - **Roles**: Select at least one role (avoid Super Admin for now)
   - **Permissions**: Optionally select direct permissions
4. Click **Create** button.

**Expected success message:**
- `A new user created!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/users`.
- New user appears in the users list.
- System auto-generates a random 8-character password.

**Mandatory WhatsApp notification (pass/fail):**
- A WhatsApp message is sent to the new user's mobile number.
- Expected message content:
  - Greeting: `Halo <Name> 👋🏻`
  - Welcome message: `Selamat Datang di SIGaP`
  - Login credentials: `*User Name:* <email>` and `*Password:* <generated_password>`
  - Login link and reset password link

**Pass criteria:**
- User created successfully.
- WhatsApp notification received with correct credentials.

**Fail criteria:**
- Creation fails.
- No WhatsApp notification received.
- Wrong credentials in notification.

### Test 5 — Create user with validation errors (negative test)

**Role:** Super Admin

1. Go to `https://sigap.suryagroup.app/users/create`.
2. Try to submit the form with:
   - **Empty name**: Leave name blank.
   - Click **Create**.

**Expected behavior:**
- Form validation errors are displayed.
- User is not created.

3. Try with **duplicate email**:
   - Fill in all required fields.
   - Use an email that already exists in the system.
   - Click **Create**.

**Expected validation error:**
- `The email has already been taken.`

4. Try with **invalid mobile phone format**:
   - Fill in all required fields.
   - Enter mobile phone: `081234567890` (doesn't start with 628).
   - Click **Create**.

**Expected validation error:**
- `The mobilephone no field must start with 628.`

**Pass criteria:**
- All validation errors display correctly.
- Invalid data is rejected.

**Fail criteria:**
- Invalid data is accepted.
- No validation errors shown.

### Test 6 — View user details

**Role:** Super Admin or user with options.users.view permission

1. Go to `https://sigap.suryagroup.app/users`.
2. Click on any user's name or **View** button.
3. Confirm the user details page loads at `https://sigap.suryagroup.app/users/{id}`.

**Expected behavior:**
- User details are displayed correctly.
- Shows user's roles and permissions (both direct and inherited from roles).
- Shows departments, locations, manager, active status.

**Pass criteria:**
- All user information displays correctly.

**Fail criteria:**
- 403 error or missing information.

### Test 7 — Edit existing user

**Role:** Super Admin or user with options.users.edit permission

1. Go to `https://sigap.suryagroup.app/users`.
2. Click **Edit** button for a test user (not Super Admin or Owner).
3. Update:
   - **Name**: `UAT Admin User 20251228-01 UPDATED`
   - **Active**: Toggle to inactive (unchecked).
   - Add/remove roles or permissions.
4. Click **Update** button.

**Expected success message:**
- `User has been updated!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/users`.
- Updated information is reflected in the users list.

**Pass criteria:**
- User updated successfully.
- Changes persist after page reload.

**Fail criteria:**
- Update fails or changes don't persist.

### Test 8 — Edit user with validation errors (negative test)

**Role:** Super Admin

1. Go to edit a user.
2. Change email to another existing user's email.
3. Click **Update**.

**Expected validation error:**
- `The email has already been taken.`

**Pass criteria:**
- Validation error displays correctly.
- User is not updated with invalid data.

---

## User Assignment (Roles, Permissions, Departments)

### Test 9 — Assign multiple roles to user

**Role:** Super Admin

1. Edit a test user.
2. Assign multiple roles (e.g., Manager, Document Control).
3. Click **Update**.

**Expected success message:**
- `User has been updated!`

**Pass criteria:**
- User has all assigned roles.
- Verify on user detail page that all roles are listed.

### Test 10 — Assign direct permissions to user

**Role:** Super Admin

1. Edit a test user.
2. Select specific permissions from the grouped permissions list (e.g., dms.documents.view, facility.schedules.view).
3. Click **Update**.

**Expected success message:**
- `User has been updated!`

**Pass criteria:**
- User has direct permissions assigned.
- Verify on user detail page under "Direct Permissions" section.

### Test 11 — Assign departments to user

**Role:** Super Admin

1. Edit a test user.
2. Select one or more departments.
3. Click **Update**.

**Expected success message:**
- `User has been updated!`

**Pass criteria:**
- User is assigned to selected departments.
- Verify on user detail page that departments are listed.

---

## User Notifications (WhatsApp)

### Test 12 — WhatsApp notification fallback to Pushover

**Role:** Super Admin

1. Create a new user with an invalid mobile phone number format that passes validation but will fail WhatsApp delivery (e.g., a test number that doesn't exist).
2. Monitor Pushover notifications.

**Expected behavior:**
- If WhatsApp delivery fails, a Pushover notification is sent to administrators.
- Pushover notification should contain:
  - Alert title: `WhatsApp Failure: User Registration Notification`
  - Chat ID that failed.
  - Original message content.

**Pass criteria:**
- Pushover fallback notification is received when WhatsApp fails.

**Fail criteria:**
- No fallback notification.
- Silent failure.

---

## Roles Management

### Test 13 — Create new role

**Role:** Super Admin or Owner

1. Go to `https://sigap.suryagroup.app/roles`.
2. Click **Create Role** button.
3. Fill in the form:
   - **Name**: `UAT Test Role 20251228`
   - **Guard Name**: `web`
   - **Permissions**: Select several permissions from different modules.
4. Click **Create** button.

**Expected success message:**
- `A new role created!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/roles`.
- New role appears in the roles list.

**Pass criteria:**
- Role created successfully.
- Selected permissions are assigned to the role.

**Fail criteria:**
- Creation fails or permissions not assigned.

### Test 14 — Create role with validation errors (negative test)

**Role:** Super Admin

1. Go to `https://sigap.suryagroup.app/roles/create`.
2. Try to create a role with an existing name (e.g., "Manager").
3. Click **Create**.

**Expected validation error:**
- `The name has already been taken.`

**Pass criteria:**
- Duplicate role names are rejected.

**Fail criteria:**
- Duplicate role is created.

### Test 15 — View role details

**Role:** Super Admin or user with options.roles.view permission

1. Go to `https://sigap.suryagroup.app/roles`.
2. Click on any role's name or **View** button.
3. Confirm the role details page loads at `https://sigap.suryagroup.app/roles/{id}`.

**Expected behavior:**
- Role details are displayed.
- Shows all permissions assigned to the role (grouped by module).
- Shows count of users with this role.

**Pass criteria:**
- All role information displays correctly.

**Fail criteria:**
- 403 error or missing information.

### Test 16 — Edit existing role

**Role:** Super Admin or Owner

1. Go to `https://sigap.suryagroup.app/roles`.
2. Click **Edit** button for a test role (not Super Admin or Owner).
3. Update:
   - **Name**: `UAT Test Role 20251228 UPDATED`
   - Add/remove permissions.
4. Click **Update** button.

**Expected success message:**
- `Role has been updated!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/roles`.
- Updated information is reflected in the roles list.
- Users with this role now have updated permissions.

**Pass criteria:**
- Role updated successfully.
- Permission changes are reflected immediately.

**Fail criteria:**
- Update fails or permission changes don't take effect.

### Test 17 — Role visibility based on user role

**Role:** Owner (not Super Admin)

1. Login as Owner.
2. Go to `https://sigap.suryagroup.app/roles`.
3. Verify that you CANNOT see the "Super Admin" role in the list.

**Pass criteria:**
- Super Admin role is hidden from Owner's view.

**Fail criteria:**
- Owner can see Super Admin role.

---

## Permissions Management

### Test 18 — Create new permission

**Role:** Super Admin or user with options.permissions.create permission

1. Go to `https://sigap.suryagroup.app/permissions`.
2. Click **Create Permission** button.
3. Fill in the form:
   - **Name**: `uat-test.permission.20251228`
   - **Description**: `UAT test permission for testing purposes`
   - **Guard Name**: `web`
4. Click **Create** button.

**Expected success message:**
- `A new permission created!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/permissions`.
- New permission appears in the permissions list.

**Pass criteria:**
- Permission created successfully.

**Fail criteria:**
- Creation fails.

### Test 19 — Create permission with validation errors (negative test)

**Role:** Super Admin

1. Go to `https://sigap.suryagroup.app/permissions/create`.
2. Try to create a permission with an existing name (e.g., "dms.admin").
3. Click **Create**.

**Expected validation error:**
- `The name has already been taken.`

**Pass criteria:**
- Duplicate permission names are rejected.

**Fail criteria:**
- Duplicate permission is created.

### Test 20 — View permission details

**Role:** Super Admin or user with options.permissions.view permission

1. Go to `https://sigap.suryagroup.app/permissions`.
2. Click on any permission's name or **View** button.
3. Confirm the permission details page loads at `https://sigap.suryagroup.app/permissions/{id}`.

**Expected behavior:**
- Permission details are displayed.
- Shows permission name, description, guard name.

**Pass criteria:**
- All permission information displays correctly.

**Fail criteria:**
- 403 error or missing information.

### Test 21 — Edit existing permission

**Role:** Super Admin or user with options.permissions.edit permission

1. Go to `https://sigap.suryagroup.app/permissions`.
2. Click **Edit** button for the test permission created in Test 18.
3. Update:
   - **Description**: `UAT test permission UPDATED`
4. Click **Update** button.

**Expected success message:**
- `Permission has been updated!`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/permissions`.
- Updated description is reflected in the permissions list.

**Pass criteria:**
- Permission updated successfully.

**Fail criteria:**
- Update fails.

---

## Authorization & Access Control

### Test 22 — Super Admin can assign Super Admin role

**Role:** Super Admin

1. Create or edit a user.
2. Assign the "Super Admin" role.
3. Click **Create** or **Update**.

**Expected success message:**
- `A new user created!` or `User has been updated!`

**Pass criteria:**
- Super Admin role is successfully assigned.

**Fail criteria:**
- Assignment is blocked or fails.

### Test 23 — Owner cannot assign Super Admin role (negative test)

**Role:** Owner

1. Login as Owner.
2. Try to create or edit a user.
3. Verify that "Super Admin" role is NOT in the available roles dropdown.

**Pass criteria:**
- Super Admin role is not available for selection.

**Fail criteria:**
- Owner can see and select Super Admin role (security breach).

### Test 24 — Owner can assign Owner role

**Role:** Owner

1. Login as Owner.
2. Create or edit a user.
3. Assign the "Owner" role.
4. Click **Create** or **Update**.

**Expected success message:**
- `A new user created!` or `User has been updated!`

**Pass criteria:**
- Owner role is successfully assigned.

**Fail criteria:**
- Assignment fails.

### Test 25 — IT Staff cannot create users (negative test)

**Role:** IT Staff (with options.users.edit permission, but NOT Super Admin or Owner role)

1. Login as IT Staff.
2. Go to `https://sigap.suryagroup.app/users`.
3. Verify there is NO **Create User** button visible.
4. Try to access `https://sigap.suryagroup.app/users/create` directly via URL.

**Expected error:**
- `403 | You do not have permission to create users.`

**Pass criteria:**
- IT Staff is blocked from creating users.

**Fail criteria:**
- IT Staff can create users (authorization breach).

### Test 26 — IT Staff cannot edit Super Admin or Owner users (negative test)

**Role:** IT Staff (with options.users.edit permission)

1. Login as IT Staff.
2. Go to `https://sigap.suryagroup.app/users`.
3. Try to click **Edit** on a user who has Super Admin or Owner role.
4. Or access the edit URL directly: `https://sigap.suryagroup.app/users/{super_admin_user_id}/edit`.

**Expected error:**
- `403 | You do not have permission to edit this user.`

**Pass criteria:**
- IT Staff is blocked from editing Super Admin or Owner users.

**Fail criteria:**
- IT Staff can edit Super Admin or Owner users (security breach).

### Test 27 — Only Super Admin can edit Super Admin role (negative test)

**Role:** Owner

1. Login as Owner.
2. Go to `https://sigap.suryagroup.app/roles`.
3. Verify you CANNOT see "Super Admin" role (should not be listed).
4. Try to access edit URL directly: `https://sigap.suryagroup.app/roles/{super_admin_role_id}/edit`.

**Expected error:**
- `403 | You do not have permission to edit this role.`

**Pass criteria:**
- Owner is blocked from editing Super Admin role.

**Fail criteria:**
- Owner can edit Super Admin role.

### Test 28 — Only Super Admin can view Super Admin role details (negative test)

**Role:** Owner

1. Login as Owner.
2. Try to access view URL directly: `https://sigap.suryagroup.app/roles/{super_admin_role_id}`.

**Expected error:**
- `403 | You do not have permission to view this role.`

**Pass criteria:**
- Owner is blocked from viewing Super Admin role.

**Fail criteria:**
- Owner can view Super Admin role.

### Test 29 — IT Staff cannot create roles (negative test)

**Role:** IT Staff (not Super Admin or Owner)

1. Login as IT Staff.
2. Try to access `https://sigap.suryagroup.app/roles/create`.

**Expected error:**
- `403 | You do not have permission to create roles.`

**Pass criteria:**
- IT Staff is blocked from creating roles.

**Fail criteria:**
- IT Staff can create roles.

---

## User Profile Management

### Test 30 — User can edit their own profile

**Role:** Any authenticated user

1. Login as any user.
2. Go to `https://sigap.suryagroup.app/profile/edit`.
3. Confirm the profile edit page loads.

**Expected behavior:**
- User can edit their own profile information.

**Pass criteria:**
- Profile edit page loads successfully.

**Fail criteria:**
- 403 error or page doesn't load.

---

## Failure Reporting Template

Use this format when reporting a test failure:

```
**Test Number:** [e.g., Test 4]
**Test Title:** [e.g., Create new user]
**Role:** [e.g., Super Admin]
**Status:** FAIL

**What happened:**
- [Describe what actually occurred]

**Expected:**
- [What should have happened according to the runbook]

**Screenshots/Evidence:**
- [Attach or link to screenshot]

**Error Messages:**
- [Copy exact error message if any]

**Browser/Environment:**
- [e.g., Chrome 120, macOS, Production URL]

**Time of Test:**
- [e.g., 2025-12-28 14:30 WIB]
```

---

**End of Admin UAT Runbook**
