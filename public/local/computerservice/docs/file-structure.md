# File Structure Documentation

This document explains the purpose and functionality of every file and folder in the Computer Service plugin.

## 📁 Root Directory Files

### Core Files

#### `index.php` (Main Entry Point)
- **Purpose**: Main plugin interface with tabbed navigation
- **Functionality**: 
  - Provides 3 tabs: Request Devices, Manage Requests, Manage Devices
  - Handles form submissions for device requests
  - Includes role-based access control for different tabs
  - Integrates with Moodle's tab navigation system
- **Key Features**:
  - Request form processing with urgent detection
  - Tab-based navigation using Moodle's tabobject
  - Context and capability checking
  - JavaScript integration for enhanced UX

#### `version.php` (Plugin Metadata)
- **Purpose**: Defines plugin version and metadata
- **Contains**:
  - Plugin component name: `local_computerservice`
  - Version number: `2025061904`
  - Minimum Moodle requirement: `2022041900` (Moodle 4.0+)
  - Maturity level: `MATURITY_STABLE`
  - Release notes and changelog
- **Version History**: Tracks changes from v1.2.0 to current v1.3.1

#### `lib.php` (Core Functions)
- **Purpose**: Moodle integration functions
- **Functions**:
  - `local_computerservice_extend_settings_navigation()`: Adds plugin links to Moodle navigation
- **Integration**: Extends Moodle's settings navigation for users with management capabilities

#### `readme.md` (User Documentation)
- **Purpose**: Comprehensive user and developer documentation
- **Sections**: Overview, features, installation, database schema, workflow system, UI navigation, technical improvements
- **Audience**: End users, administrators, and developers

## 📁 Directory Structure

### `/actions/` - AJAX Request Handlers

#### `update_request_status.php`
- **Purpose**: Handles AJAX-based approval/rejection requests
- **Security Features**:
  - Session key validation
  - Capability checking
  - Input sanitization
- **Functionality**:
  - Processes approve/reject actions
  - Updates request status in workflow
  - Returns JSON responses for AJAX calls
  - Handles rejection note requirements

### `/classes/` - PHP Classes

#### Core Workflow Classes

##### `simple_workflow_manager.php`
- **Purpose**: Main workflow engine for request processing
- **Key Methods**:
  - `get_initial_status_id()`: Returns starting workflow status
  - `approve_request()`: Moves request forward in workflow
  - `reject_request()`: Handles rejection with notes
  - `can_user_manage_status()`: Capability checking
- **Features**:
  - Hard-coded workflow logic for reliability
  - Race condition prevention
  - Session key validation
  - Fallback capability system

#### `/classes/form/` - Moodle Forms

##### `request_form.php`
- **Purpose**: Form for submitting device requests
- **Fields**:
  - Course selection (with user enrollment filtering)
  - Device type selection (active devices only)
  - Number of devices needed
  - Required date
  - Optional comments
- **Validation**:
  - Required field validation
  - Date validation (must be future date)
  - Device availability checking

##### `filter_form.php`
- **Purpose**: Filtering form for request management
- **Filters**:
  - Course filter (enrolled courses only)
  - User filter (searchable user list)
  - Status filter (all workflow statuses)
  - Urgency filter (All requests, Urgent, Not Urgent)
- **Features**:
  - Dynamic course loading based on user enrollment
  - AJAX-compatible filtering
  - Reset functionality
  - Default "All requests" filter option

##### `device_form.php`
- **Purpose**: Form for adding new device types
- **Fields**:
  - English device name
  - Arabic device name
  - Initial status (active/inactive)
- **Validation**: Required field validation for both language variants

#### `/classes/output/` - Renderable Classes

##### `manage_requests.php`
- **Purpose**: Prepares data for request management table
- **Functionality**:
  - Fetches and formats request data
  - Applies filters from filter form
  - Prepares data for Mustache template
  - Handles language-specific device names
- **Data Processing**:
  - User name formatting
  - Course name resolution
  - Status color coding
  - urgent request highlighting

### `/pages/` - Page Controllers

