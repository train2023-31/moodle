# Code Architecture

This document explains the technical architecture, design patterns, and code organization of the Room Booking plugin.

## Architectural Overview

The Room Booking plugin follows a **layered architecture** pattern with clear separation of concerns. This design promotes maintainability, testability, and extensibility.

### Layer Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │    Pages    │  │  Templates  │  │      Forms          │  │
│  │ (UI Logic)  │  │ (Mustache)  │  │ (Input Handling)    │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                               │
┌─────────────────────────────────────────────────────────────┐
│                    Business Logic Layer                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │  Services   │  │  Workflow   │  │     Utilities       │  │
│  │(Bus. Rules) │  │  Manager    │  │    (Helpers)        │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                               │
┌─────────────────────────────────────────────────────────────┐
│                    Data Access Layer                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ Repositories│  │ Data Manager│  │     Actions         │  │
│  │(Data Access)│  │(Coordination)│  │ (CRUD Operations)   │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                               │
┌─────────────────────────────────────────────────────────────┐
│                    Database Layer                           │
│                  (Moodle DB Abstraction)                    │
└─────────────────────────────────────────────────────────────┘
```

## Design Patterns

### 1. Repository Pattern

The plugin implements the Repository pattern to abstract data access and provide a clean separation between business logic and data persistence.

#### Structure
```php
interface booking_repository_interface {
    public function get_booking($id);
    public function save_booking($booking);
    public function delete_booking($id);
    public function find_bookings($criteria);
}

class booking_repository implements booking_repository_interface {
    // Implementation using Moodle's DB API
}
```

#### Benefits
- **Abstraction**: Business logic doesn't depend on specific database implementation
- **Testability**: Easy to mock repositories for unit testing
- **Maintainability**: Database logic is centralized and reusable
- **Flexibility**: Can switch database implementations without affecting business logic

### 2. Service Layer Pattern

Business logic is encapsulated in service classes that coordinate between repositories and implement business rules.

#### Structure
```php
class booking_service {
    private $booking_repository;
    private $room_repository;
    private $workflow_manager;
    
    public function create_booking($data) {
        // 1. Validate input data
        // 2. Check business rules (conflicts, permissions)
        // 3. Create booking entity
        // 4. Trigger workflow if needed
        // 5. Save via repository
        // 6. Return result
    }
}
```

#### Benefits
- **Centralized Business Logic**: All rules and workflows in one place
- **Reusability**: Services can be used by multiple controllers/pages
- **Transaction Management**: Handle complex operations atomically
- **Workflow Integration**: Coordinate with external systems

### 3. Model-View-Controller (MVC) Pattern

The plugin follows MVC principles with Moodle-specific adaptations:

- **Model**: Repository classes and data entities
- **View**: Mustache templates and renderer classes
- **Controller**: Page files that coordinate between models and views

## Class Organization

### Namespace Structure

Following Moodle's autoloading conventions:

```
local_roombooking\
├── form\                   # Form classes
│   ├── booking_form
│   ├── filter_form
│   └── room_form
├── output\                 # Output/rendering classes
│   └── renderer
├── repository\             # Data access layer
│   ├── booking_repository
│   └── room_repository
├── service\               # Business logic layer
│   └── booking_service
├── simple_workflow_manager # Workflow integration
├── data_manager           # Central data coordination
└── utils                  # Utility functions
```

### Class Dependencies

```
Pages
  ↓
Forms ← → Services ← → Workflow Manager
  ↓         ↓
Renderer  Repositories
  ↓         ↓
Templates Database
```

## Core Components

### 1. Data Manager (`data_manager.php`)

**Purpose**: Central coordination of data operations

```php
class data_manager {
    /**
     * Coordinates complex data operations
     * Manages data consistency and integrity
     * Provides abstraction for complex queries
     */
    
    public function get_booking_dashboard_data($filters) {
        // Coordinate between multiple repositories
        // Apply business logic for data presentation
        // Return structured data for dashboard
    }
}
```

**Responsibilities**:
- Coordinate between multiple repositories
- Handle complex queries that span multiple entities
- Ensure data consistency across operations
- Provide high-level data access methods

### 2. Service Layer (`service/booking_service.php`)

**Purpose**: Implement business logic and rules

```php
class booking_service {
    /**
     * Implements booking business rules
     * Handles workflow integration
     * Manages booking conflicts and validation
     */
    
