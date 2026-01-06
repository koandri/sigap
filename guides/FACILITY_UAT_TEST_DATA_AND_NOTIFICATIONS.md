# Facility Management Production UAT Test Data & Notification Checklist

**SIGaP Facility Management Module**
**Test Data Conventions & Mandatory Notification Checks**
**Version 1.0**

---

## Test Data Conventions

Use these naming conventions so test data is easy to identify and delete after UAT.

### Cleaning Schedule Naming

**Name**
- `UAT Daily Cleaning <YYYYMMDD>`
- `UAT Weekly Cleaning <YYYYMMDD>`
- `UAT Monthly Cleaning <YYYYMMDD>`

**Description**
- Start with: `UAT:`
- Example: `UAT: Test daily cleaning schedule for production floor`

**Schedule Items**
- **Item Name**: `UAT: <description>`
- Examples:
  - `UAT: Clean floors`
  - `UAT: Clean windows`
  - `UAT: Sanitize equipment`

### Cleaning Request Naming

**Requester Name** (for guest requests)
- `UAT Guest <YYYYMMDD>`
- Example: `UAT Guest 20251228`

**Requester Phone** (for guest requests)
- Use format: `628XXXXXXXXXX` (must start with 628)
- Use test numbers that can receive WhatsApp: `628123456789`, `628123456790`, etc.

**Description**
- Start with: `UAT:`
- Examples:
  - `UAT: Test guest cleaning request - spill in hallway`
  - `UAT: Test repair request - broken chair`

### Task Notes & Handling Notes

When adding notes to tasks, submissions, or request handling:
- Start with: `UAT:`
- Examples:
  - `UAT: Test submission with photos`
  - `UAT: Converted to cleaning task`
  - `UAT: Approved, good work`
  - `UAT: Rejected - photos unclear, please redo`

---

## Auto-Generated Identifiers

The system auto-generates these identifiers. Use them for tracking:

### Task Numbers
- Format: `CT-{YYMMDD}-{0001}`
- Example: `CT-251228-0001`
- Sequential per day

### Request Numbers
- Format: `CR-{YYMMDD}-{0001}`
- Example: `CR-251228-0001`
- Sequential per day

### Work Order Numbers (from repair requests)
- Format: `WO-{YYMMDD}-{0001}`
- Example: `WO-251228-0001`
- Sequential per day

---

## Mandatory WhatsApp Notification Checks

All WhatsApp notifications are mandatory pass/fail. If a notification is not received or contains incorrect information, mark the test as FAIL.

### A) Task Assignment Notification

**Trigger:** When GA Staff handles a cleaning request and creates a task (Test 28)

**Recipient:** Assigned cleaner (sent to their `mobilephone_no`)

**Expected WhatsApp message content:**
- Subject: New task assignment
- Task number (e.g., `CT-251228-0001`)
- Location name
- Item name
- Scheduled date

**PASS:**
- Notification received within 30 seconds of task creation
- All required fields present
- Correct task information
- Correct cleaner recipient

**FAIL:**
- Notification not received
- Missing any required fields
- Wrong task information
- Wrong recipient

---

### B) Task Reminder Notification

**Trigger:** `cleaning:send-reminders` command runs (default: every 2 hours, sends reminders for tasks scheduled within 2 hours)

**Recipient:** Assigned cleaner with pending tasks

**Expected WhatsApp message content:**
- Subject: Task reminder
- Task number
- Location name
- Item name
- Scheduled time
- Reminder message (e.g., "Your task is starting soon")

**PASS:**
- Notification received at the correct time
- All required fields present
- Correct task information

**FAIL:**
- Notification not sent
- Sent at wrong time
- Missing information

**Testing Note:**
- Coordinate with system admin to run manually: `php artisan cleaning:send-reminders --hours=24`
- This sends reminders for tasks scheduled within 24 hours (easier to test)

---

### C) Flagged Submission Notification

**Trigger:** Submission is randomly flagged for review (15% average, 10-20% range)

**Recipient:** General Affairs Supervisor role

**Expected WhatsApp message content:**
- Subject: Submission flagged for review
- Task number
- Location name
- Cleaner name
- Submission timestamp
- Instruction to review before mass approval

**PASS:**
- Notification received when submission is flagged
- All required fields present
- Correct supervisor recipient(s)

**FAIL:**
- No notification when submission is flagged
- Missing fields
- Wrong recipient

**Testing Note:**
- Flagging is random (15% average)
- May need to create multiple submissions (10-20) to trigger at least one flagging
- Flagging occurs during `cleaning:generate-tasks` command run

---

### D) Schedule Alert Notification

**Trigger:** Asset disposal or inactivity detected during task generation

