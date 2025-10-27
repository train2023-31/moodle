# API Reference

This document provides detailed API reference for the key classes and methods in the participant plugin.

## Core Classes

### `simple_workflow_manager`

**Namespace**: `local_participant`  
**File**: `classes/simple_workflow_manager.php`  
**Purpose**: Manages workflow transitions and status handling for participant requests

#### Constants

```php
// Status ID Constants
public const STATUS_INITIAL        = 56;  // request_submitted
public const STATUS_LEADER1_REVIEW = 57;  // leader1_review
public const STATUS_LEADER2_REVIEW = 58;  // leader2_review
public const STATUS_LEADER3_REVIEW = 59;  // leader3_review
public const STATUS_BOSS_REVIEW    = 60;  // boss_review
public const STATUS_APPROVED       = 61;  // approved
public const STATUS_REJECTED       = 62;  // rejected
```

#### Public Methods

##### `can_user_approve($userid, $current_status)`
**Purpose**: Check if a user can approve a request from the current status

**Parameters**:
- `$userid` (int): User ID to check permissions for
- `$current_status` (int): Current status ID of the request

**Returns**: `bool` - True if user can approve, false otherwise

**Example**:
```php
$workflow_manager = new \local_participant\simple_workflow_manager();
if ($workflow_manager->can_user_approve($USER->id, $request->status)) {
    // Show approve button
}
```

##### `can_user_reject($userid, $current_status)`
**Purpose**: Check if a user can reject a request from the current status

**Parameters**:
- `$userid` (int): User ID to check permissions for
- `$current_status` (int): Current status ID of the request

**Returns**: `bool` - True if user can reject, false otherwise

##### `get_next_approval_status($current_status)`
**Purpose**: Get the next status in the approval workflow

**Parameters**:
- `$current_status` (int): Current status ID

**Returns**: `int|null` - Next status ID or null if no next status

##### `approve_request($request_id, $user_id)`
**Purpose**: Process approval of a request (advance to next status)

**Parameters**:
- `$request_id` (int): ID of the request to approve
- `user_id` (int): ID of the user performing the approval

**Returns**: `bool` - True on success, false on failure

##### `reject_request($request_id, $user_id)`
**Purpose**: Process rejection of a request (move to rejected status or back to Leader 1 Review)

**Parameters**:
- `$request_id` (int): ID of the request to reject
- `$user_id` (int): ID of the user performing the rejection

**Returns**: `bool` - True on success, false on failure

**Rejection Behavior**:
- **Leader 1 Rejection**: Goes directly to Rejected (62) status
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review (57) for re-evaluation
- **Capability Required**: Each level requires its respective workflow step capability

#### Private Methods

##### `get_workflow_map()`
**Purpose**: Get the complete workflow transition map

**Returns**: `array` - Associative array mapping current status to next status and required capability

##### `get_rejection_map()`
**Purpose**: Get the complete rejection transition map

**Returns**: `array` - Associative array mapping current status to rejection target and required capability

**Rejection Map**:
```php
public static function get_rejection_map(): array {
    return [
        self::STATUS_LEADER1_REVIEW => [
            'next'       => self::STATUS_REJECTED,
            'capability' => 'local/status:participants_workflow_step1',
        ],
        self::STATUS_LEADER2_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step2',
        ],
        self::STATUS_LEADER3_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step3',
        ],
        self::STATUS_BOSS_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step4',
        ],
    ];
}
```

## Form Classes

### `request_form`

**Namespace**: `local_participant\form`  
**File**: `classes/form/request_form.php`  
**Extends**: `moodleform`  
**Purpose**: Form for creating new participant requests

#### Public Methods

##### `definition()`
**Purpose**: Define form elements and structure

**Returns**: `void`

**Form Elements**:
- Course selection
- Participant type (internal/external)
- Organization details
- Contract information
- Days/hours specification

##### `validation($data, $files)`
**Purpose**: Custom form validation

**Parameters**:
- `$data` (array): Form data
- `$files` (array): Uploaded files

**Returns**: `array` - Array of validation errors

### `requests_filter_form`

**Namespace**: `local_participant\form`  
**File**: `classes/form/requests_filter_form.php`  
**Extends**: `moodleform`  
**Purpose**: Form for filtering requests in the main view

#### Public Methods

##### `definition()`
**Purpose**: Define filter form elements

**Returns**: `void`

**Filter Options**:
- Status filtering
- Date range filtering
- User filtering
- Course filtering

## Database API Usage

### Standard Moodle Database Operations

The plugin uses Moodle's standard database API (`$DB` global object) for all database operations.

#### Common Database Patterns

##### Inserting Records
```php
$record = new stdClass();
$record->field1 = $value1;
$record->field2 = $value2;
$record->timecreated = time();

$id = $DB->insert_record('table_name', $record);
```

##### Updating Records
```php
$record = new stdClass();
$record->id = $id;
$record->field1 = $new_value;
$record->timemodified = time();

$DB->update_record('table_name', $record);
```

##### Retrieving Records
```php
// Get single record
$record = $DB->get_record('table_name', ['id' => $id]);

// Get multiple records with conditions
$records = $DB->get_records('table_name', ['status' => $status]);

// Get records with SQL
$sql = "SELECT * FROM {table_name} WHERE field = ?";
$records = $DB->get_records_sql($sql, [$value]);
```

