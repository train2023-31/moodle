# File Structure Documentation

This document provides a comprehensive breakdown of all files in the Annual Plans plugin, explaining their purpose and functionality.

## Root Directory Structure

```
local/annualplans/
├── DOCUMENTATION/          # Plugin documentation (this folder)
├── ajax/                  # AJAX endpoint files
├── classes/               # PHP classes and forms
├── db/                    # Database schema and upgrade scripts
├── js/                    # JavaScript files
├── lang/                  # Language files
├── templates/             # Mustache template files
├── add_course.php         # Course creation interface
├── approvedDisplay.php    # Approved courses display
├── index.php              # Main plugin entry point
├── lib.php                # Core plugin functions
├── manage_codes.php       # Course code management
├── manage_levels.php      # Level management
├── settings.php           # Plugin settings configuration
└── version.php            # Plugin version information
```

## Core Files

### version.php
**Purpose**: Plugin metadata and version information
**Key Components**:
- Plugin component name (`local_annualplans`)
- Version number (2025071500)
- Minimum Moodle version requirement (3.10)
- Maturity level (STABLE)
- Release information

### lib.php
**Purpose**: Core plugin functions and hooks
**Key Functions**:
- `local_annualplans_extend_navigation()`: Adds plugin to Moodle navigation
**Features**:
- Navigation integration for users with appropriate capabilities
- Dashboard link creation
- Icon and URL configuration

### settings.php
**Purpose**: Plugin administration settings configuration
**Key Components**:
- Admin category creation for "Annual Plans"
- Admin category creation for "Levels"
- Settings page with enable/disable toggle
- Path configuration settings
- External page links for:
  - Main plugin management
  - Course codes management
  - Levels management

### index.php
**Purpose**: Main plugin entry point and dashboard
**Key Components**:
- Required includes for Moodle libraries
- User authentication and capability checks
- JavaScript includes (main.js and custom_dialog.js)
- Controller instantiation and request handling
**Dependencies**:
- `classes/table.php`
- `classes/control_form.php`
- `classes/AnnualPlansController.php`
- `classes/CourseManager.php`

## Page Files

### add_course.php
**Purpose**: Course creation and editing interface
**Features**:
- Course creation form
- Dynamic shortname generation via JavaScript
- Category and level code integration
- AJAX-powered form interactions
- Real-time validation

**Key Functionality**:
- Automatic shortname generation based on codes
- Category and level ID assignment
- Form validation and submission
- Integration with course code system

### manage_codes.php
**Purpose**: Course code management system
**Features**:
- Course code CRUD operations
- Code deletion with confirmation
- Session key validation for security
- Redirect handling with notifications
- Admin-only access control

**Key Operations**:
- Create new course codes
- Edit existing codes
- Delete codes with proper validation
- Display code listings

### manage_levels.php
**Purpose**: Course level management
**Features**:
- Level creation and management
- Hierarchical level organization
- Admin access control

### approvedDisplay.php
**Purpose**: Display approved courses and plans
**Features**:
- Filtered display of approved courses
- Course status visualization
- Export functionality
- Search and filtering capabilities

## Classes Directory

### classes/AnnualPlansController.php
**Purpose**: Main controller class handling business logic
**Key Components**:
- Request handling and routing
- Form processing
- Course management operations
- Database interactions
- User interface coordination

**Key Methods**:
- `handle_request()`: Main request dispatcher
- `process_forms()`: Form submission handling
- `delete_course_row()`: Course deletion logic

### classes/CourseManager.php
**Purpose**: Course-specific business logic and data management
**Features**:
- Course CRUD operations
- Course validation
- Beneficiary management
- Status tracking
- Database abstraction for course operations

### classes/control_form.php
**Purpose**: Form definitions and validation
**Features**:
- Upload form for Excel files
- Filter form for data searching
- Course addition forms
- Annual plan management forms
- Form validation and processing

### classes/table.php
**Purpose**: Data table presentation and management
**Features**:
- Sortable data tables
- Pagination support
- Filtering capabilities
- Export functionality
- Custom column formatting

## Database Directory (db/)

### db/install.xml
**Purpose**: Database schema definition
**Key Tables**:
1. **local_annual_plan_course**: Main course data
   - Course identification and metadata
   - Scheduling and duration information
   - Beneficiary tracking
   - Approval status
   - Financial information

2. **local_annual_plan**: Annual plan container
   - Plan identification
   - Year association
   - Status tracking
   - Description and metadata

3. **Additional tables** for:
   - Course codes and categories
   - Levels and hierarchies
   - User enrollments
   - Beneficiary management

