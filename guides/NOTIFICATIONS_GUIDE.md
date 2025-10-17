# Notifications System Guide

## Overview

SIGAP uses a dual-channel notification system for reliable message delivery:
1. **WhatsApp** (Primary) - Instant notifications via WAHA API
2. **Pushover** (Fallback) - High-priority alerts when WhatsApp fails

## Architecture

### WhatsApp Notifications
- **Service**: `App\Services\WhatsAppService`
- **API Provider**: WAHA (WhatsApp HTTP API)
- **Endpoint**: `https://waha.suryagroup.app/api`
- **Features**:
  - Text messages with Markdown formatting
  - Image attachments
  - File attachments
  - Link previews

### Pushover Notifications
- **Service**: `App\Services\PushoverService`
- **API Provider**: Pushover
- **Endpoint**: `https://api.pushover.net/1/messages.json`
- **Features**:
  - High-priority alerts (Priority 2)
  - HTML formatting
  - Automatic fallback when WhatsApp fails

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# WAHA WhatsApp API
WAHA_API_KEY=your_waha_api_key_here

# WhatsApp Chat IDs (format: 62xxxxxxxxxx@c.us for individual or xxxxx@g.us for groups)
DEV_WHATSAPP_CHAT_ID=62811337678@c.us
ENGINEERING_WHATSAPP_GROUP=12132132130@g.us
WAREHOUSE_WHATSAPP_GROUP=12132132130@g.us

# Pushover API (Fallback notifications)
PUSHOVER_APP_TOKEN=your_pushover_app_token
PUSHOVER_USER_TOKEN=your_pushover_user_token
```

### Chat ID Format
- **Individual**: `62xxxxxxxxxx@c.us` (country code + phone number + @c.us)
- **Group**: `xxxxxxxxxx@g.us` (group ID + @g.us)

### Getting Chat IDs
To find WhatsApp group IDs, use the WAHA API dashboard or check WhatsApp Web developer tools.

## Notification Types

### 1. Asset Disposal Notifications
**Trigger**: When an asset is marked as disposed  
**Recipients**: Engineering WhatsApp Group  
**Service**: `AssetDisposalService`

**Message Format**:
```
🚨 Asset Disposal Alert

Asset: [Asset Name] ([Code])
WO: [Work Order Number]
Disposal Reason: [Reason]

⚠️ X maintenance schedule(s) have been automatically deactivated.

Please review: [Link]
```

### 2. User Registration Notifications
**Trigger**: When a new user is created  
**Recipients**: The new user's mobile number  
**Controller**: `UserController`

**Contains**:
- Username and login credentials
- System access information
- Initial password (must be changed on first login)

### 3. Warehouse Inventory Notifications
**Trigger**: When items are added to warehouse positions  
**Recipients**: Warehouse WhatsApp Group  
**Controller**: `ShelfInventoryController`

**Message Format**:
```
Item '[Item Name]' (Qty: [Quantity] [Unit]) has been added to position [Position Code] in warehouse '[Warehouse Name]' by [User Name]
```

### 4. Approval Workflow Notifications

#### a. Approval Request
**Trigger**: New approval required  
**Recipients**: Assigned approvers

**Message Format**:
```
📝 New Approval Request

Form: [Form Name]
Submission: [Code]
Submitted by: [Name]
Step: [Step Name]

Please review: [Link]
```

#### b. Approval Completed
**Trigger**: All approvals granted  
**Recipients**: Original submitter

**Message Format**:
```
✅ Approval Completed

Form: [Form Name]
Submission: [Code]
Status: APPROVED

Your submission has been fully approved!

View: [Link]
```

#### c. Approval Rejected
**Trigger**: Approval rejected  
**Recipients**: Original submitter

**Message Format**:
```
❌ Approval Rejected

Form: [Form Name]
Submission: [Code]
Rejected by: [Name]
Reason: [Rejection Reason]

View: [Link]
```

#### d. Approval Escalation
**Trigger**: Overdue approval  
**Recipients**: Escalation targets

**Message Format**:
```
⚠️ Approval Escalated

Form: [Form Name]
Submission: [Code]
Original Approver: [Name]
Overdue since: [Time]

Please review urgently: [Link]
```

## WhatsApp Message Formatting

### Supported Markdown
```
*Bold Text*           → Bold
_Italic Text_         → Italic
~Strikethrough~       → Strikethrough
```Code Block```      → Code
https://example.com   → Auto-link with preview
```

### Emojis
Common emojis used in notifications:
- 🚨 Alert/Warning
- ⚠️ Caution
- ✅ Success/Approved
- ❌ Rejected/Failed
- 📝 Document/Form
- 🔴 Critical/Disposed

## Pushover Fallback

### When Triggered
Pushover notifications are automatically sent when:
- WhatsApp API is unreachable
- WAHA service is down
- Message delivery fails
- Invalid chat ID

### Message Format
Pushover messages include:
- **Title**: "WhatsApp Notification Failed"
- **Notification Type**: Type of failed notification
- **Intended Recipient**: Original WhatsApp chat ID
- **Timestamp**: When the failure occurred
- **Original Message**: Full content that failed to send

### HTML Formatting
```html
<b>Bold</b>
<i>Italic</i>
<u>Underline</u>
<font color="#0000ff">Colored text</font>
<a href="url">Link</a>
```

## Testing Notifications

### Test WhatsApp Notification
```bash
php artisan whatsapp:test [chatId]
```

**Example**:
```bash
php artisan whatsapp:test 62811337678@c.us
```

This sends a test asset disposal notification to verify:
- WAHA API connectivity
- Authentication
- Message delivery
- Pushover fallback (if WhatsApp fails)

## Developer Usage

### Sending Text Messages

```php
use App\Services\WhatsAppService;
use App\Services\PushoverService;

