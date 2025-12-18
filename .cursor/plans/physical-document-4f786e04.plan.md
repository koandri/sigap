---
name: Physical Document Borrowing Feature
overview: ""
todos:
  - id: fc1f67c4-6f9a-40d3-b212-ce6508cbf70c
    content: Create document_borrows migration
    status: pending
  - id: cfb8d39d-f2fe-4c12-8cff-fa8c8801fb6c
    content: Create DocumentBorrowStatus enum
    status: pending
  - id: 1ce656e0-041a-4660-b831-14da7bbf564d
    content: Create DocumentBorrow model with relationships and scopes
    status: pending
  - id: 950aa46f-d939-4201-bb0e-3c08d171eda8
    content: Create DocumentBorrowService with business logic and WhatsApp notifications
    status: pending
  - id: 3677898e-bac6-4010-bb8b-79307107ae44
    content: Create DocumentBorrowPolicy for authorization
    status: pending
  - id: 4fc4669d-cb2b-405f-99b2-a56a966d0981
    content: Create StoreBorrowRequest validation
    status: pending
  - id: 0007f80b-780e-42b8-8ba5-d8c36ee9f310
    content: Create DocumentBorrowController with all actions
    status: pending
  - id: 164508eb-a912-4107-b8dc-1cb4924f1aa2
    content: Create Blade views for borrowing (index, create, show, pending, review)
    status: pending
  - id: 01523f45-53c9-46d1-989a-7b79893f04b1
    content: Add borrowed/overdue widgets to DMS dashboard
    status: pending
  - id: 5fb45d9c-3f65-4c6d-a463-9d0c8f95cfbc
    content: Create DocumentBorrowReportController and report views
    status: pending
  - id: afc6a489-b83c-4ee2-8907-7cc27898d334
    content: Add borrow permissions to DMSPermissionsSeeder
    status: pending
  - id: 65cbb638-106d-4e27-b663-daf3a98136fb
    content: Add routes to web.php
    status: pending
  - id: 6931710c-87cb-4233-852b-c9df949dc405
    content: Add menu items to DMS sidebar
    status: pending
  - id: 674fd9c2-54a8-4c6b-a121-4da9555a5a5f
    content: Create SendBorrowReminders scheduled command
    status: pending
---

# Physical Document Borrowing Feature

## Summary

Add a library-style system for borrowing physical document copies with:

- Checkout/return tracking
- Approval workflow (except Super Admin/Owner)
- Single copy inventory (one borrower at a time)
- WhatsApp notifications
- Dashboard widgets and reports

## Database Schema

### New Migration: `document_borrows` table

| Column | Type | Description |

|--------|------|-------------|

| id | bigint | Primary key |

| document_id | foreignId | Reference to documents table |

| user_id | foreignId | Borrower |

| status | string | pending, approved, rejected, checked_out, returned |

| due_date | timestamp nullable | Return due date (default 7 days, can be null) |

| checkout_at | timestamp nullable | When physically checked out |

| returned_at | timestamp nullable | When returned |

| approved_by | foreignId nullable | Approver (Super Admin/Owner) |

| approved_at | timestamp nullable | Approval timestamp |

| rejection_reason | text nullable | If rejected |

| notes | text nullable | Borrower notes |

| timestamps | | created_at, updated_at |

## Key Files to Create/Modify

### Models

- Create `app/Models/DocumentBorrow.php` - Eloquent model with relationships and scopes

### Enums

- Create `app/Enums/DocumentBorrowStatus.php` - pending, approved, rejected, checked_out, returned

### Services

- Create `app/Services/DocumentBorrowService.php`:
  - `createBorrowRequest()` - Submit request (auto-approve for Super Admin/Owner)
  - `approveBorrowRequest()` - Approve pending request
  - `rejectBorrowRequest()` - Reject with reason
  - `checkoutDocument()` - Mark as physically checked out
  - `returnDocument()` - Mark as returned
  - `canBorrow()` - Check if user can borrow (has access + document available)
  - `isDocumentAvailable()` - Check if physical copy is available
  - `sendNotification()` - WhatsApp notifications

### Controllers

- Create `app/Http/Controllers/DocumentBorrowController.php`:
  - `index()` - List user's borrows
  - `create()` - Show borrow request form
  - `store()` - Submit borrow request
  - `show()` - View borrow details
  - `pending()` - List pending requests (for approvers)
  - `approve()` / `reject()` - Process requests
  - `checkout()` - Mark checked out
  - `return()` - Mark returned

