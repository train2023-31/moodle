# File Structure Documentation

This document explains the purpose and functionality of each file and directory in the participant plugin.

## Root Files

### `version.php`
**Purpose**: Plugin metadata and version information
- Defines plugin component name: `local_participant`
- Current version: `2025071302` (version 2.1)
- Minimum Moodle requirement: 4.1
- Maturity level: Stable
- No cron tasks required

### `index.php`
**Purpose**: Main plugin page for viewing participant requests
- **Functionality**:
  - Displays tabbed interface (Add Request / View Requests)
  - Shows paginated list of requests with filtering
  - Requires `local/participant:view` capability
  - Includes JavaScript for CSV export and custom dialogs
  - Integrates with workflow manager for status handling
- **Dependencies**: 
  - `classes/form/requests_filter_form.php`
  - `classes/simple_workflow_manager.php`
  - `js/main.js`

### `add_request.php`
**Purpose**: Page for creating new participant requests
- **Functionality**:
  - Form-based request creation interface
  - Validates course existence
  - Processes form submission and stores requests
  - Populates audit fields (`created_by`, `time_created`, `time_modified`, `modified_by`)
  - Requires `local/participant:addrequest` capability
  - Redirects to main page after successful submission
- **Dependencies**:
  - `classes/form/request_form.php`
  - `classes/simple_workflow_manager.php`

## Directory Structure

### `/actions/`
**Purpose**: Workflow action handlers

#### `approve_request.php`
- Handles request approval actions
- Processes workflow transitions
- Updates request status to approved and audit fields (`time_modified`, `modified_by`)

#### `reject_request.php`
- Handles request rejection actions
- Processes workflow transitions based on rejection level
- Updates request status (either to rejected or back to Leader 1 Review) and audit fields (`time_modified`, `modified_by`)
- Implements new rejection behavior: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review

### `/classes/`
**Purpose**: PHP classes and object-oriented components

#### `simple_workflow_manager.php`
**Purpose**: Core workflow management system
- **Key Features**:
  - Defines workflow status constants (Initial, Leader Reviews, Boss Review, Approved, Rejected)
  - Maps workflow transitions and required capabilities
  - Handles approval/rejection logic
  - Manages workflow state transitions
- **Status Flow**:
  1. Initial (56) → Leader1 Review (57)
  2. Leader1 Review (57) → Leader2 Review (58)
  3. Leader2 Review (58) → Leader3 Review (59)
  4. Leader3 Review (59) → Boss Review (60)
  5. Boss Review (60) → Approved (61)
  6. **Rejection Flow**:
     - Leader1 Review (57) → Rejected (62) [final rejection]
     - Leader2 Review (58) → Leader1 Review (57) [return for re-evaluation]
     - Leader3 Review (59) → Leader1 Review (57) [return for re-evaluation]
     - Boss Review (60) → Leader1 Review (57) [return for re-evaluation]

#### `/classes/form/`
**Purpose**: Moodle form classes

##### `request_form.php`
- Defines the form for creating new participant requests
- Handles form validation and data processing
- Includes fields for course, participant details, organization, etc.

##### `requests_filter_form.php`
- Provides filtering interface for the requests view
- Allows users to filter requests by various criteria

#### `/classes/local/`
**Purpose**: Additional local classes
- Currently empty directory for future local class extensions

### `/db/`
**Purpose**: Database definitions and management

#### `access.php`
**Purpose**: Capability definitions
- `local/participant:view` - Permission to view requests (managers, teachers)
- `local/participant:addrequest` - Permission to add requests (managers, teachers)

#### `install.php`
- Database installation script
- Creates necessary tables during plugin installation

#### `install.xml`
- XML database schema definition
- Defines table structures and relationships

#### `upgrade.php`
- Database upgrade script
- Handles version migrations and schema updates

### `/js/`
**Purpose**: JavaScript functionality

#### `main.js`
- Client-side functionality for the plugin
- Handles dynamic interactions and UI enhancements

### `/lang/`
**Purpose**: Internationalization and language strings

#### `/lang/en/local_participant.php`
**English Language Strings**:
- Plugin interface text
- Form labels and messages
- Error messages and notifications

#### `/lang/ar/local_participant.php`
**Arabic Language Strings**:
- Arabic translations of all English strings
- RTL language support

### `/templates/`
**Purpose**: Mustache template files

#### `view_requests.mustache`
- Template for displaying the requests list
- Handles request data presentation
- Provides structure for filtering and pagination

## File Dependencies

### Core Dependencies
```
index.php
├── classes/form/requests_filter_form.php
├── classes/simple_workflow_manager.php
└── js/main.js

add_request.php
├── classes/form/request_form.php
└── classes/simple_workflow_manager.php

actions/*.php
└── classes/simple_workflow_manager.php
```

### Database Dependencies
```
All files depend on:
├── db/access.php (capabilities)
├── db/install.xml (table structure)
└── db/upgrade.php (version management)
```

### Language Dependencies
```
All user-facing files depend on:
├── lang/en/local_participant.php
└── lang/ar/local_participant.php
```

## Integration Points

1. **Moodle Core**: Integrates with Moodle's capability system, form API, and database layer
2. **External Themes**: Uses theme JavaScript files for CSV export and custom dialogs
3. **Status Plugin**: Relies on `local/status` plugin for workflow capabilities
4. **Course System**: Validates against Moodle's course table 