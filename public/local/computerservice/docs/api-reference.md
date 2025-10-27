# API Reference Documentation

This document provides a comprehensive reference for all classes, methods, and functions in the Computer Service plugin.

## 📚 Class Reference

### Core Workflow Class

#### `simple_workflow_manager`
**Location**: `/classes/simple_workflow_manager.php`  
**Purpose**: Main workflow engine for processing device requests through approval stages.

##### Constants
```php
const STATUS_INITIAL        = 15;  // Initial request submission
const STATUS_LEADER1_REVIEW = 16;  // First leader review
const STATUS_LEADER2_REVIEW = 17;  // Second leader review  
const STATUS_LEADER3_REVIEW = 18;  // Third leader review
const STATUS_BOSS_REVIEW    = 19;  // Final boss review
const STATUS_APPROVED       = 20;  // Request approved
const STATUS_REJECTED       = 21;  // Legacy rejection status (rejections now go to initial)
```

##### Static Methods

###### `get_initial_status_id()`
```php
public static function get_initial_status_id()
```
- **Returns**: `int` - The initial status ID for new requests
- **Usage**: Called when creating new device requests
- **Example**:
  ```php
  $status = simple_workflow_manager::get_initial_status_id(); // Returns 15
  ```

###### `approve_request($requestid, $userid, $sesskey, $approval_note = '')`
```php
public static function approve_request($requestid, $userid, $sesskey, $approval_note = '')
```
- **Parameters**:
  - `$requestid` (int): ID of the request to approve
  - `$userid` (int): ID of the user performing the approval
  - `$sesskey` (string): Session key for CSRF protection
  - `$approval_note` (string, optional): Optional note for approval
- **Returns**: `array` - Result array with success status and data
- **Security**: Validates session key and user capabilities
- **Example**:
  ```php
  $result = simple_workflow_manager::approve_request(123, $USER->id, sesskey(), 'Approved for project XYZ');
  if ($result['success']) {
      echo "Request approved, new status: " . $result['new_status'];
  }
  ```

###### `reject_request($requestid, $userid, $sesskey, $rejection_note)`
```php
public static function reject_request($requestid, $userid, $sesskey, $rejection_note)
```
- **Parameters**:
  - `$requestid` (int): ID of the request to reject
  - `$userid` (int): ID of the user performing the rejection
  - `$sesskey` (string): Session key for CSRF protection
  - `$rejection_note` (string): Mandatory rejection reason
- **Returns**: `array` - Result array with success status and data
- **Validation**: Rejection note is required and cannot be empty
- **Behavior**: Rejection moves request back to initial status (15) for resubmission
- **Example**:
  ```php
  $result = simple_workflow_manager::reject_request(123, $USER->id, sesskey(), 'Insufficient budget');
  if ($result['success']) {
      echo "Request rejected and returned to initial status";
  } else {
      echo "Error: " . $result['error'];
  }
  ```

###### `can_user_manage_status($userid, $status_id)`
```php
public static function can_user_manage_status($userid, $status_id)
```
- **Parameters**:
  - `$userid` (int): User ID to check permissions for
  - `$status_id` (int): Current status of the request
- **Returns**: `bool` - True if user can manage this status
- **Logic**: Checks global `managerequests` capability first, then workflow-specific capabilities
- **Example**:
  ```php
  if (simple_workflow_manager::can_user_manage_status($USER->id, 15)) {
      echo "User can approve initial requests";
  }
  ```

###### `get_next_status($current_status)`
```php
public static function get_next_status($current_status)
```
- **Parameters**: `$current_status` (int): Current workflow status
- **Returns**: `int` - Next status in the workflow
- **Throws**: `moodle_exception` for invalid status
- **Example**:
  ```php
  $next = simple_workflow_manager::get_next_status(15); // Returns 16
  ```

##### Private Methods

###### `get_capability_for_status($status_id)`
```php
private static function get_capability_for_status($status_id)
```
- **Parameters**: `$status_id` (int): Workflow status
- **Returns**: `string|null` - Capability name for the status
- **Usage**: Internal method for capability mapping

## 📝 Form Classes

### `request_form`
**Location**: `/classes/form/request_form.php`  
**Parent**: `moodleform`  
**Purpose**: Form for submitting device requests

#### Methods

##### `definition()`
```php
public function definition()
```
- **Purpose**: Defines form structure and validation rules
- **Form Elements**:
  - Course selection (filtered by user enrollment)
  - Device type selection (active devices only)
  - Number of devices
  - Required date
  - Comments (optional)