## JavaScript API

### Main JavaScript File

**File**: `js/main.js`  
**Purpose**: Client-side functionality for the plugin

#### Recent Updates (September 3, 2025)
- ✅ **AJAX URLs Fixed**: Updated from relative to absolute URLs to work correctly from any plugin context
- ✅ **Dropdown Functionality**: Employee and lecturer dropdowns now working correctly with data population
- ✅ **Cross-Plugin Integration**: Successfully integrated with requestservices plugin for seamless functionality

#### Functions

##### `init()`
**Purpose**: Initialize plugin JavaScript functionality

**Usage**:
```javascript
// Called automatically when page loads
// Sets up event handlers and UI interactions
```

#### External Dependencies

The plugin integrates with external JavaScript files from the theme:

##### CSV Export (`/theme/stream/js/export_csv.js`)
**Purpose**: Provides CSV export functionality for request data

##### Custom Dialog (`/theme/stream/js/custom_dialog.js`)
**Purpose**: Enhanced dialog boxes for user interactions

## Template API

### Mustache Templates

#### `view_requests.mustache`

**File**: `templates/view_requests.mustache`  
**Purpose**: Template for rendering the requests list view

##### Context Variables

```php
$templatecontext = [
    'requests' => [
        [
            'id' => 123,
            'course' => 'Course Name',
            'participant' => 'Participant Name',
            'status' => 'Status Name',
            'created' => 'Creation Date',
            // ... other fields
        ]
    ],
    'pagination' => [...],
    'filters' => [...],
    'can_approve' => true/false,
    'can_reject' => true/false
];
```

##### Usage Example

```php
// In PHP controller
$output = $PAGE->get_renderer('local_participant');
echo $output->render_from_template('local_participant/view_requests', $templatecontext);
```

## Language String API

### String Retrieval

#### `get_string($identifier, $component, $a = null)`

**Usage Examples**:
```php
// Simple string
$title = get_string('pluginname', 'local_participant');

// String with parameter
$message = get_string('requestadded', 'local_participant', $requestname);

// String with multiple parameters
$a = new stdClass();
$a->user = $username;
$a->date = $date;
$message = get_string('request_created_by', 'local_participant', $a);
```

### Available String Keys

#### Common Interface Strings
- `pluginname` - Plugin name
- `addrequest` - "Add Request"
- `viewrequests` - "View Requests"
- `filter` - "Filter"

#### Form Field Labels
- `course` - "Course"
- `participant` - "Participant"
- `organization` - "Organization"
- `contractduration` - "Contract Duration"
- `contractcost` - "Contract Cost"

#### Status Messages
- `requestadded` - "New request has been added"
- `invalidnumber` - "Please enter a valid number"

## Capability API

### Capability Definitions

#### Plugin-Specific Capabilities

```php
// Defined in db/access.php
'local/participant:view' => [
    'captype' => 'read',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => [
        'manager' => CAP_ALLOW,
        'editingteacher' => CAP_ALLOW,
        'teacher' => CAP_ALLOW
    ]
]

'local/participant:addrequest' => [
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
    'archetypes' => [
        'manager' => CAP_ALLOW,
        'editingteacher' => CAP_ALLOW,
        'teacher' => CAP_ALLOW
    ]
]
```

#### Capability Checking

```php
// Check capability
$context = context_system::instance();
require_capability('local/participant:view', $context);

// Check capability conditionally
if (has_capability('local/participant:addrequest', $context)) {
    // Show add request button
}
```

## Error Handling

### Exception Classes

The plugin uses standard Moodle exception classes:

#### `moodle_exception`
```php
throw new moodle_exception('error_key', 'local_participant', $redirect_url, $debug_info);
```

#### `required_capability_exception`
```php
// Automatically thrown by require_capability()
require_capability('local/participant:view', $context);
```

### Error Response Patterns

#### Form Validation Errors
```php
public function validation($data, $files) {
    $errors = parent::validation($data, $files);
    
    if (empty($data['required_field'])) {
        $errors['required_field'] = get_string('required', 'core');
    }
    
    return $errors;
}
```

#### Database Operation Errors
```php
try {
    $DB->insert_record('table_name', $record);
} catch (dml_exception $e) {
    \core\notification::error(get_string('database_error', 'local_participant'));
    redirect($redirect_url);
}
```

## Integration Points

### Moodle Core Integration

#### Page Setup
```php
$PAGE->set_url('/local/participant/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_participant'));
$PAGE->set_heading(get_string('pluginname', 'local_participant'));
```

#### Navigation Integration
```php
// Add tabs
$tabs = [];
$tabs[] = new tabobject('addrequest', $url, get_string('addrequest', 'local_participant'));
$tabs[] = new tabobject('viewrequests', $url, get_string('viewrequests', 'local_participant'));

echo $OUTPUT->tabtree($tabs, $current_tab);
```

### External Plugin Dependencies

#### Status Plugin Integration
```php
// The plugin depends on local/status for workflow capabilities
// Workflow capabilities are defined in the status plugin:
// - local/status:participants_workflow_step1
// - local/status:participants_workflow_step2
// - local/status:participants_workflow_step3
// - local/status:participants_workflow_step4
``` 