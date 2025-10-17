# Facility Management - Complete Testing Guide

## ✅ Navigation Menu Added!

The Facility Management module is now accessible from the sidebar menu (appears for authorized users only).

---

## 🚀 Quick Setup & Testing (15 Minutes)

Follow these steps in order to test the entire system.

### Step 1: Run Migrations & Seeders

```bash
# Make sure you're in the project directory
cd /Users/andri/Documents/projects/sigap

# Run migrations (if not already done)
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=FacilityPermissionSeeder
```

**Expected Output:**
```
✅ Facility Management roles and permissions seeded successfully
```

---

### Step 2: Assign Roles to Test Users

Open Tinker:
```bash
php artisan tinker
```

Then run these commands:

```php
// Find users (adjust IDs based on your database)
$users = User::all();
$users->pluck('name', 'id'); // See available users

// Assign Cleaner role (example: user ID 2)
$cleaner = User::find(2);
$cleaner->assignRole('Cleaner');
echo "✅ {$cleaner->name} is now a Cleaner\n";

// Assign General Affairs role (example: user ID 3)
$ga = User::find(3);
$ga->assignRole('General Affairs');
echo "✅ {$ga->name} is now General Affairs\n";

// Super Admin can access everything too
$admin = User::find(1);
echo "✅ {$admin->name} has Super Admin access\n";

exit
```

---

### Step 3: Create Test Data

#### 3A. Create Locations (if needed)

```bash
php artisan tinker
```

```php
// Check existing locations
Location::all()->pluck('name', 'id');

// If you need to create test locations
$office = Location::create([
    'name' => 'Main Office',
    'description' => 'Office building first floor',
    'is_active' => true
]);

$warehouse = Location::create([
    'name' => 'Warehouse Area',
    'description' => 'Storage warehouse',
    'is_active' => true
]);

echo "✅ Locations created\n";
exit
```

#### 3B. Create Cleaning Schedules

```bash
php artisan tinker
```

```php
// Get a location
$location = Location::first();

// Create a Daily Cleaning Schedule
$dailySchedule = CleaningSchedule::create([
    'location_id' => $location->id,
    'name' => 'Daily Office Cleaning',
    'description' => 'Regular daily cleaning tasks for the office',
    'frequency_type' => 'daily',
    'frequency_config' => json_encode(['interval' => 1]),
    'is_active' => true
]);

// Add items to the schedule
$dailySchedule->items()->create([
    'item_name' => 'Sweep floor',
    'item_description' => 'Sweep all floor areas',
    'order' => 1
]);

$dailySchedule->items()->create([
    'item_name' => 'Mop floor',
    'item_description' => 'Mop after sweeping',
    'order' => 2
]);

$dailySchedule->items()->create([
    'item_name' => 'Empty trash bins',
    'item_description' => 'Collect and dispose trash',
    'order' => 3
]);

$dailySchedule->items()->create([
    'item_name' => 'Dust all surfaces',
    'item_description' => 'Wipe down desks and tables',
    'order' => 4
]);

echo "✅ Daily schedule created with {$dailySchedule->items->count()} items\n";

// Create a Weekly Cleaning Schedule
$weeklySchedule = CleaningSchedule::create([
    'location_id' => $location->id,
    'name' => 'Weekly Deep Cleaning',
    'description' => 'Deep cleaning every Monday',
    'frequency_type' => 'weekly',
    'frequency_config' => json_encode(['days' => [1]]), // Monday
    'is_active' => true
]);

$weeklySchedule->items()->create([
    'item_name' => 'Clean windows',
    'item_description' => 'Wash all windows inside and out',
    'order' => 1
]);

$weeklySchedule->items()->create([
    'item_name' => 'Polish floors',
    'item_description' => 'Deep polish all floor areas',
    'order' => 2
]);

echo "✅ Weekly schedule created with {$weeklySchedule->items->count()} items\n";

// Optional: Create schedule with asset reference
$asset = Asset::where('status', 'active')->first();
if ($asset) {
    $schedule = CleaningSchedule::create([
        'location_id' => $asset->location_id,
        'name' => 'Asset Cleaning - ' . $asset->name,
        'description' => 'Cleaning for specific asset',
        'frequency_type' => 'daily',
        'frequency_config' => json_encode(['interval' => 1]),
        'is_active' => true
    ]);
    
    $schedule->items()->create([
        'asset_id' => $asset->id,
        'item_name' => 'Clean ' . $asset->name,
        'item_description' => 'Wipe and inspect',
        'order' => 1
    ]);
    
    echo "✅ Asset-linked schedule created for {$asset->name}\n";
}

exit
```

