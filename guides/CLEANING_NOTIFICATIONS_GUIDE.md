# Facility Management Cleaning System - Notifications Guide

## Overview

![Cleaning Notifications Guide](/guides-imgs/cleaning-notifications-guide.png)

The Facility Management Cleaning System includes a comprehensive notification system that sends alerts via **WhatsApp** with automatic **Pushover fallback** when WhatsApp delivery fails.

## Notification Flow

### Primary Method: WhatsApp
- Messages are sent to users' mobile phone numbers (from the `mobilephone_no` field)
- Phone numbers are automatically formatted with `@c.us` suffix for WhatsApp API
- Indonesia country code (+62) is automatically added if not present

### Fallback Method: Pushover
Pushover notifications are triggered when:
- WhatsApp message fails to send
- User has no mobile phone number configured
- WhatsApp API is unreachable

## Notification Types

### 1. Schedule Alerts
**Triggered when:** An inactive or disposed asset is detected in a cleaning schedule

**Recipients:** Users with "General Affairs" role

**Message includes:**
- Alert type (Asset Inactive / Asset Disposed)
- Schedule name
- Location
- Asset/Item details
- Detection timestamp

**Example:**
```
🚨 Cleaning Schedule Alert

Alert Type: Asset Inactive
Schedule: Daily Office Cleaning
Location: Main Office - Floor 1
Asset: AC-001 - Air Conditioner Unit
Detected: 18 Oct 2025 08:30

⚠️ Tasks will not be generated until this is resolved.
```

### 2. Task Assignments
**Triggered when:** A cleaning task is assigned to a cleaner (both scheduled and ad-hoc)

**Recipients:** The assigned cleaner

**Message includes:**
- Task number
- Schedule name
- Location
- Asset/Item details
- Scheduled date and time

**Example:**
```
✅ New Cleaning Task Assigned

Task Number: CT-251018-0001
Schedule: Hourly Restroom Cleaning
Location: Main Office - Restroom A
Item: Toilet and Sink Area
Scheduled: 18 Oct 2025 10:00

📱 Please complete this task on time.
```

### 3. Task Reminders
**Triggered when:** Automated reminders are sent for pending tasks

**Recipients:** The assigned cleaner

**Message includes:**
- Task number
- Location
- Asset/Item details
- Due date and time

**Example:**
```
⏰ Cleaning Task Reminder

Task Number: CT-251018-0001
Location: Main Office - Restroom A
Item: Toilet and Sink Area
Due: 18 Oct 2025 10:00

⚠️ Please complete this task soon!
```

### 4. Flagged for Review
**Triggered when:** A task is randomly flagged for mandatory supervisor review

**Recipients:** Users with "General Affairs Supervisor" role

**Message includes:**
- Task number
- Location
- Submission timestamp

**Example:**
```
🔍 Cleaning Task Flagged for Review

Task Number: CT-251017-0045
Location: Main Office - Floor 2
Submitted: 17 Oct 2025 16:30

📋 This task requires your review before batch approval.
```

### 5. Missed Tasks Alert
**Triggered when:** Tasks are marked as missed at end of day

**Recipients:** Users with "General Affairs Supervisor" role

**Message includes:**
- Date
- Number of missed tasks

**Example:**
```
⚠️ Missed Cleaning Tasks Alert

Date: 17 Oct 2025
Missed Tasks: 3

🔔 Please review and take necessary action.
```

## Phone Number Formatting

The system automatically formats phone numbers for WhatsApp:

1. Removes all non-numeric characters
2. Adds Indonesia country code (+62) if not present
3. Removes leading zero
4. Appends `@c.us` suffix

**Examples:**
- `081234567890` → `6281234567890@c.us`
- `+62-812-3456-7890` → `6281234567890@c.us`
- `62812-3456-7890` → `6281234567890@c.us`

## Automated Notification Schedule

### Daily Task Generation (00:00 Asia/Jakarta)
Sends notifications for:
- Schedule alerts (asset issues)
- Missed tasks summary

### Task Reminders (08:00 and 14:00 Asia/Jakarta)
Sends reminders for:
- Pending tasks scheduled within next 2 hours

