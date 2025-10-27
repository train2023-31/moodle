# File Structure Guide

This document explains every file and directory in the Finance Services plugin.

## 📁 Root Directory Files

### `index.php` (156 lines)
**Purpose**: Main entry point and controller for the plugin
**Key Functions**:
- Tab-based navigation system (add, list, manage)
- Form handling for request submission
- Request filtering and display
- Integration with workflow manager

**Code Structure**:
```php
// Page setup and security
require_once(__DIR__ . '/../../config.php');
// Tab configuration
$maintabs = [...];
// Tab content handling
if ($currenttab === 'add') { ... }
```

### `version.php` (9 lines)
**Purpose**: Plugin metadata and version information
**Contains**:
- Plugin component name
- Version number (2025061901)
- Moodle compatibility requirements
- Maturity level and release information

### `settings.php` (43 lines)
**Purpose**: Admin settings configuration
**Features**:
- Creates admin category under Site Administration
- Configurable enable/disable toggle
- Path configuration settings
- External page links for management

### `readme.md` (280 lines)
**Purpose**: User-facing documentation
**Sections**:
- Feature overview
- Installation instructions
- File structure summary
- Workflow documentation
- Developer guidelines

## 📂 Directory Structure

### `/actions/`
Contains AJAX endpoints for dynamic interactions.

#### `update_request_status.php` (59 lines)
**Purpose**: AJAX handler for workflow status changes
**Security Features**:
- Session key validation
- Permission checking
- Race condition prevention
**JSON Response Format**:
```json
{
  "success": true,
  "message": "Status updated successfully",
  "new_status": "approved"
}
```

### `/classes/`
Core PHP classes following Moodle namespacing conventions.

#### `simple_workflow_manager.php` (257 lines)
**Purpose**: Central workflow management class
**Key Methods**:
```php
public static function get_initial_status_id()
public static function can_user_approve($status_id)
public static function approve_request($id, $note)
public static function reject_request($id, $note)
public static function has_rejection_note($status_id)
```
**Workflow Steps**:
- Initial (8) → Leader 1 (9) → Leader 2 (10) → Leader 3 (11) → Boss (12) → Approved (13)
- Rejection flow: Any step → Previous step or Rejected (14)

#### `/classes/form/`
Moodle formslib-based form classes.

##### `add_form.php` (117 lines)
**Purpose**: Request submission form
**Fields**:
- Course selection (searchable dropdown)
- Funding type selection
- Price requested (numeric input)
- Request notes (textarea)
- Date required (date picker)
- Clause selection (optional)

##### `filter_form.php` (81 lines)
**Purpose**: Request filtering form for list view
**Filters**:
- Course filter
- Funding type filter
- Status filter
- Clause filter

##### `clause_form.php` (101 lines)
**Purpose**: Clause management form
**Fields**:
- Clause name (English)
- Clause name (Arabic)
- Description fields
- Active status toggle

##### `funding_type_form.php` (72 lines)
**Purpose**: Funding type management form
**Fields**:
- Funding type name (English)
- Funding type name (Arabic)
- Description
- Active status

#### `/classes/output/`
Renderer classes for template integration.

##### `tab_list.php` (85 lines)
**Purpose**: Data preparation for list.mustache template
**Methods**:
```php
public function export_for_template(renderer_base $output)
```
**Data Processing**:
- Status color coding
- Language-aware field selection
- Action button generation
- Rejection note handling

#### `/classes/event/`
Moodle event classes for activity logging.

##### `request_created.php` (113 lines)
**Purpose**: Event triggered when new request is submitted
**Data Logged**:
- Request ID
- Course ID
- Funding type
- Price requested
- User ID

##### `request_status_changed.php` (121 lines)
**Purpose**: Event triggered when request status changes
**Data Logged**:
- Previous status
- New status
- Action performer
- Notes/comments

##### `fundingtype_*.php` (4 files, ~100 lines each)
**Events**: Created, Updated, Hidden, Restored
**Purpose**: Track funding type management actions

##### `clause_*.php` (4 files, ~100 lines each)
**Events**: Created, Updated, Hidden, Restored
**Purpose**: Track clause management actions

### `/db/`
Database schema and configuration files.

#### `install.xml` (70 lines)
**Purpose**: Database table definitions using XMLDB
**Tables Created**:
1. `local_financeservices` - Main requests table
2. `local_financeservices_funding_type` - Funding types
3. `local_financeservices_clause` - Clauses/terms

