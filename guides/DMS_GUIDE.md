# Document Management System (DMS) Guide

**SIGaP Document Management System**  
**Version 1.0**

---

## Table of Contents

1. [Overview](#overview)
2. [Document Management](#document-management)
3. [Version Control](#version-control)
4. [Access Requests](#access-requests)
5. [Physical Document Borrowing](#physical-document-borrowing)
6. [Form Requests](#form-requests)
7. [Printed Forms](#printed-forms)
8. [Document Instances](#document-instances)
9. [Reports and Analytics](#reports-and-analytics)
10. [Dashboard](#dashboard)

---

## Overview

The Document Management System (DMS) module provides enterprise-grade document lifecycle management with version control, access management, and printed form tracking.

### Key Features

- **Document Version Control** - Complete versioning with two-tier approval
- **Access Request System** - Request and manage document access with watermarked downloads
- **Physical Document Borrowing** - Library-style borrowing system for physical document copies
- **Form Request Management** - Complete printed form lifecycle with QR code tracking
- **Document Instances** - Template-based memos and letters
- **OnlyOffice Integration** - Collaborative document editing (DOCX/XLSX)
- **Reports & Analytics** - Masterlist, location reports, and SLA dashboards
- **Physical Location Tracking** - Track document storage locations (room, shelf, folder)

### Document Types

SIGaP supports 8 document types:

1. **SOP** - Standard Operating Procedures
2. **Work Instruction** - Step-by-step work instructions
3. **Form** - Printable forms (can be requested as printed copies)
4. **Job Description** - Position descriptions
5. **Internal Memo** - Internal memos (template-based with instances)
6. **Incoming Letter** - External correspondence received
7. **Outgoing Letter** - Letters sent externally (template-based with instances)
8. **Other** - Miscellaneous documents

---

## Document Management

### Creating a Document

#### Step 1: Create Document Record

1. Navigate to **DMS > Documents**

![Documents List Page](/guides-imgs/dms-documents-list.png)

2. Click **"Create Document"**
3. Fill in document details:

**Basic Information:**
- **Document Number**: Unique identifier (auto-generated or manual)
- **Title**: Document title
- **Description**: Brief description
- **Document Type**: Select from 8 types (SOP, Work Instruction, Form, etc.)
- **Department**: Owner department

**Physical Location** (optional):
- **Room Number**: Storage room
- **Shelf Number**: Shelf location
- **Folder Number**: Folder identifier

4. Click **"Create"**

![Create Document Form](/guides-imgs/dms-create-document-form.png)

#### Step 2: Create First Version

After creating a document, you'll be prompted to create the first version:

1. Click **"Create Version"** on the document details page
2. Choose creation method:
   - **From Scratch** - Create new document in OnlyOffice editor
   - **Upload File** - Upload existing document (DOCX, XLSX, PDF, JPG)
   - **Copy from Existing** - Copy content from another document version
3. Follow the prompts to create/edit your document

![Create Version Options](/guides-imgs/dms-create-version-options.png)

### Document Access Control

#### Cross-Department Access

Documents can be made accessible to multiple departments:

1. Open document details
2. Click **"Manage Access"**
3. Select departments that should have access
4. Click **"Save"**

**Note:** Department access allows viewing document metadata. To view/download the actual file, users must request access (see [Access Requests](#access-requests)).

---

## Version Control

### Version Status Flow

Document versions follow this workflow:

```
Draft → Pending Manager Approval → Pending Mgmt Approval → Active → Superseded
```

#### Statuses Explained

- **Draft** - Version is being created/edited
- **Pending Manager Approval** - Submitted to manager for first-tier approval
- **Pending Mgmt Approval** - Approved by manager, awaiting management representative
- **Active** - Approved and currently in use (only one active version per document)
- **Superseded** - Replaced by newer active version

### Creating a Version

#### From Scratch (OnlyOffice)

1. Navigate to document details
2. Click **"Create Version"** → **"From Scratch"**
3. Choose file type:
   - **Word Document** (.docx)
   - **Spreadsheet** (.xlsx)
4. OnlyOffice editor opens
5. Edit document in browser
6. Click **"Save"** - Document saved automatically

#### Upload Existing File

1. Click **"Create Version"** → **"Upload File"**
2. Select file (DOCX, XLSX, PDF, JPG)
3. Enter **Version Number** (e.g., 1.0, 1.1, 2.0)
4. Enter **Revision Description** (what changed)
5. Click **"Upload"**

#### Copy from Existing

1. Click **"Create Version"** → **"Copy from Existing"**
2. Select source document version
3. Enter new version number
4. Enter revision description
5. Click **"Create"**

### Editing a Version

1. Open version details (must be in Draft status)
2. Click **"Edit in OnlyOffice"**
3. Make changes in OnlyOffice editor
4. Save automatically or click **"Save"**

### Submitting for Approval

1. Open version in Draft status
2. Click **"Submit for Approval"**
3. Version moves to **Pending Manager Approval**

**Requirements:**
- Version must be in Draft status
- Document creator must have a manager assigned
- Document must belong to a department

### Approval Process

#### Two-Tier Approval

**Tier 1: Manager Approval**

1. Manager receives notification (if configured)
2. Navigate to **DMS > Document Approvals**
3. Review version details and preview
4. Click **"Approve"** or **"Reject"**
5. Add approval notes (optional)

![Document Approvals Page](/guides-imgs/dms-approval-page.png)

**If Approved:**
- Version moves to **Pending Mgmt Approval**

**If Rejected:**
- Version returns to **Draft** with rejection notes

**Tier 2: Management Representative Approval**

1. Management representative receives notification
2. Navigate to **DMS > Document Approvals**
3. Review version
4. Click **"Approve"** or **"Reject"**
5. Add notes

**If Approved:**
- Version becomes **Active**
- Previous active version (if any) becomes **Superseded**

**If Rejected:**
- Version returns to **Pending Manager Approval**

### Viewing Versions

1. Open document details
2. View **Versions** section
3. See all versions with status indicators:
   - 🟢 **Active** - Currently in use
   - 🟡 **Pending** - Awaiting approval
   - ⚪ **Draft** - Being edited
   - ⚫ **Superseded** - Replaced by newer version

---

## Access Requests

### Requesting Document Access

Users need explicit access to view/download document versions.

#### Requesting Access

1. Navigate to **DMS > My Document Access**
2. Click **"Request Access"**
3. Search and select document
4. System automatically captures **active version** at time of request
5. Fill in request details:

![My Document Access Page](/guides-imgs/dms-my-document-access.png)
   - **Access Type**: 
     - **One-Time** - Single download allowed
     - **Multiple** - Unlimited access until expiry
   - **Requested Expiry Date** (optional) - When access should expire
   - **Purpose** - Why you need access
6. Click **"Submit Request"**

**Note:** The system automatically records the active document version at the time of request. Even if a new version becomes active later, your access is tied to the version you requested.

### Pending Requests (For Approvers)

Super Admins and Owners can approve/reject access requests:

1. Navigate to **DMS > Document Access Requests > Pending**
2. View pending requests with:
   - Requester name
   - Document and version info
   - Requested access type
   - Requested expiry date
3. Click **"Review Request"**
4. Approve or reject:
   - **Approve** - Can modify access type and expiry
   - **Reject** - Add rejection reason

### Viewing Accessible Documents

1. Navigate to **DMS > My Document Access**
2. View list of documents you have access to
3. Click **"View"** to download watermarked PDF

**Access Information:**
- Document and version details
- Access type (one-time/multiple)
- Expiry date (if set)
- Access status (active/expired)

### Viewing/Downloading Documents

When viewing a document you have access to:

1. Click **"View Document"**
2. System generates watermarked PDF on-the-fly
3. Watermark includes:
   - Your username
   - Current date/time
   - "CONFIDENTIAL" label
   - Company name
4. Download or view in browser

**One-Time Access:**
- First download consumes access
- Subsequent attempts show "Access expired" message

**Multiple Access:**
- Can download unlimited times until expiry date
- Expired access shows "Access expired" message

---

## Physical Document Borrowing

The Physical Document Borrowing system provides library-style management for borrowing physical copies of documents.

### Overview

Users can borrow physical document copies with:
- **Approval Workflow** - Super Admin/Owner auto-approved, others need approval
- **Due Date Management** - Optional due dates (default 7 days)
- **Single Copy Tracking** - Only one user can borrow a document at a time
- **WhatsApp Notifications** - All status changes and reminders
- **Access Control** - Can only borrow documents you have digital access to

### Borrowing a Document

#### Step 1: Request to Borrow

1. Navigate to **DMS > My Borrows**
2. Click **"Request to Borrow"**
3. Fill in the borrow request form:
   - **Document** - Select from available documents
   - **Due Date** - Optional (defaults to 7 days, can be left empty for no due date)
   - **Notes** - Optional reason for borrowing

4. Click **"Submit Borrow Request"**

**Important:**
- You can only borrow documents you have digital access to
- Documents currently borrowed by others are not available
- Super Admin/Owner requests are auto-approved

#### Step 2: Approval (if required)

For non-privileged users:
1. Request goes to **Pending** status
2. Super Admin/Owner reviews the request
3. Approver can **Approve** or **Reject** with reason

For Super Admin/Owner:
- Request automatically approved
- Can immediately collect the document

#### Step 3: Physical Checkout

When approved, Document Control:
1. Marks the document as **Checked Out**
2. Borrower receives WhatsApp notification
3. Physical document is collected

#### Step 4: Return

When returning:
1. Document Control marks as **Returned**
2. Borrower receives confirmation notification
3. Document becomes available for others to borrow

### Viewing Your Borrows

Navigate to **DMS > My Borrows** to see:
- Current borrowed documents
- Pending requests
- Borrow history
- Due dates and overdue status

**Status Indicators:**
- 🟡 **Pending** - Awaiting approval
- 🔵 **Approved** - Ready for collection
- 🟢 **Checked Out** - Currently borrowed
- ✅ **Returned** - Completed
- ❌ **Rejected** - Request denied

### Managing Pending Approvals (Super Admin/Owner)

1. Navigate to **DMS > Borrow Approvals**
2. View all pending borrow requests
3. Click **"Review"** to see request details
4. **Approve** or **Reject** the request

### Due Dates and Reminders

**Due Date Reminders:**
- Sent 1 day before due date via WhatsApp
- Includes document details and due date

**Overdue Notices:**
- Sent daily for overdue documents
- Shows number of days overdue
- Reminder to return immediately

### Reports

**Borrowed Documents Report:**
- View all currently borrowed documents
- Filter by status (checked out, all active, returned)
- Export and print capabilities

**Overdue Documents Report:**
- List of all overdue borrows
- Days overdue counter
- Borrower contact information
- Quick return actions

---

## Form Requests

Form requests allow users to request printed copies of form documents.

### Creating a Form Request

1. Navigate to **DMS > Form Requests**
2. Click **"Create Request"**
3. Select forms to request:

![Form Requests List](/guides-imgs/dms-form-requests-list.png)
   - Only documents with type **"Form"** are available
   - Select multiple forms
   - Enter **quantity** for each form
4. System automatically captures **active version** of each form
5. Click **"Submit Request"**

**Form Request Number:** Auto-generated as `FR-YYMMDD-XXXX`

### Form Request Lifecycle

```
Pending → Acknowledged → Processing → Ready → Collected → Completed
```

#### Statuses

- **Pending** - Request submitted, awaiting Document Control
- **Acknowledged** - Document Control received request
- **Processing** - Forms being printed/prepared
- **Ready** - Forms ready for collection
- **Collected** - Requester collected forms
- **Completed** - All forms returned/scanned

### Document Control Workflow

#### Step 1: Acknowledge Request

1. Navigate to **DMS > Form Requests**
2. View pending requests
3. Click **"Acknowledge"**
4. Request moves to **Acknowledged** status
5. Requester receives notification

#### Step 2: Start Processing

1. Click **"Start Processing"**
2. Request moves to **Processing** status
3. Prepare forms for printing

#### Step 3: Mark as Ready

1. After printing, click **"Mark as Ready"**
2. System generates form numbers (PF-YYMMDD-XXXX)
3. Creates **PrintedForm** records for each copy
4. Request moves to **Ready** status
5. Requester receives notification

#### Step 4: Generate QR Code Labels

1. Click **"Print Labels"**
2. System generates PDF with QR code labels
3. Each label contains:
   - QR code (links to printed form page)
   - Form number
   - Form name
   - Issue date
4. Print labels and attach to forms

#### Step 5: Collect Forms

When requester collects:

1. Click **"Mark as Collected"**
2. Request moves to **Collected** status
3. Forms are now in circulation

### Request Details

View request details shows:

- Request number and date
- Requester information
- Forms requested (with quantities and versions)
- Status timeline
- Form numbers generated
- Collection information

---

## Printed Forms

Printed forms track individual form copies through their lifecycle.

### Form Status Flow

```
Issued → Circulating → Returned/Lost/Spoilt → Received → Scanned
```

### Viewing a Printed Form

#### Via QR Code Scan

1. Scan QR code on form label
2. System opens printed form details page
3. View form information:
   - Form number
   - Document version used
   - Issue date and issued to
   - Current status
   - Physical location (room, shelf, folder)

#### Via Printed Forms List

1. Navigate to **DMS > Printed Forms**
2. View all printed forms with filters:
   - Status
   - Form request
   - Document
   - Date range
3. Click form number to view details

![Printed Forms List](/guides-imgs/dms-printed-forms-list.png)

### Returning Forms

#### Single Return

1. Scan QR code or open form details
2. Click **"Return Form"**
3. Select return status:
   - **Returned** - Form returned normally
   - **Lost** - Form cannot be located
   - **Spoilt** - Form damaged/unusable
4. Add notes (optional)
5. Click **"Submit Return"**

#### Bulk Return

1. Navigate to **DMS > Printed Forms**
2. Select multiple forms (checkbox)
3. Click **"Bulk Return"**
4. Select return status for all
5. Add notes
6. Click **"Submit"**

### Receiving Forms

When Document Control receives returned forms:

1. Scan QR code or open form details
2. Click **"Receive Form"**
3. Status changes to **Received**
4. Form can now be scanned (uploaded)

#### Bulk Receive

1. Select multiple returned forms
2. Click **"Bulk Receive"**
3. All selected forms marked as received

### Uploading Scanned Forms

1. Open received form details
2. Click **"Upload Scan"**
3. Upload PDF file (scanned form)
4. Form status changes to **Scanned**
5. Scanned PDF stored securely

#### Bulk Upload Scans

1. Select multiple received forms
2. Click **"Bulk Upload Scans"**
3. Upload PDF for each form
4. All uploaded forms marked as scanned

### Physical Location Tracking

Track where printed forms are stored:

1. Open printed form details
2. Click **"Update Location"**
3. Enter:
   - **Room Number**
   - **Shelf Number**
   - **Folder Number**
4. Click **"Save"**

#### Bulk Update Location

1. Select multiple forms
2. Click **"Bulk Update Location"**
3. Enter location details
4. All selected forms updated

### Viewing Scanned Forms

1. Open scanned form details
2. Click **"View Scanned"**
3. System checks access permissions
4. Download/view scanned PDF

**Access Control:**
- Same access request system as regular documents
- Users must request access to view scanned forms

---

## Document Instances

Document instances are used for **Internal Memos** and **Outgoing Letters** created from templates.

### Creating an Instance

1. Navigate to **DMS > Documents**
2. Find a document with type **Internal Memo** or **Outgoing Letter**
3. Ensure document has an **Active Version** (this is the template)
4. Navigate to **DMS > Document Instances**
5. Click **"Create Instance"**
6. Select template document
7. Fill in instance details:
   - **Subject**: Memo/letter subject
   - **Content Summary**: Brief description
   - **Recipient** (for outgoing letters): Recipient information
8. Click **"Create"**

**Instance Number:** Auto-generated format

### Instance Workflow

```
Draft → Pending Approval → Approved
```

#### Draft Phase

1. Instance created in **Draft** status
2. Can be edited
3. Final PDF not yet generated

#### Submit for Approval

1. Click **"Submit for Approval"**
2. Instance moves to **Pending Approval**
3. Notifies approver (if configured)

#### Approval

1. Approver reviews instance
2. Clicks **"Approve"** or **"Reject"**
3. If approved:
   - Instance becomes **Approved**
   - Final PDF generated automatically
   - Can be downloaded/distributed

### Viewing Instances

1. Navigate to **DMS > Document Instances**
2. View all instances with:
   - Instance number
   - Template document
   - Subject
   - Status
   - Created date
   - Creator
3. Click instance to view details

---

## Reports and Analytics

### Documents Masterlist

Generate comprehensive document listing:

1. Navigate to **DMS > Reports > Masterlist**
2. Apply filters:
   - Department
   - Document type
   - Search term
3. Click **"Generate Report"**
4. View report grouped by:
   - Department
   - Document type
   - Ordered by document number
5. Export to Excel or PDF

**Report Includes:**
- Document number and title
- Type and department
- Active version information
- Physical location
- Creator and creation date

### Location Reports

Group documents by physical storage location:

1. Navigate to **DMS > Reports > Location Report**
2. View documents grouped by:
   - Room Number
   - Shelf Number
   - Folder Number
3. Useful for physical inventory of documents

### SLA Dashboard

View Service Level Agreement metrics:

1. Navigate to **DMS > Dashboard > SLA**
2. View metrics:
   - Average approval time (by tier)
   - Overdue approvals
   - SLA compliance rate
   - Pending approvals by age
   - Approval trend charts

---

## Dashboard

### DMS Dashboard Overview

Navigate to **DMS > Dashboard** to see:

![DMS Dashboard](/guides-imgs/dms-dashboard.png)

#### Statistics Widgets

- **Total Documents** - By type
- **Pending Approvals** - By tier (Manager/Mgmt)
- **Active Access Requests** - Pending and approved
- **Active Form Requests** - By status
- **Recent Activity** - Latest document actions

#### Quick Actions

- Create Document
- Create Version
- View Pending Approvals
- Process Access Requests
- View Form Requests

#### Performance Metrics

- Documents by type (pie chart)
- Approval status distribution
- Access requests by status
- Form request status breakdown

---

## Best Practices

### Document Creation

- Use descriptive document numbers
- Assign correct document type
- Set physical location for easy retrieval
- Include detailed descriptions

### Version Control

- Always add revision descriptions
- Test OnlyOffice documents before submission
- Review version before submitting for approval
- Communicate changes to stakeholders

### Access Management

- Request access in advance
- Specify clear purpose for access requests
- Use appropriate access type (one-time vs multiple)
- Set realistic expiry dates

### Form Requests

- Plan form requests in advance
- Specify accurate quantities
- Monitor request status
- Collect forms promptly when ready

### Printed Forms

- Scan QR codes immediately upon receipt
- Update physical locations regularly
- Return forms on time
- Report lost/spoilt forms promptly
- Upload scanned forms after return

### Document Instances

- Use clear subjects and summaries
- Review instance before submitting
- Follow approval workflow
- Download final PDF for records

---

## Troubleshooting

### Common Issues

**Q: Can't create version - "Manager required"**
- Ensure document creator has a manager assigned in their user profile

**Q: Can't edit version**
- Version must be in Draft status to edit
- Only the creator can edit draft versions

**Q: Access request denied**
- Check if you have permission to request access
- Verify document has active version
- Ensure request is not duplicate

**Q: Form request stuck in Processing**
- Contact Document Control to mark as Ready
- Check if forms have been printed

**Q: QR code not scanning**
- Ensure QR code is clear and undamaged
- Try scanning from different angle
- Check if printed form exists in system

**Q: Can't view scanned form**
- Request access to the document first
- Verify access hasn't expired
- Check if form was actually scanned

---

## Document Information

**Version**: 1.0  
**Last Updated**: November 2025  
**System Version**: SIGaP Laravel 12.x  
**Module**: Document Management System (DMS)

---

**Thank you for using SIGaP Document Management System!**

*Built with ❤️ for PT. Surya Inti Aneka Pangan*

