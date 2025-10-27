# Troubleshooting Guide

This document provides solutions to common issues encountered when using the External Lecturer Management plugin version 1.5.

## Installation Issues

### Plugin Not Appearing in Notifications

**Symptoms:**
- Plugin doesn't show up in Site Administration > Notifications
- No installation prompt when visiting admin pages
- Plugin files exist but Moodle doesn't detect them

**Possible Causes:**
- Incorrect file placement
- File permission issues
- Syntax errors in version.php
- Missing required files

**Solutions:**
1. **Check File Location**
   ```bash
   # Verify plugin is in correct directory
   ls -la local/externallecturer/version.php
   
   # Check complete directory structure
   find local/externallecturer -type f -name "*.php" | head -10
   ```

2. **Verify File Permissions**
   ```bash
   # Set correct permissions
   chmod 644 local/externallecturer/version.php
   chown www-data:www-data local/externallecturer/version.php
   
   # Set permissions for entire plugin directory
   find local/externallecturer -type f -exec chmod 644 {} \;
   find local/externallecturer -type d -exec chmod 755 {} \;
   ```

3. **Check version.php Syntax**
   ```bash
   # Check for PHP syntax errors
   php -l local/externallecturer/version.php
   
   # Verify version.php content
   cat local/externallecturer/version.php
   ```

4. **Clear Moodle Caches**
   - Go to Site Administration > Development > Purge all caches
   - Or use CLI: `php admin/cli/purge_caches.php`
   - Restart web server if necessary

### Database Installation Failures

**Symptoms:**
- Error during database table creation
- Installation stops with SQL errors
- Tables not created properly

**Solutions:**
1. **Check Database Permissions**
   ```sql
   -- Verify user has CREATE privileges
   SHOW GRANTS FOR 'moodleuser'@'localhost';
   
   -- Check if user can create tables
   CREATE TABLE test_table (id INT);
   DROP TABLE test_table;
   ```

2. **Manual Table Creation**
   ```sql
   -- Create tables manually if auto-install fails
   USE moodledb;
   
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
       CONSTRAINT fk_lecturer FOREIGN KEY (lecturerid) 
           REFERENCES mdl_externallecturer(id) ON DELETE CASCADE,
       CONSTRAINT fk_course FOREIGN KEY (courseid) 
           REFERENCES mdl_course(id) ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

3. **Check Table Prefix**
   - Ensure your Moodle table prefix matches the install.xml configuration
   - Default prefix is `mdl_`
   - Verify in Moodle's config.php file

## Access and Permission Issues

### Cannot Access Plugin Interface

**Symptoms:**
- 404 error when accessing `/local/externallecturer/index.php`
- Permission denied errors
- Blank page or error message

**Solutions:**
1. **Check URL Path**
   - Ensure correct URL: `https://yourdomain.com/local/externallecturer/index.php`
   - Verify Moodle is installed in web root
   - Check for URL rewriting issues

2. **Verify User Permissions**
   - Login as site administrator
   - Check if user has system context capabilities
   - Verify `local/externallecturer:manage` capability is assigned

3. **Check Web Server Configuration**
   ```apache
   # Apache: Ensure .htaccess allows access
   <Directory "/path/to/moodle/local">
       AllowOverride All
       Require all granted
   </Directory>
   ```

### Insufficient Privileges

**Symptoms:**
- "You do not have permission to access this page" errors
- Interface loads but no data appears
- Cannot perform actions (add, edit, delete)

**Solutions:**
1. **Check User Role**
   - Verify user is assigned administrator or manager role
   - Check role capabilities in Site Administration > Users > Permissions > Define roles
   - Ensure `local/externallecturer:manage` is set to "Allow"

2. **Assign Capability**
   ```php
   // Check current user capabilities
   $context = context_system::instance();
   has_capability('local/externallecturer:manage', $context);
   ```

3. **Create Custom Role**
   - Go to Site Administration > Users > Permissions > Define roles
   - Create new role with `local/externallecturer:manage` capability
   - Assign role to appropriate users

