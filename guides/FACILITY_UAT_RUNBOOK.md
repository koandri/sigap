# Facility Management Production UAT Runbook

**SIGaP Facility Management Module (Cleaning & Maintenance Requests)**
**Production UAT Manual Test Runbook**
**Version 1.0**

---

## Table of Contents

1. [Purpose & Rules](#purpose--rules)
2. [UAT Accounts & Roles](#uat-accounts--roles)
3. [Authentication & Authorization](#authentication--authorization)
4. [Navigation Smoke](#navigation-smoke)
5. [Cleaning Schedules (Recurring Tasks)](#cleaning-schedules-recurring-tasks)
6. [Cleaning Tasks (Daily Operations)](#cleaning-tasks-daily-operations)
7. [Task Assignment & Reassignment](#task-assignment--reassignment)
8. [Task Execution (Cleaner Workflow)](#task-execution-cleaner-workflow)
9. [Task Submission with Photos & GPS](#task-submission-with-photos--gps)
10. [Cleaning Approvals & SLA Management](#cleaning-approvals--sla-management)
11. [Mass Approval & Quality Control](#mass-approval--quality-control)
12. [Cleaning Requests (Ad-hoc/Guest Requests)](#cleaning-requests-ad-hocguest-requests)
13. [Schedule Alerts (Asset Issues)](#schedule-alerts-asset-issues)
14. [WhatsApp Notifications](#whatsapp-notifications)
15. [Reports & Dashboard](#reports--dashboard)
16. [Failure Reporting Template](#failure-reporting-template)

---

## Purpose & Rules

This runbook validates the full Facility Management module via the live production UI (UAT phase). The database will be refreshed after UAT, so test data can be created/destructive.

**Important UAT rules:**

1. **All test-created data must be clearly tagged** using the naming convention in this guide.
2. **Notifications are mandatory pass/fail.** If the expected WhatsApp notification is not received, mark the test as FAIL.
3. **Photo watermarking is mandatory pass/fail.** Before/after photos must have watermarks with correct information.
4. **SLA compliance is critical.** Verify that overdue tasks are correctly identified and flagged.
5. **GPS coordinates** should be captured if device supports it (optional but verify it's attempted).
6. **Quality control (10% review threshold)** must be enforced before mass approval.

---

## UAT Accounts & Roles

Use these roles during testing:

- **General Affairs (GA) Supervisor**: Manages schedules, tasks, approvals; receives alerts and flagged task notifications; performs mass approvals.
- **General Affairs Staff**: Views tasks, handles requests, performs approvals; receives schedule alerts.
- **Cleaner**: Assigned to cleaning tasks; submits tasks with before/after photos; receives task assignment and reminder notifications.
- **Guest/Public User**: Submits cleaning or repair requests via public form (no authentication required).
- **Regular User** (authenticated): Can submit cleaning requests (same form as guest but auto-fills user info).

**Required Test Accounts:**
- At least 2 Cleaner role users with valid `mobilephone_no` (for WhatsApp testing)
- 1 General Affairs Supervisor with valid `mobilephone_no`
- 1 General Affairs Staff with valid `mobilephone_no`

---

## Authentication & Authorization

### Test 1 — Login smoke test (each role)

**Role:** General Affairs Supervisor, General Affairs Staff, Cleaner

1. Open `https://sigap.suryagroup.app/login`.
2. Login with your test account credentials.
3. Confirm you land inside SIGaP and can see the authenticated UI.

**Pass criteria:**
- Login completes successfully for all role types.

**Fail criteria:**
- Login error, redirect loop, or forbidden error.

### Test 2 — Unauthorized access (negative test)

**Role:** Regular User (no facility permissions)

1. While logged in as a Regular User, try to access `https://sigap.suryagroup.app/facility`.
2. Confirm you receive a 403 Forbidden error.

**Expected error:**
- `403 | This action is unauthorized.`

**Pass criteria:**
- Access is denied with 403 error.

**Fail criteria:**
- Page loads successfully (security breach).

---

## Navigation Smoke

### Test 3 — Facility pages load

**Role:** General Affairs Supervisor

Open each page and confirm it loads (no 500/403):

- `https://sigap.suryagroup.app/facility` (Dashboard)
- `https://sigap.suryagroup.app/facility/schedules`
- `https://sigap.suryagroup.app/facility/schedules/create`
- `https://sigap.suryagroup.app/facility/tasks`
- `https://sigap.suryagroup.app/facility/approvals`
- `https://sigap.suryagroup.app/facility/requests`
- `https://sigap.suryagroup.app/facility/request` (Public request form)
- `https://sigap.suryagroup.app/reports/facility/daily`
- `https://sigap.suryagroup.app/reports/facility/weekly`

**Pass criteria:**
- All pages load and show content or valid empty-state.

**Fail criteria:**
- Any 500 error, 403 error, or blank page.

---

## Cleaning Schedules (Recurring Tasks)

### Test 4 — Create daily cleaning schedule

**Role:** General Affairs Supervisor (with facility.schedules.create permission)

1. Go to `https://sigap.suryagroup.app/facility/schedules`.
2. Click **Create Schedule** button.
3. Fill in the form:
   - **Location**: Select any location (e.g., "Production Floor")
   - **Name**: `UAT Daily Cleaning 20251228`
   - **Description**: `UAT: Test daily cleaning schedule`
   - **Frequency Type**: `daily`
   - **Scheduled Time**: `08:00`
   - **Start Time**: `06:00`
   - **End Time**: `10:00`
   - **Active**: Checked
   - **Items**: Add at least 2 items:
     - Item 1:
       - **Asset**: Select any asset (optional)
       - **Item Name**: `UAT: Clean floors`
       - **Item Description**: `UAT: Sweep and mop all floors`
     - Item 2:
       - **Item Name**: `UAT: Clean windows`
       - **Item Description**: `UAT: Clean all windows and glass doors`
4. Click **Create Schedule** button.

**Expected success message:**
- `Cleaning schedule created successfully.`

**Expected behavior:**
- Redirects to schedule detail page (`https://sigap.suryagroup.app/facility/schedules/{id}`).
- Schedule details are displayed correctly.
- Items are listed in order.

**Pass criteria:**
- Schedule created successfully.
- All fields saved correctly.
- Items are associated with the schedule.

**Fail criteria:**
- Creation fails or data not saved.

### Test 5 — Create weekly cleaning schedule

**Role:** General Affairs Supervisor

1. Go to `https://sigap.suryagroup.app/facility/schedules/create`.
2. Create a schedule with:
   - **Frequency Type**: `weekly`
   - **Frequency Config**: Select specific days (e.g., Monday, Wednesday, Friday)
   - Other fields similar to Test 4

**Expected success message:**
- `Cleaning schedule created successfully.`

**Pass criteria:**
- Weekly schedule created with correct frequency config.

### Test 6 — Create schedule with validation errors (negative test)

**Role:** General Affairs Supervisor

1. Go to `https://sigap.suryagroup.app/facility/schedules/create`.
2. Try to submit with:
   - **Items**: Empty (no items added)
   - Click **Create Schedule**.

**Expected validation error:**
- `The items field must have at least 1 items.` or similar validation message.

**Pass criteria:**
- Validation error displays correctly.
- Schedule is not created.

**Fail criteria:**
- Invalid data is accepted.

### Test 7 — View schedule details

**Role:** General Affairs Staff (with facility.schedules.view permission)

1. Go to `https://sigap.suryagroup.app/facility/schedules`.
2. Click on a schedule name to view details.

**Expected behavior:**
- Schedule details page loads at `https://sigap.suryagroup.app/facility/schedules/{id}`.
- Shows schedule information, items, and recent tasks generated from this schedule.
- Shows unresolved alerts if any.

**Pass criteria:**
- All schedule information displays correctly.

### Test 8 — Edit existing schedule

**Role:** General Affairs Supervisor (with facility.schedules.edit permission)

1. Go to `https://sigap.suryagroup.app/facility/schedules`.
2. Click **Edit** button for a test schedule.
3. Update:
   - **Name**: `UAT Daily Cleaning 20251228 UPDATED`
   - Add a new item or modify existing item description.
4. Click **Update Schedule** button.

**Expected success message:**
- `Cleaning schedule updated successfully. Note: Changes only affect new tasks generated after midnight.`

**Expected behavior:**
- Redirects to schedule detail page.
- Updated information is displayed.

**IMPORTANT NOTE:**
- Changes to schedule **do NOT affect existing tasks** already generated for today.
- Only tasks generated after midnight will reflect the changes.

**Pass criteria:**
- Schedule updated successfully.
- Warning message about existing tasks is displayed.

**Fail criteria:**
- Update fails or changes don't persist.

### Test 9 — Delete cleaning schedule

**Role:** General Affairs Supervisor (with facility.schedules.delete permission)

1. Go to `https://sigap.suryagroup.app/facility/schedules`.
2. Click **Edit** for a test schedule.
3. Click **Delete Schedule** button.
4. Confirm deletion in the browser dialog.

**Expected success message:**
- `Cleaning schedule deleted successfully.`

**Expected behavior:**
- Redirects to schedules index page.
- Schedule is removed from the list.

**Pass criteria:**
- Schedule deleted successfully.

**Fail criteria:**
- Deletion fails or schedule still appears in list.

---

## Cleaning Tasks (Daily Operations)

**IMPORTANT CONTEXT:**
- Tasks are **auto-generated daily** by the `cleaning:generate-tasks` console command (runs at midnight).
- Each active schedule generates tasks for today based on frequency config.
- Tasks are assigned to cleaners using **round-robin distribution**.
- Task numbers follow format: `CT-{YYMMDD}-{0001}`

### Test 10 — View daily tasks list

**Role:** General Affairs Staff (with facility.tasks.view permission)

1. Go to `https://sigap.suryagroup.app/facility/tasks`.
2. Confirm tasks are displayed for today's date.
3. Filter by:
   - **Date**: Select a specific date
   - **Location**: Select one or more locations
   - **Status**: Select a status (pending, in-progress, completed, approved, rejected, missed)
4. Apply filters.

**Expected behavior:**
- Tasks are filtered correctly based on selected criteria.
- Task list shows: task number, location, item name, assigned cleaner, status.

**Pass criteria:**
- Task list loads and filters work correctly.

**Fail criteria:**
- 500 error, tasks don't load, or filters don't work.

### Test 11 — View task details

**Role:** General Affairs Staff

1. Go to `https://sigap.suryagroup.app/facility/tasks`.
2. Click on a task number to view details.

**Expected behavior:**
- Task details page loads at `https://sigap.suryagroup.app/facility/tasks/{id}`.
- Shows: task number, scheduled date, location, asset, item name/description, assigned cleaner, status, timestamps.
- Shows submission details if task has been submitted (before/after photos, notes, approval status).

**Pass criteria:**
- All task information displays correctly.

**Fail criteria:**
- 403 error or missing information.

---

## Task Assignment & Reassignment

### Test 12 — Bulk reassign tasks

**Role:** General Affairs Supervisor (with facility.tasks.bulk-assign permission)

1. Go to `https://sigap.suryagroup.app/facility/tasks`.
2. Click **Bulk Reassign** button.
3. Fill in the form:
   - **From User**: Select a cleaner who has tasks assigned
   - **To User**: Select a different cleaner
   - **Start Date**: Leave blank or select a specific date
4. Click **Reassign** button.

**Expected success message:**
- `Successfully reassigned {N} task(s).`

**Expected behavior:**
- Tasks are transferred from one cleaner to another.
- Affected tasks now show the new assigned cleaner.

**Pass criteria:**
- Tasks reassigned successfully.
- Count is accurate.

**Fail criteria:**
- Reassignment fails or tasks remain with original cleaner.

### Test 13 — Bulk reassign with validation error (negative test)

**Role:** General Affairs Supervisor

1. Try to bulk reassign with:
   - **From User**: Select a cleaner
   - **To User**: Select the **same cleaner** (same as From User)
   - Click **Reassign**.

**Expected validation error:**
- `The to user id field and from user id must be different.`

**Pass criteria:**
- Validation error displays correctly.

**Fail criteria:**
- Invalid reassignment is accepted.

---

## Task Execution (Cleaner Workflow)

### Test 14 — Cleaner views "My Tasks"

**Role:** Cleaner (with facility.tasks.view permission)

1. Login as a Cleaner role user.
2. Go to `https://sigap.suryagroup.app/facility/tasks/my-tasks`.

**Expected behavior:**
- Shows tasks assigned to the logged-in cleaner for today.
- Tasks are grouped by status (priority: pending → in-progress → completed).
- Shows other unassigned or other cleaners' tasks grouped by location (for context).

**Pass criteria:**
- "My Tasks" page loads with correct tasks.

**Fail criteria:**
- 403 error or shows tasks for wrong user.

### Test 15 — Start a cleaning task

**Role:** Cleaner (with facility.tasks.complete permission)

1. On "My Tasks" page, find a task with status **pending**.
2. Click **Start Task** button.

**Expected success message:**
- `Task started. Please take a before photo.`

**Expected behavior:**
- Task status changes to **in-progress**.
- Task is "locked" to the current user (no other cleaner can start it).
- Redirects to task submission page (`https://sigap.suryagroup.app/facility/tasks/{id}/submit`).

**Pass criteria:**
- Task status updated to in-progress.
- Redirected to submission page.

**Fail criteria:**
- Status doesn't change or submission page doesn't load.

### Test 16 — Task locking (negative test)

**Role:** Cleaner 2 (different user)

1. Login as a different Cleaner.
2. Try to access a task that Cleaner 1 started (in-progress status).
3. Try to start or submit that task.

**Expected error:**
- `You cannot submit this task.` or similar error message.

**Pass criteria:**
- Cleaner 2 cannot start/submit Cleaner 1's locked task.

**Fail criteria:**
- Task can be started by multiple users (locking mechanism broken - **CRITICAL SECURITY ISSUE**).

---

## Task Submission with Photos & GPS

### Test 17 — Submit task with before and after photos

**Role:** Cleaner (with facility.tasks.complete permission)

1. After starting a task (Test 15), you should be on the submission page.
2. Capture or upload:
   - **Before Photo**: Take photo using device camera or upload image
   - **Before GPS**: GPS coordinates should be auto-captured if device supports it
   - **After Photo**: Take photo using device camera or upload image
   - **After GPS**: GPS coordinates should be auto-captured if device supports it
   - **Notes**: `UAT: Test submission with photos`
3. Click **Submit Task** button.

**Expected success message:**
- `Task submitted successfully!`

**Expected behavior:**
- Redirects to "My Tasks" page.
- Task status changes to **completed**.
- Photos are uploaded to storage (S3).
- Photos are **watermarked** with:
  - BEFORE/AFTER label
  - Timestamp (WIB timezone)
  - Task number
  - Location name
  - Cleaner name
  - GPS coordinates (if available)
- Both watermarked and original photos are saved.
- Submission record is created.
- Approval record is created with status **pending**.

**Mandatory Photo Watermark Check (pass/fail):**
- View the submitted task details as GA staff.
- Click on before/after photos.
- Verify watermarks are present and contain:
  - Correct BEFORE/AFTER label
  - Correct timestamp
  - Correct task number
  - Correct location name
  - Correct cleaner name
  - GPS coordinates (if GPS was captured)

**Pass criteria:**
- Task submitted successfully.
- Photos uploaded with correct watermarks.
- Approval created with pending status.

**Fail criteria:**
- Submission fails.
- Photos missing or not watermarked.
- Incorrect watermark information.

### Test 18 — Submit task with missing photos (negative test)

**Role:** Cleaner

1. Start a new task.
2. Try to submit without uploading before or after photo.
3. Click **Submit**.

**Expected validation error:**
- `The before photo field is required.` and/or `The after photo field is required.`

**Pass criteria:**
- Validation error displays.
- Task cannot be submitted without photos.

**Fail criteria:**
- Task submitted without photos (photo requirement not enforced).

---

## Cleaning Approvals & SLA Management

**IMPORTANT CONTEXT:**
- **Approval Deadline**: Submission time + 1 day at 9:00 AM (24-hour SLA window)
- **SLA Status**:
  - **On-time** (green): Deadline not passed yet
  - **Warning** (yellow): < 24 hours overdue
  - **Critical** (red): > 24 hours overdue
- **Random Flagging**: 15% (average, between 10-20%) of submissions are randomly flagged for mandatory review.

### Test 19 — View pending approvals

**Role:** General Affairs Staff (with facility.submissions.review permission)

1. Go to `https://sigap.suryagroup.app/facility/approvals`.
2. Select a date (default: yesterday).
3. View pending approvals list.

**Expected behavior:**
- Shows all pending approvals for the selected date.
- Each approval shows:
  - Task number
  - Location
  - Submitted by (cleaner name)
  - Submitted at (timestamp)
  - Hours overdue
  - SLA status (on-time, warning, critical)
  - Flagged indicator (if flagged for review)
- List is sorted by hours_overdue (most overdue first).
- Statistics displayed:
  - Total pending
  - Flagged count
  - Reviewed flagged count

**Pass criteria:**
- Approvals list loads with correct information.
- SLA status is color-coded correctly.

**Fail criteria:**
- 500 error or incorrect SLA calculations.

### Test 20 — Review and approve a submission

**Role:** General Affairs Staff (with facility.submissions.approve permission)

1. On the approvals list, click **Review** for a pending approval.
2. View submission details:
   - Task information
   - Before photo (with watermark)
   - After photo (with watermark)
   - Cleaner notes
   - GPS coordinates (if available)
   - SLA status
3. Optionally add **Notes**: `UAT: Approved, good work`
4. Click **Approve** button.

**Expected success message:**
- `Submission approved successfully.`

**Expected behavior:**
- Redirects to approvals index page.
- Approval status changes to **approved**.
- Task status changes to **approved**.
- Approval is removed from pending list.

**Pass criteria:**
- Approval processed successfully.
- Statuses updated correctly.

**Fail criteria:**
- Approval fails or statuses don't update.

### Test 21 — Reject a submission

**Role:** General Affairs Staff (with facility.submissions.approve permission)

1. On the approvals list, click **Review** for a pending approval.
2. Add **Notes**: `UAT: Rejected - photos unclear, please redo` (required for rejection)
3. Click **Reject** button.

**Expected success message:**
- `Submission rejected.`

**Expected behavior:**
- Redirects to approvals index page.
- Approval status changes to **rejected**.
- Task status changes to **rejected**.
- Rejection notes are saved.

**Pass criteria:**
- Rejection processed successfully.
- Notes are required and saved.

**Fail criteria:**
- Can reject without notes (validation not enforced).

### Test 22 — Review flagged submission

**Role:** General Affairs Supervisor

1. On the approvals list, filter for **Flagged Only** submissions.
2. Click **Review** for a flagged approval.

**Expected behavior:**
- Flagged indicator is displayed prominently.
- When you open the review page, `reviewed_at` timestamp is set automatically (marks it as reviewed).
- This contributes to the 10% review threshold for mass approval.

**Mandatory WhatsApp notification (pass/fail):**
- General Affairs Supervisor should have received a WhatsApp notification when the submission was flagged.
- Expected message content:
  - Subject: Flagged task notification
  - Task number
  - Location
  - Cleaner name
  - Submission timestamp

**Pass criteria:**
- Flagged submission identified correctly.
- `reviewed_at` timestamp set when opened.
- WhatsApp notification was received (when flagged).

**Fail criteria:**
- Flagging not working or notification not received.

---

## Mass Approval & Quality Control

### Test 23 — Attempt mass approval without meeting 10% review threshold (negative test)

**Role:** General Affairs Supervisor (with facility.submissions.approve permission)

1. Go to `https://sigap.suryagroup.app/facility/approvals`.
2. Select a date with flagged submissions where < 10% have been reviewed.
3. Click **Mass Approve All** button (or similar).

**Expected error:**
- `You must review at least 10% of flagged tasks before mass approval. Currently reviewed: {X} of {Y} ({Z}%)`

**Pass criteria:**
- Mass approval is blocked.
- Error message displays the exact review threshold status.

**Fail criteria:**
- Mass approval proceeds without meeting threshold (**CRITICAL QUALITY CONTROL FAILURE**).

### Test 24 — Mass approve after meeting 10% review threshold

**Role:** General Affairs Supervisor

1. On the approvals list, identify all flagged submissions.
2. Review at least 10% of the flagged submissions (click Review and view the submission).
3. Return to the approvals list.
4. Click **Mass Approve All** button.
5. Confirm the mass approval action.

**Expected success message:**
- `Successfully approved {N} submission(s).`

**Expected behavior:**
- All pending approvals for the date are approved.
- All corresponding tasks change to **approved** status.
- Approvals are removed from pending list.

**Pass criteria:**
- Mass approval succeeds.
- All pending approvals are processed.

**Fail criteria:**
- Mass approval fails or not all approvals are processed.

---

## Cleaning Requests (Ad-hoc/Guest Requests)

### Test 25 — Submit cleaning request as guest (public)

**Role:** Guest/Public User (no authentication)

1. Open `https://sigap.suryagroup.app/facility/request` in an incognito browser window (not logged in).
2. Fill in the public request form:
   - **Requester Name**: `UAT Guest 20251228`
   - **Requester Phone**: `628123456789`
   - **Location**: Select any location
   - **Request Type**: `cleaning`
   - **Description**: `UAT: Test guest cleaning request - spill in hallway`
   - **Photo**: Upload image (optional, max 5MB)
   - **Cloudflare Turnstile CAPTCHA**: Complete the CAPTCHA
3. Click **Submit Request** button.

**Expected success message:**
- `Your request has been submitted successfully! Request number: {CR-YYMMDD-####}`

**Expected behavior:**
- Request is created with status **pending**.
- Request number is displayed to the guest.
- Photo is uploaded to storage (if provided).
- No authentication required.

**Pass criteria:**
- Request submitted successfully.
- Request number generated correctly.

**Fail criteria:**
- Submission fails or CAPTCHA not enforced.

### Test 26 — Submit repair request as authenticated user

**Role:** Regular User (authenticated)

1. Login as any authenticated user.
2. Go to `https://sigap.suryagroup.app/facility/request`.
3. Fill in the form:
   - **Requester Name**: Auto-filled from user profile
   - **Requester Phone**: Auto-filled from user profile
   - **Location**: Select any location
   - **Request Type**: `repair`
   - **Description**: `UAT: Test repair request - broken chair`
   - **Photo**: Upload image
   - **CAPTCHA**: Complete the CAPTCHA
4. Click **Submit Request** button.

**Expected success message:**
- `Your request has been submitted successfully! Request number: {CR-YYMMDD-####}`

**Expected behavior:**
- Request created with requester info from user profile.
- `requester_user_id` is set to the authenticated user's ID.

**Pass criteria:**
- Request submitted successfully with user association.

### Test 27 — GA staff views requests list

**Role:** General Affairs Staff (with facility.requests.view permission)

1. Go to `https://sigap.suryagroup.app/facility/requests`.
2. View all submitted requests.
3. Filter by:
   - **Status**: pending, completed
   - **Type**: cleaning, repair

**Expected behavior:**
- Requests are listed with: request number, requester name, location, type, description, status.
- Filters work correctly.

**Pass criteria:**
- Requests list loads and filters work.

### Test 28 — Handle cleaning request (convert to task)

**Role:** General Affairs Staff (with facility.requests.handle permission)

1. On the requests list, click **Handle** for a cleaning request with status **pending**.
2. Fill in the handling form:
   - **Scheduled Date**: Select today or future date
   - **Assigned To**: Select a cleaner
   - **Item Name**: `UAT: Ad-hoc cleaning from request {CR-####}`
   - **Handling Notes**: `UAT: Converted to cleaning task`
3. Click **Create Task** button.

**Expected success message:**
- `Cleaning task created successfully.`

**Expected behavior:**
- New cleaning task is created with:
  - Task number: `CT-YYMMDD-####`
  - `cleaning_schedule_id`: `0` (special ID for ad-hoc tasks)
  - `cleaning_schedule_item_id`: `0`
  - Location from the request
  - Assigned to selected cleaner
  - Status: **pending**
- Request status changes to **completed**.
- Request is linked to the created task.

**Mandatory WhatsApp notification (pass/fail):**
- The assigned cleaner should receive a WhatsApp notification.
- Expected message content:
  - Subject: New task assignment
  - Task number
  - Location
  - Item name
  - Scheduled date

**Pass criteria:**
- Task created successfully.
- Request marked as completed.
- WhatsApp notification sent to cleaner.

**Fail criteria:**
- Task creation fails.
- Notification not sent.

### Test 29 — Handle repair request (convert to work order)

**Role:** General Affairs Staff (with facility.requests.handle permission)

1. On the requests list, click **Handle** for a repair request with status **pending**.
2. Fill in the handling form:
   - **Priority**: Select priority (low, medium, high, critical)
   - **Description**: `UAT: Converted to work order from request {CR-####}`
   - **Handling Notes**: `UAT: Sent to maintenance module`
3. Click **Create Work Order** button.

**Expected success message:**
- `Work order created successfully in Maintenance module.`

**Expected behavior:**
- New work order is created in the Maintenance module with:
  - Work order number: `WO-YYMMDD-####`
  - Priority and description from form
  - Linked to the cleaning request
- Request status changes to **completed**.

**Pass criteria:**
- Work order created successfully.
- Request marked as completed.

**Fail criteria:**
- Work order creation fails.

---

## Schedule Alerts (Asset Issues)

**IMPORTANT CONTEXT:**
- Alerts are auto-generated by the `cleaning:generate-tasks` command.
- When a schedule item references an asset that is **disposed** or **inactive**, an alert is created instead of a task.
- Alerts notify General Affairs staff about asset issues.

### Test 30 — View schedule alerts on dashboard

**Role:** General Affairs Supervisor (with facility.dashboard.view permission)

1. Go to `https://sigap.suryagroup.app/facility` (Dashboard).
2. Scroll to the **Unresolved Schedule Alerts** section.

**Expected behavior:**
- Shows alerts for disposed or inactive assets.
- Each alert shows:
  - Schedule name
  - Schedule item
  - Asset code and name
  - Alert type (asset_disposed or asset_inactive)
  - Detected at timestamp
  - Resolve button

**Pass criteria:**
- Alerts are displayed correctly.

**Fail criteria:**
- Alerts don't show or incorrect information.

### Test 31 — Receive WhatsApp notification for schedule alert

**Role:** General Affairs Staff or Supervisor

**Expected behavior (automated):**
- When an alert is detected (asset disposed/inactive), a WhatsApp notification is sent to General Affairs role users.

**Mandatory WhatsApp notification (pass/fail):**
- Expected message content:
  - Subject: Schedule alert
  - Schedule name
  - Asset code and name
  - Alert type (disposed or inactive)
  - Instruction to resolve the issue

**Pass criteria:**
- WhatsApp notification received when alert is created.

**Fail criteria:**
- No notification sent.

### Test 32 — Resolve schedule alert

**Role:** General Affairs Supervisor (with facility.alerts.resolve permission)

1. On the dashboard or schedule detail page, find an unresolved alert.
2. Click **Resolve** button.
3. Confirm resolution.

**Expected success message:**
- `Alert resolved successfully.` or similar message.

**Expected behavior:**
- Alert is marked as resolved.
- Alert is removed from unresolved list.

**Pass criteria:**
- Alert resolved successfully.

**Fail criteria:**
- Resolution fails or alert still shows as unresolved.

---

## WhatsApp Notifications

**IMPORTANT CONTEXT:**
- WhatsApp is the primary notification channel.
- Pushover is the fallback if WhatsApp delivery fails.
- Notifications are sent via `CleaningService` methods.

### Notification Types Summary

| Notification Type | Trigger | Recipient | Tested In |
|-------------------|---------|-----------|-----------|
| Task Assignment | New task assigned to cleaner | Assigned cleaner | Test 28 |
| Task Reminder | 2 hours before scheduled time | Assigned cleaner | Test 33 (below) |
| Flagged for Review | Submission randomly flagged | General Affairs Supervisor | Test 22 |
| Schedule Alert | Asset disposed/inactive | General Affairs Staff | Test 31 |
| Missed Tasks Alert | Tasks not completed by end of day | General Affairs Supervisor | Test 34 (below) |

### Test 33 — Task reminder notification

**Role:** Cleaner

**Expected behavior (automated):**
- The `cleaning:send-reminders` command runs periodically (default: every 2 hours).
- Cleaners with pending tasks scheduled within 2 hours receive WhatsApp reminders.

**Mandatory WhatsApp notification (pass/fail):**
- Expected message content:
  - Subject: Task reminder
  - Task number
  - Location
  - Item name
  - Scheduled time

**Testing approach:**
- Coordinate with system admin to run the command manually: `php artisan cleaning:send-reminders --hours=24`
- This will send reminders for tasks scheduled within 24 hours.

**Pass criteria:**
- WhatsApp reminder received with correct information.

**Fail criteria:**
- No reminder sent or incorrect information.

### Test 34 — Missed tasks alert notification

**Role:** General Affairs Supervisor

**Expected behavior (automated):**
- The `cleaning:generate-tasks` command runs daily at midnight.
- Tasks with status **pending** or **in-progress** from the previous day are marked as **missed**.
- A WhatsApp notification is sent to General Affairs Supervisor listing all missed tasks.

**Mandatory WhatsApp notification (pass/fail):**
- Expected message content:
  - Subject: Missed tasks alert
  - List of missed task numbers
  - Locations
  - Assigned cleaners

**Testing approach:**
- Create tasks for yesterday (manually set `scheduled_date` to yesterday via tinker or admin).
- Leave them as pending/in-progress.
- Run the command manually: `php artisan cleaning:generate-tasks`
- Check for WhatsApp notification.

**Pass criteria:**
- Missed tasks marked correctly.
- WhatsApp notification sent with all missed tasks.

**Fail criteria:**
- Tasks not marked as missed or no notification.

### Test 35 — WhatsApp fallback to Pushover

**Role:** General Affairs Supervisor

**Expected behavior:**
- If a user has no `mobilephone_no` or WhatsApp sending fails, a Pushover notification is sent to administrators.

**Testing approach:**
- Assign a task to a cleaner user with invalid or no `mobilephone_no`.
- Monitor Pushover notifications.

**Expected Pushover notification:**
- Title: `WhatsApp Failure: Task Assignment Notification`
- Message: Original WhatsApp message content
- Chat ID that failed

**Pass criteria:**
- Pushover fallback notification received when WhatsApp fails.

**Fail criteria:**
- Silent failure (no notification at all).

---

## Reports & Dashboard

### Test 36 — View facility dashboard

**Role:** General Affairs Supervisor (with facility.dashboard.view permission)

1. Go to `https://sigap.suryagroup.app/facility`.
2. View dashboard sections:
   - **Cleaner Performance Ranking**: Top cleaners by completion rate
   - **Completion Statistics**: Total, completed, pending, missed tasks
   - **SLA Compliance**: Approval SLA stats
   - **Tasks by Location**: Completion rates per location
   - **Pending Approvals**: Top 10 overdue approvals
   - **Unresolved Schedule Alerts**: Asset issues
   - **Weekly Trend**: 7-week completion trend chart

**Expected behavior:**
- All dashboard widgets load with data.
- Date range filter works (start_date, end_date).

**Pass criteria:**
- Dashboard loads with correct statistics.

**Fail criteria:**
- 500 error or widgets don't load.

### Test 37 — View daily report

**Role:** General Affairs Staff (with facility.reports.view permission)

1. Go to `https://sigap.suryagroup.app/reports/facility/daily`.
2. Select a date.
3. View the daily report.

**Expected behavior:**
- Shows all tasks for the selected date grouped by location.
- Shows completion status, assigned cleaners, submission status.

**Pass criteria:**
- Daily report loads with correct data.

### Test 38 — Export daily report to PDF

**Role:** General Affairs Staff

1. On the daily report page, click **Export to PDF** button.

**Expected behavior:**
- PDF file downloads with filename: `facility-daily-report-{date}.pdf`.
- PDF contains all task information in a formatted table.

**Pass criteria:**
- PDF downloads and contains correct data.

**Fail criteria:**
- PDF generation fails or contains errors.

### Test 39 — View weekly report

**Role:** General Affairs Staff (with facility.reports.view permission)

1. Go to `https://sigap.suryagroup.app/reports/facility/weekly`.
2. Select a week (start date).
3. View the weekly report.

**Expected behavior:**
- Shows aggregated statistics for the week.
- Shows completion rates by day, location, cleaner.

**Pass criteria:**
- Weekly report loads with correct data.

### Test 40 — Export weekly report to PDF

**Role:** General Affairs Staff

1. On the weekly report page, click **Export to PDF** button.

**Expected behavior:**
- PDF file downloads with filename: `facility-weekly-report-{week}.pdf`.
- PDF contains weekly statistics and charts.

**Pass criteria:**
- PDF downloads and contains correct data.

---

## Failure Reporting Template

Use this format when reporting a test failure:

```
**Test Number:** [e.g., Test 17]
**Test Title:** [e.g., Submit task with before and after photos]
**Role:** [e.g., Cleaner]
**Status:** FAIL

**What happened:**
- [Describe what actually occurred]

**Expected:**
- [What should have happened according to the runbook]

**Screenshots/Evidence:**
- [Attach or link to screenshot]

**Error Messages:**
- [Copy exact error message if any]

**WhatsApp/Pushover Notification:**
- [If notification test failed, note whether notification was received and what content was missing/incorrect]

**Browser/Environment:**
- [e.g., Chrome 120, iOS Safari, Production URL]

**Time of Test:**
- [e.g., 2025-12-28 14:30 WIB]
```

---

**End of Facility Management UAT Runbook**
