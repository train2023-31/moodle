# Plugin File Structure

This document explains the organization and purpose of each file and directory in the External Lecturer Management plugin.

## Root Directory Files

### `version.php`
**Purpose**: Plugin metadata and version information
**Contents**:
- Plugin component name (`local_externallecturer`)
- Version number (2025081400)
- Minimum Moodle version requirement (3.11+)
- Release version (1.6.1)

### `index.php`
**Purpose**: Main entry point and controller for the plugin
**Functionality**:
- Handles pagination for both lecturers and courses
- Fetches data from database tables
- Manages session state for user preferences
- Renders the main interface using Mustache templates
- Integrates with Moodle's PAGE framework

## Directory Structure

### `/actions/` - CRUD Operations
Contains PHP files that handle AJAX requests for data manipulation:

#### `addlecturer.php`
- **Purpose**: Handles adding new external lecturers
- **Method**: POST requests with JSON responses
- **Validation**: Uses Moodle's parameter validation functions
- **Database**: Inserts records into `externallecturer` table

#### `editlecturer.php`
- **Purpose**: Updates existing lecturer information
- **Method**: POST requests with lecturer ID
- **Functionality**: Modifies records in `externallecturer` table

#### `deletelecturer.php`
- **Purpose**: Removes lecturer records
- **Safety**: Includes validation to prevent unauthorized deletions
- **Cascade**: Handles related course enrollment cleanup

#### `deletecourse.php`
- **Purpose**: Removes lecturer-course associations
- **Table**: Operates on `externallecturer_courses` table
- **Updates**: Decrements course count for affected lecturers

#### `addenrollment.php`
- **Purpose**: Enrolls lecturers in courses with cost tracking
- **Validation**: Checks for duplicate enrollments
- **Database**: Inserts into `externallecturer_courses` table

### `/classes/` - PHP Classes

#### `/classes/output/`
Contains rendering classes following Moodle's output standards:

**`renderer.php`**
- **Class**: `local_externallecturer\output\renderer`
- **Extends**: `plugin_renderer_base`
- **Methods**: 
  - `render_main($data)`: Renders main template with data
  - `is_selected_perpage($perpage_value)`: Helper for pagination

**`main.php`**
- **Purpose**: Core output class (26 lines)
- **Functionality**: Defines main renderable object

#### `/classes/event/` and `/classes/api/`
- **Status**: Currently empty directories
- **Future Use**: Reserved for event handling and API classes

### `/db/` - Database Definitions

#### `install.xml`
**Purpose**: Database schema definition for plugin installation
**Tables Defined**:
1. **`externallecturer`**: Lecturer profile storage
   - Fields: id, name, age, specialization, organization, degree, passport, courses_count
   - Keys: Primary key on id

2. **`externallecturer_courses`**: Lecturer-course relationships
   - Fields: id, lecturerid, courseid, cost
   - Keys: Primary key on id, foreign keys to lecturer and course tables

#### `upgrade.php`
- **Purpose**: Handles database schema updates during version upgrades
- **Functions**: Contains upgrade logic for database migrations

### `/templates/` - Mustache Templates

#### `externallecturer.mustache` (161 lines)
**Purpose**: Main interface template
**Features**:
- Tabbed interface for lecturers and course enrollments
- Data tables with pagination
- Dual modal trigger buttons (Add External Lecturer, Add Resident Lecturer)
- CSV export functionality
- Bilingual text content (Arabic/English)
- Audit trail columns (Created By, Last Modified) in lecturers table
- Includes rendering of both form templates at end of file

#### `form_externallecturer.mustache` (72 lines)
**Purpose**: External visitor lecturer addition/editing form
**Fields**: Name, age, specialization, organization, degree, passport, nationality
**Features**: Oracle database autocomplete based on name search, readonly passport during creation
**Lecturer Type**: Automatically sets lecturer_type to "external_visitor"
**Styling**: Bootstrap-compatible form elements

#### `form_residentlecturer.mustache` (102 lines)
**Purpose**: Resident lecturer addition form for permanent local staff
**Fields**: Civil number, name, age, specialization, organization, degree, passport, nationality
**Features**: Oracle DUNIA integration via civil number search, auto-populates name and nationality from database
**Search Functionality**: Civil number search with loading indicator and auto-population
**Lecturer Type**: Automatically sets lecturer_type to "resident"
**Oracle Integration**: Uses AJAX call to Oracle database for data validation and auto-fill

#### `edit_modal.mustache` (43 lines)
**Purpose**: Modal dialog for editing lecturer information
**Features**: Pre-populated form fields, AJAX submission

#### `form_enrolllecturer.mustache` (32 lines)
**Purpose**: Course enrollment form
**Elements**: Course dropdown, cost input, lecturer selection

### `/js/` - JavaScript Files

#### `main.js` (241 lines)
**Purpose**: Frontend functionality and AJAX operations
**Features**:
- Tab switching functionality
- Modal management
- Form submissions via AJAX
- Dynamic table updates
- CSV export integration
- Error handling and user feedback

### `/lang/` - Internationalization

#### `/lang/en/local_externallecturer.php`
**Purpose**: English language strings
**Strings**:
- `pluginname`: 'External Lecturer Management'
- `addlecturer`: 'Add External Lecturer'
- `enrollcourse`: 'Enroll to Course'
- `course`: 'Course'
- `cost`: 'Cost'

#### `/lang/ar/` 
**Purpose**: Arabic language support (directory structure present)

## Dependencies

### External JavaScript Libraries
The plugin depends on external JavaScript files:
- `/theme/stream/js/export_csv.js`: CSV export functionality
- `/theme/stream/js/custom_dialog.js`: Dialog management

### Moodle Core Integration
- Uses Moodle's database abstraction layer (`$DB`)
- Integrates with Moodle's page framework (`$PAGE`)
- Follows Moodle's template rendering system
- Uses Moodle's parameter validation functions
- Implements Moodle's notification system

## Coding Standards

The plugin follows Moodle coding standards:
- Proper namespace usage for classes
- Standard Moodle function naming conventions
- Database interaction through Moodle's `$DB` object
- Template rendering using Mustache engine
- Proper parameter validation and sanitization 