## Database Issues

### Data Not Displaying

**Symptoms:**
- Empty tables in the interface
- No lecturers or courses shown
- Database queries return no results

**Solutions:**
1. **Check Database Connection**
   ```php
   // Test database connection
   global $DB;
   $result = $DB->get_records('externallecturer');
   var_dump($result);
   ```

2. **Verify Table Structure**
   ```sql
   -- Check if tables exist
   SHOW TABLES LIKE 'mdl_externallecturer%';
   
   -- Check table structure
   DESCRIBE mdl_externallecturer;
   DESCRIBE mdl_externallecturer_courses;
   ```

3. **Check for Data**
   ```sql
   -- Count records in tables
   SELECT COUNT(*) FROM mdl_externallecturer;
   SELECT COUNT(*) FROM mdl_externallecturer_courses;
   ```

### Foreign Key Constraint Errors

**Symptoms:**
- Error when adding course enrollments
- "Cannot add or update a child row" errors
- Data integrity issues

**Solutions:**
1. **Verify Referenced Data**
   ```sql
   -- Check if lecturer exists
   SELECT * FROM mdl_externallecturer WHERE id = [lecturer_id];
   
   -- Check if course exists
   SELECT * FROM mdl_course WHERE id = [course_id];
   ```

2. **Fix Orphaned Records**
   ```sql
   -- Remove orphaned course enrollments
   DELETE FROM mdl_externallecturer_courses 
   WHERE lecturerid NOT IN (SELECT id FROM mdl_externallecturer);
   
   DELETE FROM mdl_externallecturer_courses 
   WHERE courseid NOT IN (SELECT id FROM mdl_course);
   ```

## JavaScript and UI Issues

### AJAX Operations Failing

**Symptoms:**
- Forms don't submit properly
- No response when clicking buttons
- JavaScript errors in browser console

**Solutions:**
1. **Check Browser Console**
   - Open Developer Tools (F12)
   - Look for JavaScript errors
   - Check Network tab for failed requests

2. **Verify JavaScript Files**
   ```bash
   # Check if required JS files exist
   ls -la local/externallecturer/js/
   ls -la theme/stream/js/export_csv.js
   ls -la theme/stream/js/custom_dialog.js
   ```

3. **Test AJAX Endpoints**
   ```bash
   # Test endpoint directly
   curl -X POST http://yourdomain.com/local/externallecturer/actions/addlecturer.php \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "name=Test&age=30&specialization=Test&organization=Test&degree=Test&passport=123"
   ```

### Modal Dialogs Not Working

**Symptoms:**
- Modal forms don't open
- Dialog boxes appear blank
- JavaScript errors related to dialogs

**Solutions:**
1. **Check Theme Dependencies**
   - Verify `custom_dialog.js` exists in your theme
   - Check for JavaScript conflicts with other plugins

2. **Alternative Implementation**
   ```javascript
   // Fallback modal implementation
   function openModal(modalId) {
       const modal = document.getElementById(modalId);
       if (modal) {
           modal.style.display = 'block';
       }
   }
   ```

## Language and Localization Issues

### Language Strings Not Displaying

**Symptoms:**
- Interface shows string keys instead of translated text
- Mixed language display
- Missing translations

**Solutions:**
1. **Check Language Files**
   ```bash
   # Verify language files exist
   ls -la local/externallecturer/lang/en/local_externallecturer.php
   ls -la local/externallecturer/lang/ar/local_externallecturer.php
   ```

2. **Clear Language Cache**
   - Go to Site Administration > Development > Purge all caches
   - Check Site Administration > Language > Language settings

3. **Verify String Usage**
   ```php
   // Check if strings are being retrieved correctly
   echo get_string('pluginname', 'local_externallecturer');
   ```

### RTL Layout Issues

**Symptoms:**
- Arabic text displays incorrectly
- Layout problems with right-to-left text
- Mixed direction text issues

