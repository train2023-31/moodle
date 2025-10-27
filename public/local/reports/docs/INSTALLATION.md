# Installation Guide

This document provides step-by-step instructions for installing and configuring the Student Reports plugin.

## System Requirements

### Moodle Version
- **Minimum**: Moodle 4.0 (2022041900)
- **Recommended**: Latest stable Moodle version
- **Tested On**: Moodle 4.0+

### Server Requirements
- **PHP Version**: As required by your Moodle installation
- **Database**: MySQL 5.7+ or MariaDB 10.2+ or PostgreSQL 9.6+
- **Web Server**: Apache 2.4+ or Nginx 1.16+
- **PHP Extensions**: Standard Moodle requirements

### Browser Support
- **Modern Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **JavaScript**: Required for AJAX functionality
- **Cookies**: Required for session management

## Installation Methods

### Method 1: Manual Installation (Recommended)

#### Step 1: Download and Extract
1. Download the plugin package
2. Extract the contents to your server
3. Ensure the plugin directory is named `reports`

#### Step 2: Place in Moodle Directory
```bash
# Navigate to your Moodle installation
cd /path/to/moodle

# Create the local plugins directory if it doesn't exist
mkdir -p local

# Copy the plugin to the correct location
cp -r /path/to/downloaded/reports local/
```

#### Step 3: Set Permissions
```bash
# Set appropriate permissions (adjust for your server setup)
chown -R www-data:www-data local/reports
chmod -R 755 local/reports
```

### Method 2: Git Installation (For Developers)

```bash
# Navigate to Moodle local directory
cd /path/to/moodle/local

# Clone the repository
git clone [repository-url] reports

# Set permissions
chown -R www-data:www-data reports
chmod -R 755 reports
```

## Database Installation

### Automatic Installation (Recommended)

1. **Access Admin Interface**:
   - Log in as a Moodle administrator
   - Navigate to **Site Administration > Notifications**

2. **Run Installation**:
   - Moodle will detect the new plugin
   - Click **Upgrade Moodle database now**
   - Follow the on-screen prompts

3. **Verify Installation**:
   - Check for success message
   - Verify no error messages appear

### Manual Database Installation (If Needed)

If automatic installation fails, you may need to run database scripts manually:

```sql
-- Create the main reports table
CREATE TABLE mdl_local_reports (
  id bigint(10) NOT NULL AUTO_INCREMENT,
  userid bigint(10) NOT NULL,
  courseid bigint(10) NOT NULL,
  futureexpec longtext,
  dep_op longtext,
  seg_path longtext,
  researchtitle longtext,
  status_id bigint(10) NOT NULL DEFAULT '0',
  type_id bigint(10) NOT NULL DEFAULT '0',
  timecreated bigint(10) NOT NULL,
  timemodified bigint(10) NOT NULL,
  createdby bigint(10) NOT NULL,
  modifiedby bigint(10) NOT NULL,
  PRIMARY KEY (id),
  KEY mdl_locarepo_use_ix (userid),
  KEY mdl_locarepo_cou_ix (courseid),
  KEY mdl_locarepo_cre_ix (createdby),
  KEY mdl_locarepo_mod_ix (modifiedby)
);

-- Add foreign key constraints
ALTER TABLE mdl_local_reports 
ADD CONSTRAINT mdl_locarepo_use_fk 
FOREIGN KEY (userid) REFERENCES mdl_user (id);

ALTER TABLE mdl_local_reports 
ADD CONSTRAINT mdl_locarepo_cou_fk 
FOREIGN KEY (courseid) REFERENCES mdl_course (id);
```

## Configuration

### Role and Capability Setup

> **📋 Detailed Guide**: For comprehensive information about roles and capabilities, see [`ROLES_AND_CAPABILITIES.md`](ROLES_AND_CAPABILITIES.md)

#### Default Capabilities
The plugin creates these capabilities automatically:
- `local/reports:manage` - Create and edit reports
- `local/reports:viewall` - View all reports in a course

#### Workflow Capabilities
You may need to create additional capabilities for the workflow system:
- `local/status:reports_workflow_step1` - First approval step
- `local/status:reports_workflow_step2` - Second approval step  
- `local/status:reports_workflow_step3` - Final approval step

#### Assigning Capabilities

1. **Navigate to Role Management**:
   - Go to **Site Administration > Users > Permissions > Define roles**

2. **Edit Existing Roles**:
   - Select role to modify (e.g., "Teacher", "Manager")
   - Search for "local/reports" capabilities
   - Set appropriate permissions:
     - **Allow**: Grant the capability
     - **Prohibit**: Explicitly deny
     - **Not set**: Use default/inherit

3. **Recommended Role Assignments**:
   ```
   Manager:
   - local/reports:manage: Allow
   - local/reports:viewall: Allow
   - All workflow capabilities: Allow
   
   Editing Teacher:
   - local/reports:manage: Allow
   - local/reports:viewall: Allow
   - Workflow step 1: Allow
   
   Teacher:
   - local/reports:viewall: Allow
   
   Student:
   - No capabilities (students are subjects of reports)
   ```

### Theme Integration

#### JavaScript Dependencies
The plugin requires theme-specific JavaScript files:
- `/theme/stream/js/modal.js`
- `/theme/stream/js/openreportform.js`
- `/theme/stream/js/approve.js`
- `/theme/stream/js/previewreport.js`