    public function create_recurring_booking($data) {
        // Validate recurring pattern
        // Check for conflicts across all instances
        // Create individual booking records
        // Submit to workflow if required
        // Handle rollback on errors
    }
}
```

**Key Methods**:
- `create_booking()`: Handle single booking creation
- `create_recurring_booking()`: Handle recurring booking patterns
- `update_booking()`: Modify existing bookings with validation
- `cancel_booking()`: Cancel bookings with business rules
- `check_conflicts()`: Detect scheduling conflicts

### 3. Repository Layer

#### Booking Repository (`repository/booking_repository.php`)

```php
class booking_repository {
    /**
     * Data access for booking entities
     * Implements complex queries and filters
     * Handles booking-specific database operations
     */
    
    public function find_conflicting_bookings($room_id, $start_time, $end_time) {
        // Implement conflict detection query
        // Return overlapping bookings
    }
    
    public function get_bookings_by_criteria($criteria) {
        // Build dynamic query based on criteria
        // Handle pagination and sorting
        // Return filtered results
    }
}
```

#### Room Repository (`repository/room_repository.php`)

```php
class room_repository {
    /**
     * Data access for room entities
     * Handles room-specific queries
     * Manages room availability data
     */
    
    public function get_available_rooms($date, $time_slot) {
        // Query rooms without conflicts
        // Apply availability rules
        // Return available rooms with details
    }
}
```

### 4. Workflow Integration (`simple_workflow_manager.php`)

**Purpose**: Manage approval workflows and state transitions

```php
class simple_workflow_manager {
    /**
     * Integrates with external workflow system
     * Manages state transitions
     * Handles approval/rejection logic
     * Implements rejection behavior: Leader 1 → Final rejection, others → Leader 1 Review
     */
    
    const WORKFLOW_TYPE_ID = 8;  // Configured in external system
    
    public function submit_for_approval($entity_id, $entity_type) {
        // Submit to external workflow engine
        // Set initial state to 'pending'
        // Trigger notifications
    }
    
    public function handle_approval($entity_id, $action, $comments) {
        // Process approval/rejection
        // Update entity state
        // Trigger notifications
        // Handle post-approval actions
    }
}
```

**States**:
- `pending`: Waiting for approval
- `approved`: Approved and active
- `rejected`: Rejected with reason

**Rejection Flow**:
- **Leader 1 Rejection**: Goes directly to Rejected status (terminal state)
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review for re-evaluation

## Form Architecture

### Form Inheritance Hierarchy

```php
moodleform (Moodle Core)
    ↓
booking_form extends moodleform
filter_form extends moodleform
room_form extends moodleform
```

### Form Validation Strategy

1. **Client-side Validation**: JavaScript validation for immediate feedback
2. **Server-side Validation**: PHP validation in form classes
3. **Business Rule Validation**: Additional validation in service layer
4. **Database Constraints**: Final validation at database level

### Example Form Implementation

```php
class booking_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        
        // Room selection
        $rooms = $this->get_available_rooms();
        $mform->addElement('select', 'room_id', 
            get_string('room', 'local_roombooking'), $rooms);
        
        // Date/time selection
        $mform->addElement('date_time_selector', 'start_time',
            get_string('starttime', 'local_roombooking'));
            
        // Custom validation
        $mform->addRule('room_id', null, 'required', null, 'client');
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Custom business rule validation
        if ($this->has_conflict($data)) {
            $errors['start_time'] = get_string('conflictdetected', 'local_roombooking');
        }
        
        return $errors;
    }
}
```

## Template Architecture

### Template Organization

Templates follow Moodle's Mustache template system:

```
templates/
└── table_classroom_booking.mustache
```

### Template Data Structure

Templates receive structured data from renderer classes:

```php
class renderer extends plugin_renderer_base {
    public function render_booking_table($bookings, $filters) {
        $data = [
            'bookings' => $this->format_bookings($bookings),
            'filters' => $this->format_filters($filters),
            'has_bookings' => !empty($bookings),
            'export_url' => $this->get_export_url()
        ];
        
        return $this->render_from_template('local_roombooking/table_classroom_booking', $data);
    }
}
```

### Template Best Practices

1. **Separation of Logic**: Keep logic in PHP, templates for presentation only
2. **Data Formatting**: Format data in renderer classes, not templates
3. **Reusable Components**: Create modular template sections
4. **Accessibility**: Include proper ARIA labels and semantic HTML

## Database Architecture

### Table Design Principles

1. **Normalization**: Tables are normalized to reduce redundancy
2. **Moodle Conventions**: Follow Moodle's database naming and structure conventions
3. **Performance**: Appropriate indexes for common queries
4. **Referential Integrity**: Foreign key relationships where appropriate

### Table Relationships

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│      Users      │    │     Bookings     │    │      Rooms      │
│   (Moodle)      │    │                  │    │                 │
├─────────────────┤    ├──────────────────┤    ├─────────────────┤
│ id (PK)         │◄───┤ user_id (FK)     │    │ id (PK)         │
│ username        │    │ room_id (FK)     ├───►│ name            │
│ email           │    │ start_time       │    │ capacity        │
│ ...             │    │ end_time         │    │ location        │
└─────────────────┘    │ status           │    │ ...             │
                       │ workflow_state   │    └─────────────────┘
                       │ ...              │
                       └──────────────────┘
```

