# Installation Guide - Computer Service Plugin

This guide provides step-by-step instructions for installing and configuring the Computer Service plugin.

## 📋 Prerequisites

### System Requirements
- **Moodle Version**: 4.0 or higher
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 10+
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Required Moodle Capabilities
- Administrator access to Moodle site
- Ability to install local plugins
- Database write permissions
- File system write permissions

### Dependencies
This plugin has optional dependencies that enhance functionality:
- `local_status` plugin (for advanced workflow management)
- JavaScript enabled browsers for AJAX features

## 📦 Installation Methods

### Method 1: Manual Installation (Recommended)

#### Step 1: Download Plugin Files
1. Obtain the plugin files from your source
2. Extract the files if in compressed format
3. Ensure all files are present and intact

#### Step 2: Upload to Moodle
1. **Navigate to Moodle directory**:
   ```bash
   cd /path/to/your/moodle/
   ```

2. **Create plugin directory**:
   ```bash
   mkdir -p local/computerservice
   ```

3. **Copy plugin files**:
   ```bash
   cp -r /path/to/plugin/files/* local/computerservice/
   ```

4. **Set proper permissions**:
   ```bash
   # For Apache
   chown -R www-data:www-data local/computerservice/
   chmod -R 755 local/computerservice/
   
   # For Nginx
   chown -R nginx:nginx local/computerservice/
   chmod -R 755 local/computerservice/
   ```

#### Step 3: Run Moodle Installation
1. **Access Moodle as administrator**
2. **Go to Site Administration → Notifications**
3. **Follow installation prompts**:
   - Plugin will be detected automatically
   - Database tables will be created
   - Default data will be inserted
4. **Confirm successful installation**

### Method 2: Git Installation

#### For Development Environments
```bash
# Navigate to Moodle local directory
cd /path/to/moodle/local/

# Clone repository
git clone [repository-url] computerservice

# Set permissions
chown -R www-data:www-data computerservice/
chmod -R 755 computerservice/
```

## 🛠️ Post-Installation Configuration

### 1. Verify Installation

#### Check Plugin Status
1. **Go to Site Administration → Plugins → Plugins overview**
2. **Find "Computer Service"** in the list
3. **Verify status shows "Enabled"**
4. **Check version number**: Should show 1.3.1 or current version

#### Test Basic Functionality
1. **Navigate to** `/local/computerservice/`
2. **Verify all three tabs load**:
   - Request Devices
   - Manage Requests  
   - Manage Devices
3. **Check for error messages** or missing content

### 2. Configure Capabilities and Roles

#### Default Capabilities
The plugin creates these capabilities:

| Capability | Purpose | Default Assignment |
|------------|---------|-------------------|
| `local/computerservice:submitrequest` | Submit device requests | All authenticated users |
| `local/computerservice:managerequests` | Manage and approve requests | Managers, Course creators |
| `local/computerservice:manage_devices` | Manage device types | Site administrators |
| `local/computerservice:approve_leader1` | First level approval | Department heads |
| `local/computerservice:approve_leader2` | Second level approval | Faculty managers |
| `local/computerservice:approve_leader3` | Third level approval | Administrative managers |
| `local/computerservice:approve_boss` | Boss level approval | Senior management |
| `local/computerservice:approve_final` | Final approval | Executive level |

#### Assign Capabilities
1. **Go to Site Administration → Users → Permissions → Define roles**
2. **Edit each role** that should have plugin access
3. **Find Computer Service capabilities** in the list
4. **Set appropriate permissions**:
   - **Allow**: User can perform this action
   - **Prevent**: User explicitly cannot perform this action
   - **Prohibit**: User cannot perform this action (overrides Allow)

#### Recommended Role Assignments

**Teachers/Trainers**:
- `local/computerservice:submitrequest` → Allow

**Course Creators**:
- `local/computerservice:submitrequest` → Allow
- `local/computerservice:managerequests` → Allow (for their courses)

**Managers**:
- `local/computerservice:submitrequest` → Allow
- `local/computerservice:managerequests` → Allow
- `local/computerservice:approve_leader1` → Allow
- `local/computerservice:approve_leader2` → Allow

**Site Administrators**:
- All capabilities → Allow

### 3. Configure Device Types

