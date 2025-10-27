# API Reference

This document provides a comprehensive reference for all public classes, methods, and functions in the Room Booking plugin.

## Core Classes

### `simple_workflow_manager`

**Location**: `classes/simple_workflow_manager.php`  
**Purpose**: Manages the approval workflow system and state transitions

#### Constants
```php
const WORKFLOW_TYPE_ID = 8;  // External workflow system type ID
```

#### Public Methods

##### `submit_for_approval($entity_id, $entity_type, $user_id = null)`
Submits an entity for approval through the workflow system.

**Parameters:**
- `$entity_id` (int): ID of the entity to submit
- `$entity_type` (string): Type of entity ('booking', 'room', etc.)
- `$user_id` (int, optional): User ID submitting (defaults to current user)

**Returns:** `bool` - Success status

**Example:**
```php
$workflow_manager = new simple_workflow_manager();
$success = $workflow_manager->submit_for_approval(123, 'booking');
```

##### `get_workflow_state($entity_id, $entity_type)`
Retrieves the current workflow state for an entity.

**Parameters:**
- `$entity_id` (int): Entity ID
- `$entity_type` (string): Entity type

**Returns:** `string|null` - Current workflow state or null if not found

##### `handle_approval($entity_id, $entity_type, $action, $comments = '')`
Processes approval or rejection of an entity.

**Parameters:**
- `$entity_id` (int): Entity ID
- `$entity_type` (string): Entity type
- `$action` (string): 'approve' or 'reject'
- `$comments` (string, optional): Approval/rejection comments

**Returns:** `bool` - Success status

---

### `data_manager`

**Location**: `classes/data_manager.php`  
**Purpose**: Central data coordination and complex data operations

#### Public Methods

##### `get_dashboard_data($filters = [])`
Retrieves comprehensive dashboard data including bookings, rooms, and statistics.

**Parameters:**
- `$filters` (array, optional): Filter criteria

**Returns:** `stdClass` - Object containing dashboard data

**Data Structure:**
```php
$data = (object)[
    'bookings' => [...],      // Recent bookings
    'rooms' => [...],         // Available rooms
    'statistics' => [...],    // Usage statistics
    'pending_approvals' => [...] // Pending workflow items
];
```

##### `get_booking_conflicts($room_id, $start_time, $end_time, $exclude_booking_id = null)`
Checks for booking conflicts in a given time range.

**Parameters:**
- `$room_id` (int): Room ID to check
- `$start_time` (int): Start timestamp
- `$end_time` (int): End timestamp
- `$exclude_booking_id` (int, optional): Booking ID to exclude from conflict check

**Returns:** `array` - Array of conflicting booking objects

##### `export_bookings_csv($filters = [])`
Exports booking data in CSV format.

**Parameters:**
- `$filters` (array, optional): Filter criteria

**Returns:** `string` - CSV formatted data

---

### `utils`

**Location**: `classes/utils.php`  
**Purpose**: Utility functions and helper methods

#### Static Methods

##### `format_time_slot($start_time, $end_time)`
Formats time slot for display.

**Parameters:**
- `$start_time` (int): Start timestamp
- `$end_time` (int): End timestamp

**Returns:** `string` - Formatted time slot string

##### `validate_time_range($start_time, $end_time)`
Validates that a time range is logical and valid.

**Parameters:**
- `$start_time` (int): Start timestamp
- `$end_time` (int): End timestamp

**Returns:** `bool` - Validation result

---

## Repository Classes

### `booking_repository`

**Location**: `classes/repository/booking_repository.php`  
**Purpose**: Data access layer for booking entities

#### Public Methods

##### `get_booking($id)`
Retrieves a single booking by ID.

**Parameters:**
- `$id` (int): Booking ID

**Returns:** `stdClass|false` - Booking object or false if not found

##### `get_bookings($criteria = [], $sort = '', $offset = 0, $limit = 0)`
Retrieves multiple bookings based on criteria.

**Parameters:**
- `$criteria` (array, optional): Search criteria
- `$sort` (string, optional): Sort field and direction
- `$offset` (int, optional): Result offset for pagination
- `$limit` (int, optional): Maximum results to return

**Returns:** `array` - Array of booking objects

**Criteria Format:**
```php
$criteria = [
    'user_id' => 123,
    'room_id' => 456,
    'status' => 'approved',
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
];
```

##### `save_booking($booking)`
Saves a booking (insert or update).

**Parameters:**
- `$booking` (stdClass): Booking object

