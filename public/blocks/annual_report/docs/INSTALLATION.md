# Installation Guide - Annual Report Block

## Prerequisites

### System Requirements
- **Moodle Version**: 4.0 or higher (2022041900)
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 10+
- **Server**: Apache or Nginx with appropriate modules

### Database Prerequisites
Before installing this plugin, ensure the following custom database tables exist:

```sql
-- Required tables (must be created by other plugins/modules)
local_annual_plan_course
local_annual_plan_course_level
local_financeservices
local_financeservices_clause
```

If these tables don't exist, the plugin will not function correctly. Contact your system administrator to verify database schema.

## Installation Methods

### Method 1: Manual Installation (Recommended)

**Step 1: Download/Copy Plugin Files**
```bash
# Copy the entire plugin directory to your Moodle blocks directory
cp -r annual_report /path/to/moodle/blocks/
```

**Step 2: Set Proper Permissions**
```bash
# Ensure proper file permissions
chmod -R 755 /path/to/moodle/blocks/annual_report/
chown -R www-data:www-data /path/to/moodle/blocks/annual_report/
```

**Step 3: Install via Moodle Interface**
1. Login to Moodle as administrator
2. Navigate to: **Site Administration** → **Notifications**
3. Moodle will detect the new plugin and prompt for installation
4. Click **"Upgrade Moodle database now"**
5. Verify successful installation

### Method 2: Moodle Plugin Installer (If Packaged)

**Step 1: Create Plugin Package**
```bash
# Create a ZIP file of the plugin
cd /path/to/moodle/blocks/
zip -r annual_report.zip annual_report/
```

**Step 2: Install via Admin Interface**
1. Login as administrator
2. Navigate to: **Site Administration** → **Plugins** → **Install plugins**
3. Upload the `annual_report.zip` file
4. Follow the installation wizard
5. Complete the installation process

## Post-Installation Configuration

### Step 1: Verify Database Access
Run these test queries to ensure the plugin can access required data:

```sql
-- Test course level access
SELECT COUNT(*) FROM {local_annual_plan_course_level};

-- Test course data access  
SELECT COUNT(*) FROM {local_annual_plan_course};

-- Test financial data access
SELECT COUNT(*) FROM {local_financeservices_clause};
```

### Step 2: Clear Caches
```bash
# Clear all Moodle caches
php admin/cli/purge_caches.php

# Or via web interface:
# Site Administration → Development → Purge all caches
```

### Step 3: Test Plugin Functionality
1. Navigate to any course or dashboard
2. Turn editing on
3. Select **"Add a block"**
4. Choose **"Department Annual Report"**
5. Verify the block displays without errors

## Capability Configuration

### Default Capabilities
The plugin automatically configures these capabilities:

- `block/annual_report:addinstance` - Add block to courses/pages
  - **Default**: Editing teachers, managers
- `block/annual_report:myaddinstance` - Add block to My Moodle page
  - **Default**: All users

### Custom Capability Assignment
To modify default permissions:

1. Navigate to: **Site Administration** → **Users** → **Permissions** → **Define roles**
2. Select the desired role (e.g., Teacher, Student)
3. Search for "annual_report"
4. Modify capabilities as needed
5. Save changes

## Language Configuration

### Supported Languages
- English (en) - Default
- Arabic (ar) - Full translation provided

### Language Installation
Languages are automatically installed with the plugin. To verify:

1. Navigate to: **Site Administration** → **Language** → **Language packs**
2. Ensure English and Arabic are installed
3. Test language switching functionality

### Custom Language Strings
To modify language strings:

1. Copy language files:
   ```bash
   cp blocks/annual_report/lang/en/block_annual_report.php /path/to/custom/lang/en/
   ```
2. Edit strings as needed
3. Clear language caches

## Troubleshooting Installation Issues

### Common Installation Problems

#### Problem: "Plugin not detected"
**Solution**:
1. Verify file permissions (755 for directories, 644 for files)
2. Check file ownership matches web server user
3. Ensure all required files are present
4. Clear file system caches

#### Problem: "Database error during installation"
**Solution**:
1. Verify required database tables exist
2. Check database user permissions
3. Review Moodle error logs
4. Confirm database schema matches requirements

#### Problem: "Access denied" errors
**Solution**:
1. Check file and directory permissions
2. Verify web server can read plugin files
3. Review SELinux/AppArmor policies if applicable

### Debugging Installation
Enable Moodle debugging for detailed error information:

1. Navigate to: **Site Administration** → **Development** → **Debugging**
2. Set **Debug messages** to "DEVELOPER"
3. Set **Display debug messages** to "Yes"
4. Attempt installation again
5. Review detailed error messages

### Log File Locations
```bash
# Moodle error logs (typical locations)
/var/log/apache2/error.log
/var/log/nginx/error.log
/path/to/moodle/moodledata/error.log

# Database logs
/var/log/mysql/error.log
/var/log/postgresql/postgresql.log
```

## Verification Checklist

After installation, verify these items:

- [ ] Plugin appears in **Site Administration** → **Plugins** → **Plugins overview**
- [ ] No error messages in Moodle logs
- [ ] Block can be added to courses/pages
- [ ] Block displays data without database errors
- [ ] Language switching works correctly
- [ ] Financial calculations display properly
- [ ] All CSS styling loads correctly

## Uninstallation

### Safe Uninstallation Process

**Step 1: Remove Block Instances**
1. Navigate to all pages containing the block
2. Remove block instances manually
3. Verify no active instances remain

**Step 2: Uninstall via Admin Interface**
1. Navigate to: **Site Administration** → **Plugins** → **Plugins overview**
2. Find "Annual Report Block" in the list
3. Click **"Uninstall"**
4. Confirm uninstallation
5. Clear all caches

**Step 3: Manual File Cleanup (Optional)**
```bash
# Remove plugin files
rm -rf /path/to/moodle/blocks/annual_report/

# Clear any remaining caches
php admin/cli/purge_caches.php
```

### Data Preservation
Uninstalling the plugin does NOT affect:
- Course data in `local_annual_plan_course` tables
- Financial data in `local_financeservices` tables
- User data or course content

## Upgrade Process

### Upgrading Plugin Version

**Step 1: Backup Current Installation**
```bash
# Backup plugin files
cp -r /path/to/moodle/blocks/annual_report/ /backup/location/

# Backup database (if changes expected)
mysqldump moodle_db > moodle_backup.sql
```

**Step 2: Replace Plugin Files**
```bash
# Remove old version
rm -rf /path/to/moodle/blocks/annual_report/

# Install new version
cp -r new_annual_report/ /path/to/moodle/blocks/annual_report/
```

**Step 3: Run Upgrade Process**
1. Login as administrator
2. Navigate to: **Site Administration** → **Notifications**
3. Follow upgrade prompts
4. Clear all caches
5. Test functionality

## Support and Maintenance

### Regular Maintenance Tasks
- Monitor database query performance
- Review error logs weekly
- Test functionality after Moodle upgrades
- Update language strings as needed
- Verify data accuracy periodically

### Getting Support
- Review documentation in `/docs/` directory
- Check resolved issues in `Error_AHMED.txt`
- Contact development team for technical issues
- Submit bug reports with detailed logs

### Performance Monitoring
```sql
-- Monitor query performance
SELECT 
    table_name,
    table_rows,
    avg_row_length,
    data_length
FROM information_schema.tables 
WHERE table_name LIKE 'local_annual%';
```

Monitor for:
- Query execution times > 500ms
- High memory usage during block rendering
- Database connection timeouts
- Large result sets affecting performance 