#### Add Initial Device Types
1. **Navigate to Computer Service plugin**
2. **Go to "Manage Devices" tab**
3. **Click "Add New Device"**
4. **Add common device types**:

**Example Devices**:
```
English: Projector          | Arabic: جهاز عرض
English: Laptop             | Arabic: حاسوب محمول  
English: Microphone         | Arabic: ميكروفون
English: Interactive Board  | Arabic: سبورة تفاعلية
English: Camera             | Arabic: كاميرا
English: Speaker System     | Arabic: نظام صوتي
```

#### Device Management Best Practices
- Start with essential devices only
- Add devices based on actual availability
- Use clear, descriptive names
- Ensure accurate translations
- Set appropriate initial status (active/inactive)

### 4. Language Configuration

#### Enable Multilingual Support
1. **Go to Site Administration → Language → Language settings**
2. **Verify Arabic and English are installed**
3. **Test language switching** with device names

#### Language File Locations
- English: `/local/computerservice/lang/en/local_computerservice.php`
- Arabic: `/local/computerservice/lang/ar/local_computerservice.php`

#### Custom Language Strings
To customize language strings:
1. **Go to Site Administration → Language → Language customization**
2. **Select language** (English or Arabic)
3. **Find local_computerservice.php**
4. **Modify strings as needed**
5. **Save changes**

### 5. Workflow Configuration

#### Default Workflow
The plugin comes with a pre-configured workflow:
```
Initial → Leader1 → Leader2 → Leader3 → Boss → Approved
   ↓         ↓        ↓         ↓       ↓
   └─────────┴────────┴─────────┴───────┴→ Initial (for resubmission)
```

#### Customize Workflow (Advanced)
To modify the workflow:
1. **Edit** `/classes/simple_workflow_manager.php`
2. **Update status constants** as needed
3. **Modify** `get_next_status()` method
4. **Update language strings** for new statuses
5. **Add new capabilities** in `/db/access.php`
6. **Increment version** in `version.php`
7. **Run upgrade** through Moodle admin

## 🔧 Advanced Configuration

### Database Optimization

#### Recommended Indexes
For better performance with large datasets:
```sql
-- Course and status filtering
CREATE INDEX mdl_locacomprequ_cou_sta_ix 
ON mdl_local_computerservice_requests (courseid, status_id);

-- Time-based queries
CREATE INDEX mdl_locacomprequ_tim_ix 
ON mdl_local_computerservice_requests (timecreated);

-- urgent request queries
CREATE INDEX mdl_locacomprequ_eme_ix 
ON mdl_local_computerservice_requests (is_urgent, status_id);
```

#### Database Maintenance
```sql
-- Clean old completed requests (older than 1 year)
DELETE FROM mdl_local_computerservice_requests 
WHERE status_id IN (20, 21) 
  AND timecreated < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR));

-- Archive old requests before deletion
CREATE TABLE mdl_local_computerservice_requests_archive AS 
SELECT * FROM mdl_local_computerservice_requests 
WHERE timecreated < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR));
```

### Security Configuration

#### Session Security
The plugin uses Moodle's built-in session management:
- Session key validation for AJAX requests
- CSRF protection on all forms
- Capability-based access control

#### Recommended Security Settings
```php
// In Moodle config.php
$CFG->forcelogin = true;           // Require login
$CFG->forceloginforprofiles = true; // Require login for profiles
$CFG->opentogoogle = false;        // Disable Google indexing
```

### Performance Optimization

#### Caching Configuration
1. **Enable Moodle caching**:
   - Go to Site Administration → Plugins → Caching → Configuration
   - Ensure caching is enabled
   - Configure appropriate cache stores

2. **Template Caching**:
   - Templates are automatically cached by Moodle
   - Clear cache after template modifications
   - Use Development mode for template development

#### JavaScript Optimization
The plugin includes AJAX functionality:
- Ensure jQuery is loaded
- Enable JavaScript aggregation for production
- Test AJAX features after configuration changes

### Integration with Other Systems

#### External Database Integration
To integrate with external asset management:
1. **Modify device data source** in forms
2. **Update device availability** logic
3. **Add external API calls** as needed
4. **Implement data synchronization**