**Returns:** `int|bool` - Booking ID on insert, true on update, false on failure

##### `delete_booking($id)`
Deletes a booking by ID.

**Parameters:**
- `$id` (int): Booking ID

**Returns:** `bool` - Success status

##### `count_bookings($criteria = [])`
Counts bookings matching criteria.

**Parameters:**
- `$criteria` (array, optional): Search criteria

**Returns:** `int` - Count of matching bookings

---

### `room_repository`

**Location**: `classes/repository/room_repository.php`  
**Purpose**: Data access layer for room entities

#### Public Methods

##### `get_room($id)`
Retrieves a single room by ID.

**Parameters:**
- `$id` (int): Room ID

**Returns:** `stdClass|false` - Room object or false if not found

##### `get_rooms($criteria = [], $sort = 'name ASC')`
Retrieves multiple rooms based on criteria.

**Parameters:**
- `$criteria` (array, optional): Search criteria
- `$sort` (string, optional): Sort specification

**Returns:** `array` - Array of room objects

##### `get_available_rooms($start_time, $end_time, $exclude_booking_id = null)`
Gets rooms available during a specific time period.

**Parameters:**
- `$start_time` (int): Start timestamp
- `$end_time` (int): End timestamp
- `$exclude_booking_id` (int, optional): Booking to exclude from conflict check

**Returns:** `array` - Array of available room objects

##### `save_room($room)`
Saves a room (insert or update).

**Parameters:**
- `$room` (stdClass): Room object

**Returns:** `int|bool` - Room ID on insert, true on update, false on failure

---

## Service Classes

### `booking_service`

**Location**: `classes/service/booking_service.php`  
**Purpose**: Business logic layer for booking operations

#### Public Methods

##### `create_booking($data)`
Creates a new booking with business rule validation.

**Parameters:**
- `$data` (array|stdClass): Booking data

**Returns:** `int` - New booking ID

**Throws:** `booking_conflict_exception`, `roombooking_exception`

**Example:**
```php
$service = new booking_service();
$booking_id = $service->create_booking([
    'room_id' => 123,
    'user_id' => 456,
    'title' => 'Team Meeting',
    'start_time' => strtotime('2024-06-20 10:00:00'),
    'end_time' => strtotime('2024-06-20 11:00:00')
]);
```

##### `create_recurring_booking($data, $pattern)`
Creates a series of recurring bookings.

**Parameters:**
- `$data` (array|stdClass): Base booking data
- `$pattern` (array): Recurring pattern configuration

**Returns:** `array` - Array of created booking IDs

**Pattern Format:**
```php
$pattern = [
    'type' => 'weekly',
    'interval' => 1,
    'days_of_week' => [1, 3, 5], // Mon, Wed, Fri
    'end_date' => '2024-12-31',
    'max_occurrences' => 50
];
```

##### `update_booking($id, $data)`
Updates an existing booking with validation.

**Parameters:**
- `$id` (int): Booking ID
- `$data` (array|stdClass): Updated booking data

**Returns:** `bool` - Success status

##### `cancel_booking($id, $reason = '')`
Cancels a booking.

**Parameters:**
- `$id` (int): Booking ID
- `$reason` (string, optional): Cancellation reason

**Returns:** `bool` - Success status

##### `approve_booking($id, $approver_id, $comments = '')`
Approves a booking.

**Parameters:**
- `$id` (int): Booking ID
- `$approver_id` (int): User ID of approver
- `comments` (string, optional): Approval comments

**Returns:** `bool` - Success status

##### `reject_booking($id, $approver_id, $reason)`
Rejects a booking.

**Parameters:**
- `$id` (int): Booking ID
- `$approver_id` (int): User ID of approver
- `$reason` (string): Rejection reason

**Returns:** `bool` - Success status

**Rejection Behavior:**
- **Leader 1 Rejection**: Goes directly to Rejected status (terminal state)
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review for re-evaluation
- **Capability Required**: Each level requires its respective workflow step capability

---

## Form Classes

### `booking_form`

**Location**: `classes/form/booking_form.php`  
**Purpose**: Moodle form for creating and editing bookings

#### Constructor

##### `__construct($action = null, $customdata = null)`
Creates a new booking form instance.

**Parameters:**
- `$action` (string, optional): Form action URL
- `$customdata` (array, optional): Custom data for form initialization

#### Public Methods

##### `validation($data, $files)`
Validates form data.

