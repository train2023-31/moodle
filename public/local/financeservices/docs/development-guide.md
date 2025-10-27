# Development Guide

This guide explains how to develop, extend, and maintain the Finance Services plugin.

## 🚀 Getting Started

### Prerequisites
- **Moodle 3.9+** development environment
- **PHP 7.4+** with required extensions
- **local_status plugin** installed
- **Git** for version control
- **Text editor** with PHP syntax support

### Development Environment Setup
```bash
# Clone or place plugin in Moodle
cd /path/to/moodle
cp -r financeservices local/financeservices

# Install dependencies
php admin/cli/install_database.php

# Upgrade database
php admin/cli/upgrade.php --non-interactive
```

## 🏗️ Plugin Development Patterns

### 1. Adding New Form Fields

**Step 1**: Update the form class
```php
// In classes/form/add_form.php
$mform->addElement('text', 'new_field', get_string('new_field', 'local_financeservices'));
$mform->setType('new_field', PARAM_TEXT);
$mform->addRule('new_field', get_string('required'), 'required', null, 'client');
```

**Step 2**: Update database schema
```php
// In db/upgrade.php
if ($oldversion < 2025061902) {
    $table = new xmldb_table('local_financeservices');
    $field = new xmldb_field('new_field', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'existing_field');
    
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    
    upgrade_plugin_savepoint(true, 2025061902, 'local', 'financeservices');
}
```

**Step 3**: Update language strings
```php
// In lang/en/local_financeservices.php
$string['new_field'] = 'New Field Label';
$string['new_field_help'] = 'Help text for the new field';
```

**Step 4**: Handle form data
```php
// In index.php or relevant controller
if ($data = $mform->get_data()) {
    $record->new_field = $data->new_field;
    // ... rest of processing
}
```

### 2. Adding New Workflow Status

**Step 1**: Define status in local_status plugin
```sql
INSERT INTO mdl_local_status (status_string_en, display_name_en, display_name_ar, status_position)
VALUES ('new_status', 'New Status', 'حالة جديدة', 15);
```

**Step 2**: Update workflow manager
```php
// In classes/simple_workflow_manager.php
public static function get_next_status_id($current_status_id) {
    $workflow_map = [
        8 => 9,   // Initial -> Leader 1
        9 => 10,  // Leader 1 -> Leader 2
        10 => 11, // Leader 2 -> Leader 3
        11 => 12, // Leader 3 -> Boss
        12 => 15, // Boss -> New Status
        15 => 13, // New Status -> Approved
    ];
    return $workflow_map[$current_status_id] ?? null;
}
```

**Step 3**: Update permission checks
```php
public static function can_user_approve($status_id) {
    switch ($status_id) {
        case 15: // New Status
            return has_capability('local/financeservices:new_status_approve', context_system::instance());
        // ... other cases
    }
}
```

### 3. Creating New Management Pages

**Step 1**: Create page controller
```php
// In pages/manage_newentity.php
<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
require_capability('local/financeservices:manage', $context);

$PAGE->set_url(new moodle_url('/local/financeservices/pages/manage_newentity.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_newentity', 'local_financeservices'));

// Handle actions (add, edit, delete)
$action = optional_param('action', 'list', PARAM_ALPHA);

switch ($action) {
    case 'add':
        // Handle add logic
        break;
    case 'edit':
        // Handle edit logic
        break;
    case 'delete':
        // Handle delete logic
        break;
    default:
        // Display list
        break;
}
```

**Step 2**: Create form class
```php
// In classes/form/newentity_form.php
<?php
namespace local_financeservices\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class newentity_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        
        $mform->addElement('text', 'name', get_string('name', 'local_financeservices'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');
        
        $this->add_action_buttons();
    }
}
```

### 4. Adding New Event Types

**Step 1**: Create event class
```php
// In classes/event/newentity_created.php
<?php
namespace local_financeservices\event;

defined('MOODLE_INTERNAL') || die();

class newentity_created extends \core\event\base {
    protected function init() {
        $this->data['objecttable'] = 'local_financeservices_newentity';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    
    public static function get_name() {
        return get_string('event_newentity_created', 'local_financeservices');
    }
    
    public function get_description() {
        return "User {$this->userid} created new entity {$this->objectid}";
    }
}
```

**Step 2**: Trigger event
```php
// In controller code
$event = \local_financeservices\event\newentity_created::create([
    'context' => context_system::instance(),
    'objectid' => $newentity_id,
    'other' => ['name' => $data->name]
]);
$event->trigger();
```

## 🔧 Common Development Tasks

### Database Operations

**Creating Records**:
```php
global $DB;

$record = new stdClass();
$record->field1 = $value1;
$record->field2 = $value2;
$record->timecreated = time();

$id = $DB->insert_record('local_financeservices_table', $record);
```

**Updating Records**:
```php
$record = $DB->get_record('local_financeservices_table', ['id' => $id]);
$record->field1 = $new_value;
$record->timemodified = time();

$DB->update_record('local_financeservices_table', $record);
```

**Complex Queries**:
```php
$sql = "SELECT fs.*, c.fullname AS course_name
        FROM {local_financeservices} fs
        JOIN {course} c ON fs.course_id = c.id
        WHERE fs.status_id = :status_id
        ORDER BY fs.date_time_requested DESC";

$params = ['status_id' => $status_id];
$records = $DB->get_records_sql($sql, $params);
```

### Working with Forms

**Form Validation**:
```php
public function validation($data, $files) {
    $errors = parent::validation($data, $files);
    
    if (empty($data['price_requested']) || $data['price_requested'] <= 0) {
        $errors['price_requested'] = get_string('price_positive', 'local_financeservices');
    }
    
    return $errors;
}
```