#### Email Notifications (Future Enhancement)
To add email notifications:
1. **Create notification templates**
2. **Add email triggers** in workflow manager
3. **Configure SMTP settings** in Moodle
4. **Test email delivery**

## 🧪 Testing the Installation

### Functional Testing Checklist

#### Basic Functionality
- [ ] Plugin appears in plugin list
- [ ] All three tabs load without errors
- [ ] Forms display correctly
- [ ] Database tables created successfully
- [ ] Default devices appear in device list

#### User Role Testing
- [ ] Teachers can submit requests
- [ ] Managers can approve/reject requests
- [ ] Administrators can manage devices
- [ ] Proper capability enforcement
- [ ] Navigation links appear for authorized users

#### Workflow Testing
- [ ] Request submission creates database record
- [ ] Approval advances workflow status
- [ ] Rejection saves rejection note and returns to initial status
- [ ] Status changes reflect in UI
- [ ] urgent detection works correctly

#### Multilingual Testing
- [ ] Language switching works
- [ ] Device names display in correct language
- [ ] All UI text translates properly
- [ ] Arabic text displays correctly (RTL)

#### Performance Testing
- [ ] Pages load quickly
- [ ] AJAX requests respond promptly
- [ ] Database queries are efficient
- [ ] No memory leaks or errors

### Common Installation Issues

#### Permission Errors
**Problem**: "Permission denied" errors
**Solution**:
```bash
# Fix file permissions
chmod -R 755 /path/to/moodle/local/computerservice/
chown -R www-data:www-data /path/to/moodle/local/computerservice/
```

#### Database Errors
**Problem**: Table creation fails
**Solutions**:
- Check database user permissions
- Verify database connection
- Review error logs for specific issues
- Ensure sufficient disk space

#### Missing Dependencies
**Problem**: Features not working
**Solutions**:
- Verify Moodle version compatibility
- Check PHP extensions
- Ensure JavaScript is enabled
- Clear browser cache

#### Language Issues
**Problem**: Text not displaying correctly
**Solutions**:
- Verify language packs are installed
- Clear language cache
- Check file encoding (UTF-8)
- Validate language file syntax

## 📊 Monitoring and Maintenance

### Log Monitoring
Monitor these logs for issues:
- Moodle error log
- Web server error log
- Database slow query log
- PHP error log

### Regular Maintenance Tasks

#### Weekly
- Review request volume and processing times
- Check for workflow bottlenecks
- Monitor system performance
- Backup plugin data

#### Monthly
- Update device availability
- Review user feedback
- Check for plugin updates
- Analyze usage patterns

#### Quarterly
- Review and update workflow if needed
- Assess capability assignments
- Clean old request data
- Performance optimization review

### Backup and Recovery

#### What to Backup
- Plugin files: `/local/computerservice/`
- Database tables:
  - `mdl_local_computerservice_requests`
  - `mdl_local_computerservice_devices`
- Configuration customizations
- Language customizations

#### Backup Script Example
```bash
#!/bin/bash
# Backup Computer Service plugin data

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/computerservice_${DATE}"

mkdir -p ${BACKUP_DIR}

# Backup files
tar -czf ${BACKUP_DIR}/files.tar.gz /path/to/moodle/local/computerservice/

# Backup database
mysqldump -u username -p database_name \
  mdl_local_computerservice_requests \
  mdl_local_computerservice_devices \
  > ${BACKUP_DIR}/database.sql

echo "Backup completed: ${BACKUP_DIR}"
```

## 🚀 Going Live

### Pre-Production Checklist
- [ ] All testing completed successfully
- [ ] User training conducted
- [ ] Documentation provided to administrators
- [ ] Backup procedures in place
- [ ] Monitoring configured
- [ ] Support contacts identified

### Production Deployment
1. **Schedule maintenance window**
2. **Backup existing system**
3. **Deploy plugin files**
4. **Run database upgrade**
5. **Test critical functionality**
6. **Monitor for issues**
7. **Communicate go-live to users**

### Post-Deployment Support
- Monitor system logs for first 48 hours
- Be available for user questions
- Track and resolve any issues quickly
- Gather user feedback for improvements

---

*This installation guide covers version 1.3.1 of the Computer Service plugin. Installation steps may vary for different versions.* 