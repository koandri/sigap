# Admin UAT Role Assignments

**SIGaP Admin Module**
**UAT Test Assignments by Role**
**Version 1.0**

---

## Overview

This document assigns specific tests from `ADMIN_UAT_RUNBOOK.md` to different user roles for parallel testing. Each tester should run tests assigned to their role.

**Important:**
- Test assignments are based on the authorization and permissions each role has.
- Some tests are negative tests (should fail) to verify security controls.
- All roles should verify WhatsApp notifications where applicable.

---

## A) Super Admin (Full Administrative Access)

**Role Description:** Has complete access to all admin functions including user, role, and permission management. Can assign any role including Super Admin and Owner.

**Run these tests from `ADMIN_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 3 (Admin pages load)
- Test 4 (Create new user)
- Test 5 (Create user with validation errors - negative test)
- Test 6 (View user details)
- Test 7 (Edit existing user)
- Test 8 (Edit user with validation errors - negative test)
- Test 9 (Assign multiple roles to user)
- Test 10 (Assign direct permissions to user)
- Test 11 (Assign departments to user)
- Test 12 (WhatsApp notification fallback to Pushover)
- Test 13 (Create new role)
- Test 14 (Create role with validation errors - negative test)
- Test 15 (View role details)
- Test 16 (Edit existing role)
- Test 18 (Create new permission)
- Test 19 (Create permission with validation errors - negative test)
- Test 20 (View permission details)
- Test 21 (Edit existing permission)
- Test 22 (Super Admin can assign Super Admin role)
- Test 30 (User can edit their own profile)

**Extra responsibility:**
- Verify all WhatsApp notifications are received correctly when creating new users.
- Monitor Pushover fallback notifications for WhatsApp failures.
- Verify that Super Admin role assignment is working correctly.
- Test the complete user creation workflow including all assignments (roles, permissions, departments).

---

## B) Owner (Administrative Access, Cannot Manage Super Admin)

**Role Description:** Can manage users, roles, and permissions but cannot assign or manage Super Admin role. Can assign Owner role. Can impersonate all users except Super Admin.

**Run these tests from `ADMIN_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 3 (Admin pages load)
- Test 4 (Create new user)
- Test 6 (View user details)
- Test 7 (Edit existing user)
- Test 9 (Assign multiple roles to user)
- Test 10 (Assign direct permissions to user)
- Test 11 (Assign departments to user)
- Test 13 (Create new role)
- Test 15 (View role details)
- Test 16 (Edit existing role)
- Test 17 (Role visibility based on user role - verify Super Admin role is hidden)
- Test 18 (Create new permission)
- Test 20 (View permission details)
- Test 21 (Edit existing permission)
- Test 23 (Owner cannot assign Super Admin role - negative test)
- Test 24 (Owner can assign Owner role)
- Test 27 (Only Super Admin can edit Super Admin role - negative test)
- Test 28 (Only Super Admin can view Super Admin role - negative test)
- Test 30 (User can edit their own profile)

**Extra responsibility:**
- Verify that Super Admin role is NOT visible in role listings.
- Verify that you CANNOT edit or view Super Admin role.
- Verify that you CAN assign Owner role to users.
- Confirm you can manage all other roles and users (except Super Admin).

---

## C) IT Staff (Limited User Management)

**Role Description:** Has `options.users.edit` permission. Can view and edit existing users but cannot create new users. Cannot edit Super Admin or Owner users. Cannot create or edit roles.

**Run these tests from `ADMIN_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 6 (View user details)
- Test 7 (Edit existing user - only non-Super Admin/Owner users)
- Test 25 (IT Staff cannot create users - negative test)
- Test 26 (IT Staff cannot edit Super Admin or Owner users - negative test)
- Test 29 (IT Staff cannot create roles - negative test)
- Test 30 (User can edit their own profile)

**Extra responsibility:**
- Verify that you do NOT see a "Create User" button on the users index page.
- Verify that attempting to access `/users/create` directly results in 403 error.
- Verify that you cannot edit users with Super Admin or Owner roles.
- Verify that you cannot access role or permission creation/editing pages.

---

## D) Regular User (No Admin Permissions)

**Role Description:** Standard user with no administrative permissions. Should not have access to any admin pages.

**Run these tests from `ADMIN_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 2 (Unauthorized access redirects - negative test)
- Test 30 (User can edit their own profile)

**Extra responsibility:**
- Verify that all admin URLs (users, roles, permissions) return 403 Forbidden errors.
- Verify that you cannot see any admin navigation links in the UI.
- This role is critical for security testing - confirm complete lockdown of admin features.

---

## Test Execution Guidelines

### Parallel Testing

To maximize efficiency, different role testers can work simultaneously:

1. **Super Admin tester** should focus on:
   - All positive CRUD tests for users, roles, permissions
   - User creation with WhatsApp notifications
   - Super Admin role assignment

2. **Owner tester** should focus on:
   - Verifying Super Admin restrictions (cannot see/edit Super Admin role)
   - Owner role assignment
   - All other admin functions work normally

3. **IT Staff tester** should focus on:
   - Negative tests for user creation
   - Negative tests for editing Super Admin/Owner users
   - Verifying view and edit permissions work for regular users

4. **Regular User tester** should focus on:
   - Complete negative testing of all admin URLs
   - Verifying no admin access whatsoever

### Coordination Points

Some tests require coordination between roles:

- **Test 12** (WhatsApp fallback): Super Admin creates user → Monitor Pushover notifications
- **User creation tests**: Super Admin or Owner creates → New user receives WhatsApp notification
- **Role assignment tests**: Super Admin or Owner assigns role → Verify permissions take effect

---

## Reporting

Each tester should:
1. Mark tests as PASS or FAIL in the `ADMIN_UAT_RESULTS_SHEET.csv`.
2. Record actual UI messages and any discrepancies.
3. For WhatsApp notifications, copy and paste the message content into the CSV.
4. Take screenshots of any failures.
5. Use the Failure Reporting Template from the runbook for detailed failure reports.

---

**End of Admin UAT Role Assignments**
