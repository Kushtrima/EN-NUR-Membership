# 🗄️ Database Backup & Monitoring System

Complete backup and monitoring solution for the EN NUR - MEMBERSHIP Laravel application.

## 📋 Overview

This system provides:
- **Automated database backups** with compression and retention policies
- **Real-time database monitoring** with health checks and alerts
- **Scheduled maintenance** tasks for optimal performance
- **Cloud storage integration** for offsite backups
- **Comprehensive logging** and reporting

## 🛠️ Installation

Run the setup script to configure everything:

```bash
./setup-monitoring.sh
```

Or set up manually:

```bash
# Create directories
mkdir -p storage/app/backups storage/logs
chmod 755 storage/app/backups storage/logs

# Test commands
php artisan db:backup
php artisan db:monitor
```

## 📅 Automated Scheduling

The system runs these tasks automatically:

| Task | Schedule | Description |
|------|----------|-------------|
| Daily Backup | 2:00 AM | Compressed backup, keep 30 days |
| Weekly Cloud Backup | Sunday 3:00 AM | Upload to cloud storage |
| Hourly Monitoring | Every hour | Health checks and alerts |
| Daily Report | 8:00 AM | Comprehensive monitoring report |
| Log Cleanup | Monday 4:00 AM | Remove old log files |

### Setting up Cron Job

Add this line to your crontab (`crontab -e`):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🗄️ Backup Commands

### Basic Backup
```bash
php artisan db:backup
```
Creates a basic database backup in `storage/app/backups/`

### Compressed Backup
```bash
php artisan db:backup --compress
```
Creates a compressed backup (reduces size by ~95%)

### Cloud Backup
```bash
php artisan db:backup --cloud
```
Uploads backup to configured cloud storage (S3, etc.)

### Custom Retention
```bash
php artisan db:backup --keep=14
```
Keep only the last 14 backups (default: 7)

### Combined Options
```bash
php artisan db:backup --compress --cloud --keep=30
```

## 📊 Monitoring Commands

### Basic Monitoring
```bash
php artisan db:monitor
```
Displays comprehensive database health report

### Monitoring with Alerts
```bash
php artisan db:monitor --alert
```
Sends alerts if issues are detected

### Custom Thresholds
```bash
php artisan db:monitor --threshold=200
```
Set custom response time threshold (milliseconds)

### Email Alerts
```bash
php artisan db:monitor --alert --email=admin@example.com
```

## 📈 Monitoring Metrics

The system monitors:

### Connection Health
- ✅ Database connectivity
- ⚡ Response time
- 🔗 Connection type

### Performance Metrics
- 📊 Query execution times
- 📈 Average response time
- 🔍 Complex query performance

### Data Integrity
- 🔍 Orphaned records
- 📊 Data consistency
- ✅ Integrity score (0-100%)

### Disk Usage
- 💾 Database file size
- 📊 Available disk space
- ⚠️ Usage percentage

### Backup Status
- 📅 Last backup time
- 📁 Number of backups
- 📊 Backup file sizes

### Recent Activity
- 👥 New user registrations
- 💳 Payment transactions
- ❌ Failed operations

## 🗑️ Log Management

### Clear Old Logs
```bash
php artisan log:clear
```
Remove log files older than 30 days

### Custom Retention
```bash
php artisan log:clear --days=14
```
Keep logs for only 14 days

### Force Cleanup
```bash
php artisan log:clear --force
```
Skip confirmation prompts

## 📁 File Structure

```
storage/
├── app/
│   └── backups/               # Database backup files
│       ├── backup_sqlite_2024-01-01_12-00-00.sqlite
│       └── backup_sqlite_2024-01-01_12-00-00.sqlite.gz
└── logs/
    ├── backup.log             # Backup operation logs
    ├── monitoring.log         # Monitoring reports
    └── laravel.log           # General application logs
```

## ☁️ Cloud Storage Configuration

To enable cloud backups, configure your cloud storage in `config/filesystems.php`:

### AWS S3 Example
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
],
```

Add to `.env`:
```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket
```

## 🚨 Alert Configuration

### Email Alerts
Configure email settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="EN NUR - MEMBERSHIP"
```

### Alert Thresholds
Default alert triggers:
- Response time > 1000ms
- Data integrity < 90%
- No backups found
- Backup older than 24 hours
- Disk usage > 90%

## 🔧 Troubleshooting

### Common Issues

#### "Command not found"
```bash
# Make sure you're in the project directory
cd /path/to/your/project
php artisan list | grep db:
```

#### "Permission denied"
```bash
# Fix directory permissions
chmod 755 storage/app/backups
chmod 755 storage/logs
```

#### "Database connection failed"
```bash
# Check database configuration
php artisan config:cache
php artisan config:clear
```

#### "Backup file not created"
```bash
# Check disk space
df -h
# Check permissions
ls -la storage/app/
```

### Log Files
Check these files for detailed error information:
- `storage/logs/laravel.log` - General errors
- `storage/logs/backup.log` - Backup operation logs
- `storage/logs/monitoring.log` - Monitoring reports

## 📊 Performance Optimization

### For Large Databases
```bash
# Use compression for large backups
php artisan db:backup --compress

# Increase monitoring thresholds
php artisan db:monitor --threshold=2000
```

### For High-Traffic Sites
```bash
# Monitor more frequently
# Edit app/Console/Kernel.php to run every 30 minutes
$schedule->command('db:monitor')->cron('*/30 * * * *');
```

## 🔐 Security Considerations

1. **Backup Files**: Ensure backup directory is not web-accessible
2. **Cloud Storage**: Use IAM roles with minimal required permissions
3. **Log Files**: Regularly clean sensitive information from logs
4. **Encryption**: Consider encrypting backup files for sensitive data

## 📈 Monitoring Dashboard

For a web-based monitoring interface, you can create a dashboard route:

```php
// In routes/web.php (admin-only)
Route::get('/admin/monitoring', function () {
    // Run monitoring command and display results
    Artisan::call('db:monitor');
    return view('admin.monitoring', [
        'output' => Artisan::output()
    ]);
})->middleware(['auth', 'admin']);
```

## 🎯 Best Practices

1. **Test Backups**: Regularly test backup restoration
2. **Monitor Alerts**: Set up proper email notifications
3. **Review Logs**: Check monitoring logs weekly
4. **Update Retention**: Adjust backup retention based on compliance needs
5. **Document Changes**: Keep this documentation updated

## 📞 Support

For issues or questions:
1. Check log files first
2. Review this documentation
3. Test commands manually
4. Check Laravel documentation for scheduler issues

---

**Last Updated**: June 2024  
**Version**: 1.0  
**Compatibility**: Laravel 11.x, PHP 8.2+ 