# Installation and Configuration Guide

This document provides step-by-step instructions for installing and configuring the Residence Booking plugin.

## Prerequisites

### System Requirements
- **Moodle Version**: 4.0 or higher (2022041900)
- **PHP Version**: 7.4 or higher (recommended: PHP 8.1+)
- **Database**: MySQL 8.0+ or PostgreSQL 12+
- **Disk Space**: ~50MB for plugin files and dependencies

### Required Dependencies
- **local_status plugin**: Must be installed before residence booking
- **Theme Stream** (optional): For enhanced UI features (custom dialogs, export)

### PHP Extensions
- `pdo_mysql` or `pdo_pgsql` (database connectivity)
- `json` (JSON processing)
- `mbstring` (multibyte string handling for Arabic support)
- `intl` (internationalization support)

## Installation Process

### Step 1: Download and Extract

1. **Download the plugin** from the repository or plugin directory
2. **Extract the archive** to your Moodle installation
3. **Place files** in `/path/to/moodle/local/residencebooking/`

### Step 2: Install Dependencies

#### Install local_status Plugin
```bash
# If using git
cd /path/to/moodle/local/
git clone https://github.com/your-org/moodle-local_status.git status

# Or extract from zip to local/status/
```

### Step 3: File Permissions

Ensure proper file permissions:
```bash
# Set ownership (adjust user/group as needed)
sudo chown -R www-data:www-data /path/to/moodle/local/residencebooking/

# Set permissions
sudo chmod -R 755 /path/to/moodle/local/residencebooking/
sudo chmod -R 644 /path/to/moodle/local/residencebooking/lang/
```

### Step 4: Run Moodle Upgrade

1. **Access Moodle** via web browser
2. **Navigate** to: `Site administration → Notifications`
3. **Follow upgrade prompts** to install the plugin
4. **Confirm installation** when prompted

#### Command Line Installation (Alternative)
```bash
cd /path/to/moodle/
sudo -u www-data php admin/cli/upgrade.php
```

### Step 5: Verify Installation

Check that the plugin is installed:
1. Go to `Site administration → Plugins → Plugins overview`
2. Search for "Residence Booking"
3. Verify status shows "Enabled"

## Initial Configuration

### Step 1: Plugin Settings

Navigate to: `Site administration → Plugins → Local plugins → Residence Booking`

#### Basic Settings
- **Enable Accommodation Booking**: ✅ Check to enable the plugin
- **Default User Role**: Select appropriate role for users

### Step 2: Capabilities Configuration

#### Assign Core Capabilities

Navigate to: `Site administration → Users → Permissions → Define roles`

##### For Students/Users (to submit requests):
```
Capability: local/residencebooking:submitrequest
Permission: Allow
Context: System or Course level
```

##### For Staff/Managers (to view requests):
```
Capability: local/residencebooking:viewbookings
Permission: Allow
Context: System level
```

##### For Administrators (full management):
```
Capability: local/residencebooking:manage
Permission: Allow
Context: System level
```

#### Workflow Capabilities

For multi-stage approval workflow:

##### Department Heads:
```
local/status:residence_workflow_step1 → Allow
local/status:residence_workflow_step2 → Allow
```

##### Senior Managers:
```
local/status:residence_workflow_step3 → Allow
local/status:residence_workflow_step4 → Allow
```

### Step 3: Database Setup

#### Verify Tables Created

Check that these tables exist in your database:
```sql
SHOW TABLES LIKE 'mdl_local_residencebooking%';
```

Expected tables:
- `mdl_local_residencebooking_request`
- `mdl_local_residencebooking_types`
- `mdl_local_residencebooking_purpose`

#### Initialize Sample Data

Navigate to: `Site administration → Development → Execute DB queries`

**Insert Sample Accommodation Types:**
```sql
INSERT INTO mdl_local_residencebooking_types (type_name_en, type_name_ar, deleted) VALUES
('Single Room', 'غرفة مفردة', 0),
('Double Room', 'غرفة مزدوجة', 0),
('Suite', 'جناح', 0),
('Apartment', 'شقة', 0);
```