##### `validation($data, $files)`
```php
public function validation($data, $files)
```
- **Parameters**:
  - `$data` (array): Form data
  - `$files` (array): Uploaded files (not used)
- **Returns**: `array` - Validation errors
- **Validation Rules**:
  - Required date must be in the future
  - Number of devices must be positive
  - Course must be one user is enrolled in

### `filter_form`
**Location**: `/classes/form/filter_form.php`  
**Parent**: `moodleform`  
**Purpose**: Filtering form for request management

#### Methods

##### `definition()`
```php
public function definition()
```
- **Form Elements**:
  - Course filter (enrolled courses only)
  - User filter (searchable)
  - Status filter (all workflow statuses)
  - Urgency filter (All requests, Urgent, Not Urgent)

### `device_form`
**Location**: `/classes/form/device_form.php`  
**Parent**: `moodleform`  
**Purpose**: Form for adding new device types

#### Methods

##### `definition()`
```php
public function definition()
```
- **Form Elements**:
  - English device name
  - Arabic device name
  - Initial status (active/inactive)

## 🎨 Output Classes

### `manage_requests`
**Location**: `/classes/output/manage_requests.php`  
**Implements**: `renderable`, `templatable`  
**Purpose**: Prepares data for request management template

#### Constructor
```php
public function __construct($requests_data, $filter_params = [])
```
- **Parameters**:
  - `$requests_data` (array): Raw request data from database
  - `$filter_params` (array): Applied filter parameters

#### Methods

##### `export_for_template(renderer_base $output)`
```php
public function export_for_template(renderer_base $output)
```
- **Parameters**: `$output` (renderer_base): Moodle output renderer
- **Returns**: `stdClass` - Data formatted for Mustache template
- **Processing**:
  - Formats user names
  - Resolves course names
  - Applies language-specific device names
  - Adds status classes and colors
  - Processes urgent flags

##### Private Helper Methods

###### `format_user_name($user)`
```php
private function format_user_name($user)
```
- **Parameters**: `$user` (stdClass): User object with firstname/lastname
- **Returns**: `string` - Formatted full name

###### `get_status_class($status_id)`
```php
private function get_status_class($status_id)
```
- **Parameters**: `$status_id` (int): Workflow status ID
- **Returns**: `string` - CSS class for status badge

###### `get_device_name($device, $lang)`
```php
private function get_device_name($device, $lang)
```
- **Parameters**:
  - `$device` (stdClass): Device object with both language variants
  - `$lang` (string): Current language code
- **Returns**: `string` - Device name in appropriate language

## 🌐 Global Functions

### Navigation Extension
**Location**: `/lib.php`

#### `local_computerservice_extend_settings_navigation($settingsnav, $context)`
```php
function local_computerservice_extend_settings_navigation($settingsnav, $context)
```
- **Parameters**:
  - `$settingsnav` (settings_navigation): Moodle settings navigation object
  - `$context` (context): Current context
- **Purpose**: Adds plugin links to Moodle navigation for users with management capabilities
- **Capability Check**: `local/computerservice:managerequests`

## 📊 Database Operations

### Direct Database Access Examples

#### Fetching Requests with Related Data
```php
$sql = "SELECT r.*, u.firstname, u.lastname, c.fullname as coursename,
               d.devicename_en, d.devicename_ar
        FROM {local_computerservice_requests} r
        JOIN {user} u ON r.userid = u.id
        JOIN {course} c ON r.courseid = c.id
        JOIN {local_computerservice_devices} d ON r.deviceid = d.id
        WHERE r.status_id = ?
        ORDER BY r.timecreated DESC";

$requests = $DB->get_records_sql($sql, [15]); // Get initial requests
```

#### Inserting New Request
```php
$record = (object)[
    'userid'            => $USER->id,
    'courseid'          => $courseid,
    'deviceid'          => $deviceid,
    'numdevices'        => $numdevices,
    'comments'          => $comments,
    'status_id'         => simple_workflow_manager::get_initial_status_id(),
    'timecreated'       => time(),
    'timemodified'      => time(),
    'request_needed_by' => $needed_timestamp,
    'is_urgent'      => ($needed_timestamp - time() < DAYSECS) ? 1 : 0,
];

$requestid = $DB->insert_record('local_computerservice_requests', $record);
```

#### Updating Request Status
```php
$update = (object)[
    'id'           => $requestid,
    'status_id'    => $new_status,
    'timemodified' => time(),
];

// Add notes if provided
if ($rejection_note) {
    $update->rejection_note = $rejection_note;
}
if ($approval_note) {
    $update->approval_note = $approval_note;
}

$DB->update_record('local_computerservice_requests', $update);
```

## 🎯 AJAX Endpoints

