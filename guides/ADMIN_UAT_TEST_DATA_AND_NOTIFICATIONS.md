# Admin Production UAT Test Data & Notification Checklist

**SIGaP Admin Module**
**Test Data Conventions & Mandatory Notification Checks**
**Version 1.0**

---

## Test Data Conventions

Use these naming conventions so test data is easy to identify and delete after UAT.

### User Naming

**Name**
- `UAT Admin User <YYYYMMDD>-<NN>`
- Example: `UAT Admin User 20251228-01`

**Email**
- `uat-admin-<YYYYMMDD>-<NN>@test.com`
- Example: `uat-admin-20251228-01@test.com`

**Mobile Phone**
- Use format: `628XXXXXXXXXX` (must start with 628)
- Use sequential test numbers: `6281234567801`, `6281234567802`, etc.
- **Important:** These should be valid test numbers that can receive WhatsApp messages for notification testing.

### Role Naming

**Name**
- `UAT Test Role <YYYYMMDD>`
- Example: `UAT Test Role 20251228`

### Permission Naming

**Name**
- `uat-test.permission.<YYYYMMDD>`
- Example: `uat-test.permission.20251228`

**Description**
- Start with: `UAT:`
- Example: `UAT: Test permission for UAT testing purposes`

---

## Mandatory WhatsApp Notification Checks

All WhatsApp notifications are mandatory pass/fail. If a notification is not received or contains incorrect information, mark the test as FAIL.

### A) User Registration Notification

**Trigger:** When a new user is created (Test 4)

**Recipient:** Newly created user (sent to their `mobilephone_no`)

**Expected WhatsApp message content:**

```
Halo <User Name> 👋🏻,
Selamat Datang di SIGaP (Sistem Informasi Gabungan Pelaporan) PT. SIAP.
Senang sekali saya bisa menyambut Anda.

Berikut ini adalah detail login anda:
*User Name:* <user_email>
*Password:* <auto_generated_password>

Mohon untuk tidak membagikan detil login Anda kepada siapapun.
Apabila Anda kesulitan untuk mengingat password di atas, Anda bisa melalukan "Reset Password" melalui: <reset_password_url>.

Anda dapat mengakses SIGaP melalui: <login_url>.

Terima Kasih 🙏,

Sunny ☀️
_NB: Ini adalah pesan yang dikirim oleh sistem, mohon untuk *tidak* membalas pesan ini._
```

**Required fields in notification:**
- Greeting with user's name
- Welcome message
- `*User Name:* <email>`
- `*Password:* <generated_password>` (8-character random password)
- Password reset link
- Login link

**PASS:**
- Notification received within 30 seconds of user creation
- All required fields present
- Correct user name and email
- Valid password (8 characters)
- Working login and reset password links

**FAIL:**
- Notification not received
- Missing any required fields
- Wrong user information
- Invalid or missing password
- Broken links

---

## Mandatory Pushover Notification Checks

Pushover is a fallback notification system when WhatsApp delivery fails.

### B) WhatsApp Failure Notification (Pushover)

**Trigger:** When WhatsApp delivery fails for user registration (Test 12)

**Recipient:** System administrators (via Pushover)

**Expected Pushover notification:**

**Title:**
- `WhatsApp Failure: User Registration Notification`

**Message content:**
- `Chat ID: <failed_chat_id>`
- Original WhatsApp message that failed to send

**PASS:**
- Pushover notification received when WhatsApp fails
- Contains failed chat ID
- Contains original message content
- Received by system administrators

**FAIL:**
- No Pushover notification when WhatsApp fails (silent failure)
- Missing chat ID or message content
- Not received by administrators

---

## Authorization & Security Checks

These are critical security tests. Any failure is a security breach and must be marked as CRITICAL FAIL.

### Role Assignment Authorization

| Current User Role | Can Assign Super Admin? | Can Assign Owner? | Can Assign Other Roles? |
|-------------------|------------------------|-------------------|------------------------|
| Super Admin       | ✅ YES                 | ✅ YES            | ✅ YES                 |
| Owner             | ❌ NO (Test 23)        | ✅ YES (Test 24)  | ✅ YES                 |
| IT Staff          | ❌ NO                  | ❌ NO             | ❌ NO                  |
| Regular User      | ❌ NO                  | ❌ NO             | ❌ NO                  |

### User Management Authorization

| Current User Role | Can Create Users? | Can Edit Users? | Can Edit Super Admin/Owner Users? |
|-------------------|-------------------|-----------------|-----------------------------------|
| Super Admin       | ✅ YES            | ✅ YES          | ✅ YES                            |
| Owner             | ✅ YES            | ✅ YES          | ✅ YES                            |
| IT Staff          | ❌ NO (Test 25)   | ✅ YES*         | ❌ NO (Test 26)                   |
| Regular User      | ❌ NO (Test 2)    | ❌ NO (Test 2)  | ❌ NO                             |

*IT Staff can only edit non-Super Admin/Owner users if they have `options.users.edit` permission.

