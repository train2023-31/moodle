# Installation Guide - Finance Calculator Plugin

## Quick Start

> **Note**: This is the detailed installation guide. For a quick overview, see the main `README.md` file in the plugin root.

### Prerequisites Checklist

Before installing the Finance Calculator plugin, ensure you have:

- [ ] **Moodle 4.1+** installed and running
- [ ] **PHP 7.4+** with MySQL/MariaDB support
- [ ] **Admin access** to your Moodle installation
- [ ] **Source plugins** installed:
  - `local_financeservices` (for budget and finance spending data)
  - `local_participant` (for participant spending data)
- [ ] **Database access** with CREATE TABLE permissions

### Step-by-Step Installation

#### 1. Download the Plugin

**Option A: Git Clone (Recommended)**
```bash
cd /path/to/moodle/local/
git clone https://github.com/your-repo/financecalc.git
```

**Option B: Manual Download**
1. Download the plugin ZIP file
2. Extract to `/path/to/moodle/local/financecalc/`
3. Ensure proper file permissions (755 for directories, 644 for files)

#### 2. Install via Moodle Admin

1. **Navigate to Moodle Admin**
   - Go to your Moodle site
   - Click **Site administration**

2. **Check for Installation Notifications**
   - Look for a notification banner about new plugins
   - Click **"Continue"** to proceed with installation

3. **Complete Installation**
   - Moodle will automatically:
     - Create database tables
     - Register capabilities
     - Set up navigation links
   - Click **"Continue"** when prompted

#### 3. Verify Installation

1. **Check Plugin List**
   - Go to **Site administration > Plugins > Local plugins**
   - Verify "Finance Calculator" appears in the list

2. **Test Navigation**
   - Go to **Site administration > Reports**
   - Look for "Financial Overview" link

3. **Access the Report**
   - Click **"Financial Overview"**
   - You should see the financial report interface
   - Or visit `/local/financecalc/pages/report.php` directly

### Manual Installation (Advanced)

If automatic installation fails, follow these manual steps:

#### 1. Create Database Tables

The plugin will automatically create the required table during installation. If manual creation is needed:

```sql
-- Connect to your Moodle database
USE your_moodle_database;

-- Create the cache table
CREATE TABLE IF NOT EXISTS mdl_local_financecalc_yearly (
    id INT(10) NOT NULL AUTO_INCREMENT,
    year INT(4) NOT NULL,
    spending_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    timecreated INT(10) NOT NULL,
    timemodified INT(10) NOT NULL,
    created_by INT(10) NOT NULL,
    modified_by INT(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY year (year),
    INDEX yearidx (year),
    INDEX timecreatedidx (timecreated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. Register Plugin in Moodle

1. **Clear Moodle Cache**
   - Go to **Site administration > Development > Purge all caches**

2. **Force Plugin Detection**
   - Go to **Site administration > Notifications**
   - Look for plugin installation prompts

3. **Update Version**
   - If prompted, complete the version upgrade process

### Post-Installation Configuration

#### 1. Set Up Permissions

1. **Go to Site administration > Users > Permissions > Define roles**
2. **Edit the Manager role** (or create a custom role)
3. **Add these capabilities**:
   - `local/financecalc:view` - View financial reports
   - `local/financecalc:manage` - Manage financial data

#### 2. Configure Scheduled Tasks

1. **Go to Site administration > Server > Scheduled tasks**
2. **Find "Refresh financial calculation data"**
3. **Set frequency** to "Daily" or "Weekly"
4. **Enable the task** if not already enabled

#### 3. Test Data Sources

Run the test script to verify data sources:

```bash
cd /path/to/moodle
php local/financecalc/test_queries.php
```

Expected output:
```
Testing Finance Calculator Plugin Queries
========================================
1. Checking required tables... ✓
2. Testing budget calculation... ✓
3. Testing finance spending... ✓
4. Testing participant spending... ✓
5. Testing combined financial data... ✓
Test completed successfully.
```

## Troubleshooting Installation

### Common Installation Issues

#### 1. "Plugin not found" Error

**Symptoms**: Moodle doesn't detect the plugin
**Solutions**:
- Verify file permissions (755 for directories, 644 for files)
- Check the plugin is in `/local/financecalc/`
- Clear Moodle cache: **Site administration > Development > Purge all caches**
- Restart web server if needed

#### 2. Database Table Creation Fails

**Symptoms**: Installation stops at database creation
**Solutions**:
- Check database user has CREATE TABLE permissions
- Verify database connection settings in `config.php`
- Check for conflicting table names
- Review MySQL error logs

#### 3. "Class not found" Errors

**Symptoms**: PHP errors about missing classes
**Solutions**:
- Verify `classes.php` file exists and is readable
- Check namespace declarations in PHP files
- Clear Moodle cache
- Check PHP syntax: `php -l local/financecalc/classes/data_manager.php`

#### 4. Navigation Links Missing

**Symptoms**: No "Financial Overview" in Reports menu
**Solutions**:
- Check `lib.php` file exists and is readable
- Verify navigation hook functions are correct
- Clear Moodle cache
- Check user permissions

#### 5. Scheduled Task Dependencies Missing

**Symptoms**: Scheduled task fails or skips execution
**Solutions**:
- Ensure `local_financeservices` plugin is installed
- Ensure `local_participant` plugin is installed
- Check that source tables exist and contain data
- Review scheduled task logs in Moodle admin

### File Permission Issues

#### Linux/Unix Systems
```bash
# Set correct permissions
find /path/to/moodle/local/financecalc -type d -exec chmod 755 {} \;
find /path/to/moodle/local/financecalc -type f -exec chmod 644 {} \;

# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data /path/to/moodle/local/financecalc
```

#### Windows Systems
- Right-click the `financecalc` folder
- Properties → Security
- Ensure IIS_IUSRS or IUSR has Read & Execute permissions

### Database Connection Issues

#### Check Database Settings
```php
// In config.php, verify these settings:
$CFG->dbhost = 'localhost';
$CFG->dbname = 'your_moodle_database';
$CFG->dbuser = 'your_database_user';
$CFG->dbpass = 'your_database_password';
```

#### Test Database Connection
```bash
# Test MySQL connection
mysql -u your_user -p your_database

# Test table creation
CREATE TABLE test_table (id INT);
DROP TABLE test_table;
```

### Plugin Compatibility Issues

#### Check Moodle Version
```php
// In version.php, verify compatibility
$plugin->requires = 2022112800; // Moodle 4.1
$plugin->version = 2025082800;  // Plugin version
```

#### Check PHP Version
```bash
# Check PHP version
php -v

# Should be 7.4 or higher
```

## Verification Checklist

After installation, verify these items:

### Basic Functionality
- [ ] Plugin appears in **Site administration > Plugins > Local plugins**
- [ ] "Financial Overview" link appears in **Site administration > Reports**
- [ ] Can access the financial report page
- [ ] No PHP errors in browser console or logs

### Data Sources
- [ ] Source plugins (`local_financeservices`, `local_participant`) are installed
- [ ] Source tables exist and contain data
- [ ] Test script runs without errors
- [ ] Financial data displays in the report

### Permissions
- [ ] Manager role can view reports
- [ ] Manager role can refresh data
- [ ] Regular users cannot access without permission
- [ ] Capabilities are properly registered

### Performance
- [ ] Report loads within reasonable time (< 5 seconds)
- [ ] No database timeout errors
- [ ] Cache table is created (if enabled)
- [ ] Scheduled task can run successfully

### Dependencies
- [ ] `local_financeservices` plugin is installed and active
- [ ] `local_participant` plugin is installed and active
- [ ] Source tables contain valid data
- [ ] Scheduled task dependencies are met

## Uninstallation

### Remove Plugin Data

1. **Backup Data** (if needed)
   ```sql
   -- Backup cache data
   CREATE TABLE backup_local_financecalc_yearly AS 
   SELECT * FROM mdl_local_financecalc_yearly;
   ```

2. **Remove Database Tables**
   ```sql
   DROP TABLE IF EXISTS mdl_local_financecalc_yearly;
   ```

3. **Remove Plugin Files**
   ```bash
   rm -rf /path/to/moodle/local/financecalc
   ```

4. **Clear Moodle Cache**
   - Go to **Site administration > Development > Purge all caches**

### Clean Up Permissions

1. **Remove Capabilities** from roles
2. **Remove Navigation Links** (automatic after file removal)
3. **Remove Scheduled Tasks** (automatic after file removal)

## Support

If you encounter issues during installation:

1. **Check this guide** for common solutions
2. **Review Moodle error logs** in admin interface
3. **Enable debugging** for detailed error messages
4. **Test with a clean Moodle installation** if needed

### Getting Help

- **Documentation**: Check the main README.md file
- **Error Logs**: Review Moodle admin error logs
- **Community**: Post in Moodle forums with specific error messages
- **GitHub Issues**: Report bugs with detailed information

---

**Last Updated**: January 2025  
**Compatible with**: Moodle 4.1+  
**Tested on**: XAMPP, Linux, Windows
