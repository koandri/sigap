# DMS Production UAT Test Data & Notification Checklist

**SIGaP Document Management System (DMS)**  
**Production UAT Data Convention & Mandatory Notification Checks**  
**Version 1.0**

---

## Table of Contents

1. [Test Data Conventions](#test-data-conventions)
2. [Dummy File Standards](#dummy-file-standards)
3. [Mandatory WhatsApp Notification Checks](#mandatory-whatsapp-notification-checks)
4. [OnlyOffice Mandatory Checks](#onlyoffice-mandatory-checks)
5. [Watermark Mandatory Checks](#watermark-mandatory-checks)
6. [Known Risk Areas to Watch](#known-risk-areas-to-watch)

---

## Test Data Conventions

Use these naming conventions so test data is easy to identify and delete.

### Document naming

**Document Number**
- `UAT-DMS-<YYYYMMDD>-<TYPE>-<NN>`

Examples:
- `UAT-DMS-20251223-SOP-01`
- `UAT-DMS-20251223-FORM-01`

**Title**
- `UAT DMS <TYPE> <YYYY-MM-DD> <short description>`

Examples:
- `UAT DMS SOP 2025-12-23 Access Test`
- `UAT DMS FORM 2025-12-23 Printed Forms Test`

### Notes / Reasons

When a field allows a reason/notes (access request reason, rejection reason, borrow notes), start with:
- `UAT:`

Example:
- `UAT: Testing restricted SOP access flow`

---

## Dummy File Standards

Only upload dummy files for UAT.

### DOCX / XLSX
- Create a file that contains obvious UAT text (e.g. `UAT ONLY - DO NOT USE`).
- For DOCX: include at least one heading and one paragraph.
- For XLSX: include at least one sheet with simple table data.

### PDF
- Use a simple single-page PDF.

---

## Mandatory WhatsApp Notification Checks

All notifications are mandatory pass/fail.

When a runbook step requires WhatsApp:

**PASS**
- The expected recipient receives a WhatsApp message.
- The message content matches the expected format/category.

**FAIL**
- Message not received.
- Message received by the wrong person.
- Message missing critical info (wrong document/version, missing reason, etc.).

### A) Document Version — Approval Request

Trigger: creator submits version for approval.

Expected WhatsApp prefix:
- `📄 *Document Version Approval Request*`

Expected fields:
- `Document: *<title>*`
- `Version: <number>`
- `Submitted by: <creator name>`

Recipient rules (current intended behavior):
- If creator has a manager: Tier-1 goes to that manager.
- Otherwise: goes directly to Super Admin/Owner (Management Representative).

### B) Document Version — Approved

Trigger: Tier-2 approval completes and version becomes Active.

Expected WhatsApp prefix:
- `✅ *Document Version Approved*`

Recipient:
- creator of the version.

### C) Document Version — Rejected

Trigger: approver rejects a pending approval.

Expected WhatsApp prefix:
- `❌ *Document Version Rejected*`

Must include:
- Rejection reason.

### D) Document Access — Requested

Trigger: requester submits access request.

Expected WhatsApp prefix:
- `📄 *Document Access Request*`

Recipient:
- Super Admin / Owner approver.

### E) Document Access — Approved

Trigger: Super Admin/Owner approves the access request.

Expected WhatsApp prefix:
- `✅ *Document Access Approved*`

Recipient:
- requester.

### F) Document Access — Rejected

Trigger: Super Admin/Owner rejects the access request.

Expected WhatsApp prefix:
- `❌ *Document Access Rejected*`

Recipient:
- requester.

Must include:
- rejection reason.

### G) Borrow Requests

New request:
- `📚 *New Document Borrow Request*` (to approvers)

Approved:
- `✅ *Document Borrow Request Approved*` (to requester)

Rejected:
- `❌ *Document Borrow Request Rejected*` (to requester)

Checked out:
- `📖 *Document Checked Out*` (to requester)

Returned:
- `✅ *Document Returned Successfully*` (to requester)

### H) Form Requests (Status Updates)

Expected WhatsApp prefix:
- `📋 *Form Request Status Update*`

Statuses that must trigger WhatsApp:
- Requested → Acknowledged
- Acknowledged → Processing
- Processing → Ready for Collection
- Ready for Collection → Collected

---

## OnlyOffice Mandatory Checks

OnlyOffice DOCX/XLSX must load.

**PASS**
- `https://sigap.suryagroup.app/document-versions/{id}/editor` loads with the OnlyOffice editor.
- User can type/edit (when draft and owned by creator) and content persists.

**FAIL**
- Editor shows an error box titled `OnlyOffice Connection Error`.
- Document cannot be edited/viewed in OnlyOffice.

---

## Watermark Mandatory Checks

Watermarking is mandatory pass/fail for restricted documents.

When viewing/downloading a restricted document (after access approval), watermark text must be visible and match:

```
CONFIDENTIAL
<Viewer Name>
<YYYY-MM-DD HH:MM:SS>
PT. Surya Inti Aneka Pangan
```

**PASS**
- Watermark is clearly visible.

**FAIL**
- No watermark visible.
- Watermark shows wrong name or wrong format.

---

## Known Risk Areas to Watch

1. **OnlyOffice dependency / connectivity**
- If the OnlyOffice server URL is unreachable, version creation/editing fails.

2. **Watermarking dependency**
- If PDF watermarking libraries are missing in production, watermarking may not render visibly.
- This should be reported as a FAIL in UAT because watermarking is a hard requirement.

3. **Authorization boundaries**
- Document Access approvals must be Super Admin/Owner only.
- The runbook includes a negative test to ensure Manager/Document Control cannot approve.