public function __construct(
    private readonly WhatsAppService $whatsAppService,
    private readonly PushoverService $pushoverService
) {}

public function sendNotification(): void
{
    $chatId = '62811337678@c.us';
    $message = "*Alert*\n\nYour message here";
    
    $success = $this->whatsAppService->sendMessage($chatId, $message);
    
    if (!$success) {
        // Automatic Pushover fallback
        $this->pushoverService->sendWhatsAppFailureNotification(
            'Notification Type',
            $chatId,
            $message
        );
    }
}
```

### Sending Images

```php
$this->whatsAppService->sendImage(
    chatId: '62811337678@c.us',
    fileUrl: 'https://example.com/image.jpg',
    filename: 'report.jpg',
    mimetype: 'image/jpeg',
    caption: 'Monthly report'
);
```

### Sending Files

```php
$this->whatsAppService->sendFile(
    chatId: '62811337678@c.us',
    fileUrl: 'https://example.com/document.pdf',
    filename: 'report.pdf',
    mimetype: 'application/pdf',
    caption: 'Q1 Report'
);
```

### Sending to Multiple Recipients

```php
$chatIds = [
    '62811111111@c.us',
    '62822222222@c.us',
    '12132132130@g.us', // Group
];

$results = $this->whatsAppService->sendToMultiple($chatIds, $message);

foreach ($results as $chatId => $success) {
    if (!$success) {
        $this->pushoverService->sendWhatsAppFailureNotification(
            'Broadcast Message',
            $chatId,
            $message
        );
    }
}
```

## Troubleshooting

### WhatsApp Messages Not Sending

1. **Check API Key**:
   ```bash
   php artisan tinker
   >>> env('WAHA_API_KEY')
   ```

2. **Verify Chat ID Format**:
   - Individual: `62xxxxxxxxxx@c.us`
   - Group: `xxxxxxxxxx@g.us`

3. **Test WAHA API**:
   ```bash
   curl -X POST https://waha.suryagroup.app/api/sendText \
     -H "X-Api-Key: YOUR_KEY" \
     -H "Content-Type: application/json" \
     -d '{"chatId":"62811337678@c.us","text":"Test","session":"ptsiap"}'
   ```

4. **Check Logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep WhatsApp
   ```

### Pushover Not Working

1. **Verify Tokens**:
   ```bash
   php artisan tinker
   >>> env('PUSHOVER_APP_TOKEN')
   >>> env('PUSHOVER_USER_TOKEN')
   ```

2. **Test Pushover API**:
   ```bash
   curl -X POST https://api.pushover.net/1/messages.json \
     -d "token=YOUR_APP_TOKEN" \
     -d "user=YOUR_USER_TOKEN" \
     -d "message=Test" \
     -d "priority=2"
   ```

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| "WAHA_API_KEY not configured" | Missing env variable | Add `WAHA_API_KEY` to `.env` |
| "Chat ID not configured" | Missing group/chat ID | Add group IDs to `.env` |
| Message sent but not received | Invalid chat ID | Verify chat ID format |
| Pushover always triggering | WhatsApp always failing | Check WAHA service status |

## Security Considerations

### API Keys
- Never commit API keys to version control
- Use `.env` file for all credentials
- Rotate keys periodically
- Use different keys for staging/production

### Chat IDs
- Store sensitive chat IDs in `.env`
- Don't hardcode phone numbers in code
- Validate phone numbers before sending
- Use groups for team notifications

### Message Content
- Don't include sensitive credentials in messages
- Sanitize user input before sending
- Log notification failures for audit
- Implement rate limiting for broadcast messages

## Best Practices

1. **Always implement Pushover fallback** for critical notifications
2. **Use markdown formatting** for better readability
3. **Include relevant links** in messages for quick access
4. **Keep messages concise** - WhatsApp works best with short messages
5. **Test notifications** in development before deploying
6. **Monitor logs** for failed deliveries
7. **Use groups** for team notifications instead of multiple individual messages

## Monitoring

### Log Locations
- WhatsApp logs: `storage/logs/laravel.log` (search "WhatsApp")
- Pushover logs: `storage/logs/laravel.log` (search "Pushover")

### Success Metrics
Monitor these in your logs:
- Total notifications sent
- WhatsApp success rate
- Pushover fallback rate
- Delivery failures

### Alerts
Set up monitoring for:
- High Pushover fallback rate (indicates WhatsApp issues)
- Repeated delivery failures
- API authentication failures

## Support

### WAHA API
- Dashboard: https://waha.suryagroup.app
- Documentation: Check WAHA documentation
- Session: `ptsiap`

### Pushover
- Dashboard: https://pushover.net
- Documentation: https://pushover.net/api

## Changelog

### Version 1.0.0 (2025-10-17)
- Initial implementation of WhatsApp notification system
- Integration with WAHA API
- Pushover fallback system
- Asset disposal notifications
- User registration notifications
- Warehouse inventory notifications
- Approval workflow notifications
- Test command for debugging

