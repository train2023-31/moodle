# File Structure Documentation

This document explains the purpose and functionality of every file in the Student Reports plugin.

## Root Directory Files

### Core Plugin Files

#### `version.php`
**Purpose**: Plugin metadata and version information
- **Component**: `local_reports`
- **Version**: 2025041702
- **Requires**: Moodle 4.0 or later (2022041900)
- **Maturity**: MATURITY_ALPHA
- **Release**: v0.1

#### `lib.php`
**Purpose**: Main library functions for the plugin
- **Function**: `local_reports_extend_navigation_course()`
  - Adds plugin link to course navigation
  - Checks `local/reports:viewall` capability
  - Creates navigation node for the reports section

### Main Interface Files

#### `index.php`
**Purpose**: Main course-level reports interface
- **Features**:
  - Course-specific report management
  - Tab interface (pending/approved reports)
  - AJAX form integration
  - Workflow management
  - Requires `local/reports:manage` capability
- **JavaScript Dependencies**:
  - `/theme/stream/js/modal.js`
  - `/theme/stream/js/openreportform.js`
  - `/theme/stream/js/approve.js`
  - `/theme/stream/js/previewreport.js`

#### `allreports.php`
**Purpose**: System-wide reports overview
- **Features**:
  - View all reports across all courses
  - Course and student name filtering
  - Tab interface for pending/approved reports
  - Requires `local/status:reports_workflow_step3` capability
- **Access Level**: System-wide administration

### AJAX Endpoints

#### `form_ajax.php`
**Purpose**: Handles report form submission and editing
- **Size**: 14KB, 338 lines
- **Functionality**:
  - Process form submissions
  - Handle report creation and updates
  - Return JSON responses
  - Form validation and error handling

#### `approve_ajax.php`
**Purpose**: Handles report approval workflow
- **Size**: 5.6KB, 164 lines
- **Functionality**:
  - Process approval requests
  - Update report status
  - Handle workflow transitions
  - Send notifications

#### `disapprove_ajax.php`
**Purpose**: Handles report disapproval workflow
- **Size**: 5.8KB, 171 lines
- **Functionality**:
  - Process disapproval requests
  - Handle rejection reasons
  - Update report status
  - Workflow management

## Directory Structure

### `/templates/` Directory
**Purpose**: Mustache templates for HTML rendering

#### `reports_list.mustache`
- **Size**: 2.2KB, 72 lines
- **Purpose**: Template for displaying lists of reports
- **Usage**: Renders report tables and lists

#### `report_modal_pdf.mustache`
- **Size**: 3.5KB, 150 lines
- **Purpose**: PDF modal template (vertical layout)
- **Usage**: Generate PDF reports in standard format

#### `report_modal_pdf_Horizntal.mustache`
- **Size**: 4.3KB, 182 lines
- **Purpose**: PDF modal template (horizontal layout)
- **Usage**: Generate PDF reports in landscape format

### `/lang/` Directory
**Purpose**: Multi-language support

#### `/lang/en/local_reports.php`
- **Size**: 1.8KB, 40 lines
- **Purpose**: English language strings
- **Key Strings**:
  - Plugin name and navigation
  - Form field labels
  - Action buttons and messages
  - Workflow status text

#### `/lang/ar/local_reports.php`
- **Size**: 2.4KB, 41 lines
- **Purpose**: Arabic language strings
- **Features**: RTL language support

### `/assets/` Directory
**Purpose**: Static assets and media files

#### `image1.png` & `image2.png`
- **Size**: 2.2MB each
- **Purpose**: Plugin assets (possibly logos or UI elements)
- **Usage**: Referenced in templates or forms

### `/classes/` Directory
**Purpose**: PHP classes and object-oriented code

#### `/classes/form/report_form.php`
- **Size**: 5.5KB, 138 lines
- **Purpose**: Main report form class
- **Extends**: `\moodleform`
- **Features**:
  - Dynamic form generation
  - Student information display
  - Report field definitions
  - Validation and processing

#### `/classes/output/reports_list.php`
- **Size**: 613B, 29 lines
- **Purpose**: Output renderer for reports lists
- **Usage**: Handles template rendering and data preparation

### `/db/` Directory
**Purpose**: Database definitions and capabilities

#### `install.xml`
- **Size**: 2.5KB, 52 lines
- **Purpose**: Database schema definition
- **Tables**: Defines `local_reports` table structure
- **Fields**:
  - `id`, `userid`, `courseid`
  - `futureexpec`, `dep_op`, `seg_path`, `researchtitle`
  - `status_id`, `type_id`
  - `timecreated`, `timemodified`
  - `createdby`, `modifiedby`

#### `access.php`
- **Size**: 622B, 27 lines
- **Purpose**: Plugin capabilities definition
- **Capabilities**:
  - `local/reports:manage` - Create and manage reports
  - `local/reports:viewall` - View all reports
- **Archetypes**: 
  - Manager and editing teacher roles

## File Dependencies

### JavaScript Dependencies
- Theme-specific JS files (modal, form handling, approval)
- AJAX communication with backend endpoints

### PHP Dependencies
- Moodle core libraries (`config.php`, `formslib.php`)
- Grade query libraries
- User and course management functions

### Template Dependencies
- Mustache templating engine
- Output renderer classes
- Language string system

## Development Notes

- All AJAX endpoints return JSON responses
- Form validation follows Moodle standards
- Database operations use Moodle DML
- Capability checks are enforced throughout
- Multi-language support is implemented
- Templates support both RTL and LTR layouts 