**Insert Sample Purposes:**
```sql
INSERT INTO mdl_local_residencebooking_purpose (purpose_name_en, purpose_name_ar, description_en, description_ar, deleted) VALUES
('Business Trip', 'رحلة عمل', 'Official business travel', 'سفر رسمي للعمل', 0),
('Training', 'تدريب', 'Educational or professional training', 'تدريب تعليمي أو مهني', 0),
('Conference', 'مؤتمر', 'Attending conferences or seminars', 'حضور المؤتمرات أو الندوات', 0),
('Medical', 'طبي', 'Medical treatment or consultation', 'علاج طبي أو استشارة', 0);
```

### Step 4: Workflow Status Setup

#### Verify local_status Integration

Check that workflow statuses exist:
```sql
SELECT id, type_id, status_name_en, status_name_ar 
FROM mdl_local_status 
WHERE type_id = 3;
```

If missing, insert workflow statuses:
```sql
INSERT INTO mdl_local_status (id, type_id, status_name_en, status_name_ar) VALUES
(15, 3, 'Initial', 'مبدئي'),
(16, 3, 'Leader 1 Review', 'مراجعة القائد الأول'),
(17, 3, 'Leader 2 Review', 'مراجعة القائد الثاني'),
(18, 3, 'Leader 3 Review', 'مراجعة القائد الثالث'),
(19, 3, 'Boss Review', 'مراجعة المدير'),
(20, 3, 'Approved', 'موافق عليه'),
(21, 3, 'Rejected', 'مرفوض');
```

**Note**: The workflow system has been updated so that rejections from Leader 2, 3, and Boss levels return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status.

## Advanced Configuration

### Language Configuration

#### Enable Multilingual Interface

1. **Install Arabic Language Pack**:
   - `Site administration → Language → Language packs`
   - Install Arabic (ar)

2. **Configure Language Settings**:
   - `Site administration → Language → Language settings`
   - Enable "Display language menu"

#### Custom Language Strings

To customize language strings:
1. Navigate to: `Site administration → Language → Language customisation`
2. Select "local_residencebooking" component
3. Modify strings as needed

### Theme Integration

#### With Theme Stream

If using Theme Stream, additional features are available:
- Custom dialog boxes for confirmations
- Enhanced export functionality
- Improved responsive design

Configure in theme settings:
```css
/* Custom CSS for residence booking */
.residence-booking-table {
    /* Enhanced styling */
}
```

### Email Configuration

#### Setup Email Notifications

Create custom email templates:

1. **Navigate to**: `Site administration → Messaging → Notification settings`
2. **Create templates** for:
   - Request submitted
   - Status changed
   - Request approved/rejected

#### SMTP Configuration

Ensure SMTP is configured for email notifications:
```php
// In config.php
$CFG->smtphosts = 'your.smtp.server';
$CFG->smtpuser = 'your-email@domain.com';
$CFG->smtppass = 'your-password';
$CFG->smtpsecure = 'tls';
$CFG->smtpport = 587;
```

### Performance Optimization

#### Database Indexing

Add custom indexes for better performance:
```sql
-- Index on request status for fast filtering
CREATE INDEX idx_residence_status ON mdl_local_residencebooking_request(status_id);

-- Index on guest name for search
CREATE INDEX idx_residence_guest ON mdl_local_residencebooking_request(guest_name);

-- Index on dates for date range queries
CREATE INDEX idx_residence_dates ON mdl_local_residencebooking_request(start_date, end_date);
```

#### Caching Configuration

Enable caching for better performance:
```php
// In config.php
$CFG->cachejs = true;
$CFG->yuicombo = true;

// Enable application cache
$CFG->alternative_file_system_class = '\tool_objectfs\local\store\s3\file_system';
```

## Security Configuration

### Input Validation

Ensure proper input validation is enabled:
```php
// In config.php (for development)
$CFG->debug = E_ALL & ~E_NOTICE;
$CFG->debugdisplay = 0; // Don't display errors to users
```

### Capability Security

#### Principle of Least Privilege