**Recipient:** General Affairs staff (users with facility.requests.handle or facility.schedules.view permission)

**Expected WhatsApp message content:**
- Subject: Schedule alert
- Schedule name
- Asset code and name
- Alert type:
  - "Asset has been disposed"
  - "Asset is inactive"
- Instruction to resolve the alert

**PASS:**
- Notification received when alert is created
- All required fields present
- Correct asset and schedule information
- Correct GA staff recipients

**FAIL:**
- No notification for asset issues
- Missing fields
- Wrong recipients

**Testing Note:**
- To trigger this alert:
  - Create a schedule with an item linked to an asset
  - Mark the asset as disposed or inactive (via asset management)
  - Run `php artisan cleaning:generate-tasks`
  - Alert should be created and notification sent

---

### E) Missed Tasks Alert Notification

**Trigger:** `cleaning:generate-tasks` command marks tasks as missed (runs daily at midnight)

**Recipient:** General Affairs Supervisor role

**Expected WhatsApp message content:**
- Subject: Missed tasks alert
- Date (yesterday's date)
- List of missed task numbers
- Locations
- Assigned cleaners
- Count of missed tasks

**PASS:**
- Notification received with all missed tasks
- All required fields present
- Accurate task list and count

**FAIL:**
- No notification for missed tasks
- Incomplete task list
- Incorrect information

**Testing Note:**
- To trigger this alert:
  - Create tasks for yesterday (manually via tinker or admin)
  - Leave them as pending or in-progress status
  - Run `php artisan cleaning:generate-tasks`
  - Tasks should be marked as missed and notification sent

---

## Mandatory Pushover Notification Checks

Pushover is a fallback notification system when WhatsApp delivery fails.

### F) WhatsApp Failure Notification (Pushover)

**Trigger:** When WhatsApp delivery fails for any notification type

**Recipient:** System administrators (via Pushover)

**Expected Pushover notification:**

**Title:**
- `WhatsApp Failure: <NotificationType> Notification`
- Examples:
  - `WhatsApp Failure: Task Assignment Notification`
  - `WhatsApp Failure: Schedule Alert Notification`

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

**Testing Note:**
- To trigger Pushover fallback:
  - Assign task to a user with invalid `mobilephone_no` (e.g., no number set)
  - Or temporarily break WhatsApp service connection
  - Verify Pushover notification is sent

---

## Photo Watermarking Requirements

All submitted task photos (before and after) must be watermarked. This is a mandatory pass/fail check.

### Watermark Components

Each watermarked photo must include:

1. **Label**: "BEFORE" or "AFTER" (in large text, usually top-left)
2. **Timestamp**: Date and time in WIB timezone (e.g., "2025-12-28 14:30 WIB")
3. **Task Number**: (e.g., "CT-251228-0001")
4. **Location**: Location name (e.g., "Production Floor")
5. **Operator**: Cleaner name (e.g., "John Doe")
6. **GPS Coordinates**: Latitude and longitude (if GPS was captured)

### Watermark Verification Steps

1. After a cleaner submits a task (Test 17), log in as GA Staff.
2. Go to the task details page.
3. View the before and after photos (click on them to see full size).
4. Verify all watermark components are present and correct.

### Storage Details

- **Watermarked photos**: Stored on S3 at `cleaning_submissions/watermarked/`
- **Original photos**: Stored on S3 at `cleaning_submissions/original/`
- Both versions are kept for audit purposes

### Watermarking Failure Handling

- If watermarking fails (library error, S3 error), the original photo is used as fallback.
- Task submission should still succeed (watermarking failure doesn't block submission).
- System logs should record watermarking failures.

**PASS:**
- Both photos have watermarks
- All components are present and correct
- Photos are accessible

**FAIL:**
- Photos missing watermarks
- Watermark information is incorrect (wrong task number, location, name, etc.)
- GPS missing when device captured it (or vice versa)

---

## SLA & Quality Control Business Rules

### Approval SLA (24-Hour Window)

**Deadline Calculation:**
- Submission time + 1 day at 9:00 AM
- Example:
  - Submitted: 2025-12-27 15:30
  - Deadline: 2025-12-28 09:00 (next day 9:00 AM)

**SLA Status:**
- **On-time** (green): Current time < deadline (0 hours overdue)
- **Warning** (yellow): 0 < hours overdue < 24 (deadline passed but within 24 hours)
- **Critical** (red): hours overdue >= 24 (more than 24 hours past deadline)

**Testing SLA Status:**
- Create submissions at different times relative to today to simulate different SLA statuses
- Verify color coding on the approvals list (green/yellow/red)
- Verify hours_overdue calculation is accurate

---

### Mass Approval Quality Control (10% Threshold)

**Rule:**
- Before mass approval can be performed, at least 10% of flagged submissions must be reviewed.

**Flagged Submissions:**
- System randomly flags 15% (average, 10-20% range) of daily submissions.
- Flagging occurs during `cleaning:generate-tasks` command run.
- Flagged submissions are marked with `is_flagged_for_review = true`.

**Reviewed Count:**
- When a GA staff member opens a flagged submission for review (Test 22), `reviewed_at` is set.
- Reviewed count = flagged submissions with `reviewed_at` NOT NULL.

**Threshold Check:**
- Percentage = (reviewed_count / flagged_count) * 100
- Must be >= 10% for mass approval to proceed

**Error Message:**
- If threshold not met: `You must review at least 10% of flagged tasks before mass approval. Currently reviewed: {X} of {Y} ({Z}%)`

**Testing:**
- Test 23: Attempt mass approval with < 10% reviewed (should FAIL)
- Test 24: Review 10%+ of flagged submissions, then mass approve (should PASS)

---

## Task Status Workflow

### Status Progression

| Current Status | Allowed Next Status | Action | Who |
|----------------|---------------------|--------|-----|
| pending | in-progress | Start task | Cleaner |
| in-progress | completed | Submit task with photos | Cleaner (who started it) |
| completed | approved | Approve submission | GA Staff |
| completed | rejected | Reject submission | GA Staff |
| pending | missed | Auto-marked by cron (not completed by end of day) | System |
| in-progress | pending | Auto-released by cron (inactive > 2 hours) | System |

### Special Cases

**Ad-hoc Tasks (from Requests):**
- `cleaning_schedule_id` = 0
- `cleaning_schedule_item_id` = 0
- Created by GA Staff handling cleaning requests

**Missed Tasks:**
- Tasks with status `pending` or `in-progress` from previous day
- Marked as `missed` by `cleaning:generate-tasks` command at midnight

**Released Tasks:**
- Tasks `in-progress` for > 2 hours are auto-released back to `pending`
- Allows task reassignment if cleaner abandons task

---

## Validation Rules Reference

### Cleaning Schedule Creation/Update

| Field | Rule | Error Message |
|-------|------|---------------|
| location_id | required, exists:locations,id | The selected location id is invalid. |
| name | required, string, max:255 | The name field is required. / The name may not be greater than 255 characters. |
| description | nullable, string | - |
| frequency_type | required, in:hourly,daily,weekly,monthly,yearly | The selected frequency type is invalid. |
| frequency_config | nullable, array | - |
| scheduled_time | nullable, date_format:H:i | The scheduled time does not match the format H:i. |
| start_time | nullable, date_format:H:i | The start time does not match the format H:i. |
| end_time | nullable, date_format:H:i | The end time does not match the format H:i. |
| is_active | boolean | - |
| items | required, array, min:1 | The items field must have at least 1 items. |
| items.*.asset_id | nullable, exists:assets,id | The selected items.X.asset id is invalid. |
| items.*.item_name | required, string, max:255 | The items.X.item name field is required. |
| items.*.item_description | nullable, string | - |

### Task Submission

| Field | Rule | Error Message |
|-------|------|---------------|
| before_photo | required, string (base64) | The before photo field is required. |
| before_gps | nullable, array | - |
| after_photo | required, string (base64) | The after photo field is required. |
| after_gps | nullable, array | - |
| notes | nullable, string, max:1000 | The notes may not be greater than 1000 characters. |

### Bulk Task Reassignment

| Field | Rule | Error Message |
|-------|------|---------------|
| from_user_id | required, exists:users,id | The selected from user id is invalid. |
| to_user_id | required, exists:users,id, different:from_user_id | The to user id field and from user id must be different. |
| start_date | nullable, date | The start date is not a valid date. |

### Cleaning Request Submission

| Field | Rule | Error Message |
|-------|------|---------------|
| requester_name | required, string, max:255 | The requester name field is required. |
| requester_phone | required, string, max:20 | The requester phone field is required. |
| location_id | required, exists:locations,id | The selected location id is invalid. |
| request_type | required, in:cleaning,repair | The selected request type is invalid. |
| description | required, string, max:1000 | The description field is required. |
| photo | nullable, image, max:5120 | The photo must be an image. / The photo may not be greater than 5120 kilobytes. |
| cf-turnstile-response | required, Rule::turnstile() | The CAPTCHA verification failed. Please try again. |

### Request Handling (Cleaning)

| Field | Rule | Error Message |
|-------|------|---------------|
| scheduled_date | required, date, after_or_equal:today | The scheduled date must be a date after or equal to today. |
| assigned_to | required, exists:users,id | The selected assigned to is invalid. |
| item_name | required, string, max:255 | The item name field is required. |
| handling_notes | nullable, string, max:1000 | - |

### Request Handling (Repair)

| Field | Rule | Error Message |
|-------|------|---------------|
| priority | required, in:low,medium,high,critical | The selected priority is invalid. |
| description | nullable, string, max:1000 | - |
| handling_notes | nullable, string, max:1000 | - |

### Approval/Rejection

| Field | Rule | Error Message |
|-------|------|---------------|
| notes (approve) | nullable, string, max:1000 | - |
| notes (reject) | **required**, string, max:1000 | The notes field is required. (for rejection) |

---

## Risk Areas & Special Attention

### Critical Security Risks

1. **Task Locking Mechanism (Test 16):**
   - Verify only the user who started a task can submit it.
   - Vulnerability: Multiple cleaners submitting the same task would create duplicate submissions.
   - Test: Cleaner 1 starts task → Cleaner 2 tries to submit → Should FAIL with 403 error.

2. **Unauthorized Access (Test 2):**
   - Verify non-GA/non-Cleaner users cannot access facility pages.
   - Vulnerability: Unauthorized users viewing or manipulating cleaning data.
   - Test: Regular User accesses facility URLs → Should get 403 Forbidden.

3. **Quality Control Threshold (Test 23):**
   - Verify 10% review threshold is enforced before mass approval.
   - Vulnerability: Mass approval without quality checks could approve poor work.
   - Test: Attempt mass approval with < 10% reviewed → Should FAIL with error message.

4. **Request CAPTCHA (Test 25):**
   - Verify Cloudflare Turnstile CAPTCHA is required for public requests.
   - Vulnerability: Spam or bot submissions.
   - Test: Submit request without completing CAPTCHA → Should FAIL.

### Data Integrity Risks

1. **Photo Watermarking (Test 17):**
   - Watermarks provide audit trail and prevent photo manipulation.
   - Risk: Photos without watermarks cannot be trusted.
   - Verify: All components present and correct.

2. **SLA Tracking Accuracy:**
   - Incorrect SLA calculations could miss overdue approvals.
   - Verify: Hours overdue and SLA status are calculated correctly (compare manual calculation with system).

3. **Task Assignment Round-Robin:**
   - Tasks should be distributed evenly among cleaners.
   - Verify: Over multiple days, cleaners receive similar task counts (check dashboard cleaner ranking).

### Notification Risks

1. **Silent Failures:**
   - WhatsApp fails but no Pushover fallback = no one knows about the failure.
   - Verify: Pushover notification sent when WhatsApp fails (Test 35).

2. **Missing Critical Alerts:**
   - Missed tasks or flagged submissions not notified = no follow-up action.
   - Verify: All notification types are sent and received.

---

## Post-UAT Cleanup

After UAT is complete, all test data should be removed:

### Schedules to Delete

Delete all cleaning schedules where:
- Name starts with `UAT Daily`, `UAT Weekly`, `UAT Monthly`, etc.

### Tasks to Delete

Delete all cleaning tasks where:
- Task number belongs to test dates (e.g., `CT-251228-*`)
- OR `item_name` starts with `UAT:`

### Requests to Delete

Delete all cleaning requests where:
- Request number belongs to test dates (e.g., `CR-251228-*`)
- OR `requester_name` starts with `UAT Guest`
- OR `description` starts with `UAT:`

### Submissions & Approvals to Delete

Delete all cleaning submissions and approvals where:
- Associated task is a test task (from above)

### Photos to Delete (S3)

Delete all photos in S3 buckets:
- `cleaning_submissions/watermarked/` with test task numbers
- `cleaning_submissions/original/` with test task numbers
- `cleaning_requests/` with test request numbers

**Cleanup Query Examples:**

```sql
-- Delete test schedules
DELETE FROM cleaning_schedules WHERE name LIKE 'UAT %';

-- Delete test tasks
DELETE FROM cleaning_tasks WHERE task_number LIKE 'CT-251228-%' OR item_name LIKE 'UAT:%';

-- Delete test requests
DELETE FROM cleaning_requests WHERE request_number LIKE 'CR-251228-%' OR requester_name LIKE 'UAT Guest%';

-- Delete test submissions (cascade will delete approvals)
DELETE FROM cleaning_submissions WHERE cleaning_task_id IN (
  SELECT id FROM cleaning_tasks WHERE task_number LIKE 'CT-251228-%'
);
```

**Important:** Verify no production data accidentally matches these patterns before running cleanup.

---

**End of Facility Management UAT Test Data & Notification Checklist**