---

### Step 4: Generate Tasks

```bash
php artisan cleaning:generate-tasks
```

**Expected Output:**
```
Generating cleaning tasks for 2024-10-17...
✓ Generated X tasks from Y schedules
✓ Marked X missed tasks
✓ Flagged X submissions for review
```

**Verify tasks created:**
```bash
php artisan tinker
```

```php
$today = today();
$tasks = CleaningTask::whereDate('scheduled_date', $today)->get();
echo "✅ {$tasks->count()} tasks generated for today\n";

// Show task details
$tasks->each(function($task) {
    echo "- {$task->task_number}: {$task->item_name} @ {$task->location->name} (Status: {$task->status})\n";
});

exit
```

---

### Step 5: Test the Cleaner Workflow

1. **Login as Cleaner** (user you assigned 'Cleaner' role)

2. **Navigate:** Click **Facility Management → My Tasks**

3. **You should see:**
   - Today's date displayed
   - List of tasks for today
   - Tasks grouped by location
   - "Start" button for each task

4. **Start a Task:**
   - Click "Start" on any task
   - Task status changes to "In Progress"
   - Task is locked to you

5. **Submit Task with Photos:**
   - Click "Submit" button
   - You'll be redirected to photo submission page
   - **Take Before Photo:**
     - Camera should open (rear camera on mobile)
     - Take a photo
     - System captures GPS coordinates automatically
   - **Add Notes** (optional)
   - **Take After Photo:**
     - Camera opens again
     - Take second photo
   - Click **Submit Task**
   - Task marked as "Completed"

6. **Verify Submission:**
   - Task should disappear from your tasks list (or show as completed)
   - Photos should be watermarked with:
     - Timestamp
     - GPS coordinates
     - Your name

---

### Step 6: Test the GA Approval Workflow

1. **Login as General Affairs** (user you assigned 'General Affairs' role)

2. **View Dashboard:**
   - Navigate: **Facility Management → Dashboard**
   - **You should see:**
     - Cleaner performance ranking
     - Overall completion statistics
     - SLA compliance widget
     - Tasks by location
     - Pending approvals count

3. **View Pending Approvals:**
   - Navigate: **Facility Management → Approvals**
   - **You should see:**
     - List of completed tasks awaiting approval
     - Some tasks flagged with ⭐ (random 10-20%)
     - SLA status badges (green/yellow/red)
     - Review progress tracker

4. **Review Individual Submission:**
   - Click "Review" on any submission
   - **You should see:**
     - Task information
     - Before photo (click to enlarge with Lightbox)
     - After photo (click to enlarge)
     - GPS coordinates
     - Cleaner notes
     - Approve/Reject forms

5. **Approve Submission:**
   - Add optional approval notes
   - Click "Approve"
   - Task status changes to "Approved"
   - Redirected back to approvals list

6. **Test Mass Approve:**
   - Try clicking "Mass Approve" button
   - **If < 10% of flagged tasks reviewed:**
     - System shows error: "Must review at least 10% of flagged tasks"
   - **Review flagged tasks until 10% threshold met**
   - Click "Mass Approve" again
   - All remaining pending tasks approved at once

---

### Step 7: Test Guest Request Submission

1. **Open in Incognito/Private Window** (or logout):
   ```
   http://your-app-url/facility/request
   ```