#### Ensure Theme Compatibility
1. **Verify Theme**: Ensure your theme includes the required JavaScript files
2. **Alternative Themes**: If using a different theme, you may need to:
   - Copy JS files to your theme
   - Modify file paths in plugin code
   - Create theme-specific versions

### Language Configuration

#### Default Languages
The plugin includes:
- **English** (`en`): Complete translation
- **Arabic** (`ar`): Complete translation with RTL support

#### Adding Additional Languages
1. **Create Language Directory**:
   ```bash
   mkdir local/reports/lang/[language_code]
   ```

2. **Copy Base Language File**:
   ```bash
   cp local/reports/lang/en/local_reports.php local/reports/lang/[language_code]/
   ```

3. **Translate Strings**:
   - Edit the copied file
   - Translate all string values
   - Keep string keys unchanged

4. **Clear Language Cache**:
   - Go to **Site Administration > Development > Purge caches**
   - Select **Language strings** and purge

## Verification

### Installation Verification

#### 1. Plugin Detection
- Navigate to **Site Administration > Plugins > Plugins overview**
- Search for "Student Reports" or "local_reports"
- Verify plugin appears with correct version number

#### 2. Database Tables
Check that database tables were created:
```sql
SHOW TABLES LIKE '%local_reports%';
DESCRIBE mdl_local_reports;
```

#### 3. Capabilities
- Go to **Site Administration > Users > Permissions > Capability overview**
- Search for "local/reports"
- Verify capabilities exist and are assigned to appropriate roles

#### 4. Navigation
- Access a course where you have appropriate permissions
- Look for "Student Reports" link in course navigation
- Verify link leads to plugin interface

### Functionality Testing

#### 1. Basic Access
- Navigate to plugin interface
- Verify page loads without errors
- Check that tabs (Pending/Approved) display correctly

#### 2. Form Functionality
- Click "Add Report" button
- Verify modal dialog opens
- Test form submission (may require test data)

#### 3. Permission Testing
- Test with different user roles
- Verify appropriate features are available/hidden
- Test capability restrictions

#### 4. Language Testing
- Switch Moodle language (if multiple languages configured)
- Verify plugin interface displays in correct language
- Test RTL layout (if Arabic is configured)

## Troubleshooting

### Common Installation Issues

#### 1. Plugin Not Detected
**Symptoms**: Plugin doesn't appear in notifications
**Solutions**:
- Verify directory structure: `moodle/local/reports/`
- Check file permissions (web server must read files)
- Ensure `version.php` exists and is readable
- Clear Moodle caches

#### 2. Database Installation Fails
**Symptoms**: Error messages during database upgrade
**Solutions**:
- Check database user has CREATE/ALTER permissions
- Verify database connection
- Review Moodle logs for specific SQL errors
- Consider manual database installation

#### 3. JavaScript Errors
**Symptoms**: AJAX functionality not working, console errors
**Solutions**:
- Verify theme includes required JavaScript files
- Check file paths in browser developer tools
- Ensure JavaScript is enabled in browser
- Review browser console for specific errors

#### 4. Permission Denied Errors
**Symptoms**: "You do not have permission" messages
**Solutions**:
- Verify user has required capabilities
- Check role assignments at course/system level
- Review capability inheritance
- Clear Moodle caches after capability changes

#### 5. Template/Display Issues
**Symptoms**: Broken layout, missing elements
**Solutions**:
- Verify template files exist and are readable
- Check Mustache template syntax
- Clear template caches
- Review theme compatibility

### Getting Help

#### Log Files
Check Moodle logs for detailed error information:
- **Location**: `moodle/admin/tool/log/`
- **Database**: `mdl_logstore_standard_log` table
- **PHP Errors**: Server error logs

#### Debug Mode
Enable Moodle debugging for detailed error information:
```php
// In config.php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

#### Community Support
- Moodle forums and community
- Plugin documentation
- GitHub issues (if applicable)

## Uninstallation

### Complete Removal

#### 1. Remove from Moodle Interface
- Go to **Site Administration > Plugins > Plugins overview**
- Find "Student Reports" plugin
- Click **Uninstall**
- Follow prompts to remove database tables

#### 2. Remove Files
```bash
# Remove plugin directory
rm -rf /path/to/moodle/local/reports
```

#### 3. Clean Up (Optional)
- Remove custom capabilities if no longer needed
- Clear all caches
- Remove language customizations

### Data Preservation
If you want to preserve data:
1. **Export Data**: Create database backup before uninstallation
2. **Selective Removal**: Remove plugin files but keep database tables
3. **Migration**: Export data to another format before removal

## Maintenance

### Regular Maintenance Tasks

#### 1. Updates
- Monitor for plugin updates
- Test updates in staging environment
- Backup data before applying updates
- Follow upgrade procedures

#### 2. Database Maintenance
- Monitor table growth
- Implement archiving strategy if needed
- Regular database backups
- Performance monitoring

#### 3. Permission Review
- Regularly review capability assignments
- Adjust permissions as organizational needs change
- Remove unnecessary access
- Audit user activities

#### 4. Cache Management
- Clear caches after configuration changes
- Monitor cache performance
- Optimize if necessary

This completes the installation guide. For specific configuration and development information, refer to the other documentation files in this folder. 