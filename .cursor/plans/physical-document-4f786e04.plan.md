<!-- 4f786e04-86a6-4a63-a10d-0d3ff95fd983 0fbb8045-d8e5-4557-b9ed-d1857241470a -->
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

### To-dos

- [ ] Create document_borrows migration
- [ ] Create DocumentBorrowStatus enum
- [ ] Create DocumentBorrow model with relationships and scopes
- [ ] Create DocumentBorrowService with business logic and WhatsApp notifications
- [ ] Create DocumentBorrowPolicy for authorization
- [ ] Create StoreBorrowRequest validation
- [ ] Create DocumentBorrowController with all actions
- [ ] Create Blade views for borrowing (index, create, show, pending, review)
- [ ] Add borrowed/overdue widgets to DMS dashboard
- [ ] Create DocumentBorrowReportController and report views
- [ ] Add borrow permissions to DMSPermissionsSeeder
- [ ] Add routes to web.php
- [ ] Add menu items to DMS sidebar
- [ ] Create SendBorrowReminders scheduled command