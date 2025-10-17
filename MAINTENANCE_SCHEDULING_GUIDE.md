# Maintenance Scheduling & Work Order Generation Guide

## Overview

This system provides automatic and manual work order generation from maintenance schedules, with visibility into upcoming scheduled maintenance.

---

## Features

### 1. **Automatic Work Order Generation**

Work orders are automatically generated from overdue maintenance schedules.

**Configuration:**
- Location: `routes/console.php`
- Schedule: Daily at 00:00 Asia/Jakarta timezone
- Status: **Disabled by default** (commented out)

**To enable:**
1. Uncomment the schedule block in `routes/console.php`
2. Ensure your server crontab has:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

**Manual execution:**
```bash
php artisan maintenance:generate-work-orders
```

---

### 2. **Upcoming Maintenance Visibility** ⭐ NEW

The Work Orders page now displays upcoming maintenance schedules at the top.

**What it shows:**
- Schedules due in the next **14 days**
- Due date with visual badges (Overdue, Today, Tomorrow, X days)
- Asset information
- Maintenance type
- Frequency pattern
- Assigned operator
- Status (Scheduled or WO Exists)

**Actions available:**
- **Create WO** button - Manually generate work order immediately
- **View WO** button - If work order already exists
- **View Schedule** - See full schedule details

**Benefits:**
- ✅ See what maintenance is coming up (no more surprises at midnight!)
- ✅ Plan ahead for parts and resources
- ✅ Generate work orders early if needed
- ✅ Track if work orders already exist for scheduled maintenance

---

## How It Works

### Automatic Generation Process

1. **Scheduler runs** at 00:00 Jakarta time (when enabled)
2. **Checks for overdue schedules** where `next_due_date` < current time
3. **Prevents duplicates** - Won't create if open work order already exists for same asset + maintenance type
4. **Creates work order** with status "assigned"
5. **Auto-assigns** to the user specified in the schedule
6. **Logs activity** to `storage/logs/laravel.log`

### Manual Generation Options

**Option 1: From Upcoming Schedules Widget**
- Go to Work Orders page
- Find schedule in "Upcoming Maintenance Schedules" section
- Click "Create WO" button
- Work order created and schedule updated immediately

**Option 2: From Schedule Detail Page**
- Go to Maintenance Schedules
- Find specific schedule
- Click "Trigger" button
- Work order created immediately

**Option 3: Run Command**
```bash
php artisan maintenance:generate-work-orders
```

---

## Schedule Configuration

### Frequency Types Supported

- **Hourly**: Every X hours (e.g., every 4 hours)
- **Daily**: Every X days
- **Weekly**: Specific day(s) of the week
- **Monthly**: 
  - Specific date (e.g., 5th of every month)
  - Last day of month
  - Specific weekday (e.g., first Monday)
- **Yearly**: Specific date each year

### Next Due Date Calculation

After a work order is completed:
1. Schedule's `last_performed_at` is updated to completion time
2. `next_due_date` is automatically calculated based on frequency type
3. Schedule becomes available for next generation cycle

---

## Work Order Lifecycle

```
Maintenance Schedule (Active)
    ↓ (when overdue or manually triggered)
Work Order Created (Status: assigned)
    ↓
Operator Starts Work (Status: in-progress)
    ↓
Work Completed, Submit for Verification (Status: pending-verification)
    ↓
Engineering Verifies (Status: verified)
    ↓
Requester Closes (Status: completed)
    ↓
Schedule's next_due_date updated
```

---

## Best Practices

### 1. **Use the Upcoming Widget for Planning**
- Check the Work Orders page regularly
- Look at the 14-day forecast
- Generate work orders early if you need lead time for parts

### 2. **Don't Rely Solely on Automatic Generation**
- Automatic generation (when enabled) only runs at midnight
- Use manual generation for urgent or time-sensitive maintenance

### 3. **Keep Schedules Active**
- Inactive schedules won't generate work orders
- Deactivate schedules for equipment that's retired or out of service

### 4. **Monitor Logs**
- Check `storage/logs/laravel.log` for generation activity
- Look for errors if work orders aren't being created

### 5. **Assign Operators to Schedules**
- Work orders inherit assignment from schedules
- Reduces manual assignment work

---

## Troubleshooting

### Work orders not generating automatically

**Check:**
1. Is the schedule in `routes/console.php` uncommented?
2. Is the Laravel scheduler cron job running?
3. Is the schedule active (`is_active = true`)?
4. Is `next_due_date` in the past?
5. Check logs for errors: `storage/logs/laravel.log`

### "Create WO" button not appearing

**Possible reasons:**
1. Work order already exists for that asset + maintenance type
2. User doesn't have permission to create work orders
3. Schedule may be inactive

### Schedule not appearing in upcoming widget

**Check:**
1. Is schedule active?
2. Is `next_due_date` within the next 14 days?
3. Clear cache: `php artisan view:clear`

---

## Technical Details

### Database Tables

- `maintenance_schedules` - Recurring maintenance definitions
- `work_orders` - Individual maintenance tasks
- No direct FK between them (one-way generation)

### Key Models

- `MaintenanceSchedule` - Template for recurring maintenance
- `WorkOrder` - Actual maintenance task instance
- `MaintenanceService` - Business logic for generation and calculations

### Key Methods

- `MaintenanceService::generateWorkOrdersFromSchedules()` - Automatic generation
- `MaintenanceScheduleController::trigger()` - Manual generation
- `MaintenanceService::calculateNextDueDate()` - Date calculation

---

## Future Enhancements

Potential improvements:
- Dashboard widget showing upcoming schedules
- Email notifications for upcoming maintenance
- Advanced lead time configuration per schedule
- Calendar view integration
- Mobile app notifications
- Predictive maintenance based on usage metrics

---

## Support

For questions or issues:
1. Check this guide
2. Review code in:
   - `app/Models/MaintenanceSchedule.php`
   - `app/Services/MaintenanceService.php`
   - `app/Http/Controllers/WorkOrderController.php`
   - `app/Console/Commands/GenerateMaintenanceWorkOrders.php`