Only grant necessary capabilities:
```
Student role:
- local/residencebooking:submitrequest ✅
- local/residencebooking:viewbookings ❌ (only own requests)
- local/residencebooking:manage ❌

Manager role:
- local/residencebooking:submitrequest ✅
- local/residencebooking:viewbookings ✅
- local/residencebooking:manage ✅
```

### CSRF Protection

CSRF protection is enabled by default. Ensure it's working:
1. Check that `require_sesskey()` is called in all state-changing operations
2. Verify forms include sesskey hidden fields

## Testing Installation

### Functional Testing

#### Test User Journey
1. **Login as student** → Submit a request
2. **Login as manager** → View and approve request
3. **Check workflow** → Verify status transitions
4. **Test languages** → Switch between English/Arabic

#### Test Workflow
```bash
# Test database connectivity
php -r "require_once('/path/to/moodle/config.php'); 
        echo 'DB Connection: ' . ($DB ? 'OK' : 'Failed') . '\n';"

# Test plugin installation
php admin/cli/upgrade.php --non-interactive
```

### Performance Testing

#### Load Testing
```bash
# Test concurrent requests
ab -n 100 -c 10 http://your-moodle-site/local/residencebooking/index.php
```

#### Database Performance
```sql
-- Check query performance
EXPLAIN SELECT * FROM mdl_local_residencebooking_request 
WHERE status_id = 15 AND start_date >= UNIX_TIMESTAMP();
```

## Troubleshooting

### Common Installation Issues

#### Plugin Not Appearing
- **Check file permissions**: Ensure web server can read files
- **Verify file structure**: Confirm files are in correct directory
- **Clear cache**: `Site administration → Development → Purge all caches`

#### Database Errors
```sql
-- Check table existence
SHOW TABLES LIKE 'mdl_local_residencebooking%';

-- Verify foreign key constraints
SHOW CREATE TABLE mdl_local_residencebooking_request;
```

#### Permission Denied Errors
- **Check capabilities**: Verify role assignments
- **Context levels**: Ensure capabilities assigned at correct context
- **Role inheritance**: Check if roles inherit properly

#### Language Issues
- **Install language packs**: Ensure both English and Arabic are installed
- **Check string files**: Verify language files are present and readable
- **Clear language cache**: Force reload of language strings

### Log Analysis

#### Enable Debugging
```php
// In config.php for troubleshooting
$CFG->debug = E_ALL | E_STRICT;
$CFG->debugdisplay = 1;
$CFG->debugpageinfo = 1;
```

#### Check Error Logs
```bash
# Check Moodle error log
tail -f /path/to/moodle/moodledata/error.log

# Check web server error log
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

## Backup and Maintenance

### Regular Backups

#### Database Backup
```bash
# Backup residence booking tables
mysqldump -u user -p database_name \
  mdl_local_residencebooking_request \
  mdl_local_residencebooking_types \
  mdl_local_residencebooking_purpose > residence_backup.sql
```

#### File Backup
```bash
# Backup plugin files
tar -czf residence_booking_backup.tar.gz /path/to/moodle/local/residencebooking/
```

### Maintenance Tasks

#### Weekly Tasks
- Review approval queue for stuck requests
- Check error logs for issues
- Monitor database performance

#### Monthly Tasks
- Update plugin if new version available
- Review and optimize database queries
- Check storage usage and cleanup old data

#### Upgrade Procedures
1. **Backup current installation**
2. **Test upgrade on staging environment**
3. **Update plugin files**
4. **Run Moodle upgrade process**
5. **Verify functionality**
6. **Monitor for issues**

## Support and Resources

### Getting Help
- **Documentation**: Refer to other docs in this directory
- **Error Logs**: Check Moodle and web server logs
- **Community**: Moodle community forums
- **Professional Support**: Contact development team

### Useful Commands

```bash
# Clear all caches
php admin/cli/purge_caches.php

# Reset user password (if needed for testing)
php admin/cli/reset_password.php --username=admin

# Check plugin status
php admin/cli/uninstall_plugins.php --show-all | grep residence

# Database maintenance
php admin/cli/mysql_engine.php --list
``` 