### db/access.php
**Purpose**: Capability definitions and permissions
**Capabilities**:
- `local/annualplans:manage`: Full management access
- `local/annualplans:view`: Read-only access

**Default Permissions**:
- Managers: Full access to all capabilities
- System-level context requirements

### db/install.php
**Purpose**: Post-installation database setup
**Features**:
- Initial data population
- Default configuration setup
- Table relationships establishment

### db/upgrade.php
**Purpose**: Database schema upgrades between versions
**Features**:
- Version-specific upgrade routines
- Data migration scripts
- Schema modification handling
- Backward compatibility maintenance

### db/caches.php
**Purpose**: Cache definitions for performance optimization
**Features**:
- Cache configuration
- Performance optimization settings

## AJAX Directory

### ajax/get_employees.php
**Purpose**: Employee data retrieval
**Features**:
- Employee list retrieval
- Role-based filtering
- JSON response format

### ajax/get_beneficiaries.php
**Purpose**: Beneficiary data management
**Features**:
- Beneficiary list retrieval
- Course-specific beneficiary data

### ajax/get_roles.php
**Purpose**: Role data retrieval
**Features**:
- System role information
- Permission-based filtering

### ajax/save_beneficiaries.php
**Purpose**: Beneficiary data persistence
**Features**:
- Batch beneficiary saving
- Validation and error handling
- Transaction management

### ajax/get_annual_plan_year.php
**Purpose**: Annual plan year data
**Features**:
- Year-based plan retrieval
- Plan filtering by year

### ajax/get_code.php
**Purpose**: Course code retrieval
**Features**:
- Code lookup by ID
- Code validation

### ajax/get_code_description.php
**Purpose**: Code description retrieval
**Features**:
- Detailed code information
- Description and metadata

### ajax/get_type_id.php
**Purpose**: Type ID resolution
**Features**:
- Type identification
- ID mapping and resolution

## Templates Directory

### templates/courses_table.mustache
**Purpose**: Main courses data table template
**Features**:
- Responsive table layout
- Sortable columns
- Action buttons
- Status indicators
- Pagination controls

**Key Components**:
- Course listing with full metadata
- Filter controls
- Export options
- Bulk action support

### templates/approved_table.mustache
**Purpose**: Approved courses display template
**Features**:
- Approved course visualization
- Status-specific formatting
- Read-only interface
- Export capabilities

### templates/hidden_fields.mustache
**Purpose**: Hidden form field template
**Features**:
- Form state management
- Security token handling
- Session data preservation

## JavaScript Directory

### js/main.js
**Purpose**: Main JavaScript functionality
**Features**:
- AJAX request handling
- Form validation
- Dynamic UI updates
- Event handling
- User interaction management

**Key Functions**:
- Form submission handlers
- Data table interactions
- Filter application
- Real-time validation
- Progress indicators

## Language Directory

### lang/en/local_annualplans.php
**Purpose**: English language strings
**Key String Categories**:
- Interface labels and buttons
- Form field labels
- Error messages
- Success notifications
- Help text and descriptions

**Key Strings**:
- Plugin name and descriptions
- Form labels and instructions
- Status messages
- Error handling text
- Administrative interface text

### lang/ar/ (Directory)
**Purpose**: Arabic language support
**Features**:
- Full Arabic translation
- RTL text support
- Cultural localization

## File Dependencies and Relationships

### Core Dependencies
```
index.php
├── classes/AnnualPlansController.php
├── classes/CourseManager.php
├── classes/table.php
├── classes/control_form.php
└── js/main.js
```

### Form Dependencies
```
add_course.php
├── classes/control_form.php
├── ajax/get_code.php
├── ajax/get_type_id.php
└── ajax/get_annual_plan_year.php
```

### Template Dependencies
```
Controller Classes
├── templates/courses_table.mustache
├── templates/approved_table.mustache
└── templates/hidden_fields.mustache
```

### Database Dependencies
```
All PHP Files
├── db/install.xml (schema)
├── db/access.php (permissions)
└── db/upgrade.php (migrations)
```

## File Interaction Flow

1. **Entry Point**: `index.php` loads core dependencies
2. **Authentication**: Capability checks via `db/access.php`
3. **Controller**: `AnnualPlansController.php` handles requests
4. **Forms**: `control_form.php` manages form interactions
5. **Data**: `CourseManager.php` handles business logic
6. **Presentation**: Mustache templates render UI
7. **Client-side**: `main.js` provides interactive features
8. **AJAX**: Various AJAX endpoints handle async operations
9. **Language**: Language files provide localized strings

This file structure provides a comprehensive foundation for managing annual training plans with proper separation of concerns, security, and extensibility. 