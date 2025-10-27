# Development Guide

This guide provides comprehensive instructions for developers working on the Annual Plans plugin, including setup, development practices, and extension guidelines.

## Development Environment Setup

### Prerequisites

1. **Moodle Development Environment**
   - Moodle 3.10 or higher
   - PHP 7.4 or higher
   - MySQL 5.7+ or PostgreSQL 10+
   - Web server (Apache/Nginx)

2. **Development Tools**
   - Code editor with PHP support (VS Code, PhpStorm)
   - Git version control
   - Browser developer tools
   - Database management tool

3. **Moodle Development Configuration**
   ```php
   // config.php development settings
   $CFG->debug = (E_ALL | E_STRICT);
   $CFG->debugdisplay = 1;
   $CFG->debugdeveloper = true;
   $CFG->perfdebug = 15;
   $CFG->debugpageinfo = true;
   $CFG->allowthemechangeonurl = true;
   $CFG->cachejs = false;
   $CFG->yuicomboloading = false;
   ```

### Installation for Development

1. **Clone/Copy Plugin**
   ```bash
   # Navigate to Moodle local directory
   cd /path/to/moodle/local/
   
   # Copy plugin files
   cp -r /source/annualplans ./
   ```

2. **Install Plugin**
   - Visit Site Administration → Notifications
   - Complete installation process
   - Verify database tables are created

3. **Set Development Permissions**
   - Assign capabilities to developer user
   - Configure test data if needed

## Plugin Architecture

### MVC Pattern Implementation

The plugin follows a Model-View-Controller architecture:

#### **Models** (Data Layer)
- Database interactions via Moodle DB API
- Data validation and business rules
- Located in: Database operations within classes

#### **Views** (Presentation Layer)
- Mustache templates in `templates/`
- Form definitions in `classes/control_form.php`
- Page layouts and UI components

#### **Controllers** (Business Logic)
- `classes/AnnualPlansController.php` - Main controller
- `classes/CourseManager.php` - Course operations
- Page controllers in root PHP files

### Directory Structure for Development

```
local/annualplans/
├── classes/               # PHP classes
│   ├── AnnualPlansController.php
│   ├── CourseManager.php
│   ├── control_form.php
│   └── table.php
├── db/                   # Database definitions
│   ├── install.xml
│   ├── upgrade.php
│   ├── install.php
│   ├── access.php
│   └── caches.php
├── ajax/                 # AJAX endpoints
├── js/                   # JavaScript files
├── templates/            # Mustache templates
├── lang/                 # Language files
└── DOCUMENTATION/        # This documentation
```

## Development Workflow

### 1. Setting Up New Features

#### Create Feature Branch
```bash
git checkout -b feature/new-feature-name
```

#### Follow Moodle Coding Standards
- Use Moodle coding style
- Follow PSR-1 and PSR-12 where applicable
- Use proper documentation blocks

#### Example Class Structure
```php
<?php
/**
 * Class description.
 *
 * @package    local_annualplans
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class new_feature_class {
    
    /**
     * Constructor.
     */
    public function __construct() {
        // Implementation
    }
    
    /**
     * Method description.
     *
     * @param int $param1 Parameter description
     * @param string $param2 Parameter description
     * @return bool Success status
     */
    public function method_name($param1, $param2) {
        // Implementation
    }
}
```

### 2. Database Changes

#### Schema Modifications
1. **Update install.xml**
   ```xml
   <FIELD NAME="new_field" TYPE="char" LENGTH="255" NOTNULL="false" SEQUENCE="false" COMMENT="New field description"/>
   ```

2. **Create Upgrade Script** (db/upgrade.php)
   ```php
   if ($oldversion < 2025071501) {
       $table = new xmldb_table('local_annual_plan_course');
       $field = new xmldb_field('new_field', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'existing_field');
       
       if (!$dbman->field_exists($table, $field)) {
           $dbman->add_field($table, $field);
       }
       
       upgrade_plugin_savepoint(true, 2025071501, 'local', 'annualplans');
   }
   ```

