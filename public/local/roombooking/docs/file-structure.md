# File Structure Documentation

This document provides a detailed explanation of every file and directory in the Room Booking plugin.

## Root Directory Structure

```
local/roombooking/
├── actions/               # Action handlers for CRUD operations
├── classes/               # PHP classes organized by functionality
├── db/                   # Database schema and upgrade scripts
├── docs/                 # Documentation files
├── lang/                 # Language strings for internationalization
├── pages/                # User interface pages
├── templates/            # Mustache templates for rendering
├── index.php             # Main plugin entry point
├── lib.php               # Core plugin functions and hooks
├── settings.php          # Plugin configuration settings
├── version.php           # Plugin version and metadata
└── README.md             # Basic plugin information
```

## Core Files

### `index.php` (11KB, 241 lines)
**Purpose**: Main entry point for the plugin
- Handles the primary navigation and routing for the room booking system
- Displays the main dashboard with booking overview
- Implements the main user interface logic
- Includes capability checks and user authentication
- Contains the main controller logic for displaying booking information

### `lib.php` (1.2KB, 32 lines)
**Purpose**: Core plugin functions and Moodle integration hooks
- **Function**: `local_roombooking_extend_navigation()`
  - Adds the Room Booking plugin to Moodle's navigation system
  - Checks user capabilities (`moodle/site:config`)
  - Creates navigation nodes in the user dashboard
  - Integrates with Moodle's global navigation structure

### `settings.php` (2.3KB, 46 lines)
**Purpose**: Plugin configuration and admin settings
- Defines admin settings page configuration
- Contains plugin configuration options
- Integrates with Moodle's admin settings tree
- Provides administrative controls for the plugin

### `version.php` (784B, 17 lines)
**Purpose**: Plugin metadata and version information
- **Component**: `local_roombooking`
- **Version**: `2025061600` (YYYYMMDDRR format)
- **Requires**: Moodle 4.1 minimum (`2022111800`)
- **Maturity**: `MATURITY_STABLE`
- **Release**: `2.0 (approval workflow)`
- Contains version history and upgrade trigger information

## Actions Directory (`actions/`)

### `action_booking.php` (2.1KB, 56 lines)
**Purpose**: Handles booking-related CRUD operations
- Processes booking creation, updates, and deletions
- Handles AJAX requests for booking operations
- Implements booking validation logic
- Manages booking state changes and workflow transitions

## Classes Directory (`classes/`)

The classes directory is organized into functional subdirectories following Moodle's autoloading conventions.

### Root Classes

#### `simple_workflow_manager.php` (11KB, 282 lines)
**Purpose**: Manages the approval workflow system
- Implements workflow state management
- Handles approval/rejection processes
- Integrates with the generic workflow engine (type_id = 8)
- Manages workflow transitions and state validation
- **Rejection Behavior**: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review for re-evaluation

#### `data_manager.php` (5.0KB, 123 lines)
**Purpose**: Central data management and business logic
- Handles complex data operations
- Implements business rules and validation
- Manages data consistency and integrity
- Provides abstraction layer for data operations

#### `utils.php` (570B, 25 lines)
**Purpose**: Utility functions and helper methods
- Common utility functions used across the plugin
- Helper methods for data formatting and manipulation
- Shared functionality for various components

### Repository Pattern (`classes/repository/`)

#### `room_repository.php` (1.4KB, 45 lines)
**Purpose**: Data access layer for room entities
- Handles room CRUD operations
- Implements repository pattern for room data
- Provides abstraction for room data access
- Contains room-specific database queries

#### `booking_repository.php` (4.9KB, 102 lines)
**Purpose**: Data access layer for booking entities
- Handles booking CRUD operations
- Implements complex booking queries and filters
- Manages booking relationships and dependencies
- Provides booking-specific data access methods

### Service Layer (`classes/service/`)

#### `booking_service.php` (11KB, 241 lines)
**Purpose**: Business logic layer for booking operations
- Implements booking business rules
- Handles complex booking scenarios (recurring bookings, conflicts)
- Manages booking validation and workflow integration
- Provides service layer abstraction for booking operations

### Form Classes (`classes/form/`)

#### `booking_form.php` (13KB, 236 lines)
**Purpose**: Moodle form for creating and editing bookings
- Implements Moodle's form API for booking management
- Handles form validation and data processing
- Supports recurring booking configurations
- Integrates with workflow system for approval processes

#### `filter_form.php` (2.4KB, 57 lines)
**Purpose**: Form for filtering and searching bookings
- Provides search and filter interface
- Handles date range filtering
- Implements room and status filtering options
- Generates query parameters for booking lists