## Error Handling Strategy

### Exception Hierarchy

```php
// Custom exceptions for specific error types
class roombooking_exception extends moodle_exception {}
class booking_conflict_exception extends roombooking_exception {}
class insufficient_permission_exception extends roombooking_exception {}
```

### Error Handling Layers

1. **Validation Layer**: Form validation and input sanitization
2. **Business Logic Layer**: Business rule violations
3. **Data Access Layer**: Database and data integrity errors
4. **Integration Layer**: External system communication errors

### Error Response Strategy

```php
try {
    $service->create_booking($data);
    redirect($success_url, get_string('bookingcreated', 'local_roombooking'));
} catch (booking_conflict_exception $e) {
    // Handle specific conflict error
    $form->set_error('start_time', $e->getMessage());
} catch (roombooking_exception $e) {
    // Handle general plugin errors
    print_error($e->errorcode, 'local_roombooking');
} catch (Exception $e) {
    // Handle unexpected errors
    debugging($e->getMessage(), DEBUG_DEVELOPER);
    print_error('generalerror', 'local_roombooking');
}
```

## Performance Considerations

### Query Optimization

1. **Index Strategy**: Indexes on frequently queried columns
2. **Lazy Loading**: Load related data only when needed
3. **Batch Operations**: Reduce database round trips
4. **Query Caching**: Cache expensive query results

### Caching Strategy

```php
// Example caching implementation
class booking_service {
    public function get_room_availability($room_id, $date) {
        $cache_key = "room_availability_{$room_id}_{$date}";
        $cache = cache::make('local_roombooking', 'availability');
        
        $availability = $cache->get($cache_key);
        if ($availability === false) {
            $availability = $this->calculate_room_availability($room_id, $date);
            $cache->set($cache_key, $availability, 300); // 5 minute cache
        }
        
        return $availability;
    }
}
```

## Security Architecture

### Input Validation

1. **Parameter Validation**: Use Moodle's PARAM_* constants
2. **Form Validation**: Multiple validation layers
3. **SQL Injection Prevention**: Use Moodle's database API exclusively
4. **XSS Prevention**: Proper output escaping

### Access Control

```php
// Capability checking at multiple levels
class booking_service {
    public function create_booking($data) {
        $context = context_system::instance();
        
        // Check basic capability
        require_capability('local/roombooking:create', $context);
        
        // Check ownership or admin rights
        if ($data->user_id !== $USER->id) {
            require_capability('local/roombooking:manage', $context);
        }
        
        // Proceed with creation
    }
}
```

## Testing Architecture

### Unit Testing Structure

```php
class booking_service_test extends advanced_testcase {
    private $booking_service;
    private $mock_repository;
    
    public function setUp(): void {
        $this->resetAfterTest(true);
        $this->mock_repository = $this->createMock(booking_repository::class);
        $this->booking_service = new booking_service($this->mock_repository);
    }
    
    public function test_create_booking_success() {
        // Test successful booking creation
    }
    
    public function test_create_booking_conflict() {
        // Test conflict detection
    }
}
```

### Integration Testing

- Test complete workflows from form submission to database storage
- Test workflow integration with external systems
- Test permission enforcement across all layers

This architecture ensures the plugin is maintainable, scalable, and follows Moodle best practices while providing a robust room booking system. 