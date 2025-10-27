# API Documentation

This document provides comprehensive documentation for all classes, methods, and API endpoints in the Annual Plans plugin.

## PHP Classes

### 1. AnnualPlansController

**Location**: `classes/AnnualPlansController.php`
**Purpose**: Main controller class that handles request routing and business logic

#### Properties
- `$upload_form`: Upload form instance
- `$filter_form`: Filter form instance  
- `$annual_plan_delete_form`: Plan deletion form instance
- `$CourseManager`: Course manager instance
- `$add_course_form`: Course addition form instance
- `$context`: System context
- `$PAGE`: Moodle page object
- `$SESSION`: Session object
- `$OUTPUT`: Output renderer
- `$DB`: Database manager
- `$USER`: Current user object

#### Methods

##### `__construct()`
**Purpose**: Initialize controller with global objects and form instances
**Parameters**: None
**Returns**: void

##### `handle_request()`
**Purpose**: Main request dispatcher and handler
**Parameters**: None
**Returns**: void
**Description**: Processes forms, handles deletions, and coordinates response

##### `process_forms()`
**Purpose**: Process form submissions and handle form-specific logic
**Parameters**: None
**Returns**: void
**Description**: Handles upload forms, filter forms, and course management forms

##### `delete_course_row()`
**Purpose**: Handle course deletion from annual plan
**Parameters**: None
**Returns**: void
**Description**: Soft delete courses with proper validation and audit trail

---

### 2. CourseManager

**Location**: `classes/CourseManager.php`
**Purpose**: Manages course-specific business logic and data operations

#### Key Responsibilities
- Course CRUD operations
- Course validation
- Beneficiary management
- Status tracking
- Database abstraction for course operations

#### Methods (Inferred from usage)

##### Course Management
- Course creation and editing
- Course status updates
- Course approval/rejection
- Course deletion (soft delete)

##### Data Validation
- Course data validation
- Required field checking
- Business rule enforcement

##### Integration
- Moodle course integration
- User permission validation
- Category and level integration

---

### 3. control_form (extends moodleform)

**Location**: `classes/control_form.php`
**Purpose**: Form definitions and validation for the plugin

#### Form Classes

##### `upload_form`
**Purpose**: File upload form for Excel/CSV imports
**Fields**:
- File upload field with validation
- Encoding options
- Delimiter configuration

##### `filter_form`
**Purpose**: Data filtering and search form
**Fields**:
- Date range filters
- Status filters
- Category filters
- Plan selection
- Search text input

##### `add_course_form`
**Purpose**: Course creation and editing form
**Fields**:
- Course identification
- Course metadata
- Scheduling information
- Beneficiary data
- Location details

##### `annual_plan_delete_form`
**Purpose**: Annual plan deletion confirmation
**Fields**:
- Plan selection
- Deletion confirmation
- Deletion notes

#### Validation Methods
- File type validation
- Date range validation
- Required field validation
- Business rule validation

---

### 4. table (extends flexible_table)

**Location**: `classes/table.php`
**Purpose**: Data table presentation and management

#### Features
- Sortable columns
- Pagination support
- Filtering capabilities
- Export functionality
- Custom column formatting

#### Methods

##### Table Configuration
- Column definition
- Sorting configuration
- Pagination setup
- CSS class assignment

##### Data Processing
- Data formatting
- Status display
- Action button generation
- Link creation

##### Export Features
- CSV export
- Excel export
- Filtered exports
- Custom formatting

## AJAX Endpoints

### Employee Management

#### `/ajax/get_employees.php`
**Method**: GET/POST
**Purpose**: Retrieve employee data
**Parameters**:
- `role_id` (optional): Filter by role
- `department_id` (optional): Filter by department
**Returns**: JSON array of employee objects
**Example Response**:
```json
[
  {
    "id": 1,
    "name": "Employee Name",
    "role": "Role Name",
    "department": "Department Name"
  }
]
```

### Beneficiary Management

#### `/ajax/get_beneficiaries.php`
**Method**: GET/POST
**Purpose**: Retrieve beneficiary data for courses
**Parameters**:
- `course_id`: Course identifier
**Returns**: JSON array of beneficiary objects (when available, includes `roleid` per record)
**Example Response**:
```json
[
  {
    "id": 1,
    "type": "internal",
    "count": 25,
    "target_group": "Management"
  }
]
```

#### `/ajax/save_beneficiaries.php`
**Method**: POST
**Purpose**: Save beneficiary data and audit fields
**Parameters**:
- `courseid` (string): Course identifier
- `coursedate` (int): Course date (timestamp)
- `annualplanid` (int): Plan id
- `beneficiaries` (JSON): Map of `pf_number` → `fullname`
- `roles` (JSON, optional): Map of `pf_number` → `roleid`
**Returns**: JSON success/error response
**Example Request**:
```json
{
  "course_id": 123,
  "beneficiaries": [
    {
      "type": "internal",
      "count": 25,
      "target_group": "Management"
    }
  ]
}
```

