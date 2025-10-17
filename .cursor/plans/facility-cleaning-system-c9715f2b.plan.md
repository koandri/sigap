<!-- c9715f2b-adbb-46d0-913b-6a3e9f86c961 0580f0ce-ab06-40a5-870e-0ef5a9035500 -->
# Facility Management Cleaning System Implementation Plan

## Database Structure

### New Models & Migrations

1. **CleaningSchedule** - Defines cleaning requirements per location

   - `location_id`, `name`, `description`, `frequency_type`, `frequency_config`, `is_active`

2. **CleaningScheduleItem** - Items to clean (flexible: asset-linked or text)

   - `cleaning_schedule_id`, `asset_id` (nullable), `item_name`, `item_description`, `order`

3. **CleaningTask** - Daily auto-generated tasks (one task per schedule item)

   - `task_number`, `cleaning_schedule_id`, `cleaning_schedule_item_id`, `location_id`, `asset_id` (nullable), `item_name`, `item_description`, `scheduled_date`, `assigned_to`, `started_by` (nullable), `started_at` (nullable), `status` (pending/in-progress/completed/missed/approved/rejected), `completed_at`, `completed_by`, `skip_reason` (nullable)

5. **CleaningSubmission** - Cleaner's submission with photos

   - `cleaning_task_id`, `submitted_by`, `submitted_at`, `before_photos` (JSON), `after_photos` (JSON), `notes`

6. **CleaningApproval** - Approval tracking with flagging system

   - `cleaning_submission_id`, `is_flagged_for_review`, `reviewed_at`, `approved_by`, `status` (pending/approved/rejected), `notes`

7. **CleaningRequest** - Guest/user cleaning or repair requests

   - `request_number`, `requester_name`, `requester_phone`, `requester_user_id` (nullable), `location_id`, `request_type` (cleaning/repair), `description`, `photo` (nullable), `status`, `handled_by`, `handled_at`

8. **CleaningScheduleAlert** - Tracks schedule issues with inactive/disposed assets

   - `cleaning_schedule_id`, `cleaning_schedule_item_id`, `asset_id`, `alert_type` (asset_inactive/asset_disposed), `detected_at`, `resolved_at`, `resolved_by`, `resolution_notes`

## Role & Permission Setup

### New Roles

- **Cleaner** - Can view/complete assigned cleaning tasks
- **General Affairs** - Can manage schedules, review tasks, approve submissions

### Permissions (create seeder)

- `facility.dashboard` - View facility dashboard
- `facility.schedules.view/create/edit/delete` - Manage cleaning schedules
- `facility.tasks.view/assign/complete` - Manage cleaning tasks
- `facility.submissions.review/approve` - Review and approve submissions
- `facility.requests.view/handle` - Handle guest requests
- `facility.reports.view` - View reports

## Asset Lifecycle Management

### Detection (during task generation)

- Command detects asset references where asset is inactive/disposed
- Creates `CleaningScheduleAlert` record for tracking
- Skips problematic items in task with note: "Asset [X] unavailable - schedule needs update"
- **TODO:** Add notification system (placeholder for future implementation)

### Resolution (in Dashboard)

- GA Dashboard shows "⚠️ Schedule Maintenance Required" widget
- Lists schedules with inactive/disposed assets
- Quick actions:
  - **Replace Asset** → modal to select replacement asset
  - **Convert to General Item** → remove asset link, keep as text description
  - **Dismiss Alert** → if issue resolved manually

## Auto-Generation System

### Command: `GenerateCleaningTasks`

- Similar to `GenerateMaintenanceWorkOrders`
- Runs daily at 00:00 Jakarta time
- Logic:

  1. Fetch active cleaning schedules due for today
  2. Generate tasks with items
  3. Check asset status for each asset-linked item:

     - If asset inactive/disposed: skip item, create alert, add skip note
     - If asset active: include in task normally

  1. Log generation activity and alerts created

### Service: `CleaningService`

Key methods:

