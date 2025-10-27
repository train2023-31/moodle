# API Reference

## Core Functions

### Navigation Functions

#### `local_requestservices_extend_navigation_course($navigation, $course, $context)`
**Location**: `lib.php`  
**Purpose**: Extends course navigation to include the Request Services tab

**Parameters**:
- `$navigation` (navigation_node): Moodle navigation node
- `$course` (stdClass): Course object
- `$context` (context): Course context

**Behavior**:
- Checks user capability (`local/requestservices:view`)
- Adds navigation node to course menu
- Sets URL to plugin's main page

**Example Usage**:
```php
// This function is automatically called by Moodle's navigation system
// No manual invocation required
```

---

## Capabilities

### `local/requestservices:view`
**Context Level**: `CONTEXT_COURSE`  
**Type**: `read`  
**Default Permissions**:
- `editingteacher`: Allow
- `teacher`: Allow

**Purpose**: Controls access to the Request Services plugin within courses

**Usage in Code**:
```php
require_capability('local/requestservices:view', $context);

// Or for conditional checks
if (has_capability('local/requestservices:view', $context)) {
    // Display content
}
```

---

## Template Renderer Classes

### Namespace: `local_requestservices\output`

All renderer classes implement `\renderable` and `\templatable` interfaces.

#### `computerservices_requests`
**Location**: `/classes/output/computerservices_requests.php`  
**Template**: `computerservices_requests.mustache`

**Methods**:
- `export_for_template(\renderer_base $output)`: Exports data for template rendering

**Template Data Structure**:
```php
[
    'requests' => [
        [
            'course' => 'Course Name',
            'requestdate' => 'YYYY-MM-DD',
            'status' => 'Status Text',
            'numdevices' => 'Number',
            'devices' => 'Device List'
        ]
    ]
]
```

#### `financialservicesview`
**Location**: `/classes/output/financialservicesview.php`  
**Template**: `financialservicesview.mustache`

**Template Data Structure**:
```php
[
    'requests' => [
        [
            'course' => 'Course Name',
            'requestdate' => 'YYYY-MM-DD',
            'status' => 'Status Text',
            'fundingtype' => 'Funding Type',
            'pricerequested' => 'Amount'
        ]
    ]
]
```

#### `participantview`
**Location**: `/classes/output/participantview.php`  
**Template**: `participantview.mustache`

**Template Data Structure**:
```php
[
    'requests' => [
        [
            'course' => 'Course Name',
            'requestdate' => 'YYYY-MM-DD',
            'status' => 'Status Text'
        ]
    ]
]
```

#### `registeringroomview`
**Location**: `/classes/output/registeringroomview.php`  
**Template**: `registeringroomview.mustache`

**Template Data Structure**:
```php
[
    'complex_form_data' => [
        // Complex room registration form data
        // Detailed structure varies based on implementation
    ]
]
```

#### `residencebookingview`
**Location**: `/classes/output/residencebookingview.php`  
**Template**: `residencebookingview.mustache`

**Template Data Structure**:
```php
[
    'complex_booking_data' => [
        // Complex residence booking form data
        // Detailed structure varies based on implementation
    ]
]
```

---

## Tab System API

### Main Tab Controller

#### URL Structure
```
/local/requestservices/index.php?id={courseid}&tab={tabname}
```

**Parameters**:
- `id` (PARAM_INT): Course ID (required)
- `tab` (PARAM_ALPHA): Tab name (optional, defaults to 'computerservicesTab')

#### Available Tab Names
- `allrequests`: Overview of all requests
- `computerservicesTab`: Computer services requests
- `financialservices`: Financial services requests
- `registeringroom`: Room registration requests
- `requestparticipant`: Participant requests
- `residencebooking`: Residence booking requests

### Subtab System

#### URL Structure (for allrequests tab)
```
/local/requestservices/index.php?id={courseid}&tab=allrequests&subtab={subtabname}
```

**Parameters**:
- `subtab` (PARAM_ALPHA): Subtab name (optional, defaults to 'computerservicesview')

#### Available Subtab Names
- `computerservicesview`: Computer services view
- `financialservicesview`: Financial services view
- `registeringroomview`: Room registration view
- `participantview`: Participant view
- `residencebookingview`: Residence booking view

---

## Language String API