**Parameters:**
- `$data` (array): Form data
- `$files` (array): Uploaded files

**Returns:** `array` - Validation errors

##### `get_data()`
Gets validated form data.

**Returns:** `stdClass|null` - Form data or null if not submitted/valid

---

### `filter_form`

**Location**: `classes/form/filter_form.php`  
**Purpose**: Form for filtering and searching bookings

#### Methods

##### `get_filter_data()`
Gets current filter values.

**Returns:** `array` - Filter criteria

---

### `room_form`

**Location**: `classes/form/room_form.php`  
**Purpose**: Form for managing room information

#### Methods

##### `validation($data, $files)`
Validates room data.

**Parameters:**
- `$data` (array): Form data
- `$files` (array): Uploaded files

**Returns:** `array` - Validation errors

---

## Output Classes

### `renderer`

**Location**: `classes/output/renderer.php`  
**Purpose**: Custom renderer for plugin output

#### Public Methods

##### `render_booking_table($bookings, $filters = [])`
Renders a table of bookings.

**Parameters:**
- `$bookings` (array): Array of booking objects
- `$filters` (array, optional): Current filter settings

**Returns:** `string` - Rendered HTML

##### `render_room_list($rooms)`
Renders a list of rooms.

**Parameters:**
- `$rooms` (array): Array of room objects

**Returns:** `string` - Rendered HTML

##### `render_dashboard($data)`
Renders the main dashboard.

**Parameters:**
- `$data` (stdClass): Dashboard data object

**Returns:** `string` - Rendered HTML

---

## Global Functions

### Navigation

#### `local_roombooking_extend_navigation($navigation)`

**Location**: `lib.php`  
**Purpose**: Extends Moodle's navigation with plugin links

**Parameters:**
- `$navigation` (global_navigation): Moodle navigation object

**Returns:** `void`

---

## Exception Classes

### `roombooking_exception`

Base exception class for the plugin.

### `booking_conflict_exception`

Thrown when a booking conflict is detected.

### `insufficient_permission_exception`

Thrown when user lacks required permissions.

---

## Constants

### Status Constants

```php
// Booking statuses
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';
const STATUS_REJECTED = 'rejected';
const STATUS_CANCELLED = 'cancelled';

// Workflow states
const WORKFLOW_PENDING = 'pending';
const WORKFLOW_APPROVED = 'approved';
const WORKFLOW_REJECTED = 'rejected';

// Recurring pattern types
const PATTERN_DAILY = 'daily';
const PATTERN_WEEKLY = 'weekly';
const PATTERN_MONTHLY = 'monthly';
const PATTERN_YEARLY = 'yearly';
```

---

## Usage Examples

### Creating a Simple Booking

```php
// Initialize services
$booking_service = new booking_service();

// Create booking data
$booking_data = [
    'room_id' => 123,
    'user_id' => $USER->id,
    'title' => 'Staff Meeting',
    'description' => 'Weekly team meeting',
    'start_time' => strtotime('2024-06-20 14:00:00'),
    'end_time' => strtotime('2024-06-20 15:00:00')
];

try {
    $booking_id = $booking_service->create_booking($booking_data);
    echo "Booking created with ID: $booking_id";
} catch (booking_conflict_exception $e) {
    echo "Conflict detected: " . $e->getMessage();
} catch (roombooking_exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Creating a Recurring Booking

```php
$booking_service = new booking_service();

$booking_data = [
    'room_id' => 123,
    'title' => 'Weekly Team Meeting',
    'start_time' => strtotime('2024-06-20 14:00:00'),
    'end_time' => strtotime('2024-06-20 15:00:00')
];

$pattern = [
    'type' => 'weekly',
    'interval' => 1,
    'days_of_week' => [4], // Thursday
    'end_date' => '2024-12-31'
];

$booking_ids = $booking_service->create_recurring_booking($booking_data, $pattern);
echo "Created " . count($booking_ids) . " recurring bookings";
```

### Searching for Available Rooms

```php
$room_repository = new room_repository();

$start_time = strtotime('2024-06-20 10:00:00');
$end_time = strtotime('2024-06-20 12:00:00');

$available_rooms = $room_repository->get_available_rooms($start_time, $end_time);

foreach ($available_rooms as $room) {
    echo "Room: {$room->name} (Capacity: {$room->capacity})\n";
}
```

This API reference provides comprehensive documentation for all public interfaces in the Room Booking plugin. For implementation details and private methods, refer to the source code files. 