3. **Update Version Number** (version.php)
   ```php
   $plugin->version = 2025071501; // Increment version
   ```

### 3. Adding New Pages

#### Create Page File
```php
<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Security checks
require_login();
$context = context_system::instance();
require_capability('local/annualplans:manage', $context);

// Page setup
$PAGE->set_url(new moodle_url('/local/annualplans/new_page.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pagetitle', 'local_annualplans'));
$PAGE->set_heading(get_string('pageheading', 'local_annualplans'));

// Business logic
// ...

// Output
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheading', 'local_annualplans'));
// Page content
echo $OUTPUT->footer();
```

#### Add Language Strings
```php
// lang/en/local_annualplans.php
$string['pagetitle'] = 'Page Title';
$string['pageheading'] = 'Page Heading';
```

#### Add Navigation Links
```php
// settings.php
$newpage = new admin_externalpage('local_annualplans_newpage', 
    get_string('pagetitle', 'local_annualplans'), 
    new moodle_url('/local/annualplans/new_page.php'), 
    'local/annualplans:manage');
$ADMIN->add('local_annualplans', $newpage);
```

### 4. Adding AJAX Endpoints

#### Create AJAX File
```php
<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

// Security checks
require_login();
require_capability('local/annualplans:view', context_system::instance());

$action = required_param('action', PARAM_ALPHA);

$response = array();

try {
    switch($action) {
        case 'get_data':
            $id = required_param('id', PARAM_INT);
            $response['data'] = get_data_function($id);
            $response['success'] = true;
            break;
            
        default:
            throw new invalid_parameter_exception('Invalid action');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
```

#### JavaScript Integration
```javascript
// js/main.js
function callAjaxEndpoint(action, data, callback) {
    $.ajax({
        url: M.cfg.wwwroot + '/local/annualplans/ajax/endpoint.php',
        method: 'POST',
        data: {
            action: action,
            ...data,
            sesskey: M.cfg.sesskey
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                callback(response.data);
            } else {
                console.error('AJAX Error:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Request Failed:', error);
        }
    });
}
```

### 5. Working with Forms

#### Create Form Class
```php
// classes/new_form.php
class new_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        
        // Add form elements
        $mform->addElement('text', 'name', get_string('name', 'local_annualplans'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        
        $this->add_action_buttons();
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Custom validation
        if (empty($data['name'])) {
            $errors['name'] = get_string('required');
        }
        
        return $errors;
    }
}
```

#### Use Form in Controller
```php
$form = new new_form();

if ($form->is_cancelled()) {
    redirect($return_url);
} else if ($data = $form->get_data()) {
    // Process form data
    // Save to database
    redirect($return_url, get_string('success', 'local_annualplans'));
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
```

## Testing and Quality Assurance

### 1. Unit Testing

#### PHPUnit Test Example
```php
<?php
/**
 * Unit tests for Annual Plans plugin.
 *
 * @package    local_annualplans
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_annualplans_testcase extends advanced_testcase {
    
    public function setUp(): void {
        $this->resetAfterTest(true);
    }
    
    public function test_create_annual_plan() {
        global $DB;
        
        $plan = new stdClass();
        $plan->year = 2025;
        $plan->title = 'Test Plan';
        $plan->status = 'active';
        $plan->date_created = time();
        
        $id = $DB->insert_record('local_annual_plan', $plan);
        
        $this->assertNotEmpty($id);
        $this->assertTrue($DB->record_exists('local_annual_plan', ['id' => $id]));
    }
}
```

### 2. Code Quality Checks

#### Run Moodle Code Checker
```bash
# Install Moodle Code Checker
composer global require moodlehq/moodle-local_codechecker

# Check code
phpcbf --standard=moodle /path/to/local/annualplans/
phpcs --standard=moodle /path/to/local/annualplans/
```

