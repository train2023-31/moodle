# Development Workflow Guide

This guide explains how to develop, maintain, and extend the Computer Service plugin.

## 🏗️ Development Environment Setup

### Prerequisites
- Moodle 4.0+ development environment
- PHP 7.4+ with required extensions
- MySQL/PostgreSQL database
- Git for version control
- Text editor with PHP support

### Local Development Setup
1. **Clone or navigate to plugin directory**:
   ```bash
   cd /path/to/moodle/local/computerservice/
   ```

2. **Ensure proper permissions**:
   ```bash
   chmod -R 755 .
   chown -R www-data:www-data .  # For Apache
   ```

3. **Install/upgrade the plugin**:
   - Go to Site Administration → Notifications
   - Follow the installation/upgrade prompts

## 🔄 Development Workflow

### 1. Understanding the Codebase

#### Start Here
1. **Read the main files** in this order:
   - `readme.md` - Overall understanding
   - `version.php` - Current version and changelog
   - `index.php` - Main functionality
   - `classes/simple_workflow_manager.php` - Core workflow logic

2. **Explore the structure**:
   ```
   /classes/           → Core PHP classes
   /pages/             → Individual page controllers  
   /templates/         → Mustache UI templates
   /actions/           → AJAX handlers
   /db/                → Database schema and upgrades
   /lang/              → Language strings
   ```

### 2. Making Changes

#### Code Modification Process

1. **For Database Changes**:
   - Update `version.php` with new version number
   - Add upgrade script in `db/upgrade.php`
   - Update `install.xml` if adding new tables
   - Test upgrade path from previous versions

2. **For New Features**:
   - Create/modify classes in `/classes/`
   - Add new language strings to `/lang/en/` and `/lang/ar/`
   - Update templates in `/templates/` if UI changes needed
   - Add pages in `/pages/` if new pages required

3. **For Bug Fixes**:
   - Identify the affected file(s)
   - Make minimal changes to fix the issue
   - Update version number in `version.php`
   - Add changelog entry

#### File-Specific Development Guidelines

##### Working with Forms (`/classes/form/`)
```php
// Extend moodleform for new forms
class my_new_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add form elements
        $mform->addElement('text', 'fieldname', get_string('label', 'local_computerservice'));
        $mform->setType('fieldname', PARAM_TEXT);
        $mform->addRule('fieldname', null, 'required', null, 'client');
    }
}
```

##### Working with Workflow (`classes/simple_workflow_manager.php`)
```php
// Adding new workflow statuses
const STATUS_NEW_STEP = 22;

// Adding new workflow methods
public static function custom_workflow_action($requestid, $action) {
    // Validate session and capabilities
    // Update database
    // Return result
}
```

##### Working with Templates (`/templates/`)
```mustache
{{!-- Use Mustache syntax for templates --}}
{{#requests}}
    <tr>
        <td>{{username}}</td>
        <td>{{coursename}}</td>
        <td><span class="badge-{{status_class}}">{{status_name}}</span></td>
    </tr>
{{/requests}}
```

### 3. Testing Your Changes

#### Manual Testing Checklist

1. **Basic Functionality**:
   - [ ] Plugin installs without errors
   - [ ] All three tabs load correctly
   - [ ] Forms submit and validate properly
   - [ ] Database operations work correctly

2. **Workflow Testing**:
   - [ ] Request submission works
   - [ ] Approval/rejection processes correctly
   - [ ] Status changes are reflected in UI
   - [ ] Rejection notes are saved and displayed

3. **Security Testing**:
   - [ ] Capability checks work correctly
   - [ ] Session key validation prevents unauthorized access
   - [ ] Input sanitization prevents XSS/SQL injection

4. **Multilingual Testing**:
   - [ ] Switch language to Arabic and test all features
   - [ ] Device names display in correct language
   - [ ] All UI text is translated
   - [ ] Urgency filter options display in correct language

#### Database Testing
```sql
-- Check request table structure
DESCRIBE mdl_local_computerservice_requests;

-- Verify data integrity
SELECT * FROM mdl_local_computerservice_requests WHERE status_id NOT IN (15,16,17,18,19,20,21);

-- Test bilingual device names
SELECT id, devicename_en, devicename_ar, status FROM mdl_local_computerservice_devices;

-- Test urgency filtering
SELECT COUNT(*) as urgent_count FROM mdl_local_computerservice_requests WHERE is_urgent = 1;
SELECT COUNT(*) as non_urgent_count FROM mdl_local_computerservice_requests WHERE is_urgent = 0;
```

### 4. Version Management

#### Version Numbering Scheme
- Format: `YYYYMMDDRR` (Year, Month, Day, Revision)
- Example: `2025061904` = June 19, 2025, 4th revision

#### Updating Versions
1. **Update `version.php`**:
   ```php
   $plugin->version = 2025061905;  // New version
   $plugin->release = '1.3.2 (Build: 2025061905) - Description of changes';
   ```

2. **Add changelog entry**:
   ```php
   /**
    * ▸ 2025-06-19  v1.3.2  (Build 2025061905)
    *     • New feature: Description
    *     • Bug fix: Issue description
    *     • Enhancement: Improvement description
    */
   ```

