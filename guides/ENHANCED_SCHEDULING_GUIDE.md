# Enhanced Maintenance Scheduling System

## Overview

The maintenance scheduling system has been enhanced to support multiple frequency types beyond simple daily intervals. You can now schedule maintenance using:

- **Hourly**: Every X hours (e.g., every 4 hours)
- **Daily**: Every X days (existing functionality, now enhanced)
- **Weekly**: Specific day(s) of the week (e.g., every Monday and Friday)
- **Monthly**: 
  - Specific date (e.g., 5th of every month)
  - Last day of month
  - Specific weekday (e.g., first Monday of each month)
- **Yearly**: Specific date each year (e.g., January 15th)

## What Changed

### Database Changes
- Added `frequency_type` field (enum: hourly, daily, weekly, monthly, yearly)
- Added `frequency_config` field (JSON) to store type-specific configuration
- Made `frequency_days` nullable for backward compatibility
- Existing schedules were migrated to use `daily` frequency type

### Backend Changes

#### 1. **FrequencyType Enum** (`app/Enums/FrequencyType.php`)
- New enum defining all supported frequency types
- Provides labels and descriptions for each type

#### 2. **MaintenanceSchedule Model** (`app/Models/MaintenanceSchedule.php`)
- Added new fillable fields: `frequency_type`, `frequency_config`
- Added cast for `frequency_type` as enum
- Added cast for `frequency_config` as array
- Added `getFrequencyDescriptionAttribute()` to generate human-readable frequency descriptions
- Helper methods for each frequency type's description

#### 3. **MaintenanceService** (`app/Services/MaintenanceService.php`)
- Enhanced `calculateNextDueDate()` method to handle all frequency types
- Added private calculation methods for each frequency type:
  - `calculateHourlyNextDate()`
  - `calculateDailyNextDate()`
  - `calculateWeeklyNextDate()`
  - `calculateMonthlyNextDate()`
  - `calculateYearlyNextDate()`
  - `getNthWeekdayOfMonth()` (helper for "first Monday" type patterns)

#### 4. **MaintenanceScheduleController** (`app/Http/Controllers/MaintenanceScheduleController.php`)
- Updated validation rules to include `frequency_type` and `frequency_config`
- Modified `store()` to calculate next due date using the service
- Modified `update()` to recalculate dates when frequency changes

### Frontend Changes

#### 1. **Create Form** (`resources/views/maintenance/schedules/create.blade.php`)
- Added frequency type selector dropdown
- Added dynamic frequency configuration sections that show/hide based on selected type
- JavaScript functions to manage dynamic form fields:
  - `updateFrequencyFields()` - Shows relevant config section
  - `updateMonthlyType()` - Handles monthly sub-options

#### 2. **Edit Form** (`resources/views/maintenance/schedules/edit.blade.php`)
- Created new edit form with same dynamic functionality as create
- Pre-populates all fields with existing schedule data

#### 3. **Index Page** (`resources/views/maintenance/schedules/index.blade.php`)
- Updated frequency display to use `$schedule->frequency_description`
- Shows human-readable frequency instead of just "X days"

## Usage Examples

### Hourly Maintenance
**Use Case**: Critical equipment requiring frequent checks
```
Type: Hourly
Interval: 4
Result: "Every 4 hours"
```

### Daily Maintenance
**Use Case**: Daily inspections
```
Type: Daily
Interval: 1
Result: "Daily"
```

### Weekly Maintenance
**Use Case**: Weekly preventive maintenance on specific days
```
Type: Weekly
Interval: 1
Days: [1, 3, 5] (Monday, Wednesday, Friday)
Result: "Every Monday, Wednesday, Friday"
```

### Monthly Maintenance - Specific Date
**Use Case**: Monthly inspection on the 5th
```
Type: Monthly
Interval: 1
Sub-type: Date of month
Date: 5
Result: "Monthly on the 5th"
```

### Monthly Maintenance - Last Day
**Use Case**: End-of-month reporting
```
Type: Monthly
Interval: 1
Sub-type: Last day of month
Result: "Monthly on the last day"
```

### Monthly Maintenance - Weekday
**Use Case**: First Monday of each month inspection
```
Type: Monthly
Interval: 1
Sub-type: Day of week
Week: 1 (First)
Day: 1 (Monday)
Result: "Monthly on the first Monday"
```

### Yearly Maintenance
**Use Case**: Annual certification
```
Type: Yearly
Month: 6 (June)
Date: 15
Result: "Yearly on June 15th"
```

