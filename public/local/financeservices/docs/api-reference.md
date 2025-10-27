# API Reference

This document provides a comprehensive reference for all classes, methods, and functions in the Finance Services plugin.

## 📚 Class Reference

### 1. Core Workflow Management

#### `local_financeservices\simple_workflow_manager`

**Purpose**: Central workflow management and status transition handling.

**File**: `classes/simple_workflow_manager.php`

##### Static Methods

**`get_initial_status_id()`**
```php
public static function get_initial_status_id(): int
```
- **Returns**: `8` (Initial status ID for new requests)
- **Purpose**: Gets the default status for newly created requests
- **Example**:
```php
$status_id = simple_workflow_manager::get_initial_status_id();
// Returns: 8
```

---

**`get_next_status_id($current_status_id)`**
```php
public static function get_next_status_id(int $current_status_id): ?int
```
- **Parameters**: 
  - `$current_status_id` (int) - Current status ID
- **Returns**: Next status ID in workflow or `null` if no next status
- **Purpose**: Determines the next status in the approval workflow
- **Example**:
```php
$next_status = simple_workflow_manager::get_next_status_id(9);
// Returns: 10 (Leader 1 -> Leader 2)
```

---

**`get_previous_status_id($current_status_id)`**
```php
public static function get_previous_status_id(int $current_status_id): int
```
- **Parameters**: 
  - `$current_status_id` (int) - Current status ID
- **Returns**: Previous status ID for rejection flow
- **Purpose**: Determines where to send request when rejected (always goes back to Leader 1 except for Leader 1 which goes to final rejection)
- **Example**:
```php
$prev_status = simple_workflow_manager::get_previous_status_id(10);
// Returns: 9 (Leader 2 -> Leader 1 on rejection)

$prev_status = simple_workflow_manager::get_previous_status_id(12);
// Returns: 9 (Boss -> Leader 1 on rejection)
```

---

**`can_user_approve($status_id)`**
```php
public static function can_user_approve(int $status_id): bool
```
- **Parameters**: 
  - `$status_id` (int) - Status ID to check approval capability for
- **Returns**: `true` if current user can approve, `false` otherwise
- **Purpose**: Checks if current user has capability to approve at given status
- **Example**:
```php
if (simple_workflow_manager::can_user_approve(9)) {
    // User can approve at Leader 1 level
    echo "Show approve button";
}
```

---

**`approve_request($request_id, $note)`**
```php
public static function approve_request(int $request_id, string $note = ''): bool
```
- **Parameters**: 
  - `$request_id` (int) - ID of request to approve
  - `$note` (string) - Optional approval note
- **Returns**: `true` on success, `false` on failure
- **Purpose**: Approves a request and moves it to next workflow status
- **Throws**: `moodle_exception` on permission or validation errors
- **Example**:
```php
$success = simple_workflow_manager::approve_request(123, 'Approved for training');
if ($success) {
    echo "Request approved successfully";
}
```

---

**`reject_request($request_id, $note)`**
```php
public static function reject_request(int $request_id, string $note): bool
```
- **Parameters**: 
  - `$request_id` (int) - ID of request to reject
  - `$note` (string) - Required rejection reason
- **Returns**: `true` on success, `false` on failure
- **Purpose**: Rejects a request and moves it backward in workflow
- **Throws**: `moodle_exception` if note is empty or user lacks permission
- **Example**:
```php
$success = simple_workflow_manager::reject_request(123, 'Insufficient budget justification');
```

---

**`has_rejection_note($status_id)`**
```php
public static function has_rejection_note(int $status_id): bool
```
- **Parameters**: 
  - `$status_id` (int) - Status ID to check
- **Returns**: `true` if status indicates request was previously rejected
- **Purpose**: Determines if rejection note should be displayed
- **Example**:
```php
if (simple_workflow_manager::has_rejection_note($request->status_id)) {
    echo "Previously rejected: " . $request->rejection_note;
}
```

### 2. Form Classes

#### `local_financeservices\form\add_form`

**Purpose**: Form for submitting new finance requests.

**File**: `classes/form/add_form.php`

**Extends**: `moodleform`

##### Methods

**`definition()`**
```php
protected function definition(): void
```
- **Purpose**: Defines form elements and validation rules
- **Form Elements**:
  - Course selection (autocomplete)
  - Funding type selection
  - Price requested (numeric)
  - Request notes (textarea)
  - Date required (date picker)
  - Clause selection (optional)

**`validation($data, $files)`**
```php
public function validation(array $data, array $files): array
```
- **Parameters**: 
  - `$data` (array) - Form data
  - `$files` (array) - File uploads
- **Returns**: Array of validation errors
- **Purpose**: Validates form input and business rules

---

#### `local_financeservices\form\filter_form`

**Purpose**: Form for filtering request lists.

**File**: `classes/form/filter_form.php`

##### Methods