### Real-time Notifications
Sent immediately when:
- Task is assigned
- Task is flagged for review

## Configuration

### Environment Variables Required

```env
# WhatsApp API Configuration
WAHA_API_KEY=your_waha_api_key_here

# Pushover Configuration
PUSHOVER_APP_TOKEN=your_pushover_app_token_here
PUSHOVER_USER_TOKEN=your_pushover_user_token_here
```

### User Requirements
- Users must have `mobilephone_no` field filled in their profile
- Users must be active (`active = true`)
- Users must have appropriate role assigned

## Manual Commands

### Generate Tasks (with notifications)
```bash
php artisan cleaning:generate-tasks
```

Generates tasks for today and sends:
- Schedule alerts for problematic assets
- Missed tasks notifications
- Flagged review notifications

### Send Reminders
```bash
php artisan cleaning:send-reminders --hours=2
```

Sends reminders for tasks scheduled within the specified hours.

**Options:**
- `--hours=N` : Hours ahead to check for pending tasks (default: 2)

## Enabling Automated Notifications

Edit `routes/console.php` and uncomment the schedule commands:

```php
// Enable task generation and notifications
Schedule::command('cleaning:generate-tasks')
    ->dailyAt('00:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground();

// Enable task reminders
Schedule::command('cleaning:send-reminders --hours=2')
    ->twiceDaily(8, 14)
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground();
```

## Logging

All notifications are logged with relevant context:

**Successful WhatsApp delivery:**
```
[INFO] WhatsApp message sent successfully
  chatId: 6281234567890@c.us
  response: {...}
```

**WhatsApp failure with Pushover fallback:**
```
[WARNING] WhatsApp notification failed, sending Pushover fallback
  user_id: 123
  user_name: John Doe
  chat_id: 6281234567890@c.us
  notification_type: Task Assignment
```

**User without phone number:**
```
[WARNING] User has no mobile phone number for WhatsApp notification
  user_id: 123
  user_name: Jane Smith
  notification_type: Task Assignment
```

## Testing Notifications

### Test Schedule Alert
1. Create a cleaning schedule with an active asset
2. Mark the asset as inactive
3. Run task generation: `php artisan cleaning:generate-tasks`
4. Check logs for notification attempts

### Test Task Assignment
1. Create an ad-hoc cleaning request
2. Handle the request and assign to a cleaner
3. Check that the cleaner receives WhatsApp notification

### Test Reminders
1. Create tasks scheduled within next 2 hours
2. Run: `php artisan cleaning:send-reminders --hours=2`
3. Assigned cleaners should receive reminders

## Troubleshooting

### WhatsApp not sending
1. Check `WAHA_API_KEY` is correctly configured
2. Verify WhatsApp session is active
3. Check logs for API error responses
4. Verify phone number format

### Pushover not working
1. Check `PUSHOVER_APP_TOKEN` and `PUSHOVER_USER_TOKEN`
2. Verify tokens at pushover.net
3. Check logs for API errors

### User not receiving notifications
1. Verify user has `mobilephone_no` filled
2. Check user is active (`active = true`)
3. Verify user has correct role assigned
4. Check logs for delivery attempts

## Role Requirements

The following roles receive notifications:

| Role | Notification Types |
|------|-------------------|
| Cleaner | Task assignments, Task reminders |
| General Affairs | Schedule alerts |
| General Affairs Supervisor | Flagged reviews, Missed tasks |

## Best Practices

1. **Keep phone numbers updated** - Regularly verify users' mobile phone numbers
2. **Monitor logs** - Review notification logs to ensure delivery
3. **Test regularly** - Run manual commands to test notification flow
4. **Configure both services** - Ensure both WhatsApp and Pushover are configured for reliability
5. **Role assignments** - Ensure users have correct roles for receiving relevant notifications

## Security Considerations

- Phone numbers are logged but not exposed in user-facing interfaces
- API keys are stored in environment variables, not in code
- Failed deliveries trigger fallback, ensuring critical alerts are received
- All notification attempts are logged for audit purposes