- `generateDailyTasks()` - Create tasks from schedules
- `calculateNextScheduleDate()` - Calculate frequency
- `flagRandomTasksForReview()` - Randomly flag 10-20% of tasks
- `canApproveBatch()` - Verify flagged tasks reviewed before mass approval
- `detectAssetIssues()` - Check schedule items for problematic assets
- `createScheduleAlert()` - Create alert when asset issue detected
- `resolveAlert()` - Mark alert as resolved with action taken

## Controllers & Routes

### FacilityDashboardController

- `index()` - Statistics: cleaner performance ranking, completion rates, charts

### CleaningScheduleController

- Standard CRUD for schedules
- Manage schedule items (assets or text-based)

### CleaningTaskController

- `index()` - List tasks (cleaners see assigned first, then by location)
- `myTasks()` - Today's tasks for current cleaner
- `assign()` - Assign tasks to cleaners
- `startTask()` - Cleaner starts task
- `submitTask()` - Submit with before/after photos (watermarked)

### CleaningApprovalController

- `pendingApprovals()` - Yesterday's submissions awaiting approval
- `review()` - Review individual submission (mark flagged as reviewed)
- `massApprove()` - Approve batch (checks 10-20% reviewed)
- `approve/reject()` - Individual approval actions

### CleaningRequestController

- `guestForm()` - Anonymous submission form
- `store()` - Create request
- `index()` - GA staff views requests
- `handle()` - Convert to cleaning task or maintenance work order

### CleaningReportController

- `dailyReport()` - Tasks for location on specific date (web + PDF)
- `weeklyReport()` - Week overview grid (tick/warning/X indicators)
- `weeklyPdf()` - PDF export A4 landscape
- `cellDetails()` - AJAX modal for cell details

## Key Features Implementation

### 1. Mobile Photo Submission with Watermarking

Reuse Forms live photo implementation from `FormSubmissionController`:

- Force rear camera
- Capture GPS coordinates
- Extract EXIF data
- Apply watermark with timestamp, location, user info
- Store in `storage/app/sigap/cleaning/`

### 2. Smart Approval with Random Flagging

In `CleaningService::flagRandomTasksForReview()`:

```php
$submissions = CleaningSubmission::whereDate('submitted_at', yesterday())->get();
$flagCount = max(ceil($submissions->count() * 0.15), 1); // 15% average
$flagged = $submissions->random($flagCount);
foreach ($flagged as $submission) {
    $submission->approval->update(['is_flagged_for_review' => true]);
}
```

In `CleaningApprovalController::massApprove()`:

```php
$flaggedCount = CleaningApproval::whereFlaggedForReview()->count();
$reviewedCount = CleaningApproval::whereFlaggedForReview()->whereNotNull('reviewed_at')->count();
if ($flaggedCount > 0 && $reviewedCount / $flaggedCount < 0.1) {
    return error('Must review at least 10% of flagged tasks');
}
```

### 3. Cleaner Task View

Order tasks:

1. Assigned to current user (top priority)
2. Unassigned tasks grouped by location (can be completed by anyone)

Show only today's date tasks for cleaners.

### 4. Guest Request Handling

Anonymous form with name + phone:

- If type=cleaning: GA creates new cleaning task
- If type=repair: Auto-create WorkOrder in maintenance module

### 5. Approval Deadline SLA Tracking

**Implementation Details:**

Add to `CleaningApproval` model:

```php
public function getApprovalDeadlineAttribute(): Carbon
{
    // Deadline is 9am the day after task completion
    return $this->cleaningSubmission->submitted_at->addDay()->setTime(9, 0, 0);
}

public function getHoursOverdueAttribute(): float
{
    if ($this->status !== 'pending') {
        return 0; // Already approved/rejected
    }
    
    $now = now();
    $deadline = $this->approval_deadline;
    
    if ($now->lt($deadline)) {
        return 0; // Not yet overdue
    }
    
    return $deadline->diffInHours($now, true);
}

public function getSlaStatusAttribute(): string
{
    $hours = $this->hours_overdue;
    
    if ($hours == 0) return 'on-time'; // green
    if ($hours < 24) return 'warning'; // yellow
    return 'critical'; // red (>24hrs overdue)
}

public function getSlaColorAttribute(): string
{
    return match($this->sla_status) {
        'on-time' => 'success',
        'warning' => 'warning',
        'critical' => 'danger',
    };
}
```