**`definition()`**
```php
protected function definition(): void
```
- **Purpose**: Defines filter form elements
- **Filter Options**:
  - Course filter
  - Funding type filter
  - Status filter
  - Clause filter

---

#### `local_financeservices\form\funding_type_form`

**Purpose**: Form for managing funding types.

**File**: `classes/form/funding_type_form.php`

##### Methods

**`definition()`**
```php
protected function definition(): void
```
- **Form Elements**:
  - English name
  - Arabic name
  - English description
  - Arabic description
  - Active status

---

#### `local_financeservices\form\clause_form`

**Purpose**: Form for managing clauses/terms.

**File**: `classes/form/clause_form.php`

##### Methods

**`definition()`**
```php
protected function definition(): void
```
- **Form Elements**:
  - English clause name
  - Arabic clause name
  - English description
  - Arabic description
  - Active status

### 3. Output/Renderer Classes

#### `local_financeservices\output\tab_list`

**Purpose**: Prepares request data for template rendering.

**File**: `classes/output/tab_list.php`

**Implements**: `renderable`, `templatable`

##### Methods

**`__construct($requests)`**
```php
public function __construct(array $requests)
```
- **Parameters**: 
  - `$requests` (array) - Array of request records
- **Purpose**: Initializes renderer with request data

**`export_for_template($output)`**
```php
public function export_for_template(renderer_base $output): array
```
- **Parameters**: 
  - `$output` (renderer_base) - Moodle renderer
- **Returns**: Array of template data
- **Purpose**: Prepares data for Mustache template
- **Template Data Structure**:
```php
[
    'requests' => [
        [
            'id' => 123,
            'course' => 'Course Name',
            'funding_type' => 'Training',
            'price_requested' => '500.00',
            'status_display' => 'Pending Approval',
            'status_class' => 'warning',
            'can_approve' => true,
            'approve_label' => 'Approve',
            'reject_label' => 'Reject',
            'show_rejection_note' => false,
            'rejection_note' => '',
            'is_final_state' => false
        ]
    ],
    'has_requests' => true,
    'no_requests_message' => 'No requests found'
]
```

### 4. Event Classes

#### `local_financeservices\event\request_created`

**Purpose**: Event triggered when new request is created.

**File**: `classes/event/request_created.php`

**Extends**: `\core\event\base`

##### Methods

**`create($data)`**
```php
public static function create(array $data): self
```
- **Parameters**: 
  - `$data` (array) - Event data including context, objectid, other
- **Returns**: Event instance
- **Example**:
```php
$event = \local_financeservices\event\request_created::create([
    'context' => context_system::instance(),
    'objectid' => $request_id,
    'other' => [
        'course_id' => $course_id,
        'funding_type_id' => $funding_type_id,
        'price_requested' => $price
    ]
]);
$event->trigger();
```

---

#### `local_financeservices\event\request_status_changed`

**Purpose**: Event triggered when request status changes.

**File**: `classes/event/request_status_changed.php`

##### Methods

**`create($data)`**
```php
public static function create(array $data): self
```
- **Event Data**:
  - `old_status` - Previous status ID
  - `new_status` - New status ID
  - `action` - Action performed (approve/reject)
  - `note` - Action note/comment

### 5. Database Functions

#### Global Database Operations

**`get_finance_requests($filters)`**
```php
function get_finance_requests(array $filters = []): array
```
- **Parameters**: 
  - `$filters` (array) - Optional filters (course_id, status_id, user_id, etc.)
- **Returns**: Array of request records with joined data
- **Purpose**: Retrieves filtered list of finance requests
- **Example**:
```php
$requests = get_finance_requests([
    'user_id' => $USER->id,
    'status_id' => [8, 9, 10] // Pending statuses
]);
```

**`get_funding_types($active_only)`**
```php
function get_funding_types(bool $active_only = true): array
```
- **Parameters**: 
  - `$active_only` (bool) - Whether to return only active types
- **Returns**: Array of funding type records
- **Purpose**: Gets available funding types for selection
- **Example**:
```php
$types = get_funding_types(true); // Only active types
foreach ($types as $type) {
    echo $type->funding_type_en;
}
```

**`get_clauses($active_only)`**
```php
function get_clauses(bool $active_only = true): array
```
- **Parameters**: 
  - `$active_only` (bool) - Whether to return only active clauses
- **Returns**: Array of clause records
- **Purpose**: Gets available clauses for selection

## 🔧 Utility Functions

### Language Helper Functions

**`get_language_field($base_field)`**
```php
function get_language_field(string $base_field): string
```
- **Parameters**: 
  - `$base_field` (string) - Base field name (e.g., 'name')
- **Returns**: Language-specific field name (e.g., 'name_en' or 'name_ar')
- **Purpose**: Gets appropriate field based on current language
- **Example**:
```php
$field = get_language_field('funding_type');
// Returns: 'funding_type_en' or 'funding_type_ar'

$sql = "SELECT {$field} AS display_name FROM table";
```