#### ESLint for JavaScript
```bash
# Install ESLint
npm install -g eslint

# Check JavaScript
eslint js/main.js
```

### 3. Browser Testing

#### Test Checklist
- [ ] All forms submit correctly
- [ ] AJAX requests work properly
- [ ] Responsive design functions
- [ ] Cross-browser compatibility
- [ ] Accessibility compliance

## Security Best Practices

### 1. Input Validation
```php
// Always validate and clean input
$id = required_param('id', PARAM_INT);
$text = clean_param($input, PARAM_TEXT);

// Use optional_param for optional parameters
$sort = optional_param('sort', 'name', PARAM_ALPHA);
```

### 2. Capability Checks
```php
// Always check capabilities
require_capability('local/annualplans:manage', context_system::instance());

// Check before displaying sensitive content
if (has_capability('local/annualplans:view', $context)) {
    // Show content
}
```

### 3. Output Escaping
```php
// Escape output to prevent XSS
echo s($user_input);
echo format_text($content, FORMAT_HTML);

// Use proper URL generation
$url = new moodle_url('/local/annualplans/page.php', ['id' => $id]);
```

### 4. CSRF Protection
```php
// Include session key in forms
echo html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey()
]);

// Verify session key
require_sesskey();
```

## Performance Optimization

### 1. Database Optimization
```php
// Use efficient queries
$records = $DB->get_records_sql('
    SELECT c.*, p.title as plan_title 
    FROM {local_annual_plan_course} c 
    JOIN {local_annual_plan} p ON c.annualplanid = p.id 
    WHERE c.status = ?', ['active']);

// Use recordsets for large datasets
$recordset = $DB->get_recordset('table_name', $conditions);
foreach ($recordset as $record) {
    // Process record
}
$recordset->close();
```

### 2. Caching
```php
// Use Moodle cache API
$cache = cache::make('local_annualplans', 'plans');
$data = $cache->get('key');
if ($data === false) {
    $data = expensive_operation();
    $cache->set('key', $data);
}
```

### 3. JavaScript Optimization
```javascript
// Use AMD modules
require(['jquery'], function($) {
    // Code here
});

// Minimize DOM operations
var $container = $('#container');
$container.html(content);
```

## Debugging and Troubleshooting

### 1. Enable Debug Mode
```php
// config.php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

### 2. Logging
```php
// Use Moodle debugging functions
debugging('Debug message', DEBUG_DEVELOPER);
mtrace('Message for CLI scripts');

// Log to Moodle logs
add_to_log(SITEID, 'local_annualplans', 'action', 'url', 'info');
```

### 3. Database Debugging
```php
// Enable SQL debugging
$CFG->perfdebug = 15;

// Manual query debugging
$sql = "SELECT * FROM {table} WHERE id = ?";
$params = [123];
echo $DB->fix_sql_params($sql, $params)[0];
```

## Deployment Guidelines

### 1. Version Management
```php
// Update version.php
$plugin->version = 2025071502;
$plugin->release = '1.8 (Build: 2025071502)';
```

### 2. Database Migrations
- Test upgrade scripts thoroughly
- Backup database before upgrades
- Use transactions where possible

### 3. Documentation Updates
- Update CHANGELOG.md
- Update user documentation
- Update API documentation

## Contributing Guidelines

### 1. Code Standards
- Follow Moodle coding guidelines
- Use meaningful variable names
- Add proper documentation
- Include unit tests

### 2. Git Workflow
```bash
# Create feature branch
git checkout -b feature/description

# Make commits with descriptive messages
git commit -m "Add new feature: detailed description"

# Push and create pull request
git push origin feature/description
```

### 3. Review Process
- Code review required
- Test in clean environment
- Update documentation
- Verify database migrations

This development guide provides a comprehensive foundation for working with the Annual Plans plugin while maintaining code quality, security, and performance standards. 