**In Dashboard:**

- Show SLA widget with color-coded badges
- Count: X pending (Y overdue)
- Average approval time in hours
- Worst performer (longest pending)

**In Approval List:**

- Badge next to each submission showing SLA status
- Sort by hours overdue (most critical first)
- Filter by SLA status

### 6. Dashboard Statistics

- Cleaner ranking by completion percentage
- Overall completion vs pending rate (pie/bar chart)
- **Average approval time** (from submission to approval)
- **SLA compliance rate** (% approved within 24hrs of deadline)
- Tasks by location breakdown
- Weekly/monthly trends

### 6. Reports

**Daily Report** (`/facility/reports/daily?location_id=X&date=Y`):

- List all tasks for location on date
- Show completion status, photos, notes
- Print PDF button

**Weekly Report** (`/facility/reports/weekly?week=W&year=Y&locations=[]`):

- 7-column grid (Mon-Sun) x N rows (locations)
- Cell indicators: ✓ (all done), ⚠ (partial), ✗ (none done)
- Click cell → modal with task details
- Export PDF A4 landscape

## Views Structure

```
resources/views/facility/
├── dashboard.blade.php
├── schedules/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── tasks/
│   ├── index.blade.php (GA view)
│   ├── my-tasks.blade.php (Cleaner view)
│   ├── show.blade.php
│   └── submit.blade.php (mobile-optimized photo capture)
├── approvals/
│   ├── index.blade.php
│   └── review.blade.php
├── requests/
│   ├── guest-form.blade.php (public)
│   ├── index.blade.php
│   └── handle.blade.php
└── reports/
    ├── daily.blade.php
    ├── daily-pdf.blade.php
    └── weekly.blade.php
```

## Navigation & Access Control

Add to main menu (visible only to Super Admin, Owner, General Affairs, Cleaner):

```blade
@canany(['facility.dashboard', 'facility.tasks.view'])
<li class="nav-item">
  <a class="nav-link" href="{{ route('facility.dashboard') }}">
    <i class="fa fa-broom"></i> Facility Management
  </a>
</li>
@endcanany
```

## Files to Create/Modify

### Create:

- 7 migrations for new tables
- 7 models (CleaningSchedule, CleaningScheduleItem, CleaningTask, CleaningTaskItem, CleaningSubmission, CleaningApproval, CleaningRequest)
- 1 service (CleaningService)
- 6 controllers (FacilityDashboardController, CleaningScheduleController, CleaningTaskController, CleaningApprovalController, CleaningRequestController, CleaningReportController)
- 1 command (GenerateCleaningTasks)
- 1 seeder (FacilityPermissionSeeder)
- ~15 Blade views
- 1 route file (add to `web.php` or create `routes/facility.php`)

### Modify:

- `database/seeders/DatabaseSeeder.php` - Add role seeder calls
- `routes/console.php` - Schedule daily task generation
- Main layout navigation

## Implementation Order

1. Database (migrations, models, seeder)
2. Service layer (CleaningService with core logic)
3. Command (auto-generation)
4. Controllers (basic CRUD)
5. Views (dashboard, schedules, tasks)
6. Photo submission & watermarking
7. Approval workflow with flagging
8. Guest request system
9. Reports (daily, weekly)
10. Testing & refinement

### To-dos

- [ ] Create migrations and models for cleaning system
- [ ] Set up roles and permissions seeder
- [ ] Build CleaningService with core logic
- [ ] Create command for daily task generation
- [ ] Build schedule CRUD (controller + views)
- [ ] Build task management (controller + views)
- [ ] Implement mobile photo capture with watermarking
- [ ] Build approval system with random flagging
- [ ] Create guest request submission and handling
- [ ] Build dashboard with statistics and charts
- [ ] Create daily and weekly reports with PDF export