### Status Helper Functions

**`get_status_class($status_id)`**
```php
function get_status_class(int $status_id): string
```
- **Parameters**: 
  - `$status_id` (int) - Status ID
- **Returns**: CSS class for status display
- **Purpose**: Gets Bootstrap class for status styling
- **Return Values**:
  - `'success'` for approved (13)
  - `'danger'` for rejected (14)
  - `'warning'` for pending (all others)

**`is_final_status($status_id)`**
```php
function is_final_status(int $status_id): bool
```
- **Parameters**: 
  - `$status_id` (int) - Status ID to check
- **Returns**: `true` if status is final (approved/rejected)
- **Purpose**: Determines if workflow is complete

## 🌐 AJAX Endpoints

### `/actions/update_request_status.php`

**Purpose**: Handles AJAX requests for workflow status changes.

**HTTP Method**: POST

**Content-Type**: application/json

**Request Format**:
```json
{
    "action": "approve|reject",
    "id": 123,
    "note": "Optional note",
    "sesskey": "moodle_session_key"
}
```

**Response Format**:
```json
{
    "success": true|false,
    "message": "Status updated successfully",
    "new_status": "approved",
    "error_code": "optional_error_code"
}
```

**Security**:
- Requires valid Moodle session
- Validates session key (CSRF protection)
- Checks user capabilities
- Validates request ownership

**Error Codes**:
- `invalid_action` - Action not in [approve, reject]
- `invalid_request_id` - Request ID not found
- `nopermission` - User lacks required capability
- `rejection_note_required` - Rejection attempted without note
- `status_changed_concurrent` - Request status changed by another user

## 📊 Constants and Configuration

### Status ID Constants

```php
define('FINANCESERVICES_STATUS_INITIAL', 8);
define('FINANCESERVICES_STATUS_LEADER1', 9);
define('FINANCESERVICES_STATUS_LEADER2', 10);
define('FINANCESERVICES_STATUS_LEADER3', 11);
define('FINANCESERVICES_STATUS_BOSS', 12);
define('FINANCESERVICES_STATUS_APPROVED', 13);
define('FINANCESERVICES_STATUS_REJECTED', 14);
```

### Capability Constants

```php
define('FINANCESERVICES_CAP_VIEW', 'local/financeservices:view');
define('FINANCESERVICES_CAP_MANAGE', 'local/financeservices:manage');
define('FINANCESERVICES_CAP_APPROVE_L1', 'local/financeservices:approve_level1');
define('FINANCESERVICES_CAP_APPROVE_L2', 'local/financeservices:approve_level2');
define('FINANCESERVICES_CAP_APPROVE_L3', 'local/financeservices:approve_level3');
define('FINANCESERVICES_CAP_APPROVE_BOSS', 'local/financeservices:approve_boss');
```

## 🧪 Testing APIs

### Unit Test Helpers

**`create_test_request($overrides)`**
```php
function create_test_request(array $overrides = []): stdClass
```
- **Parameters**: 
  - `$overrides` (array) - Override default values
- **Returns**: Test request record
- **Purpose**: Creates test request for unit testing

**`create_test_funding_type($overrides)`**
```php
function create_test_funding_type(array $overrides = []): stdClass
```
- **Purpose**: Creates test funding type for testing

## 📝 Usage Examples

### Complete Request Submission Flow

```php
// 1. Create form
$form = new \local_financeservices\form\add_form();

// 2. Process form submission
if ($data = $form->get_data()) {
    // 3. Create request record
    $record = new stdClass();
    $record->course_id = $data->courseid;
    $record->funding_type_id = $data->funding_type_id;
    $record->price_requested = $data->price_requested;
    $record->notes = $data->notes;
    $record->user_id = $USER->id;
    $record->date_time_requested = time();
    $record->status_id = simple_workflow_manager::get_initial_status_id();
    
    // 4. Insert to database
    $request_id = $DB->insert_record('local_financeservices', $record);
    
    // 5. Trigger event
    $event = \local_financeservices\event\request_created::create([
        'context' => context_system::instance(),
        'objectid' => $request_id,
        'other' => [
            'course_id' => $record->course_id,
            'funding_type_id' => $record->funding_type_id,
            'price_requested' => $record->price_requested
        ]
    ]);
    $event->trigger();
    
    // 6. Redirect to list
    redirect(new moodle_url('/local/financeservices/index.php', ['tab' => 'list']));
}
```

### Request List Display

```php
// 1. Get filtered requests
$requests = get_finance_requests(['status_id' => [8, 9, 10, 11, 12]]);

// 2. Prepare for template
$renderable = new \local_financeservices\output\tab_list($requests);
$template_data = $renderable->export_for_template($OUTPUT);

// 3. Render template
echo $OUTPUT->render_from_template('local_financeservices/list', $template_data);
```

This API reference provides complete documentation for integrating with and extending the Finance Services plugin. 