3. **Database upgrades** (if needed):
   ```php
   // In db/upgrade.php
   if ($oldversion < 2025061905) {
       // Upgrade code here
       upgrade_plugin_savepoint(true, 2025061905, 'local', 'computerservice');
   }
   ```

## 🧪 Advanced Development

### Adding New Workflow Steps

1. **Define new status constants** in `simple_workflow_manager.php`:
   ```php
   const STATUS_NEW_STEP = 22;
   ```

2. **Update workflow logic**:
   ```php
   public static function get_next_status($current_status) {
       switch ($current_status) {
           case self::STATUS_EXISTING:
               return self::STATUS_NEW_STEP;
           case self::STATUS_NEW_STEP:
               return self::STATUS_NEXT;
       }
   }
   ```

3. **Add language strings**:
   ```php
   // In lang/en/local_computerservice.php
   $string['status_new_step'] = 'New Step Review';
   
   // In lang/ar/local_computerservice.php  
   $string['status_new_step'] = 'مراجعة الخطوة الجديدة';
   ```

4. **Update capabilities** in `db/access.php`:
   ```php
   'local/computerservice:approve_new_step' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_SYSTEM,
       'archetypes' => array(
           'manager' => CAP_ALLOW,
       ),
   ),
   ```

### Creating New Pages

1. **Create page file** in `/pages/`:
   ```php
   <?php
   // pages/my_new_page.php
   require_once('../../../config.php');
   require_login();
   
   $context = context_system::instance();
   require_capability('local/computerservice:some_capability', $context);
   
   $PAGE->set_url('/local/computerservice/pages/my_new_page.php');
   $PAGE->set_context($context);
   $PAGE->set_title(get_string('page_title', 'local_computerservice'));
   
   echo $OUTPUT->header();
   // Page content here
   echo $OUTPUT->footer();
   ```

2. **Add navigation** (if needed) in main `index.php`:
   ```php
   $tabs[] = new tabobject('newpage', 
       new moodle_url('/local/computerservice/pages/my_new_page.php'), 
       get_string('newpage', 'local_computerservice')
   );
   ```

### AJAX Development

1. **Create AJAX handler** in `/actions/`:
   ```php
   <?php
   // actions/my_ajax_action.php
   define('AJAX_SCRIPT', true);
   require_once('../../../config.php');
   
   $action = required_param('action', PARAM_ALPHA);
   $sesskey = required_param('sesskey', PARAM_RAW);
   
   if (!confirm_sesskey($sesskey)) {
       die(json_encode(['success' => false, 'error' => 'Invalid session']));
   }
   
   // Process action and return JSON
   echo json_encode(['success' => true, 'data' => $result]);
   ```

2. **Add JavaScript** to call AJAX:
   ```javascript
   // In template or page
   $.ajax({
       url: M.cfg.wwwroot + '/local/computerservice/actions/my_ajax_action.php',
       type: 'POST',
       data: {
           action: 'myaction',
           sesskey: M.cfg.sesskey,
           param: value
       },
       success: function(response) {
           // Handle response
       }
   });
   ```

## 🔍 Debugging and Troubleshooting

### Common Issues and Solutions

1. **Database Errors**:
   - Check `db/install.xml` for proper table definitions
   - Verify upgrade scripts in `db/upgrade.php`
   - Use Moodle debugging: `$CFG->debug = E_ALL; $CFG->debugdisplay = 1;`

2. **Permission Issues**:
   - Check capability definitions in `db/access.php`
   - Verify role assignments in Moodle admin
   - Test with different user roles

3. **Language String Issues**:
   - Ensure strings exist in both `/lang/en/` and `/lang/ar/`
   - Clear Moodle caches after adding new strings
   - Use `get_string()` function correctly

4. **Template Issues**:
   - Validate Mustache syntax
   - Check data passed to template in output classes
   - Clear template cache in Moodle admin

### Debug Techniques

```php
// Enable debugging in config.php
$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;

// Use Moodle debugging functions
debugging('Debug message here', DEBUG_DEVELOPER);

// Log to Moodle log
error_log('Custom log message: ' . print_r($variable, true));

// Use var_dump for development (remove before production)
var_dump($data);
```

## 📚 Best Practices

### Code Quality
- Follow Moodle coding standards
- Use proper indentation and commenting
- Validate all inputs and sanitize outputs
- Handle errors gracefully

### Security
- Always use `required_param()` and `optional_param()`
- Implement proper capability checks
- Use session keys for AJAX requests
- Sanitize database inputs and outputs

### Performance
- Use database efficiently (avoid N+1 queries)
- Cache results when appropriate
- Minimize AJAX requests
- Optimize template rendering

### Maintainability
- Keep functions small and focused
- Use meaningful variable and function names
- Document complex logic
- Update version numbers and changelogs

## 🚀 Deployment

### Pre-deployment Checklist
- [ ] All tests pass
- [ ] Version number updated
- [ ] Database upgrade scripts tested
- [ ] Language strings complete
- [ ] Documentation updated
- [ ] Backup taken of existing version

### Deployment Process
1. **Backup current version**
2. **Upload new files**
3. **Run Moodle upgrade** (Site Administration → Notifications)
4. **Test critical functionality**
5. **Monitor for errors** in Moodle logs

### Post-deployment
- Monitor system performance
- Check for any error logs
- Verify all functionality works as expected
- Update documentation if needed 