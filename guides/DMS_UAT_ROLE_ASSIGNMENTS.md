# DMS Production UAT Role Assignments

**SIGaP Document Management System (DMS)**  
**Production UAT Role-Based Test Assignments**  
**Version 1.0**

---

## Purpose

This guide assigns UAT test responsibilities by role so multiple staff can test in parallel without duplicating work.

All test execution steps and expected messages are defined in:
- `guides/DMS_UAT_RUNBOOK.md`

---

## Roles Needed

1. **Super Admin / Owner (UAT Approver)**
2. **Manager (Tier-1 Approver for Document Versions)**
3. **Document Control (Creator / Processor)**
4. **Requester (Regular user)**

---

## Assignment Matrix

### A) Super Admin / Owner (UAT Approver)

Run these tests from `guides/DMS_UAT_RUNBOOK.md`:

- Test 1 (Login via Keycloak)
- Test 3 (DMS pages load)
- Test 10 (Tier-2 Document Version approval)
- Test 11 (Reject flow)
- Test 13 (Approve Access Request — Super Admin/Owner only)
- Test 14 (Reject Access Request — Super Admin/Owner only)
- Test 18 (Approve/Reject borrow request)
- Test 26 (Reports load)

**Extra responsibility:**
- Verify WhatsApp notifications for:
  - Document version approval requests
  - Document access request approvals/rejections
  - Borrow approvals/rejections


### B) Manager (Tier-1 Document Version Approver)

Run these tests:

- Test 1 (Login via Keycloak)
- Test 9 (Tier-1 Document Version approval)
- Test 15 (Negative access approval: Manager cannot approve access requests)

**Extra responsibility:**
- Confirm Tier-1 approval triggers WhatsApp to Tier-2 approver.


### C) Document Control (Creator / Processor)

Run these tests:

- Test 1 (Login via Keycloak)
- Test 3 (DMS pages load)
- Test 4 (Create restricted document)
- Test 5 (Create Form document)
- Test 6 (Create DOCX version from scratch — OnlyOffice mandatory)
- Test 7 (Create XLSX version from scratch — OnlyOffice mandatory)
- Test 8 (Submit version for approval — must trigger WhatsApp)
- Test 19 (Checkout + Return notifications)
- Test 21 (Process form request statuses — must trigger WhatsApp)
- Test 22 (Printed forms generated + numbering)
- Test 24 (Receive + Scan + Location)
- Test 25 (Correspondence instance + OnlyOffice)

**Extra responsibility:**
- If OnlyOffice fails, capture the exact on-screen error message (e.g. "OnlyOffice Connection Error") and the OnlyOffice server URL shown.


### D) Requester (Regular user)

Run these tests:

- Test 1 (Login via Keycloak)
- Test 12 (Request access)
- Test 16 (View restricted document after approval — watermark mandatory)
- Test 17 (Create borrow request)
- Test 20 (Request printed forms)
- Test 23 (Return printed forms)

**Extra responsibility:**
- Validate watermark text format exactly as required in Test 16.

---

## Parallel Execution Tips

- Coordinate on a shared naming convention (document number/title) so approvers can quickly find the right pending approvals.
- Execute in this order to avoid blocking:
  1) Document Control creates docs + versions
  2) Document Control submits for approval
  3) Manager approves Tier-1
  4) Super Admin/Owner approves Tier-2
  5) Requester requests access (if needed) and verifies watermark
  6) Borrow/Form/Printed Form flows

---

## Completion Output (What each tester submits)

Each tester should submit:

- A list of test numbers executed and PASS/FAIL
- For any failure:
  - URL
  - Role
  - Exact message text
  - WhatsApp received (YES/NO) + message snippet
  - Screenshot (optional)