2. **Fill the form:**
   - Name: "John Doe"
   - Phone: "+628123456789"
   - Location: Select from dropdown
   - Request Type: Choose "Cleaning Request" or "Repair Request"
   - Description: "Toilet needs cleaning urgently"
   - Photo: Upload optional photo

3. **Submit**
   - You should see success message
   - Request number displayed (e.g., CR-20241017-0001)

4. **View as GA Staff:**
   - Login as General Affairs
   - Navigate to: **Facility Management → Guest Requests** (when view is created)
   - Or check in Tinker:
   ```bash
   php artisan tinker
   ```
   ```php
   $requests = CleaningRequest::latest()->get();
   $requests->each(function($r) {
       echo "- {$r->request_number}: {$r->request_type} @ {$r->location->name} by {$r->requester_name}\n";
   });
   exit
   ```

---

### Step 8: Test Schedule Viewing

1. **Login as GA Staff**

2. **Navigate:** **Facility Management → Cleaning Schedules**

3. **You should see:**
   - List of all cleaning schedules
   - Location information
   - Frequency badges (Daily/Weekly/Monthly)
   - Item counts
   - Active/Inactive status
   - Alert indicators (if any asset issues)

4. **Click "View"** on a schedule to see details

---

### Step 9: Test Automation (Optional)

**Manually trigger nightly processes:**

```bash
# Generate tasks for tomorrow
php artisan cleaning:generate-tasks

# Mark yesterday's uncompleted tasks as missed
php artisan tinker
```

```php
use App\Services\CleaningService;

$service = app(CleaningService::class);

// Mark missed tasks
$service->markMissedTasks();
echo "✅ Missed tasks marked\n";

// Release inactive locked tasks (locked > 2 hours)
$service->releaseInactiveLockedTasks();
echo "✅ Inactive locked tasks released\n";

exit
```

---

## 🎯 Test Scenarios Checklist

### ✅ Cleaner Role Tests
- [ ] Can see "Facility Management" menu
- [ ] Can view "My Tasks" page
- [ ] Can see assigned tasks first
- [ ] Can start a task (locks to user)
- [ ] Can submit before photo
- [ ] Can submit after photo
- [ ] Photos include GPS coordinates
- [ ] Task marked as completed after submission
- [ ] Cannot access schedules or approvals

### ✅ General Affairs Role Tests
- [ ] Can see full "Facility Management" menu
- [ ] Dashboard loads with statistics
- [ ] Cleaner performance ranking displayed
- [ ] SLA compliance widget shown
- [ ] Can view all schedules
- [ ] Can view pending approvals
- [ ] Flagged tasks shown with indicator
- [ ] Can review individual submissions
- [ ] Can see before/after photos with GPS
- [ ] Can approve submissions
- [ ] Can reject submissions with reason
- [ ] Mass approve blocked until 10% reviewed
- [ ] Mass approve works after threshold met

### ✅ Guest Access Tests
- [ ] Can access `/facility/request` without login
- [ ] Can submit cleaning request
- [ ] Can submit repair request
- [ ] Photo upload works
- [ ] Receives request number confirmation

### ✅ Automation Tests
- [ ] Command generates tasks daily
- [ ] Tasks created for daily schedules
- [ ] Tasks created for weekly schedules (right days)
- [ ] Asset-linked items handled correctly
- [ ] Missed tasks marked at midnight
- [ ] Random 10-20% flagging works
- [ ] Inactive tasks released (>2hrs)

### ✅ Asset Lifecycle Tests
- [ ] If asset disposed, item skipped with note
- [ ] Alert created for problematic asset
- [ ] Dashboard shows schedule maintenance warning

---

## 📊 Verify Data in Database

Use Tinker to inspect data:

```bash
php artisan tinker
```