**Dynamic Form Elements**:
```php
public function definition() {
    global $DB;
    
    $mform = $this->_form;
    
    // Get dynamic options from database
    $options = $DB->get_records_menu('local_financeservices_funding_type', 
        ['active' => 1], 'name_en', 'id, name_en');
    
    $mform->addElement('select', 'funding_type_id', get_string('funding_type', 'local_financeservices'), $options);
}
```

### Template Development

**Mustache Template Structure**:
```mustache
{{! templates/new_template.mustache }}
<div class="finance-services-container">
    {{#items}}
    <div class="item-card">
        <h3>{{title}}</h3>
        <p>{{description}}</p>
        {{#show_actions}}
        <div class="actions">
            <button class="btn btn-primary" data-id="{{id}}">{{action_label}}</button>
        </div>
        {{/show_actions}}
    </div>
    {{/items}}
    {{^items}}
    <p class="no-items">{{no_items_message}}</p>
    {{/items}}
</div>
```

**Renderer Class**:
```php
// In classes/output/new_renderer.php
class new_renderer implements \renderable, \templatable {
    private $items;
    
    public function __construct($items) {
        $this->items = $items;
    }
    
    public function export_for_template(\renderer_base $output) {
        $data = ['items' => []];
        
        foreach ($this->items as $item) {
            $data['items'][] = [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'show_actions' => has_capability('local/financeservices:manage', context_system::instance()),
                'action_label' => get_string('edit', 'local_financeservices')
            ];
        }
        
        $data['no_items_message'] = get_string('no_items', 'local_financeservices');
        
        return $data;
    }
}
```

### AJAX Development

**Client-side AJAX**:
```javascript
// In templates or external JS files
function updateStatus(requestId, action, note) {
    const data = {
        action: action,
        id: requestId,
        note: note || '',
        sesskey: M.cfg.sesskey
    };
    
    fetch('/local/financeservices/actions/update_request_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Or update UI dynamically
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
```

**Server-side AJAX Handler**:
```php
// In actions/new_action.php
<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
require_sesskey();

$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? '';
$id = (int)($input['id'] ?? 0);

$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'new_action':
            // Handle action
            $response['success'] = true;
            $response['message'] = get_string('success_message', 'local_financeservices');
            break;
        default:
            $response['message'] = get_string('invalid_action', 'local_financeservices');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
```

## 🧪 Testing Guidelines

### Unit Testing
```php
// In tests/workflow_test.php
<?php
namespace local_financeservices;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/financeservices/classes/simple_workflow_manager.php');

class workflow_test extends \advanced_testcase {
    protected function setUp(): void {
        $this->resetAfterTest();
    }
    
    public function test_initial_status() {
        $status_id = simple_workflow_manager::get_initial_status_id();
        $this->assertEquals(8, $status_id);
    }
    
    public function test_approval_workflow() {
        // Create test data
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        
        // Test workflow logic
        $result = simple_workflow_manager::can_user_approve(9);
        $this->assertIsBool($result);
    }
}
```

### Behat Testing
```gherkin
# In tests/behat/features/finance_request.feature
Feature: Finance request submission
  As a staff member
  I want to submit finance requests
  So that I can get funding approval

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | staff1   | Staff     | User     | staff@example.com |
    And I log in as "staff1"

  Scenario: Submit a new finance request
    Given I am on the finance services page
    When I click on "Add" tab
    And I fill in the request form
    And I press "Submit Request"
    Then I should see "Request submitted successfully"
```

## 🔒 Security Best Practices

### Input Validation
```php
// Always validate and clean input
$courseid = required_param('courseid', PARAM_INT);
$notes = optional_param('notes', '', PARAM_TEXT);
$price = required_param('price', PARAM_FLOAT);

// Validate business logic
if ($price <= 0) {
    throw new moodle_exception('invalid_price', 'local_financeservices');
}
```

### Permission Checks
```php
// Check capabilities before allowing actions
$context = context_system::instance();
require_capability('local/financeservices:manage', $context);

// Check record ownership
if ($request->user_id !== $USER->id && !has_capability('local/financeservices:manage', $context)) {
    throw new moodle_exception('nopermission', 'local_financeservices');
}
```

### CSRF Protection
```php
// In forms
require_sesskey();

// In AJAX
if (!confirm_sesskey($input['sesskey'])) {
    throw new moodle_exception('invalidsesskey');
}
```

## 📝 Documentation Standards

### Code Comments
```php
/**
 * Approves a finance request and moves it to the next workflow step.
 *
 * @param int $request_id The ID of the request to approve
 * @param string $note Optional approval note
 * @return bool True if approval successful, false otherwise
 * @throws moodle_exception If user doesn't have permission or request not found
 */
public static function approve_request($request_id, $note = '') {
    // Implementation...
}
```

### Language Strings
```php
// Always add help strings for complex fields
$string['funding_type'] = 'Funding Type';
$string['funding_type_help'] = 'Select the type of funding you are requesting. This determines the approval workflow.';
```

## 🚀 Deployment Guidelines

### Version Management
1. Update `version.php` with new version number
2. Add upgrade steps in `upgrade.php` if needed
3. Test upgrade process on copy of production data
4. Document breaking changes

### Release Checklist
- [ ] All tests passing
- [ ] Language strings complete
- [ ] Database upgrades tested
- [ ] Documentation updated
- [ ] Code review completed
- [ ] Security review passed

This guide should help developers effectively work with and extend the Finance Services plugin while maintaining code quality and security standards. 