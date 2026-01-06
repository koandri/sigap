# Facility Management UAT Role Assignments

**SIGaP Facility Management Module**
**UAT Test Assignments by Role**
**Version 1.0**

---

## Overview

This document assigns specific tests from `FACILITY_UAT_RUNBOOK.md` to different user roles for parallel testing. Each tester should run tests assigned to their role.

**Important:**
- Test assignments are based on the permissions and responsibilities each role has.
- Some tests are negative tests (should fail) to verify security controls.
- All roles should verify WhatsApp notifications where applicable.
- Coordinate between roles for workflow tests (e.g., Cleaner submits → GA staff approves).

---

## A) General Affairs (GA) Supervisor (Full Facility Management)

**Role Description:** Has complete access to all facility management functions. Can create and manage cleaning schedules, assign and reassign tasks, approve submissions, perform mass approvals, resolve alerts, and view all reports. Receives notifications for flagged submissions and missed tasks.

**Permissions:**
- facility.dashboard.view
- facility.schedules.* (create, view, edit, delete)
- facility.tasks.* (view, assign, bulk-assign, complete)
- facility.submissions.* (review, approve)
- facility.requests.* (view, handle)
- facility.alerts.resolve
- facility.reports.view

**Run these tests from `FACILITY_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 3 (Facility pages load)
- Test 4 (Create daily cleaning schedule)
- Test 5 (Create weekly cleaning schedule)
- Test 6 (Create schedule with validation errors - negative)
- Test 7 (View schedule details)
- Test 8 (Edit existing schedule)
- Test 9 (Delete cleaning schedule)
- Test 10 (View daily tasks list)
- Test 11 (View task details)
- Test 12 (Bulk reassign tasks)
- Test 13 (Bulk reassign with validation error - negative)
- Test 22 (Review flagged submission)
- Test 23 (Attempt mass approval without 10% threshold - negative)
- Test 24 (Mass approve after meeting 10% threshold)
- Test 30 (View schedule alerts on dashboard)
- Test 31 (Receive WhatsApp notification for schedule alert)
- Test 32 (Resolve schedule alert)
- Test 34 (Missed tasks alert notification)
- Test 35 (WhatsApp fallback to Pushover)
- Test 36 (View facility dashboard)
- Test 37 (View daily report)
- Test 38 (Export daily report to PDF)
- Test 39 (View weekly report)
- Test 40 (Export weekly report to PDF)

**Extra responsibility:**
- **Critical:** Verify the 10% review threshold for mass approval is properly enforced (Test 23-24).
- Monitor all WhatsApp notifications for flagged submissions and missed tasks.
- Monitor Pushover fallback notifications when WhatsApp fails.
- Verify SLA compliance tracking is accurate on the dashboard.
- Test all schedule CRUD operations (create, read, update, delete).
- Test bulk task reassignment functionality.

---

## B) General Affairs (GA) Staff (Facility Operations)

**Role Description:** Handles day-to-day facility operations. Can view schedules and tasks, handle cleaning/repair requests (convert to tasks/work orders), review and approve submissions. Receives schedule alert notifications.

**Permissions:**
- facility.dashboard.view
- facility.schedules.view
- facility.tasks.view
- facility.submissions.review
- facility.submissions.approve
- facility.requests.view
- facility.requests.handle
- facility.reports.view

**Run these tests from `FACILITY_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 3 (Facility pages load)
- Test 7 (View schedule details)
- Test 10 (View daily tasks list)
- Test 11 (View task details)
- Test 19 (View pending approvals)
- Test 20 (Review and approve a submission)
- Test 21 (Reject a submission)
- Test 27 (GA staff views requests list)
- Test 28 (Handle cleaning request - convert to task)
- Test 29 (Handle repair request - convert to work order)
- Test 31 (Receive WhatsApp notification for schedule alert)
- Test 36 (View facility dashboard)
- Test 37 (View daily report)
- Test 38 (Export daily report to PDF)
- Test 39 (View weekly report)
- Test 40 (Export weekly report to PDF)

**Extra responsibility:**
- Verify approval workflow: review → approve/reject.
- Verify rejection requires notes (Test 21).
- Verify request handling workflow: request → task/work order conversion.
- Test both cleaning and repair request handling.
- Monitor WhatsApp notifications for schedule alerts.
- Verify SLA status is displayed correctly (on-time, warning, critical).

---

## C) Cleaner (Task Execution)

**Role Description:** Assigned to cleaning tasks. Views "My Tasks", starts tasks, submits tasks with before/after photos and GPS. Receives WhatsApp notifications for task assignments and reminders.

**Permissions:**
- facility.tasks.view
- facility.tasks.complete

