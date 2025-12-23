# DMS UI E2E Test Plan (Playwright MCP, Live)

## Goals

- Validate DMS workflows via the real UI on `https://sigap.suryagroup.app`.
- Use Playwright MCP to drive the browser and collect evidence.
- Prefer code-driven reality (controllers/services/policies) over guides.
- Allow destructive testing (create a few documents/versions/requests).

## Evidence / Output

- Save screenshots to `.playwright-mcp/` with consistent names:
- `dms-01-login.png`, `dms-02-dashboard.png`, `dms-03-documents.png`, ...
- Capture browser console errors and failing network requests per step.

## Required Inputs (provided by you)

### 1) Test Users (do NOT store passwords in this repo)

Provide the following users (emails/usernames + roles). Passwords should be supplied at runtime (see below).

- `dms_admin`
- Role: `Super Admin` or `Owner`
- Used for: tier-2 approvals, access approvals, borrow approvals
- Email: ____________________
- `dms_manager`
- Role: `Manager`
- Used for: tier-1 approvals
- Email: ____________________
- `dms_creator`
- Role: `Document Control`
- Must have: `manager_id` set to `dms_manager`
- Used for: creating documents, creating versions, submitting for approval
- Email: ____________________
- `dms_requester` (optional)
- Role: any role that can request access + borrow (or reuse `dms_manager`)
- Email: ____________________

### 2) Department assignments (recommended)

Document visibility is department-based. Ensure test users have realistic department assignments so we can test:

- Access to documents within own department
- Cross-department access via `accessible_departments`
- Access request flow when outside permitted departments

### 3) Login method

Confirm whether the live site supports:

- Email/password login, OR
- SSO-only (Keycloak)

This affects how much of the login flow can be automated.

## Where to put test user info (recommended)

### Preferred (local-only): `.env` (NOT committed)

Put credentials in your local `.env` and keep passwords out of the repo:

- `DMS_TEST_ADMIN_EMAIL=...`
- `DMS_TEST_ADMIN_PASSWORD=...`
- `DMS_TEST_MANAGER_EMAIL=...`
- `DMS_TEST_MANAGER_PASSWORD=...`
- `DMS_TEST_CREATOR_EMAIL=...`
- `DMS_TEST_CREATOR_PASSWORD=...`
- `DMS_TEST_REQUESTER_EMAIL=...`
- `DMS_TEST_REQUESTER_PASSWORD=...`

### Alternative: provide passwords in chat at runtime

If you prefer not to write credentials to disk at all, you can paste passwords into chat when we are about to log in.

## Test Data Naming Convention

Use predictable naming to make cleanup easy:

- Document Number: `E2E-DMS-<YYYYMMDD>-<short>`
- Title: `E2E DMS <YYYY-MM-DD> <short>`

## Test Checklist (Step-by-step)

### dms-setup — Confirm users/roles

- Verify the test accounts exist and roles are correct.
- Verify `dms_creator.manager_id` points to `dms_manager`.

### dms-login — Verify login on live

- Navigate to `/login` and authenticate.
- Screenshot: `.playwright-mcp/dms-01-login.png`
- Record console/network errors.

### dms-smoke — Navigation smoke

Verify these pages load (no 500/403):

- `/dms-dashboard`
- `/documents`
- `/document-approvals`
- `/my-document-access`
- `/document-access-requests`
- `/document-borrows`

Screenshot each key page.

### dms-doc-create — Create document

As `dms_creator`:

- Create a document type that supports versions and requires access request:
- Recommended: `SOP` or `Work Instruction` or `Job Description`
- Fill required metadata and create.
- Screenshot the created document show page.

### dms-version-create — Create version

As `dms_creator`:

- Create a version using:
- Prefer: `scratch` + `docx` (exercises OnlyOffice), OR
- Upload a simple file if needed
- Verify OnlyOffice editor loads in `/document-versions/{id}/editor`.
- Screenshot editor loaded state.

### dms-submit — Submit for approval

As `dms_creator`:

- Trigger submit-for-approval action.
- Expect a pending approval to be created.
- Screenshot the post-submit status.

### dms-approve1 — Tier-1 manager approval

As `dms_manager`:

- Open `/document-approvals`.
- Approve the pending request.
- Screenshot the approvals list after action.

### dms-approve2 — Tier-2 management representative approval

As `dms_admin`:

- Open `/document-approvals`.
- Approve the pending request.
- Verify version becomes Active.
- Screenshot active status on `/documents/{id}`.

### dms-access-request — Request access

As requester (either `dms_requester` or `dms_manager`):

- Attempt to view the active version.
- If blocked, submit access request via `/documents/{id}/request-access`.
- Screenshot confirmation and `/my-document-access`.

### dms-access-approve — Approve access request

As `dms_admin`:

- Review pending requests in `/document-access-requests`.
- Approve the request.
- Screenshot the approval result.

### dms-view-download — View/download and watermark

As requester:

- Verify view/download works.
- Confirm watermark behavior (visually).
- Screenshot the viewer.

### dms-borrow — Borrow request flow

As requester:

- Create borrow request in `/document-borrows/create`.

As `dms_admin`:

- Approve in `/document-borrows/pending`.
- Optionally test checkout/return if UI allows.

### dms-report — Reports pages load

As `dms_admin`:

- Open DMS reports pages and confirm they render:
- `/reports/document-management/masterlist`
- `/reports/document-management/locations`
- `/reports/document-management/sla`

### dms-evidence — Save findings

- Summarize pass/fail per step.
- List console errors and failed requests.