#### `manage.php`
- **Purpose**: Request management page controller
- **Functionality**:
  - Initializes filter form
  - Processes filter parameters
  - Sets up renderable data for template
- **Integration**: Included by main index.php for manage tab

#### `device_management.php`
- **Purpose**: Device management page entry point
- **Features**:
  - Device list display
  - Add new device link
  - Device status toggle functionality
- **Access Control**: Requires `local/computerservice:manage_devices` capability

#### `add_device.php`
- **Purpose**: Add new device type page
- **Functionality**:
  - Device form display and processing
  - Bilingual device name handling
  - Database insertion
  - Success/error messaging

#### `manage_devices.php`
- **Purpose**: Device list and management page
- **Features**:
  - Displays all device types with both language variants
  - Toggle active/inactive status
  - Edit device information
  - Status indicators

#### `toggle_device.php`
- **Purpose**: AJAX handler for device status toggling
- **Functionality**:
  - Toggles device between active/inactive
  - Updates database
  - Redirects back to management page

### `/templates/` - Mustache Templates

#### `manage_requests.mustache`
- **Purpose**: Template for request management interface
- **Features**:
  - Responsive table layout
  - Color-coded status indicators
  - AJAX-enabled action buttons
  - Rejection note display
  - Export functionality
- **Components**:
  - Filter form integration
  - Request table with pagination
  - Action buttons (approve/reject)
  - Status badges
  - urgent request indicators

### `/db/` - Database Schema

#### `install.xml`
- **Purpose**: Database table definitions
- **Tables Defined**:
  - `local_computerservice_requests`: Main request storage
  - `local_computerservice_devices`: Device type definitions
- **Schema Features**:
  - Proper indexing for performance
  - Foreign key relationships
  - Field type optimization

#### `install.php`
- **Purpose**: Initial data setup after installation
- **Functionality**:
  - Inserts default device types
  - Sets up initial bilingual device entries
  - Ensures proper initial configuration

#### `upgrade.php`
- **Purpose**: Database schema upgrades between versions
- **Upgrade Path**: Handles version migrations from 2025061901 to 2025061904
- **Changes**:
  - Adds `rejection_note` and `approval_note` fields
  - Removes deprecated `unapprove_note` field
  - Fixes column naming issues

#### `access.php`
- **Purpose**: Capability definitions
- **Capabilities Defined**:
  - `local/computerservice:submitrequest`: Submit device requests
  - `local/computerservice:managerequests`: Manage and approve requests
  - `local/computerservice:manage_devices`: Manage device types
  - Workflow-specific capabilities for each approval step

### `/lang/` - Language Support

#### `/lang/en/local_computerservice.php`
- **Purpose**: English language strings
- **Contains**: All user-facing text in English
- **Categories**: Form labels, status messages, error messages, navigation

#### `/lang/ar/local_computerservice.php`
- **Purpose**: Arabic language strings
- **Contains**: All user-facing text in Arabic
- **Features**: Right-to-left text support, culturally appropriate translations

## 🔧 File Dependencies

### Core Dependencies
```
index.php
├── classes/form/request_form.php
├── classes/form/filter_form.php
├── classes/output/manage_requests.php
├── classes/simple_workflow_manager.php
└── pages/*.php
```

### Template Dependencies
```
manage_requests.mustache
├── classes/output/manage_requests.php (data provider)
└── JavaScript files for AJAX functionality
```

### Database Dependencies
```
install.xml → install.php → upgrade.php → access.php
```

## 📝 File Naming Conventions

- **Classes**: PascalCase (e.g., `simple_workflow_manager.php`)
- **Forms**: Suffix with `_form.php` (e.g., `request_form.php`)
- **Pages**: Descriptive names (e.g., `manage_devices.php`)
- **Templates**: `.mustache` extension (e.g., `manage_requests.mustache`)
- **Language files**: Match component name (e.g., `local_computerservice.php`)

## 🚀 Performance Considerations

- **AJAX Integration**: Reduces page loads in actions/
- **Lazy Loading**: Forms and templates loaded only when needed
- **Database Optimization**: Proper indexing in install.xml
- **Caching**: Moodle's built-in caching for language strings and templates 