#### `room_form.php` (2.2KB, 63 lines)
**Purpose**: Form for managing room information
- Handles room creation and editing
- Implements room validation rules
- Manages room properties and configuration
- Integrates with room repository for data persistence

### Output Layer (`classes/output/`)

#### `renderer.php` (7.3KB, 174 lines)
**Purpose**: Custom renderer for plugin output
- Extends Moodle's renderer system
- Handles template rendering and data preparation
- Implements custom output methods for booking displays
- Manages UI component rendering

### Persistence Layer (`classes/persistence/`)
**Status**: Currently empty - reserved for future persistence abstractions

## Database Directory (`db/`)

### `install.xml` (3.4KB, 58 lines)
**Purpose**: Database schema definition
- Defines initial database table structures
- Specifies field types, indexes, and constraints
- Contains table definitions for rooms and bookings
- Follows Moodle's XMLDB format

### `install.php` (2.2KB, 55 lines)
**Purpose**: Post-installation setup script
- Executes after plugin installation
- Sets up initial data and configurations
- Performs post-installation tasks
- Handles initial capability assignments

### `upgrade.php` (5.1KB, 123 lines)
**Purpose**: Database upgrade script
- Handles database schema upgrades between versions
- Implements version-specific upgrade logic
- Manages data migration and transformation
- Ensures backward compatibility during upgrades

### `access.php` (1.9KB, 49 lines)
**Purpose**: Capability definitions
- Defines plugin-specific capabilities and permissions
- Specifies default role assignments
- Integrates with Moodle's permission system
- Controls access to plugin features

## Language Directory (`lang/`)

### English (`lang/en/`)
#### `local_roombooking.php` (11KB, 229 lines)
**Purpose**: English language strings
- Contains all plugin text in English
- Includes interface labels, messages, and help text
- Follows Moodle's localization standards
- Provides default language support

### Arabic (`lang/ar/`)
#### `local_roombooking.php` (14KB, 230 lines)
**Purpose**: Arabic language strings
- Complete Arabic translation of the plugin
- Supports right-to-left (RTL) text direction
- Includes all interface elements in Arabic
- Provides multilingual support

## Pages Directory (`pages/`)

### `classroom_management.php` (5.2KB, 106 lines)
**Purpose**: Room/classroom management interface
- Displays room listing and management interface
- Handles room creation, editing, and deletion
- Implements room search and filtering
- Provides administrative interface for room management

### `manage_rooms.php` (12KB, 269 lines)
**Purpose**: Comprehensive room management system
- Advanced room management functionality
- Handles bulk operations on rooms
- Implements room import/export features
- Provides detailed room configuration options

### `bookings.php` (4.7KB, 143 lines)
**Purpose**: Booking listing and management interface
- Displays user bookings with filtering options
- Handles booking search and sorting
- Implements booking status management
- Provides user interface for booking operations

### `delete.php` (3.9KB, 77 lines)
**Purpose**: Handles deletion operations
- Implements safe deletion with confirmations
- Handles cascade deletions and dependencies
- Provides deletion interfaces for various entities
- Ensures data integrity during deletion operations

### `manage_bookings.php` (1.7KB, 53 lines)
**Purpose**: Administrative booking management
- Provides admin interface for booking oversight
- Handles administrative booking operations
- Implements admin-level booking controls
- Manages system-wide booking settings

## Templates Directory (`templates/`)

### `table_classroom_booking.mustache` (6.5KB, 138 lines)
**Purpose**: Mustache template for booking table display
- Renders booking information in table format
- Implements responsive table design
- Handles data formatting and display
- Provides consistent UI for booking lists
- Includes sorting and filtering interface elements

## File Naming Conventions

1. **PHP Classes**: Follow PSR-4 autoloading standards
2. **Database Files**: Use Moodle's standard naming (`install.xml`, `upgrade.php`, etc.)
3. **Language Files**: Named after the plugin component (`local_roombooking.php`)
4. **Templates**: Use descriptive names with `.mustache` extension
5. **Pages**: Use descriptive names indicating their primary function

## Code Organization Principles

1. **Separation of Concerns**: Each file has a single, well-defined responsibility
2. **Repository Pattern**: Data access is abstracted through repository classes
3. **Service Layer**: Business logic is centralized in service classes
4. **Form Handling**: Moodle forms are used for all user input
5. **Template Rendering**: UI is separated from logic using Mustache templates

## Dependencies and Relationships

- **Forms** depend on **Services** for business logic
- **Services** depend on **Repositories** for data access
- **Pages** coordinate between **Forms**, **Services**, and **Templates**
- **Workflow Manager** integrates with external workflow system
- **Language files** support internationalization across all components 