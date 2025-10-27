# File Structure Documentation

This document explains the purpose and functionality of every file and directory in the Residence Booking plugin.

## Root Directory Files

### `index.php` (8.8KB, 216 lines)
**Main Entry Point**
- Contains the primary user interface with tabbed navigation
- **Tabs**: Apply (submit requests), Manage (approve/reject), Lookups (admin management)
- Integrates with local_status workflow engine (type_id = 3)
- Handles form processing, request management, and admin functions
- Requires login and appropriate capabilities
- Includes CSS and JavaScript dependencies

### `version.php` (1.5KB, 38 lines)
**Plugin Version Information**
- Defines plugin component, version number, and Moodle requirements
- Contains detailed version history with upgrade notes
- Current version: 2025081200 (v0.6-audit-fields)
- Maturity level: ALPHA
- Requires Moodle 4.0+

### `settings.php` (2.7KB, 65 lines)
**Admin Settings Configuration**
- Creates admin category under Site Administration
- Defines plugin enable/disable setting
- Role selection for default user permissions
- External page links for management functions
- Requires admin capabilities for access

### `status.php` (1.4KB, 42 lines)
**Status Management Page**
- Handles status updates for booking requests
- Integrates with workflow system for status transitions
- Provides AJAX endpoints for status changes

### `view_requests.php` (1.0KB, 29 lines)
**Request Viewing Interface**
- Displays booking requests in tabular format
- Includes filtering and pagination capabilities
- Uses Mustache templating for rendering

### `styles.css` (0KB, 0 lines)
**Custom Styling**
- Currently empty placeholder for plugin-specific styles
- Ready for custom CSS implementations

## Directory Structure

### `/ajax/`
Contains AJAX endpoint files for dynamic functionality.

#### `guest_search.php` (1.7KB, 59 lines)
**Guest Autocomplete Service**
- Provides AJAX endpoint for guest name autocomplete
- Searches existing guest records
- Returns JSON formatted results
- Used by the booking form for guest selection

### `/amd/` (Asset Module Definition)
JavaScript modules following Moodle's AMD pattern.

#### `/src/`
Source JavaScript files for development.

##### `guest_autocomplete.js` (2.3KB, 80 lines)
**Guest Autocomplete Module**
- Implements real-time guest search functionality
- Provides autocomplete dropdown for guest names
- Uses AJAX calls to guest_search.php
- AMD module pattern for Moodle integration

#### `/build/`
Compiled/minified JavaScript files for production.

##### `guest_autocomplete.min.js` (981B, 31 lines)
**Minified Guest Autocomplete**
- Production-ready minified version
- Includes source map for debugging

##### `guest_autocomplete.min.js.map` (1.4KB, 1 line)
**Source Map**
- Maps minified code back to source for debugging

### `/classes/`
PHP classes following Moodle's autoloading conventions.

#### `simple_workflow_manager.php` (7.1KB, 183 lines)
**Workflow Management Class**
- Manages the approval workflow states
- Defines status constants (INITIAL, LEADER1_REVIEW, etc.)
- Handles status transitions and capabilities
- Integrates with local_status plugin
- Provides workflow progression logic
- **Rejection Behavior**: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review

#### `/form/`
Moodle form classes extending moodleform.

##### `residencebooking_form.php` (7.9KB, 209 lines)
**Main Booking Form**
- Extends moodleform for request submission
- Defines form fields: guest name, service number, dates, etc.
- Includes validation rules and processing logic
- Populates dropdowns from database lookups
- Handles form submission and data storage

#### `/output/`
Output classes for rendering data.

##### `manage_requests_table.php` (7.5KB, 165 lines)
**Request Management Table**
- Renders booking requests in tabular format
- Provides filtering and pagination functionality
- Includes action buttons (approve, reject, delete)
- Integrates with workflow manager for status handling
- Exports data formatting capabilities

### `/db/`
Database-related files for plugin installation and upgrades.

#### `install.xml` (4.5KB, 78 lines)
**Database Schema Definition**
- Defines database tables structure
- **Tables**:
  - `local_residencebooking_request`: Main booking requests
  - `local_residencebooking_types`: Accommodation types (multilingual)
  - `local_residencebooking_purpose`: Booking purposes (multilingual)
- Includes foreign key relationships and indexes

#### `install.php` (3.2KB, 96 lines)
**Installation Script**
- Runs during plugin installation
- Creates initial data and default records
- Sets up required configurations
- Initializes workflow status records

#### `upgrade.php` (14KB, 293 lines)
**Database Upgrade Script**
- Handles database schema changes between versions
- Migration scripts for each version
- Data transformation during upgrades
- Ensures backward compatibility

#### `access.php` (1.6KB, 44 lines)
**Capability Definitions**
- Defines plugin-specific capabilities
- Sets default capability assignments to roles
- Controls access to different plugin features

#### `services.php` (416B, 12 lines)
**Web Service Definitions**
- Defines external web service functions
- Currently minimal implementation
- Ready for API expansion

### `/lang/`
Language string files for internationalization.

#### `/en/`
English language strings.

##### `local_residencebooking.php` (6.4KB, 123 lines)
**English Language Strings**
- All English text used in the plugin
- Form labels, messages, navigation items
- Error messages and confirmations
- Admin interface strings

#### `/ar/`
Arabic language strings.

##### `local_residencebooking.php` (7.7KB, 126 lines)
**Arabic Language Strings**
- Complete Arabic translation
- RTL (Right-to-Left) text support
- Cultural adaptation of terms
- Maintains same string keys as English

### `/lib/`
External library files and dependencies.

#### Select2 Library Files
- `select2.full.js` (116B, 2 lines): Select2 loader
- `select2.full.min.js` (75KB, 2 lines): Full Select2 library
- `select2.js` (328B, 9 lines): Custom Select2 integration
- `select2.min.css` (16KB, 1 line): Select2 styling

### `/pages/`
Individual page files for specific administrative functions.

#### `manage.php` (2.4KB, 61 lines)
**General Management Page**
- Administrative dashboard for plugin management
- Links to various admin functions
- Overview of system status

#### `manage_purposes.php` (10KB, 244 lines)
**Purpose Management Interface**
- CRUD operations for booking purposes
- Multilingual field management (English/Arabic)
- Soft delete functionality
- Filtering and search capabilities

#### `manage_types.php` (10KB, 241 lines)
**Accommodation Types Management**
- CRUD operations for accommodation types
- Multilingual support for type names
- Soft delete implementation
- Administrative controls

### `/templates/`
Mustache templates for rendering HTML output.

#### `view_requests.mustache` (8.5KB, 192 lines)
**Request Viewing Template**
- Renders booking requests table
- Includes filtering controls
- Pagination support
- Action buttons for each request
- Responsive design elements

## File Relationships

### Data Flow
1. **User Input**: `index.php` → `residencebooking_form.php`
2. **Storage**: Form → Database tables
3. **Management**: `manage_requests_table.php` → `view_requests.mustache`
4. **Workflow**: `simple_workflow_manager.php` ↔ local_status plugin
5. **AJAX**: `guest_autocomplete.js` → `guest_search.php`

### Dependencies
- **Moodle Core**: All files depend on Moodle framework
- **local_status**: Workflow integration dependency
- **Select2**: Enhanced dropdown functionality
- **Theme Stream**: Custom dialog and export features

## File Naming Conventions

- **Classes**: CamelCase with namespace `local_residencebooking`
- **Database**: Lowercase with underscores
- **Templates**: Lowercase with underscores, `.mustache` extension
- **Language**: `local_residencebooking.php` in each language folder
- **JavaScript**: Lowercase with underscores, AMD pattern 