### `update_request_status.php`
**Location**: `/actions/update_request_status.php`  
**Method**: POST  
**Content-Type**: application/json

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `requestid` | int | Yes | ID of request to update |
| `action` | string | Yes | 'approve' or 'reject' |
| `sesskey` | string | Yes | Session key for CSRF protection |
| `approval_note` | string | No | Note for approval (optional) |
| `rejection_note` | string | Yes (for reject) | Mandatory rejection reason |

#### Response Format
```json
{
    "success": true,
    "new_status": 16,
    "message": "Request approved successfully"
}
```

Or on error:
```json
{
    "success": false,
    "error": "Insufficient permissions"
}
```

#### Example Usage
```javascript
$.ajax({
    url: M.cfg.wwwroot + '/local/computerservice/actions/update_request_status.php',
    type: 'POST',
    data: {
        requestid: 123,
        action: 'approve',
        sesskey: M.cfg.sesskey,
        approval_note: 'Approved for educational purposes'
    },
    dataType: 'json',
    success: function(response) {
        if (response.success) {
            updateUIStatus(123, response.new_status);
        } else {
            showError(response.error);
        }
    }
});
```

## 🛡️ Security Functions

### Session Validation
```php
// Built-in Moodle function used throughout the plugin
if (!confirm_sesskey($sesskey)) {
    throw new moodle_exception('invalidsesskey');
}
```

### Capability Checking
```php
// Context-based capability check
$context = context_system::instance();
require_capability('local/computerservice:managerequests', $context);

// User-specific capability check
if (has_capability('local/computerservice:managerequests', $context, $userid)) {
    // User has permission
}
```

### Parameter Validation
```php
// Required parameters
$requestid = required_param('requestid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

// Optional parameters
$note = optional_param('note', '', PARAM_TEXT);

// Clean parameters
$clean_text = clean_param($user_input, PARAM_TEXT);
```

## 📋 Language String Functions

### Getting Localized Strings
```php
// Basic string retrieval
$string = get_string('stringkey', 'local_computerservice');

// String with parameters
$string = get_string('stringkey', 'local_computerservice', $parameter);

// Check if string exists
if (get_string_manager()->string_exists('stringkey', 'local_computerservice')) {
    $string = get_string('stringkey', 'local_computerservice');
}
```

### Dynamic Language Resolution
```php
// Get device name based on current language
$lang = current_language();
$device_name = ($lang === 'ar') ? $device->devicename_ar : $device->devicename_en;
```

## 🔧 Utility Functions

### Time Formatting
```php
// Convert Unix timestamp to readable format
$readable_date = userdate($timestamp, get_string('strftimedaydatetime'));

// Check if request is urgent (today or tomorrow)
$is_urgent = ($needed_timestamp - time()) < DAYSECS;
```

### URL Generation
```php
// Generate plugin URLs
$url = new moodle_url('/local/computerservice/index.php', ['tab' => 'manage']);

// Add parameters to existing URL
$url->param('filter', 'active');
```

## 📊 Debug and Logging Functions

### Debug Output
```php
// Development debugging
debugging('Debug message', DEBUG_DEVELOPER);

// Variable dumping (development only)
var_dump($variable);

// Error logging
error_log('Error message: ' . print_r($data, true));
```

### Performance Monitoring
```php
// Simple timing
$start_time = microtime(true);
// ... code to measure ...
$execution_time = microtime(true) - $start_time;
debugging("Execution time: {$execution_time} seconds", DEBUG_DEVELOPER);
```

## 🚀 Extension Points

### Adding Custom Workflow Actions
```php
// Extend simple_workflow_manager class
class custom_workflow_manager extends simple_workflow_manager {
    
    public static function custom_action($requestid, $action_data) {
        // Custom workflow logic
        parent::validate_request($requestid);
        // ... custom processing ...
        return ['success' => true, 'data' => $result];
    }
}
```

### Custom Form Elements
```php
// Extend existing forms
class extended_request_form extends \local_computerservice\form\request_form {
    
    public function definition() {
        parent::definition();
        
        $mform = $this->_form;
        $mform->addElement('text', 'custom_field', 'Custom Field');
        $mform->setType('custom_field', PARAM_TEXT);
    }
}
```

### Custom Template Data
```php
// Extend output classes
class extended_manage_requests extends \local_computerservice\output\manage_requests {
    
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        
        // Add custom data
        $data->custom_field = $this->get_custom_data();
        
        return $data;
    }
}
```

This API reference provides comprehensive documentation for developers working with the Computer Service plugin. Each function and method includes usage examples, parameter descriptions, and security considerations. 