### Role Management Authorization

| Current User Role | Can Create Roles? | Can Edit Roles? | Can Edit Super Admin Role? |
|-------------------|-------------------|-----------------|---------------------------|
| Super Admin       | ✅ YES            | ✅ YES          | ✅ YES                    |
| Owner             | ✅ YES            | ✅ YES          | ❌ NO (Test 27, 28)       |
| IT Staff          | ❌ NO (Test 29)   | ❌ NO           | ❌ NO                     |
| Regular User      | ❌ NO             | ❌ NO           | ❌ NO                     |

### Permission Management Authorization

| Current User Role | Can Create Permissions? | Can Edit Permissions? | Can View Permissions? |
|-------------------|------------------------|-----------------------|-----------------------|
| Super Admin       | ✅ YES                 | ✅ YES                | ✅ YES                |
| Owner             | ✅ YES                 | ✅ YES                | ✅ YES                |
| IT Staff          | ✅ YES*                | ✅ YES*               | ✅ YES*               |
| Regular User      | ❌ NO                  | ❌ NO                 | ❌ NO                 |

*Only if they have the specific `options.permissions.create/edit/view` permission.

---

## Validation Rules Reference

### User Creation/Update Validation

| Field          | Rule                                                      | Error Message                                    |
|----------------|-----------------------------------------------------------|--------------------------------------------------|
| name           | required, string                                          | The name field is required.                      |
| email          | required, email, unique:users,email                       | The email has already been taken.                |
| mobilephone_no | required, string, max:16, starts_with:628, unique         | The mobilephone no field must start with 628.    |
| manager_id     | nullable, integer, exists:users,id                        | The selected manager id is invalid.              |
| departments    | nullable, array; departments.*: integer, exists:departments,id | The selected departments.X is invalid.      |
| roles          | nullable, array; roles.*: integer, exists:roles,id        | The selected roles.X is invalid.                 |
| permissions    | nullable, array; permissions.*: integer, exists:permissions,id | The selected permissions.X is invalid.      |
| active         | required, boolean (on update only)                        | The active field is required.                    |

### Role Creation/Update Validation

| Field       | Rule                               | Error Message                          |
|-------------|------------------------------------|----------------------------------------|
| name        | required, string, max:50, unique:roles | The name has already been taken.   |
| guard_name  | required, string                   | The guard name field is required.      |
| permissions | nullable, array; permissions.*: integer, exists:permissions,id | The selected permissions.X is invalid. |

### Permission Creation/Update Validation

| Field       | Rule                                      | Error Message                          |
|-------------|-------------------------------------------|----------------------------------------|
| name        | required, string, max:50, unique:permissions | The name has already been taken.    |
| description | nullable, string, max:500                 | -                                      |
| guard_name  | required, string                          | The guard name field is required.      |

---

## Risk Areas & Special Attention

### Critical Security Risks

1. **Role Escalation:**
   - Verify users cannot assign themselves higher privilege roles.
   - Verify role assignment restrictions are enforced (Test 22-24).

2. **Unauthorized Access:**
   - Regular users must NOT access admin pages (Test 2).
   - IT Staff must NOT create users or edit Super Admin/Owner (Test 25-26).
   - Owner must NOT manage Super Admin role (Test 23, 27-28).

3. **Data Validation:**
   - Email uniqueness must be enforced (Test 5).
   - Mobile phone format must start with 628 (Test 5).
   - Duplicate role/permission names must be rejected (Test 14, 19).

### Notification Risks

1. **WhatsApp Delivery:**
   - User must receive login credentials immediately after creation.
   - Failed WhatsApp must trigger Pushover fallback.
   - No silent failures allowed.

2. **Password Security:**
   - Auto-generated passwords must be random and 8 characters.
   - Passwords sent only via WhatsApp, never displayed in UI after creation.

### Data Integrity Risks

1. **Cascade Effects:**
   - Updating role permissions affects all users with that role immediately.
   - Deactivating a user (`active = false`) should prevent login.

2. **Orphaned Data:**
   - Verify that removing departments/roles from user doesn't break relationships.

---

## Post-UAT Cleanup

After UAT is complete, all test data should be removed:

### Users to Delete

Delete all users where:
- Name starts with `UAT Admin User`
- Email matches `uat-admin-*@test.com`

### Roles to Delete

Delete all roles where:
- Name starts with `UAT Test Role`

### Permissions to Delete

Delete all permissions where:
- Name starts with `uat-test.permission.`
- Description starts with `UAT:`

**Cleanup Query Examples:**

```sql
-- Delete test users
DELETE FROM users WHERE name LIKE 'UAT Admin User%';

-- Delete test roles (and cascade to role_has_permissions)
DELETE FROM roles WHERE name LIKE 'UAT Test Role%';

-- Delete test permissions
DELETE FROM permissions WHERE name LIKE 'uat-test.permission.%';
```

**Important:** Verify no production data accidentally matches these patterns before running cleanup.

---

**End of Admin UAT Test Data & Notification Checklist**