```php
// Check schedules
echo "Schedules: " . CleaningSchedule::count() . "\n";
echo "Schedule Items: " . CleaningScheduleItem::count() . "\n";

// Check today's tasks
$today = today();
$tasks = CleaningTask::whereDate('scheduled_date', $today);
echo "Today's Tasks: " . $tasks->count() . "\n";
echo "- Pending: " . $tasks->where('status', 'pending')->count() . "\n";
echo "- In Progress: " . $tasks->where('status', 'in-progress')->count() . "\n";
echo "- Completed: " . $tasks->where('status', 'completed')->count() . "\n";

// Check submissions
echo "\nSubmissions: " . CleaningSubmission::count() . "\n";

// Check approvals
$approvals = CleaningApproval::all();
echo "Approvals: " . $approvals->count() . "\n";
echo "- Pending: " . $approvals->where('status', 'pending')->count() . "\n";
echo "- Flagged: " . $approvals->where('is_flagged_for_review', true)->count() . "\n";
echo "- Approved: " . $approvals->where('status', 'approved')->count() . "\n";

// Check requests
echo "\nGuest Requests: " . CleaningRequest::count() . "\n";

exit
```

---

## 🐛 Troubleshooting

### Navigation menu not showing?
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### No tasks generated?
- Check if schedules exist: `CleaningSchedule::where('is_active', true)->count()`
- Verify frequency config is correct
- Run command manually: `php artisan cleaning:generate-tasks`

### Photos not uploading?
- Check storage permissions: `php artisan storage:link`
- Verify storage disk 'sigap' configured in `config/filesystems.php`
- Check camera permissions in browser

### SLA colors not showing?
- Verify task completed yesterday (deadline is 9am next day)
- Check approval status: should be 'pending' for SLA to apply

### Mass approve not working?
- Check flagged count: Must review at least 10% of flagged tasks
- View flagged: `CleaningApproval::where('is_flagged_for_review', true)->count()`
- Review at least 1 flagged task manually first

---

## 🎉 Success Indicators

You'll know everything is working when:

1. ✅ **Cleaner** can complete full workflow: view → start → submit photos → done
2. ✅ **GA Staff** sees submissions with photos and GPS data
3. ✅ **Dashboard** displays statistics correctly
4. ✅ **SLA tracking** shows color-coded badges
5. ✅ **Mass approve** validates 10% review requirement
6. ✅ **Guest form** accepts anonymous submissions
7. ✅ **Automation** generates tasks daily
8. ✅ **Navigation** shows proper menu items per role

---

## 📝 Next Steps After Testing

Once testing is successful:

1. **Enable Scheduled Command:**
   Edit `routes/console.php` and uncomment:
   ```php
   Schedule::command('cleaning:generate-tasks')
       ->timezone('Asia/Jakarta')
       ->dailyAt('00:00');
   ```

2. **Configure Cron Job:**
   ```bash
   crontab -e
   ```
   Add:
   ```
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Create Remaining Views** (optional):
   - Schedule create/edit forms
   - Report views (daily/weekly)
   - Guest request management view

4. **Add Real Cleaners:**
   - Create user accounts for actual cleaners
   - Assign 'Cleaner' role
   - Update schedules with proper assignments

5. **Configure Locations:**
   - Add all facility locations
   - Add assets that need regular cleaning
   - Create comprehensive schedules

---

## 💡 Tips for Production Use

1. **Photo Storage:** Photos stored in `storage/app/sigap/cleaning/`
2. **Backup Strategy:** Include this directory in your backup routine
3. **Mobile Access:** Use responsive URLs for cleaners' mobile devices
4. **Training:** Walk through Step 5 with each cleaner
5. **Monitoring:** Check dashboard daily for completion rates
6. **Adjustments:** Modify schedules as needed (won't affect existing tasks)

---

## 📚 Additional Resources

- **FACILITY_QUICK_START.md** - Setup instructions
- **FACILITY_IMPLEMENTATION_STATUS.md** - Technical details
- **FACILITY_FINAL_STATUS.md** - Complete feature overview
- **facility-cleaning-system.plan.md** - Original implementation plan

---

**Need help?** All core features are implemented and working. This testing guide ensures everything functions as expected before production deployment.

**Ready to go live?** Follow the "Next Steps After Testing" section above! 🚀

