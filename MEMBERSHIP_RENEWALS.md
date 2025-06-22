# Membership Renewal Notification System

## Overview

The membership renewal notification system automatically tracks membership expiration dates and provides super admins with tools to send renewal reminders to users whose memberships are approaching expiration.

## Features

### 🔔 **Automated Tracking**
- Automatically creates renewal records for all completed membership payments
- Daily scheduled check to update expiration days and detect renewed memberships
- Calculates days until expiry based on 1-year membership duration

### 📊 **Super Admin Dashboard**
- Dedicated "Membership Renewal Notifications" section (super admins only)
- Real-time statistics: Active memberships, expiring within 30/7 days, expired
- Color-coded priority system:
  - **Red (Expired)**: Membership has expired
  - **Yellow (Critical)**: 1 day remaining
  - **Orange (Urgent)**: 2-7 days remaining  
  - **Blue (Warning)**: 8-30 days remaining

### 📧 **Email Notifications**
- One-click "Send Notification" button for each user
- Automatic email content based on days remaining:
  - **Expired**: "Membership Expired - Immediate Renewal Required"
  - **1 Day**: "Membership Expires Tomorrow - Urgent Renewal Required"
  - **2-7 Days**: "Membership Expires in X Days - Renewal Required"
  - **8-30 Days**: "Membership Renewal Reminder - X Days Remaining"
- Professional email template with membership details and renewal link
- Tracks sent notifications to prevent spam

### 👁️ **Management Tools**
- **Hide Button**: Remove users from notification list (e.g., if they decide not to renew)
- **Notification History**: See when notifications were sent and for how many days
- **Automatic Renewal Detection**: System detects when users renew and creates new renewal records

## Usage Instructions

### For Super Admins

1. **Access Dashboard**: Login as super admin and visit `/admin/dashboard`
2. **View Renewals**: Scroll to "Membership Renewal Notifications" section
3. **Send Notifications**: Click "📧 Send Notification" for any user
4. **Hide Users**: Click "👁️ Hide" to remove users who won't renew
5. **Monitor Statistics**: Check the summary cards for overview

### Email Notification Process

1. Click "Send Notification" button
2. System sends personalized email with:
   - Days remaining until expiration
   - Membership start/end dates
   - Direct link to renewal page
   - Professional branding and styling
3. Button shows "✅ Sent!" confirmation
4. Notification is logged to prevent duplicates

## Automated Processes

### Daily Scheduled Task
The system runs `membership:check-renewals` daily at 6:00 AM to:
- Create renewal records for new membership payments
- Update days until expiry for all active renewals
- Detect and process renewed memberships
- Mark expired memberships

### Renewal Detection
When a user makes a new membership payment:
- Old renewal record is marked as "renewed"
- New renewal record is created for the new membership
- System maintains complete audit trail

## Database Structure

### `membership_renewals` Table
- `user_id`: User who owns the membership
- `payment_id`: Original membership payment
- `membership_start_date`: When membership started
- `membership_end_date`: When membership expires
- `days_until_expiry`: Current days remaining
- `notifications_sent`: Array of notification days sent
- `last_notification_sent_at`: Timestamp of last notification
- `is_hidden`: Admin can hide from notification list
- `is_expired`: Whether membership has expired
- `is_renewed`: Whether user has renewed
- `renewal_payment_id`: New payment if renewed

## Commands

### Check Renewals (Scheduled Daily)
```bash
php artisan membership:check-renewals
```
Creates missing renewal records, updates expiry days, detects renewals.

### Test Scenarios (Development Only)
```bash
# Create test scenarios with different expiry dates
php artisan membership:test-scenarios

# Reset to original dates
php artisan membership:test-scenarios --reset
```

## API Endpoints (Super Admin Only)

- `POST /admin/renewals/{renewal}/notify` - Send notification email
- `POST /admin/renewals/{renewal}/hide` - Hide renewal from list
- `POST /admin/renewals/{renewal}/show` - Show hidden renewal
- `GET /admin/renewals/{renewal}/details` - Get renewal details

## Security

- All renewal management restricted to super admins only
- CSRF protection on all AJAX requests
- Email notifications use secure mail configuration
- Audit trail maintained for all actions

## Email Configuration

Ensure your `.env` file has proper mail configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-from-email
MAIL_FROM_NAME="Your Organization"
```

## Troubleshooting

### No Renewal Records
Run: `php artisan membership:check-renewals`

### Emails Not Sending
Check mail configuration and logs: `storage/logs/laravel.log`

### Missing Notifications Section
Ensure user has super admin role: `role = 'super_admin'`

### Testing
Use test scenarios: `php artisan membership:test-scenarios`

## Support

For issues or questions about the membership renewal system, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Renewal logs: `storage/logs/membership-renewals.log`
3. Email logs in your mail provider dashboard 