### Form Requests

- Create `app/Http/Requests/StoreBorrowRequest.php` - Validate borrow requests

### Policies

- Create `app/Policies/DocumentBorrowPolicy.php`:
  - Only allow borrowing documents user has access to
  - Super Admin/Owner can approve/manage all
  - Users can view/cancel their own requests

### Views (in `resources/views/document-borrows/`)

- `index.blade.php` - My borrows list
- `create.blade.php` - Borrow request form (document select, due date with 7-day default, notes)
- `show.blade.php` - Borrow details
- `pending.blade.php` - Pending approvals list
- `review.blade.php` - Approve/reject form

### Dashboard Updates

Modify [resources/views/dashboards/dms.blade.php](resources/views/dashboards/dms.blade.php) and [app/Http/Controllers/DocumentManagementDashboardController.php](app/Http/Controllers/DocumentManagementDashboardController.php):

- Add "Documents Borrowed" stat card
- Add "Overdue Borrows" stat card (with warning color)

### Reports

- Create `app/Http/Controllers/DocumentBorrowReportController.php`:
  - `borrowedDocuments()` - Currently borrowed documents report
  - `overdueDocuments()` - Overdue borrows report
- Create views in `resources/views/reports/document-borrows/`:
  - `borrowed.blade.php`
  - `overdue.blade.php`

### Permissions

Update [database/seeders/DMSPermissionsSeeder.php](database/seeders/DMSPermissionsSeeder.php):

- `dms.borrows.request` - Request to borrow
- `dms.borrows.approve` - Approve borrow requests
- `dms.borrows.manage` - Checkout/return management
- `dms.borrows.view` - View borrow records

### Routes

Add to DMS route group in [routes/web.php](routes/web.php):

```php
// Document Borrowing
Route::get('document-borrows', [DocumentBorrowController::class, 'index'])->name('document-borrows.index');
Route::get('document-borrows/create', [DocumentBorrowController::class, 'create'])->name('document-borrows.create');
Route::post('document-borrows', [DocumentBorrowController::class, 'store'])->name('document-borrows.store');
Route::get('document-borrows/pending', [DocumentBorrowController::class, 'pending'])->name('document-borrows.pending');
Route::get('document-borrows/{borrow}', [DocumentBorrowController::class, 'show'])->name('document-borrows.show');
Route::post('document-borrows/{borrow}/approve', [DocumentBorrowController::class, 'approve'])->name('document-borrows.approve');
Route::post('document-borrows/{borrow}/reject', [DocumentBorrowController::class, 'reject'])->name('document-borrows.reject');
Route::post('document-borrows/{borrow}/checkout', [DocumentBorrowController::class, 'checkout'])->name('document-borrows.checkout');
Route::post('document-borrows/{borrow}/return', [DocumentBorrowController::class, 'returnDocument'])->name('document-borrows.return');

// Reports
Route::get('reports/dms/borrowed-documents', [DocumentBorrowReportController::class, 'borrowedDocuments'])->name('reports.dms.borrowed-documents');
Route::get('reports/dms/overdue-documents', [DocumentBorrowReportController::class, 'overdueDocuments'])->name('reports.dms.overdue-documents');
```

### Navigation

Add menu items to DMS sidebar for:

- "My Borrows" (all users with permission)
- "Pending Borrow Approvals" (Super Admin/Owner only)
- Reports section links

## Notifications (WhatsApp)

Using existing [app/Services/WhatsAppService.php](app/Services/WhatsAppService.php):

1. **Borrow Request Submitted** - Notify Super Admin/Owner approvers
2. **Borrow Approved** - Notify requester
3. **Borrow Rejected** - Notify requester with reason
4. **Document Checked Out** - Confirm to borrower
5. **Due Date Reminder** - 1 day before due (scheduled command)
6. **Overdue Notice** - When past due date (scheduled command)
7. **Document Returned** - Confirm to borrower

### Scheduled Command

Create `app/Console/Commands/SendBorrowReminders.php` for due date reminders and overdue notifications.

## Access Control Logic

A user can only borrow a document if:

1. User has digital access to the document (via `DocumentService::checkUserCanAccess()`)
2. The document's physical copy is not currently borrowed (status != checked_out)
3. User doesn't already have an active borrow request for the same document

## Workflow

```
User Request → [Super Admin/Owner: Auto-approve] → Checked Out → Returned
            → [Other users: Pending] → Approved → Checked Out → Returned
                                     → Rejected
```