### Role Management

#### `/ajax/get_roles.php`
**Method**: GET
**Purpose**: Retrieve available roles
**Parameters**: None
**Returns**: JSON array of role objects
**Example Response**:
```json
[
  {
    "id": 1,
    "name": "Instructor",
    "code": "INST",
    "description": "Course Instructor"
  }
]
```

Implementation details:
- Populates `timecreated`, `timemodified`, `created_by`, `modified_by` on insert using the current user context.
- Validates role IDs when provided.

### Code Management

#### `/ajax/get_code.php`
**Method**: GET/POST
**Purpose**: Retrieve course code by ID
**Parameters**:
- `id`: Code identifier
**Returns**: String code value
**Example Response**: `"CAT001"`

#### `/ajax/get_code_description.php`
**Method**: GET/POST
**Purpose**: Retrieve code description
**Parameters**:
- `id`: Code identifier
**Returns**: JSON object with code details
**Example Response**:
```json
{
  "id": 1,
  "code": "CAT001",
  "description": "Management Category",
  "type": "category"
}
```

#### `/ajax/get_type_id.php`
**Method**: GET/POST
**Purpose**: Get type ID from code record
**Parameters**:
- `code_id`: Code identifier
**Returns**: Integer type ID
**Example Response**: `5`

### Annual Plan Management

#### `/ajax/get_annual_plan_year.php`
**Method**: GET/POST
**Purpose**: Retrieve annual plan year data
**Parameters**:
- `plan_id` (optional): Specific plan ID
**Returns**: JSON array of plan year objects
**Example Response**:
```json
[
  {
    "id": 1,
    "year": 2025,
    "title": "Annual Plan 2025"
  }
]
```

## Database API Patterns

### Standard CRUD Operations

#### Create Operations
```php
$record = new stdClass();
$record->field1 = $value1;
$record->field2 = $value2;
$id = $DB->insert_record('table_name', $record);
```

#### Read Operations
```php
// Single record
$record = $DB->get_record('table_name', ['id' => $id]);

// Multiple records
$records = $DB->get_records('table_name', $conditions, $sort);

// Specific fields
$records = $DB->get_records_select('table_name', $where, $params, $sort, $fields);
```

#### Update Operations
```php
$record = $DB->get_record('table_name', ['id' => $id]);
$record->field1 = $new_value;
$DB->update_record('table_name', $record);
```

#### Delete Operations
```php
// Soft delete (preferred)
$DB->set_field('table_name', 'disabled', 1, ['id' => $id]);

// Hard delete (use with caution)
$DB->delete_records('table_name', ['id' => $id]);
```

## JavaScript API

### Main JavaScript Functions

**Location**: `js/main.js`

#### Form Handling
```javascript
// Form submission
function submitForm(formData, successCallback, errorCallback)

// Form validation
function validateForm(formId)

// Dynamic form updates
function updateFormFields(dependencies)
```

#### AJAX Operations
```javascript
// Generic AJAX call
function makeAjaxRequest(url, data, method, successCallback, errorCallback)

// Specific data retrieval
function getEmployees(filters, callback)
function getBeneficiaries(courseId, callback)
function getCodes(type, callback)
```

#### UI Updates
```javascript
// Table updates
function refreshTable(tableId, filters)

// Progress indicators
function showProgress(message)
function hideProgress()

// Notifications
function showNotification(message, type)
```

## Error Handling

### Standard Error Responses

#### AJAX Error Format
```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": "Detailed error information"
}
```

#### PHP Exception Handling
```php
try {
    // Operation
} catch (Exception $e) {
    // Log error
    debugging('Error message: ' . $e->getMessage(), DEBUG_DEVELOPER);
    
    // User feedback
    \core\notification::error('User-friendly error message');
}
```

## Security Considerations

### Capability Checks
```php
// System level capability
require_capability('local/annualplans:manage', context_system::instance());

// User-specific checks
if (has_capability('local/annualplans:view', $context)) {
    // Allow operation
}
```

### Input Validation
```php
// Required parameters
$id = required_param('id', PARAM_INT);
$text = required_param('text', PARAM_TEXT);

// Optional parameters
$optional = optional_param('optional', 'default', PARAM_ALPHA);

// Clean parameters
$clean_text = clean_param($input, PARAM_TEXT);
```

### Output Escaping
```php
// HTML output
echo s($user_input);

// URL parameters
echo html_writer::link(new moodle_url('/path', ['param' => $value]), $text);

// JSON output
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
```

## Integration Points

### Moodle Core Integration
- User management system
- Course categories
- Capability system
- Navigation framework
- Theme system

### Database Integration
- Moodle database API
- Transaction support
- Foreign key relationships
- Index optimization

### File System Integration
- File upload handling
- Temporary file management
- Export file generation
- Security validation

This API documentation provides a comprehensive reference for developers working with the Annual Plans plugin, covering all major components and integration points. 