**Solutions:**
1. **Check CSS Support**
   - Ensure theme supports RTL layouts
   - Add RTL-specific CSS if needed

2. **Verify Language Settings**
   - Check user's language preference
   - Ensure Arabic language is enabled in Moodle

## Performance Issues

### Slow Page Loading

**Symptoms:**
- Interface takes long time to load
- Database queries are slow
- High server resource usage

**Solutions:**
1. **Optimize Database Queries**
   ```sql
   -- Add indexes for better performance
   CREATE INDEX idx_externallecturer_name ON mdl_externallecturer(name);
   CREATE INDEX idx_externallecturer_courses_lecturer ON mdl_externallecturer_courses(lecturerid);
   ```

2. **Enable Caching**
   - Go to Site Administration > Plugins > Caching
   - Configure appropriate cache stores
   - Monitor cache performance

3. **Reduce Page Size**
   - Use smaller pagination values
   - Implement lazy loading for large datasets

### Memory Issues

**Symptoms:**
- PHP memory limit exceeded
- Out of memory errors
- Slow performance with large datasets

**Solutions:**
1. **Increase PHP Memory Limit**
   ```ini
   ; In php.ini
   memory_limit = 512M
   ```

2. **Optimize Data Retrieval**
   - Use pagination for large datasets
   - Implement data filtering
   - Cache frequently accessed data

## Security Issues

### Permission Bypass Attempts

**Symptoms:**
- Users accessing plugin without proper permissions
- Unauthorized data access
- Security audit failures

**Solutions:**
1. **Review Permission Assignments**
   - Audit all role assignments
   - Remove unnecessary permissions
   - Implement principle of least privilege

2. **Add Security Logging**
   ```php
   // Log access attempts
   $event = \local_externallecturer\event\lecturer_accessed::create([
       'context' => context_system::instance(),
       'userid' => $USER->id
   ]);
   $event->trigger();
   ```

### Data Validation Issues

**Symptoms:**
- Invalid data being stored
- SQL injection vulnerabilities
- XSS attacks

**Solutions:**
1. **Enhance Input Validation**
   ```php
   // Use Moodle's parameter validation
   $name = required_param('name', PARAM_TEXT);
   $age = required_param('age', PARAM_INT);
   ```

2. **Implement Output Escaping**
   ```php
   // Escape output to prevent XSS
   echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
   ```

## Diagnostic Commands

### System Information
```bash
# Check PHP version and extensions
php -v
php -m | grep -E "(mysql|pdo|json)"

# Check Moodle version
php admin/cli/version.php

# Check file permissions
ls -la local/externallecturer/
```

### Database Diagnostics
```sql
-- Check table status
SHOW TABLE STATUS LIKE 'mdl_externallecturer%';

-- Check for errors
SHOW ENGINE INNODB STATUS;

-- Monitor slow queries
SHOW VARIABLES LIKE 'slow_query_log';
```

### Log Analysis
```bash
# Check Moodle logs
tail -f /path/to/moodle/log/apache_error.log

# Check PHP error log
tail -f /var/log/php_errors.log

# Check database logs
tail -f /var/log/mysql/error.log
```

## Getting Help

### Before Seeking Support
1. **Collect Information**
   - Moodle version and configuration
   - PHP version and extensions
   - Database type and version
   - Error messages and logs

2. **Test in Isolation**
   - Disable other plugins temporarily
   - Test with default theme
   - Check in different browsers

3. **Document Steps**
   - Record exact steps to reproduce issue
   - Note any error messages
   - Include relevant log entries

### Support Resources
- **Moodle Forums**: Search for similar issues
- **Plugin Documentation**: Review this troubleshooting guide
- **Developer Documentation**: Check Moodle developer docs
- **Community Support**: Reach out to Moodle community

### Reporting Issues
When reporting issues, include:
- Complete error messages
- Steps to reproduce
- System information
- Relevant log entries
- Screenshots if applicable