**Run these tests from `FACILITY_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 14 (Cleaner views "My Tasks")
- Test 15 (Start a cleaning task)
- Test 16 (Task locking - negative test, coordinate with another Cleaner)
- Test 17 (Submit task with before and after photos)
- Test 18 (Submit task with missing photos - negative)
- Test 28 (Receive WhatsApp notification when task assigned - verify as recipient)
- Test 33 (Task reminder notification - verify as recipient)

**Extra responsibility:**
- **Critical:** Verify photo watermarking is working correctly (Test 17).
  - Check that watermarks contain: BEFORE/AFTER label, timestamp, task number, location, your name, GPS (if captured).
- **Critical:** Verify task locking mechanism works (Test 16).
  - Ensure only you can submit a task you started.
  - Coordinate with another Cleaner to test that they cannot submit your task.
- Verify WhatsApp notifications are received for:
  - Task assignment (Test 28)
  - Task reminders (Test 33)
- Test GPS capture functionality (if device supports it).
- Verify that "My Tasks" page only shows your assigned tasks.

---

## D) Guest / Public User (Request Submission)

**Role Description:** Public users with no authentication. Can submit cleaning or repair requests via the public form. Must complete CAPTCHA.

**Permissions:** None (public access)

**Run these tests from `FACILITY_UAT_RUNBOOK.md`:**

- Test 25 (Submit cleaning request as guest - public)

**Extra responsibility:**
- Verify CAPTCHA (Cloudflare Turnstile) is required and working.
- Verify request submission works without authentication.
- Verify request number is displayed after submission.
- Note the request number for GA Staff to handle (coordinate with GA Staff for Test 28).

---

## E) Regular User (Authenticated Request Submission)

**Role Description:** Authenticated user (any role) who is not GA staff or Cleaner. Can submit cleaning/repair requests with auto-filled user information.

**Permissions:** None (or minimal non-facility permissions)

**Run these tests from `FACILITY_UAT_RUNBOOK.md`:**

- Test 1 (Login smoke test)
- Test 2 (Unauthorized access - negative test)
- Test 26 (Submit repair request as authenticated user)

**Extra responsibility:**
- Verify that you **cannot** access any facility management pages (Test 2).
- Verify that request form auto-fills your name and phone from profile.
- Verify `requester_user_id` is set correctly (coordinate with GA Staff to verify).

---

## Test Execution Guidelines

### Parallel Testing

To maximize efficiency, different role testers can work simultaneously:

1. **GA Supervisor tester** should focus on:
   - Schedule CRUD operations (create, edit, delete)
   - Bulk task reassignment
   - Mass approval with quality control threshold
   - Dashboard and reports
   - Alert resolution

2. **GA Staff tester** should focus on:
   - Individual approval/rejection workflow
   - Request handling (convert to tasks/work orders)
   - Daily operations tasks
   - Reports

3. **Cleaner 1 and Cleaner 2** should focus on:
   - Task execution workflow (start → submit)
   - Photo and GPS capture
   - Task locking mechanism (test with each other)
   - WhatsApp notifications for assignments and reminders

4. **Guest/Public User tester** should focus on:
   - Public request form submission
   - CAPTCHA verification

5. **Regular User tester** should focus on:
   - Negative access control testing
   - Authenticated request submission

### Coordination Points

Some tests require coordination between roles:

**Cleaner → GA Staff Workflow:**
- Cleaner submits task (Test 17) → GA Staff approves (Test 20) or rejects (Test 21)

**Guest/User → GA Staff Workflow:**
- Guest submits cleaning request (Test 25) → GA Staff handles request (Test 28)
- User submits repair request (Test 26) → GA Staff handles request (Test 29)

**GA Supervisor → Cleaner Workflow:**
- GA Supervisor creates schedule (Test 4) → Tasks auto-generated → Cleaner views in "My Tasks" (Test 14)
- GA Supervisor bulk reassigns tasks (Test 12) → Cleaner sees tasks reassigned

**Cleaner 1 ↔ Cleaner 2 Coordination:**
- Cleaner 1 starts task (Test 15) → Cleaner 2 tries to submit (Test 16 - should fail)

### WhatsApp Notification Coordination

| Notification Type | Sender Action | Recipient | Test # |
|-------------------|---------------|-----------|--------|
| Task Assignment | GA Staff handles request | Cleaner | 28 |
| Task Reminder | System (cron job) | Cleaner | 33 |
| Flagged Submission | System (random 15%) | GA Supervisor | 22, 31 |
| Schedule Alert | System (asset issue) | GA Staff | 31 |
| Missed Tasks | System (daily cron) | GA Supervisor | 34 |

---

## Reporting

Each tester should:
1. Mark tests as PASS or FAIL in the `FACILITY_UAT_RESULTS_SHEET.csv`.
2. Record actual UI messages and any discrepancies.
3. For WhatsApp notifications, copy and paste the message content into the CSV.
4. For photo watermarking, take screenshots of watermarked photos.
5. Use the Failure Reporting Template from the runbook for detailed failure reports.

---

## Critical Security & Quality Tests

These tests verify critical security and quality control mechanisms. Any failure is a **CRITICAL ISSUE**:

| Test # | Test | Role | What It Verifies | Criticality |
|--------|------|------|------------------|-------------|
| Test 2 | Unauthorized access | Regular User | Access control enforcement | **CRITICAL** |
| Test 16 | Task locking | Cleaner 2 | Task locking prevents concurrent editing | **CRITICAL** |
| Test 23 | 10% review threshold | GA Supervisor | Quality control enforcement | **CRITICAL** |
| Test 17 | Photo watermarking | Cleaner | Photo watermarks for audit trail | **HIGH** |
| Test 28 | Task assignment notification | Cleaner | Cleaner notified of new assignments | **HIGH** |
| Test 31 | Schedule alert notification | GA Staff | Staff notified of asset issues | **MEDIUM** |

---

**End of Facility Management UAT Role Assignments**