**Key Fields**:
```xml
<FIELD NAME="id" TYPE="int" LENGTH="10" SEQUENCE="true"/>
<FIELD NAME="course_id" TYPE="int" LENGTH="10" NOTNULL="true"/>
<FIELD NAME="status_id" TYPE="int" LENGTH="10" NOTNULL="true"/>
```

#### `upgrade.php` (322 lines)
**Purpose**: Database migration handling
**Version Upgrades**:
- Field additions
- Index creation
- Data migrations
- Table modifications

#### `install.php` (51 lines)
**Purpose**: Initial data population
**Sample Data**:
- Default funding types
- Sample clauses
- Initial status mappings

#### `access.php` (22 lines)
**Purpose**: Plugin capability definitions
**Capabilities**:
```php
'local/financeservices:view' => [
    'captype' => 'read',
    'contextlevel' => CONTEXT_SYSTEM,
]
'local/financeservices:manage' => [
    'captype' => 'write',
    'contextlevel' => CONTEXT_SYSTEM,
]
```

### `/lang/`
Internationalization support for bilingual functionality.

#### `/lang/en/local_financeservices.php` (146 lines)
**Purpose**: English language strings
**Categories**:
- Plugin information
- Form labels
- Button text
- Status messages
- Error messages

#### `/lang/ar/local_financeservices.php` (151 lines)
**Purpose**: Arabic language strings
**Features**:
- RTL text support
- Cultural adaptations
- Complete translation coverage

### `/pages/`
Individual page controllers for management functions.

#### `manage.php` (35 lines)
**Purpose**: Management dashboard with cards layout
**Features**:
- Bootstrap card layout
- Quick access links
- Statistics display

#### `manage_fundingtypes.php` (174 lines)
**Purpose**: CRUD interface for funding types
**Features**:
- Data table display
- Add/Edit/Delete actions
- Bulk operations
- Search and filtering

#### `manage_clauses.php` (210 lines)
**Purpose**: CRUD interface for clauses
**Features**:
- Tabular data display
- Inline editing
- Status management
- Validation

#### Management Action Pages:
- `add_fundingtype.php` (90 lines) - Add new funding type
- `edit_fundingtype.php` (71 lines) - Edit existing funding type
- `delete_fundingtype.php` (62 lines) - Delete funding type with confirmation
- `add_clause.php` (97 lines) - Add new clause
- `edit_clause.php` (123 lines) - Edit existing clause
- `delete_clause.php` (67 lines) - Delete clause with confirmation

### `/templates/`
Mustache template files for HTML rendering.

#### `list.mustache` (180 lines)
**Purpose**: Request list display template
**Features**:
- Responsive table layout
- Color-coded status indicators
- Action buttons based on permissions
- Export functionality
- AJAX integration

**Template Structure**:
```mustache
{{#requests}}
<tr class="status-{{status_class}}">
  <td>{{course}}</td>
  <td>{{funding_type}}</td>
  <td>{{price_requested}}</td>
  {{#can_approve}}
    <button class="btn btn-success approve-btn">Approve</button>
  {{/can_approve}}
</tr>
{{/requests}}
```

## 🔗 File Relationships

### Data Flow
```
index.php → forms → simple_workflow_manager → database
    ↓
templates ← output/tab_list ← database queries
```

### Event Flow
```
User Action → Form Submission → Event Creation → Database Update → Redirect
```

### Language Flow
```
current_language() → lang/[en|ar]/ → string selection → display
```

## 📊 File Size Analysis

**Largest Files**:
1. `upgrade.php` (322 lines) - Database migrations
2. `simple_workflow_manager.php` (257 lines) - Core logic
3. `manage_clauses.php` (210 lines) - Management interface
4. `list.mustache` (180 lines) - Display template

**Critical Files** (most frequently accessed):
1. `index.php` - Main entry point
2. `simple_workflow_manager.php` - Workflow operations
3. `update_request_status.php` - AJAX handling
4. Language files - String lookup

## 🔧 Development Guidelines

### Adding New Files
1. Follow Moodle naming conventions
2. Include proper license headers
3. Use appropriate namespacing
4. Document public methods

### Modifying Existing Files
1. Update version number in `version.php`
2. Add upgrade steps in `upgrade.php` if needed
3. Update language strings
4. Test in both languages

### File Organization Rules
- **Controllers**: Root directory or `/pages/`
- **Models**: `/classes/`
- **Views**: `/templates/`
- **Configuration**: `/db/`
- **Localization**: `/lang/`
- **AJAX**: `/actions/`

This structure ensures maintainability and follows Moodle plugin standards. 