# Installation and Configuration Guide

This document provides step-by-step instructions for installing and configuring the External Lecturer Management plugin version 1.5.

## System Requirements

### Minimum Requirements
- **Moodle Version**: 3.11 or higher
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Browser Support**: Modern browsers with JavaScript enabled

### Recommended Requirements
- **Moodle Version**: 4.0 or higher
- **PHP Version**: 8.0 or higher
- **Memory**: 512MB PHP memory limit minimum
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

## Installation Methods

### Method 1: Manual Installation (Recommended)

1. **Download the Plugin**
   - Obtain the plugin files from your source
   - Ensure you have the complete `externallecturer` directory

2. **Copy Plugin Files**
   ```bash
   # Navigate to your Moodle root directory
   cd /path/to/moodle
   
   # Copy the plugin to the local plugins directory
   cp -r /path/to/externallecturer local/
   ```

3. **Set File Permissions**
   ```bash
   # Ensure proper ownership (adjust user/group as needed)
   chown -R www-data:www-data local/externallecturer
   
   # Set appropriate permissions
   find local/externallecturer -type f -exec chmod 644 {} \;
   find local/externallecturer -type d -exec chmod 755 {} \;
   ```

### Method 2: Git Installation

1. **Clone from Repository**
   ```bash
   cd /path/to/moodle/local/
   git clone [repository-url] externallecturer
   ```

2. **Set Permissions** (same as Method 1, step 3)

## Database Installation

### Automatic Installation (Recommended)

1. **Access Moodle Admin Interface**
   - Log in as a site administrator
   - Navigate to `Site Administration > Notifications`

2. **Complete Installation**
   - Moodle will detect the new plugin
   - Click "Upgrade Moodle database now"
   - Follow the installation prompts

3. **Verify Installation**
   - Check that the installation completed successfully
   - Verify no error messages were displayed

### Manual Database Installation (Advanced Users)

If automatic installation fails, you can manually create the database tables:

```sql
-- Create externallecturer table
CREATE TABLE mdl_externallecturer (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    age INT(3) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    degree VARCHAR(255) NOT NULL,
    passport VARCHAR(20) NOT NULL,
    civil_number VARCHAR(20) NULL,
    courses_count INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create externallecturer_courses table
CREATE TABLE mdl_externallecturer_courses (
    id BIGINT(10) NOT NULL AUTO_INCREMENT,
    lecturerid BIGINT(10) NOT NULL,
    courseid BIGINT(10) NOT NULL,
    cost VARCHAR(10) NOT NULL,
    PRIMARY KEY (id),
    KEY lecturerid (lecturerid),
    KEY courseid (courseid),
    CONSTRAINT fk_lecturer FOREIGN KEY (lecturerid) REFERENCES mdl_externallecturer(id) ON DELETE CASCADE,
    CONSTRAINT fk_course FOREIGN KEY (courseid) REFERENCES mdl_course(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Configuration

### 1. Permission Setup

1. **Access Role Management**
   - Go to `Site Administration > Users > Permissions > Define roles`
   - Select the role you want to grant access to (e.g., Manager)

2. **Assign Capability**
   - Find `local/externallecturer:manage` in the capability list
   - Set it to "Allow"
   - Save changes

3. **Assign Role**
   - Go to `Site Administration > Users > Permissions > Assign system roles`
   - Assign the role to appropriate users

### 2. Language Configuration

The plugin supports English and Arabic by default:

1. **Enable Languages**
   - Go to `Site Administration > Language > Language settings`
   - Enable Arabic language if not already enabled

2. **Set Default Language**
   - Users can switch languages using Moodle's language selector
   - The plugin will automatically display in the user's preferred language

### 3. Plugin Settings

1. **Access Plugin Settings**
   - Navigate to `Site Administration > Plugins > Local plugins > External Lecturer Management`
   - Configure any available settings

2. **Verify Installation**
   - Visit `/local/externallecturer/index.php` to test the interface
   - Ensure all functionality works correctly

## Post-Installation Verification

### 1. Database Verification

Check that the database tables were created correctly:

```sql
-- Verify tables exist
SHOW TABLES LIKE 'mdl_externallecturer%';

-- Check table structure
DESCRIBE mdl_externallecturer;
DESCRIBE mdl_externallecturer_courses;
```

### 2. File System Verification

Ensure all required files are present:

```bash
# Check main plugin files
ls -la local/externallecturer/
ls -la local/externallecturer/actions/
ls -la local/externallecturer/lang/
ls -la local/externallecturer/templates/
```

### 3. Functionality Testing

1. **Access Test**
   - Navigate to the plugin URL
   - Verify the interface loads correctly

2. **Feature Test**
   - Try adding a test external visitor lecturer (using name autocomplete)
   - Try adding a test resident lecturer (using civil number search)
   - Test the edit and delete functions
   - Verify course enrollment works
   - Check that audit trail columns (Created By, Last Modified) are visible
   - Confirm `timecreated`/`timemodified` and `created_by`/`modified_by` populate correctly
   - Verify lecturer type field displays correctly in table
   - Test both form types render properly

3. **Language Test**
   - Switch between English and Arabic
   - Verify all text displays correctly
   - Confirm audit trail column headers are translated

## Troubleshooting

### Common Issues

1. **Plugin Not Appearing**
   - Check file permissions
   - Verify version.php syntax
   - Clear Moodle caches

2. **Database Errors**
   - Check database user permissions
   - Verify table prefix settings
   - Review error logs

3. **Permission Issues**
   - Verify capability assignment
   - Check user role permissions
   - Ensure proper context access

### Getting Help

If you encounter issues:

1. **Check Logs**
   - Review Moodle error logs
   - Check web server error logs
   - Verify PHP error logs

2. **Common Solutions**
   - Clear all caches
   - Check file permissions
   - Verify database connectivity

3. **Support Resources**
   - Review the [Troubleshooting Guide](TROUBLESHOOTING.md)
   - Check the [API Documentation](API.md)
   - Consult the [Examples](EXAMPLES.md)

## Security Considerations

### File Permissions
- Ensure plugin files are not writable by web server
- Set appropriate ownership for security

### Database Security
- Use strong database passwords
- Limit database user privileges
- Regular backup procedures

### Access Control
- Regularly review user permissions
- Monitor access logs
- Implement proper role management

## Performance Optimization

### Database Optimization
- Regular database maintenance
- Monitor query performance
- Index optimization for large datasets

### Caching
- Enable Moodle caching
- Monitor cache performance
- Regular cache purging

## Backup and Recovery

### Regular Backups
- Database backup procedures
- File system backup
- Configuration backup

### Recovery Procedures
- Database restoration
- File restoration
- Configuration recovery

## Upgrading

### From Previous Versions
1. **Backup Current Installation**
   - Database backup
   - File system backup

2. **Install New Version**
   - Replace plugin files
   - Run database upgrade

3. **Verify Functionality**
   - Test all features
   - Check data integrity

### Version-Specific Notes
- 1.4 → 1.5: Automatic database upgrade adds civil_number field
- 1.5 → 1.6: Upgrade 2025081200 standardizes audit fields (`timecreated`, `timemodified`, `created_by`, `modified_by`) and removes legacy datetime fields
- 1.6 → 1.6.1: Adds lecturer_type and nationality fields to support dual lecturer types
- 1.6.1 → 1.7: Implements dual form interface with Oracle DUNIA integration for resident lecturers
- Future versions: Check changelog for specific upgrade notes