### Plugin Identification Strings
```php
$string['pluginname'] = 'Request Services Tab';
$string['requestservices'] = 'Request Services';
$string['requestservices:view'] = 'View Request Services tab in course';
```

### Tab Navigation Strings
```php
$string['allrequests'] = 'All Request ';
$string['computerservicesTab'] = ' computer services ';
$string['financialservices'] = ' financial services  ';
$string['registeringroom'] = '  room registration ';
$string['requestparticipant'] = '  request lecturer/role player ';
$string['residencebooking'] = '  request residence booking ';
```

### Global Display Strings
```php
$string['course'] = 'Course';
$string['requestdate'] = 'Request date';
$string['status'] = 'Status';
```

### Service-Specific Strings
```php
// Computer Services
$string['numdevices'] = 'Number of devices';
$string['devices'] = 'Requested devices';

// Financial Services
$string['fundingtype'] = 'Funding type';
$string['pricerequested'] = 'Requested amount';
```

### Error Messages
```php
$string['invalidtab'] = 'working on page   ';
```

---

## Template System API

### Template Usage Pattern
```php
// In tab files
$renderer = $PAGE->get_renderer('local_requestservices');
$renderable = new \local_requestservices\output\templatename();
echo $renderer->render($renderable);
```

### Template Data Access
All templates receive data through the `export_for_template()` method of their corresponding renderer class.

**Common Template Variables**:
- `{{course}}`: Course name
- `{{requestdate}}`: Request submission date
- `{{status}}`: Current request status

**Template Helpers**:
Templates can use standard Mustache syntax including:
- `{{#variable}}...{{/variable}}`: Conditional sections
- `{{^variable}}...{{/variable}}`: Inverted sections  
- `{{&variable}}`: Unescaped output (use carefully)

---

## External Dependencies

### Required External Plugins

#### `local_computerservice`
**Files Used**:
- `/classes/form/request_form.php`: Computer service request form
- `/classes/simple_workflow_manager.php`: Workflow management

**Usage**:
```php
require_once($CFG->dirroot . '/local/computerservice/classes/form/request_form.php');
require_once($CFG->dirroot . '/local/computerservice/classes/simple_workflow_manager.php');

$mform = new \local_computerservice\form\request_form($actionurl, array('id' => $courseid));
```

### Moodle Core Dependencies

#### Navigation API
```php
$navigation->add($text, $url, $type, $shortname, $key);
```

#### Capability System
```php
require_capability($capability, $context);
has_capability($capability, $context);
```

#### Template System
```php
$renderer = $PAGE->get_renderer('pluginname');
echo $renderer->render($renderable);
```

#### Tab System
```php
$tabs[] = new tabobject($id, $url, $text);
print_tabs([$tabs], $selected);
```

---

## Error Handling

### Common Error Scenarios

#### Invalid Tab Access
```php
if (file_exists($tabfile)) {
    include($tabfile);
} else {
    echo $OUTPUT->notification(get_string('invalidtab', 'local_requestservices'), 'error');
}
```

#### Permission Denied
```php
try {
    require_capability('local/requestservices:view', $context);
} catch (required_capability_exception $e) {
    // Handled by Moodle's exception system
}
```

#### Missing Course Context
```php
$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid); // Throws exception if invalid
```

---

## Performance Considerations

### Lazy Loading
- Tab content is only loaded when accessed
- Templates are rendered on-demand
- Database queries are minimized

### Caching
- Language strings are cached by Moodle
- Template compilation is cached
- Navigation nodes are cached per session

### Optimization Tips
- Use `optional_param()` for optional parameters
- Minimize database queries in renderer classes
- Use Moodle's built-in caching when possible 

## Recent Updates

### September 3, 2025
- ✅ **Bug #2 RESOLVED**: Fixed requestparticipant dropdown data loading issue
- ✅ AJAX endpoints now working correctly with absolute URLs
- ✅ Employee and lecturer data loading successfully
- ✅ Conditional field behavior fully functional
- ✅ All debugging code removed for production use

## Current Status

**Bug Fixes**: 2 of 3 resolved (67%)  
**Tab Modernization**: 2 of 6 completed (33%)  
**Template Internationalization**: 1 of 6 completed (17%)

### Next Development Target
- **Residence Booking Tab**: Modernization and security improvements 