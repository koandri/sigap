# Facility Management Cleaning System - Notification Implementation Summary

## Overview
Implemented comprehensive WhatsApp notifications with Pushover fallback for the Facility Management Cleaning System.

## Changes Made

### 1. CleaningService Enhancement (`app/Services/CleaningService.php`)

**Added Dependencies:**
- Injected `WhatsAppService` and `PushoverService` via constructor

**New Private Methods:**
- `sendNotificationToUser()` - Send notification to individual user with WhatsApp, fallback to Pushover
- `sendNotificationToRole()` - Send notification to all users with specific role
- `formatWhatsAppChatId()` - Format phone number to WhatsApp chat ID format (adds @c.us)

**New Public Notification Methods:**
- `notifyScheduleAlert()` - Alert GA staff about inactive/disposed assets
- `notifyTaskAssigned()` - Notify cleaner about new task assignment
- `notifyPendingTaskReminder()` - Send reminder for pending tasks
- `notifyFlaggedForReview()` - Alert supervisor about flagged submissions
- `notifyMissedTasks()` - Alert supervisor about missed tasks

**Integrated Notifications:**
- `createScheduleAlert()` - Now sends notification when alert is created
- `markMissedTasks()` - Now sends notification about missed tasks
- `flagRandomTasksForReview()` - Now sends notification for each flagged task

### 2. New Console Command (`app/Console/Commands/SendCleaningTaskReminders.php`)

**Purpose:** Send reminders for upcoming cleaning tasks

**Features:**
- Configurable time window (default: 2 hours ahead)
- Sends WhatsApp notifications to assigned cleaners
- Automatic Pushover fallback on failure
- Comprehensive logging

**Usage:**
```bash
php artisan cleaning:send-reminders --hours=2
```

### 3. CleaningRequestController Update (`app/Http/Controllers/CleaningRequestController.php`)

**Added:**
- Injected `CleaningService` via constructor
- Notification sent when ad-hoc cleaning task is created
- Cleaner receives immediate WhatsApp notification for new assignment

### 4. Console Schedule Configuration (`routes/console.php`)

**Added Schedule (commented out, ready to enable):**
- Task reminders: Runs twice daily at 08:00 and 14:00 Asia/Jakarta timezone
- Sends reminders for tasks scheduled within next 2 hours

### 5. Comprehensive Documentation (`guides/CLEANING_NOTIFICATIONS_GUIDE.md`)

**Includes:**
- Complete notification flow explanation
- All notification types with examples
- Phone number formatting logic
- Configuration requirements
- Manual command usage
- Automated schedule setup
- Logging and troubleshooting
- Role requirements
- Best practices and security considerations

## Notification Types Implemented

### 1. Schedule Alerts (🚨)
- **Trigger:** Inactive/disposed asset detected
- **Recipients:** General Affairs role
- **When:** During task generation

### 2. Task Assignments (✅)
- **Trigger:** Task assigned to cleaner
- **Recipients:** Assigned cleaner
- **When:** Real-time (scheduled and ad-hoc tasks)

### 3. Task Reminders (⏰)
- **Trigger:** Pending tasks approaching scheduled time
- **Recipients:** Assigned cleaner
- **When:** Automated (twice daily) or manual command

### 4. Flagged for Review (🔍)
- **Trigger:** Submission randomly flagged
- **Recipients:** General Affairs Supervisor role
- **When:** During task generation

### 5. Missed Tasks Alert (⚠️)
- **Trigger:** Tasks not completed by end of day
- **Recipients:** General Affairs Supervisor role
- **When:** During task generation

## Phone Number Formatting

**Automatic formatting to WhatsApp chat ID:**
```
081234567890 → 6281234567890@c.us
+62-812-3456-7890 → 6281234567890@c.us
62812-3456-7890 → 6281234567890@c.us
```

## Notification Flow

```
1. Trigger Event
   ↓
2. Build Notification Message
   ↓
3. Format WhatsApp Chat ID
   ↓
4. Attempt WhatsApp Delivery
   ↓
   ├─→ Success → Log & Complete
   │
   └─→ Failure → Send Pushover Notification
              → Log Failure
              → Complete
```

## Configuration Required

### Environment Variables
```env
WAHA_API_KEY=your_waha_api_key_here
PUSHOVER_APP_TOKEN=your_pushover_app_token_here
PUSHOVER_USER_TOKEN=your_pushover_user_token_here
```

### User Data
- Users must have `mobilephone_no` field populated
- Users must be active
- Users must have appropriate role assigned

## Testing Commands

### Generate tasks and trigger notifications
```bash
php artisan cleaning:generate-tasks
```

### Send task reminders manually
```bash
php artisan cleaning:send-reminders --hours=2
```

## Enabling Automated Notifications

Edit `routes/console.php` and uncomment:
```php
Schedule::command('cleaning:send-reminders --hours=2')
    ->twiceDaily(8, 14)
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground();
```

## Role-Based Notification Matrix

| Role | Schedule Alerts | Task Assignments | Reminders | Flagged Reviews | Missed Tasks |
|------|----------------|------------------|-----------|-----------------|--------------|
| Cleaner | - | ✓ | ✓ | - | - |
| General Affairs | ✓ | - | - | - | - |
| General Affairs Supervisor | - | - | - | ✓ | ✓ |

## Benefits

1. **Real-time Communication** - Cleaners receive immediate task notifications
2. **Reliability** - Automatic fallback ensures critical alerts are received
3. **Accountability** - Supervisors alerted to missed tasks and reviews
4. **Proactive Management** - Reminders help prevent missed tasks
5. **Asset Management** - Alerts for problematic assets prevent task generation issues
6. **Audit Trail** - All notifications logged for review

## Next Steps

1. Ensure WhatsApp API (WAHA) is configured and session is active
2. Configure Pushover tokens for fallback notifications
3. Verify all users have mobile phone numbers populated
4. Test notification flow with manual commands
5. Enable automated schedules by uncommenting in `routes/console.php`
6. Monitor logs to ensure successful delivery

## Technical Notes

- All notification methods are in `CleaningService` for centralized management
- Service uses dependency injection (readonly properties)
- Phone formatting handles Indonesian numbers (+62 country code)
- Fallback is automatic and logged
- Compatible with existing cleaning system workflow
- No breaking changes to existing functionality

