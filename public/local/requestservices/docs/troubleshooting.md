# Troubleshooting Guide

## Recent Issue Resolutions

### September 3, 2025 - Bug #2 RESOLVED ✅
**Issue**: requestparticipant tab dropdowns not populating with data  
**Root Cause**: JavaScript using relative URLs that resolved to wrong context  
**Solution**: Fixed AJAX URLs to use absolute paths pointing to correct participant plugin endpoints  
**Result**: Dropdowns now working correctly with employee and lecturer data

## Current Known Issues

**Status**: 2 of 3 major bugs resolved (67%)  
**Next Target**: Modernize residencebooking tab (Bug #3 - low priority)

## Common Issues and Solutions

### Installation Problems

#### Plugin Does Not Appear in Plugin List

**Symptoms**:
- Plugin not visible in Site Administration → Plugins → Plugins overview
- Installation notification does not appear

**Possible Causes**:
1. Incorrect directory structure
2. File permission issues
3. PHP syntax errors
4. Missing required files

**Solutions**:

**Check Directory Structure**:
```bash
# Verify correct location
ls -la /path/to/moodle/local/requestservices/

# Should contain:
# - version.php
# - index.php
# - lib.php
# - db/ directory
# - lang/ directory
# - tabs/ directory
# - templates/ directory
# - classes/ directory
```

**Fix File Permissions**:
```bash
# Set correct permissions
find /path/to/moodle/local/requestservices/ -type f -exec chmod 644 {} \;
find /path/to/moodle/local/requestservices/ -type d -exec chmod 755 {} \;
chown -R www-data:www-data /path/to/moodle/local/requestservices/
```

**Check PHP Syntax**:
```bash
# Test main files for syntax errors
php -l /path/to/moodle/local/requestservices/version.php
php -l /path/to/moodle/local/requestservices/index.php
php -l /path/to/moodle/local/requestservices/lib.php
```

#### Installation Fails with Database Errors

**Symptoms**:
- Error during installation process
- Database-related error messages
- Plugin status shows "Requires upgrade"

**Solutions**:

**Check Database Permissions**:
```sql
-- Verify Moodle database user has necessary privileges
SHOW GRANTS FOR 'moodle_user'@'localhost';
-- Should include CREATE, ALTER, INSERT, UPDATE, DELETE, SELECT
```

**Clear Installation Cache**:
```bash
# Clear Moodle caches
php /path/to/moodle/admin/cli/purge_caches.php

# Or manually delete cache directories
rm -rf /path/to/moodle/cache/*
```

**Manual Installation Reset**:
```sql
-- Remove plugin entry if installation failed
DELETE FROM mdl_config_plugins WHERE plugin = 'local_requestservices';

-- Clear component cache
DELETE FROM mdl_cache_flags WHERE flagtype = 'plugininfo';
```

### Navigation Issues

#### Request Services Tab Not Appearing in Course Navigation

**Symptoms**:
- Plugin installed successfully
- User has appropriate role (teacher/editing teacher)
- Tab still not visible in course navigation

**Diagnostic Steps**:

**Check User Capabilities**:
1. Navigate to Site Administration → Users → Permissions → Check permissions
2. Select the user and course context
3. Search for `local/requestservices:view`
4. Verify capability is "Allow"

**Check Role Assignments**:
```sql
-- Check if user has teacher role in the course
SELECT r.shortname, ra.userid, ra.contextid 
FROM mdl_role_assignments ra
JOIN mdl_role r ON r.id = ra.roleid
JOIN mdl_context c ON c.id = ra.contextid
WHERE c.contextlevel = 50 -- CONTEXT_COURSE
AND c.instanceid = [COURSE_ID]
AND ra.userid = [USER_ID];
```

**Clear Navigation Cache**:
```bash
# Clear all caches
php /path/to/moodle/admin/cli/purge_caches.php

# Force navigation rebuild
# Log out and log back in as the user
```

**Verify lib.php Function**:
Check that `local_requestservices_extend_navigation_course()` function exists and is properly defined in `lib.php`.

#### Permission Denied When Accessing Tab

**Symptoms**:
- Tab appears in navigation
- Clicking tab results in "Access denied" or capability error
- Error message about missing permissions

**Solutions**:

**Check Capability Context**:
```php
// Add debugging to index.php temporarily
if (!has_capability('local/requestservices:view', $context)) {
    debugging('User lacks local/requestservices:view capability', DEBUG_DEVELOPER);
    debugging('User ID: ' . $USER->id, DEBUG_DEVELOPER);
    debugging('Context ID: ' . $context->id, DEBUG_DEVELOPER);
}
```

**Verify Context Setup**:
```php
// Check that context is properly initialized in index.php
var_dump($context); // Should show course context object
var_dump($courseid); // Should show valid course ID
```

**Reset Role Capabilities**:
1. Site Administration → Users → Permissions → Define roles
2. Select "Teacher" or "Editing teacher" role
3. Search for `local/requestservices:view`
4. Set to "Allow"
5. Click "Save changes"

### Template and Display Issues

#### Templates Not Rendering Correctly

**Symptoms**:
- Blank pages or incomplete content
- Missing styling or layout issues
- JavaScript errors in browser console

**Solutions**:

**Check Template Files**:
```bash
# Verify template files exist
ls -la /path/to/moodle/local/requestservices/templates/
# Should contain .mustache files
```

**Verify Renderer Classes**:
```bash
# Check renderer class files
ls -la /path/to/moodle/local/requestservices/classes/output/
# Should contain corresponding PHP files
```

**Clear Template Cache**:
```bash
# Clear template caches specifically
php /path/to/moodle/admin/cli/purge_caches.php --theme
# Or manually
rm -rf /path/to/moodle/localcache/mustache/*
```

**Check Template Syntax**:
- Validate Mustache template syntax
- Ensure proper variable names and structure
- Check for missing closing tags

#### Language Strings Not Displaying

**Symptoms**:
- Text appears as language string keys (e.g., `[[requestservices,local_requestservices]]`)
- Missing translations
- Mixed language content

**Solutions**:

**Verify Language Files**:
```bash
# Check language file structure
ls -la /path/to/moodle/local/requestservices/lang/en/
ls -la /path/to/moodle/local/requestservices/lang/ar/
# Should contain local_requestservices.php
```

**Check Language File Syntax**:
```bash
# Test PHP syntax
php -l /path/to/moodle/local/requestservices/lang/en/local_requestservices.php
```

**Clear Language Cache**:
```bash
# Clear language caches
php /path/to/moodle/admin/cli/purge_caches.php --lang
```

**Verify String Usage**:
```php
// In PHP files, ensure proper string usage
get_string('stringkey', 'local_requestservices')
// Not: get_string('stringkey', 'requestservices')
```

### Tab Functionality Issues

#### Tabs Not Switching Properly

**Symptoms**:
- Clicking tabs doesn't change content
- URL changes but content remains the same
- JavaScript errors related to tabs

**Solutions**:

**Check Tab File Existence**:
```bash
# Verify all tab files exist
ls -la /path/to/moodle/local/requestservices/tabs/
# Should contain: allrequests.php, computerservicesTab.php, etc.
```

**Check Tab Parameter Handling**:
```php
// In index.php, verify tab parameter
$tab = optional_param('tab', 'computerservicesTab', PARAM_ALPHA);
debugging('Current tab: ' . $tab, DEBUG_DEVELOPER);
```

**Verify Tab File Include Logic**:
```php
// Check file existence before include
$tabfile = $CFG->dirroot . '/local/requestservices/tabs/' . $tab . '.php';
if (file_exists($tabfile)) {
    debugging('Including tab file: ' . $tabfile, DEBUG_DEVELOPER);
    include($tabfile);
} else {
    debugging('Tab file not found: ' . $tabfile, DEBUG_DEVELOPER);
}
```

#### Invalid Tab Error

**Symptoms**:
- "Working on page" error message
- Tab content doesn't load
- Specific tab always shows error

**Solutions**:

**Check Tab File Names**:
- Ensure tab file names match exactly what's defined in `$tabnames` array
- Check for typos or case sensitivity issues
- Verify file extensions (.php)

**Check Tab Array Definition**:
```php
// In index.php, verify tab names array
$tabnames = ['allrequests', 'computerservicesTab', 'financialservices', 
            'registeringroom', 'requestparticipant', 'residencebooking'];
```

### Dependency Issues

#### Computer Service Integration Errors

**Symptoms**:
- Errors when accessing computer services tab
- Missing form classes
- "Class not found" errors

**Solutions**:

**Verify Dependency Installation**:
```bash
# Check if local_computerservice plugin exists
ls -la /path/to/moodle/local/computerservice/
```

**Check Required Classes**:
```bash
# Verify required files exist
ls -la /path/to/moodle/local/computerservice/classes/form/request_form.php
ls -la /path/to/moodle/local/computerservice/classes/simple_workflow_manager.php
```

**Install Missing Dependency**:
If `local_computerservice` is missing:
1. Install the required plugin first
2. Ensure it's enabled and functional
3. Clear caches
4. Test requestservices plugin again

### Performance Issues

#### Slow Page Loading

**Symptoms**:
- Long loading times for tabs
- Browser timeout errors
- High server resource usage

**Solutions**:

**Enable Performance Debugging**:
```php
// Add to config.php temporarily
$CFG->perfdebug = 15;
$CFG->debugpageinfo = 1;
```

**Check Database Queries**:
- Look for inefficient database queries in renderer classes
- Optimize data retrieval in templates
- Consider caching frequently accessed data

**Optimize Template Complexity**:
- Reduce complex logic in Mustache templates
- Move data processing to renderer classes
- Minimize nested loops and conditions

### Browser-Specific Issues

#### JavaScript Errors

**Symptoms**:
- Console errors in browser
- Interactive elements not working
- Layout issues in specific browsers

**Solutions**:

**Check Browser Console**:
- Open developer tools (F12)
- Check console for JavaScript errors
- Note specific error messages and line numbers

**Verify Bootstrap Compatibility**:
- Ensure Moodle's Bootstrap version compatibility
- Check for CSS conflicts
- Test in multiple browsers

**Clear Browser Cache**:
- Force refresh (Ctrl+F5)
- Clear browser cache and cookies
- Test in incognito/private mode

## Debugging Tools and Techniques

### Enable Moodle Debugging

**Add to config.php**:
```php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
$CFG->debugpageinfo = 1;
$CFG->perfdebug = 15;
$CFG->debugstringids = 1; // Shows language string IDs
```

### Custom Debugging in Plugin

**Add debugging statements**:
```php
// In plugin files
debugging('Debug message here', DEBUG_DEVELOPER);
error_log('Log message: ' . print_r($variable, true));
```

### Database Debugging

**Monitor database queries**:
```php
// Add to config.php
$CFG->debugsqltrace = true;
```

### Log File Locations

**Check these log files**:
- Web server error log (usually `/var/log/apache2/error.log`)
- Moodle data directory logs
- PHP error log
- Database error log

## Getting Additional Help

### Before Seeking Help

1. **Gather Information**:
   - Exact error messages
   - Steps to reproduce the issue
   - Browser and Moodle version
   - Recent changes or updates

2. **Check Logs**:
   - Review all relevant log files
   - Note timestamps and error patterns
   - Include relevant log excerpts

3. **Test Environment**:
   - Try reproducing in a test environment
   - Test with different user roles
   - Check multiple browsers

### Support Resources

1. **Documentation**: Check all documentation files in `/docs/` directory
2. **Moodle Community**: Search Moodle forums for similar issues
3. **Plugin Developer**: Contact the plugin developer with detailed information
4. **System Administrator**: Consult your Moodle system administrator

### Providing Effective Bug Reports

**Include the following information**:
- Moodle version and build number
- Plugin version
- PHP version and server environment
- Exact error messages (with screenshots if helpful)
- Steps to reproduce the issue
- Expected vs. actual behavior
- Browser and operating system details
- Any recent changes or customizations 