## How Next Due Dates are Calculated

### Hourly
Adds the specified number of hours to the base date (last performed or created date).

### Daily
Adds the specified number of days to the base date.

### Weekly
Finds the next occurrence of the specified day(s) of the week, respecting the interval (e.g., every 2 weeks).

### Monthly
- **Date**: Sets the day of the month, adjusting for months with fewer days
- **Last Day**: Always sets to the last day of the month
- **Weekday**: Calculates the Nth occurrence of a specific weekday in the month

### Yearly
Sets the specific month and day, adjusting for leap years if necessary.

## Backward Compatibility

All existing schedules remain functional:
- Migrated to use `frequency_type: 'daily'`
- `frequency_config` populated with `{interval: <frequency_days>}`
- `frequency_days` field retained for compatibility
- Existing schedules will display properly with their frequency

## Technical Notes

### Date Calculation Edge Cases
The system handles various edge cases:
- **February 30th**: Adjusts to February 28th (or 29th in leap years)
- **Fifth Monday**: If a month doesn't have a fifth Monday, uses the last Monday
- **Weekly intervals**: Correctly calculates "every 2 weeks on Monday"
- **Leap years**: Properly handles February 29th

### Validation
The system validates:
- Hourly: 1-24 hours
- Daily: 1-365 days
- Weekly: 1-52 weeks, at least one day selected
- Monthly: 1-12 months, valid date/weekday configuration
- Yearly: Valid month (1-12) and date (1-31)

## Migration Details

The migration (`2025_10_17_030055_add_enhanced_frequency_to_maintenance_schedules_table.php`) automatically:
1. Adds new `frequency_type` and `frequency_config` columns
2. Migrates existing schedules to use `daily` frequency type
3. Populates `frequency_config` with interval from `frequency_days`
4. Makes `frequency_days` nullable

## Automatic Work Order Generation

The system automatically generates work orders from overdue maintenance schedules.

### How It Works

1. **Scheduled Command**: `maintenance:generate-work-orders` can run daily at midnight (Asia/Jakarta timezone)
2. **Checks Overdue Schedules**: Finds all active schedules where `next_due_date` < current time
3. **Prevents Duplicates**: Only creates work orders if no open work order exists for the same asset + maintenance type
4. **Auto-Assignment**: Generated work orders are automatically assigned to the user specified in the schedule
5. **Status**: Work orders are created with status "assigned" (ready for the operator to start work)

### Command Usage

**Manual Execution** (for testing or immediate generation):
```bash
php artisan maintenance:generate-work-orders
```

**Automated Execution**: Currently disabled by default. To enable, uncomment the schedule in `routes/console.php`

### Scheduler Setup

The scheduler is configured in `routes/console.php` to run daily at 00:00 Asia/Jakarta timezone. It is currently **disabled by default** (commented out).

**To enable automatic work order generation:**
1. Open `routes/console.php`
2. Uncomment the `Schedule::command('maintenance:generate-work-orders')` block
3. Ensure your server's crontab has this entry:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This single cron entry will run Laravel's scheduler every minute, which then executes scheduled tasks (like our daily midnight work order generation) at their specified times.

### Monitoring

The command logs its activity:
- **Success**: Logs the number of work orders generated
- **Failure**: Logs errors with stack traces
- **Location**: Check `storage/logs/laravel.log`

### Manual Trigger (Alternative)

You can also manually trigger a work order from a specific schedule via the UI using the "Trigger" button, which immediately creates a work order and updates the schedule's next due date.

## Testing Recommendations

1. **Create schedules** with each frequency type
2. **Verify** the calculated next due dates are correct
3. **Edit existing schedules** and change frequency types
4. **Trigger work orders** from schedules to ensure integration works
5. **Check** that overdue detection works for all frequency types
6. **Test automatic generation**: Set a schedule's next_due_date to the past and run the command
7. **Monitor logs**: Check that the command executes successfully

## Future Enhancements

Potential improvements for the future:
- **Time-specific scheduling**: Schedule at specific times (e.g., 9:00 AM daily)
- **Blackout dates**: Skip holidays or specific dates
- **Advanced patterns**: Every other month, quarterly, bi-annually
- **Conditional scheduling**: Based on usage hours or cycles
- **Calendar integration**: Sync with external calendars

## Support

If you encounter any issues or need clarification, refer to:
- Model methods in `app/Models/MaintenanceSchedule.php`
- Service methods in `app/Services/MaintenanceService.php`
- Controller logic in `app/Http/Controllers/MaintenanceScheduleController.php`

