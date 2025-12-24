# DMS Production UAT Runbook

**SIGaP Document Management System (DMS)**  
**Production UAT Manual Test Runbook**  
**Version 1.0**

---

## Table of Contents

1. [Purpose & Rules](#purpose--rules)
2. [UAT Accounts & Roles](#uat-accounts--roles)
3. [Authentication (Keycloak)](#authentication-keycloak)
4. [DMS Navigation Smoke](#dms-navigation-smoke)
5. [Documents (Metadata)](#documents-metadata)
6. [Document Versions + OnlyOffice (DOCX/XLSX)](#document-versions--onlyoffice-docxxlsx)
7. [Document Version Approvals (Two-Tier)](#document-version-approvals-two-tier)
8. [Document Access Requests (Super Admin/Owner only)](#document-access-requests-super-adminowner-only)
9. [Viewing/Downloading Restricted Documents + Watermarking](#viewingdownloading-restricted-documents--watermarking)
10. [Physical Document Borrowing](#physical-document-borrowing)
11. [Form Requests (Printed Copies)](#form-requests-printed-copies)
12. [Printed Forms Lifecycle](#printed-forms-lifecycle)
13. [Correspondences (Document Instances)](#correspondences-document-instances)
14. [Reports Smoke](#reports-smoke)
15. [Failure Reporting Template](#failure-reporting-template)

---

## Purpose & Rules

This runbook is used to validate the full DMS module via the live production UI (UAT phase). The database will be refreshed after UAT, so test data can be created/destructive.

**Important UAT rules:**

1. **All test-created data must be clearly tagged** using the naming convention in this guide.
2. **Do not upload real confidential content.** Use dummy DOCX/XLSX/PDF.
3. **Notifications are mandatory pass/fail.** If the expected WhatsApp notification is not received, mark the test as FAIL.
4. **OnlyOffice DOCX/XLSX editing is mandatory pass/fail.**
5. **Watermarking on restricted document view/download is mandatory pass/fail.**

---

## UAT Accounts & Roles

Use these roles during testing:

- **Super Admin / Owner**: Approves Access Requests; performs Tier-2 document approvals; can access everything.
- **Manager**: Tier-1 approver for Document Versions when the creator has `manager_id`.
- **Document Control**: Creates Documents and Versions; processes Forms/Printed Forms.
- **Requester (Regular user)**: Requests access, borrows documents, requests printed forms.

All UAT users must have valid `mobilephone_no` set.

---

## Authentication (Keycloak)

### Test 1 — Login via Keycloak

**Role:** each test user

1. Open `https://sigap.suryagroup.app/login`.
2. Confirm the app redirects you to Keycloak.
3. Login with the UAT account.
4. Confirm you return to SIGaP and can see the authenticated UI (navbar, your profile name).

**Pass criteria:**
- Login completes and you land inside SIGaP.

**Fail criteria:**
- Redirect loop, error page, or forbidden after login.

### Test 2 — Unauthorized access redirects to Keycloak

**Role:** any user

1. Open a protected URL in a logged-out browser session (e.g. `https://sigap.suryagroup.app/documents`).
2. Confirm redirect to Keycloak.

**Pass criteria:** redirect happens.

---

## DMS Navigation Smoke

### Test 3 — DMS pages load

**Role:** Super Admin / Owner and Document Control

Open each page and confirm it loads (no 500/403):

- `https://sigap.suryagroup.app/dms-dashboard`
- `https://sigap.suryagroup.app/documents`
- `https://sigap.suryagroup.app/document-approvals`
- `https://sigap.suryagroup.app/document-access-requests` (pending approvals)
- `https://sigap.suryagroup.app/my-document-access`
- `https://sigap.suryagroup.app/document-borrows`
- `https://sigap.suryagroup.app/form-requests`
- `https://sigap.suryagroup.app/printed-forms`
- `https://sigap.suryagroup.app/correspondences`

**Pass criteria:**
- Page loads and shows content or a valid empty-state.

---

## Documents (Metadata)

### Test 4 — Create restricted document (requires access request)

**Role:** Document Control

1. Go to `https://sigap.suryagroup.app/documents`.
2. Click **Create Document**.
3. Create a document of a restricted type (examples: SOP / Work Instruction / Job Description).
4. Fill in required fields.

**Required UAT naming:**
- Document Number: `UAT-DMS-<YYYYMMDD>-SOP-01`
- Title: `UAT DMS SOP <YYYY-MM-DD> Access Test`

5. Click **Create**.

**Expected success message:**
- `Document created successfully.`

### Test 5 — Create Form document

**Role:** Document Control

Repeat Test 4 but create a document type **Form**.

**Expected success message:**
- `Document created successfully.`

---

## Document Versions + OnlyOffice (DOCX/XLSX)

OnlyOffice must load and allow editing for DOCX/XLSX.

### Test 6 — Create DOCX version from scratch

**Role:** Document Control

1. Open the document page `https://sigap.suryagroup.app/documents/{id}`.
2. Click **Create New Version**.
3. Set:
   - Creation Method: `Create from scratch`
   - File Type: `docx`
4. Click **Create Version**.

**Expected success message:**
- `Document version created successfully.`

**Expected behavior:**
- Redirects to `https://sigap.suryagroup.app/document-versions/{id}/editor`.
- OnlyOffice editor loads (not an error state).

**Expected OnlyOffice error UI if it fails (FAIL):**
- Alert title: `OnlyOffice Connection Error`
- Example messages include:
  - `Failed to load OnlyOffice editor...`
  - `OnlyOffice API is not available`

### Test 7 — Create XLSX version from scratch

Repeat Test 6 with file type `xlsx`.

---

## Document Version Approvals (Two-Tier)

Document version approvals are two-tier **when the creator has a manager assigned**.

### Test 8 — Submit version for approval

**Role:** version creator (Document Control)

1. On `https://sigap.suryagroup.app/document-versions/{id}/editor`, click **Submit for Approval**.
2. Confirm browser dialog: `Submit this version for approval?`

**Expected success message:**
- `Version submitted for approval.`

**Mandatory WhatsApp notification (pass/fail):**
- A WhatsApp message is received by the next approver.
- Expected message prefix:
  - `📄 *Document Version Approval Request*`
- Expected fields in the message:
  - `Document: *<title>*`
  - `Version: <number>`
  - `Submitted by: <creator name>`

### Test 9 — Tier-1 approval (Manager)

**Role:** Manager (must be the creator’s `manager_id`)

1. Open `https://sigap.suryagroup.app/document-approvals`.
2. Find the pending approval.
3. Click **Approve** and submit.

**Expected success message:**
- `Document version approved successfully.`

**Mandatory WhatsApp notification (pass/fail):**
- A WhatsApp message is received by the Tier-2 approver (Super Admin/Owner / Management Representative).
- Expected message prefix:
  - `📄 *Document Version Approval Request*`

### Test 10 — Tier-2 approval (Management Representative)

**Role:** Super Admin or Owner

1. Open `https://sigap.suryagroup.app/document-approvals`.
2. Approve the pending approval.

**Expected success message:**
- `Document version approved successfully.`

**Expected system behavior:**
- Approved version becomes **Active**.
- Any previous active version becomes **Superseded**.

**Mandatory WhatsApp notification (pass/fail):**
- Creator receives:
  - `✅ *Document Version Approved*`
- Message includes:
  - `Document: *<title>*`
  - `Version: <number>`
  - A statement that it is now active.

### Test 11 — Rejection flow

**Role:** Tier-1 Manager OR Tier-2 Super Admin/Owner

1. From `https://sigap.suryagroup.app/document-approvals`, reject a pending approval.
2. Enter a rejection reason.

**Expected success message:**
- `Document version rejected.`

**Mandatory WhatsApp notification (pass/fail):**
- Creator receives:
  - `❌ *Document Version Rejected*`
- Must include rejection reason.

---

## Document Access Requests (Super Admin/Owner only)

Restricted document types require explicit access requests to view/download files. Users can see the document and must request access before viewing file content.

### Test 12 — Request access (Requester)

**Role:** Requester (NOT Super Admin/Owner/Document Control)

1. Open `https://sigap.suryagroup.app/documents/{restrictedDocId}`.
2. Click **Request Access**.
3. Confirm you see the `Access Control Required` info alert.
4. Fill and submit:
   - Access Type (required)
   - Expiry date (required for Multiple Access)
   - Reason (required; begin with `UAT:`)

**Expected success message:**
- `Access request submitted successfully.`

**Mandatory WhatsApp notification (pass/fail):**
- Super Admin/Owner approver receives WhatsApp.
- Expected prefix:
  - `📄 *Document Access Request*`
- Must include document title, requester, access type, and review link.

### Test 13 — Approve access request (Super Admin/Owner only)

**Role:** Super Admin or Owner

1. Open `https://sigap.suryagroup.app/document-access-requests`.
2. Approve the pending request.

**Expected success message:**
- `Access request approved successfully.`

**Mandatory WhatsApp notification (pass/fail):**
- Requester receives:
  - `✅ *Document Access Approved*`

### Test 14 — Reject access request (Super Admin/Owner only)

**Role:** Super Admin or Owner

1. Open `https://sigap.suryagroup.app/document-access-requests`.
2. Reject the pending request and provide a reason.

**Expected success message:**
- `Access request rejected successfully.`

**Mandatory WhatsApp notification (pass/fail):**
- Requester receives:
  - `❌ *Document Access Rejected*`
- Must include rejection reason.

### Test 15 — Negative authorization test (non-admin cannot approve access)

**Role:** Manager and Document Control

1. Attempt to open `https://sigap.suryagroup.app/document-access-requests`.

**Pass criteria:**
- Access is denied (expected 403) OR the UI has no approval actions.

**Fail criteria:**
- Non-admin can approve or reject access requests.

---

## Viewing/Downloading Restricted Documents + Watermarking

Watermarking is mandatory pass/fail.

### Test 16 — View restricted document after access approval (watermark must be visible)

**Role:** Requester (after access approved)

1. Open `https://sigap.suryagroup.app/my-document-access`.
2. Click **View** for the approved document.
3. Confirm the document opens.
4. Confirm watermark is visible.

**Expected watermark text format (mandatory):**

```
CONFIDENTIAL
<Your Name>
<YYYY-MM-DD HH:MM:SS>
PT. Surya Inti Aneka Pangan
```

**Pass criteria:** watermark text is clearly visible.

**Fail criteria:**
- No watermark visible on restricted document view/download.

---

## Physical Document Borrowing

### Test 17 — Create borrow request

**Role:** Requester

1. Open `https://sigap.suryagroup.app/document-borrows/create`.
2. Select a document.
3. Submit borrow request.

**Expected success message:**
- Normal user: `Borrow request submitted successfully. Please wait for approval.`
- If requester is Super Admin/Owner: `Borrow request created and auto-approved. You can now collect the document.`

**Mandatory WhatsApp notification (pass/fail):**
- If normal user: approver receives `📚 *New Document Borrow Request*`
- If auto-approved: requester receives `✅ *Document Borrow Request Approved*`

### Test 18 — Approve / Reject borrow request

**Role:** Super Admin or Owner

1. Open pending borrow requests (e.g. `https://sigap.suryagroup.app/document-borrows/pending` if available in UI).
2. Approve or reject.

**Expected success messages:**
- Approve: `Borrow request approved successfully.`
- Reject: `Borrow request rejected.`

**Mandatory WhatsApp:**
- Approved: `✅ *Document Borrow Request Approved*`
- Rejected: `❌ *Document Borrow Request Rejected*`

### Test 19 — Checkout + Return

**Role:** Document Control or Admin

1. For an approved borrow, mark as checked out.
2. Later mark as returned.

**Mandatory WhatsApp:**
- Checkout: requester receives `📖 *Document Checked Out*`
- Return: requester receives `✅ *Document Returned Successfully*`

---

## Form Requests (Printed Copies)

### Test 20 — Request printed forms

**Role:** Requester

1. Open `https://sigap.suryagroup.app/form-requests/create`.
2. Select active form(s).
3. Set quantity.
4. Submit.

**Expected success message:**
- `Form request submitted successfully.`

### Test 21 — Process form request status changes

**Role:** Document Control

For the same request, execute in order:

1. **Acknowledge**
   - Expected success message: `Form request acknowledged successfully.`
   - Mandatory WhatsApp: requester receives `📋 *Form Request Status Update*` (Requested → Acknowledged)

2. **Start Processing**
   - Expected success message: `Form request processing started.`
   - Mandatory WhatsApp: status update (Acknowledged → Processing)

3. **Mark Ready**
   - Expected success message: `Form request marked as ready for collection.`
   - Mandatory WhatsApp: status update including `✅ Your forms are ready for collection!`

4. **Mark Collected**
   - Expected success message: `Form request marked as collected.`
   - Mandatory WhatsApp: status update (Ready → Collected)

---

## Printed Forms Lifecycle

### Test 22 — Printed forms generation and numbering

**Role:** Document Control

1. When processing starts, confirm printed forms are created.
2. Confirm printed form numbers follow:
   - `PF-<YYMMDD>-<NNNN>`

### Test 23 — Return printed forms

**Role:** Requester (or eligible staff)

1. Open printed form details.
2. Return with one of:
   - Returned
   - Lost (requires notes)
   - Spoilt (requires notes)

**Expected success messages (examples):**
- `Form marked as returned successfully.`
- `Form marked as lost successfully.`
- `Form marked as spoilt successfully.`

**Expected validation/guardrail message:**
- If not circulating: `Only forms in "Circulating" status can be returned. Current status: <StatusLabel>`

### Test 24 — Receive + Scan + Location

**Role:** Document Control

1. Receive returned forms.
   - Expected message: `Form marked as received.`
2. Upload scanned PDF.
   - Expected message: `Scanned form uploaded successfully.`
3. Update physical location.
   - Expected message: `Physical location updated successfully.`

---

## Correspondences (Document Instances)

### Test 25 — Create correspondence instance (OnlyOffice mandatory)

**Role:** authorized user

1. Ensure there is an active template document (Internal Memo or Outgoing Letter) with an active version.
2. Create correspondence from template.
3. Confirm OnlyOffice editor loads for editing.

**Expected success message:**
- `Correspondence created successfully. You can now edit it.`

---

## Reports Smoke

### Test 26 — Reports load

**Role:** Super Admin/Owner and/or Document Control

Open each report and confirm it renders:

- `https://sigap.suryagroup.app/reports/document-management/masterlist`
- `https://sigap.suryagroup.app/reports/document-management/locations`
- `https://sigap.suryagroup.app/reports/document-management/sla`
- `https://sigap.suryagroup.app/reports/dms/borrowed-documents`
- `https://sigap.suryagroup.app/reports/dms/overdue-documents`

---

## Failure Reporting Template

When a test fails, record:

- Test number (e.g. Test 16)
- Role used (Requester / Document Control / Manager / Super Admin)
- URL
- Exact on-screen message (success/warning/error)
- Whether WhatsApp notification was received (YES/NO) and the message snippet
- Screenshot (optional but recommended)
