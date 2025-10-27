# File Structure Documentation - Status Plugin

## Overview

This document provides a comprehensive overview of every file and directory in the Status Plugin, explaining their purpose, functionality, and relationships.

## Root Directory Files

### Core Plugin Files

#### `version.php` (299B, 9 lines)
**Plugin Version Information**
- Defines plugin component (`local_status`)
- Current version: 2025070803 (PARTICIPANTS WORKFLOW ADDED)
- Minimum Moodle requirement: 3.9+ (2020061500)
- Maturity level: STABLE
- Release information for tracking changes

#### `lib.php` (877B, 29 lines)  
**Core Plugin Functions**
- Contains `local_status_can_approve()` function
- Validates user approval permissions for workflow steps
- Supports capability-based and "any user" approval types
- Used by other plugins to check approval permissions

#### `settings.php` (2.3KB, 66 lines)
**Admin Settings Configuration**
- Creates admin category under Site Administration > Local plugins
- Defines main dashboard external page  
- Sets up management pages (workflows, steps, history)
- Configures global settings (audit, notifications, retention)
- Requires `moodle/site:config` capability

#### `index.php` (2.3KB, 63 lines)
**Main Entry Point**
- Redirects to new dashboard structure
- Handles legacy action URLs and redirects appropriately
- Authenticates users and checks permissions
- Routes to specific action handlers (workflow_actions, step_actions)
- Maintains backward compatibility

#### `styles.css` (5.0KB, 262 lines)
**Plugin Styling**
- Custom CSS for workflow dashboard interface
- Responsive design for workflow tables and forms
- Color-coded status indicators
- Bootstrap integration and overrides
- Button styles and spacing

#### `diagnostic_workflow.php` (5.2KB, 145 lines)
**Development Diagnostic Tool**
- CLI script for troubleshooting workflow issues
- Validates workflow types and steps
- Checks capability assignments
- Identifies database inconsistencies
- Usage: `php diagnostic_workflow.php`

### Documentation Files

#### `README.md` (14KB, 437 lines)
**Main Plugin Documentation**
- Comprehensive feature overview
- Installation and setup instructions
- Architecture explanation
- Quick start guide
- Department structure details

#### `WORKFLOW_SEPARATION_SUMMARY.md` (4.1KB, 117 lines)
**Workflow Separation Documentation**
- Explains workflow manager separation
- Documents class responsibilities
- Lists updated files and changes

#### `WORKFLOW_RENAMING_SUMMARY.md` (4.6KB, 123 lines)
**Renaming Summary Documentation**
- Documents class renaming from old structure
- New class names and responsibilities
- Integration updates for dependent plugins

#### `DYNAMIC_APPROVER_README.md` (13KB, 401 lines)
**Dynamic Approver System Documentation**
- Person-based approval workflows
- Approver management interface
- Database schema for approvers
- API usage examples

#### `DEPARTMENT_CAPABILITIES.md` (15KB, 345 lines)
**Department Structure Documentation**
- 8-department organizational structure
- 4-tier hierarchy definitions
- Arabic terminology standards
- Capability naming conventions

## Directory Structure

### `/db/` - Database Definitions

#### `access.php` (8.6KB, 260 lines)
**Capability Definitions**
- Defines all workflow-related capabilities
- Department-specific capability structure
- 8 departments × 4 tiers = 32 core capabilities
- System-level context assignments
- Default archetype mappings

#### `install.xml` (9.9KB, 160 lines)
**Database Schema Definition**
- Defines 6 core tables with relationships
- Primary keys, foreign keys, and indexes
- Field types and constraints
- Comments for documentation

**Tables Defined:**
- `local_status_type` - Workflow definitions
- `local_status` - Workflow steps
- `local_status_instance` - Active workflow instances  
- `local_status_history` - Action audit log
- `local_status_approvers` - Dynamic approvers
- `local_status_transition` - Step transition rules

#### `install.php` (16KB, 229 lines)
**Initial Data Population**
- Populates 9 workflow types on installation
- Creates department-specific workflow steps
- Inserts capability-based approval steps
- Handles both English and Arabic labels

#### `upgrade.php` (11KB, 221 lines)
**Database Upgrade Management**
- Handles schema migrations between versions
- Data migration for new features
- Backward compatibility maintenance
- Version-specific upgrade logic

### `/pages/` - User Interface Components

#### `dashboard.php` (2.4KB, 76 lines)
**Main Dashboard Interface**
- Tab-based interface for workflow management
- Integrates workflows, steps, and history views
- Handles tab switching and content rendering
- Uses renderer classes for output

#### `manage_approvers.php` (14KB, 343 lines)
**Approver Management Interface**
- Add, remove, and reorder step approvers
- Person-based approval configuration
- AJAX-enabled user search and assignment
- Real-time approver list updates

### `/pages/forms/` - Form Definitions

#### `step_form.php` (16KB, 377 lines)
**Workflow Step Form**
- Create and edit workflow steps
- Capability selection interface
- Approval type configuration (capability/user/any)
- Sequence position management
- Validation and error handling

#### `workflow_form.php` (6.8KB, 187 lines)
**Workflow Type Form**
- Create and edit workflow types
- Multilingual name input (English/Arabic)
- Plugin assignment selection
- Active/inactive state toggle
- Sort order configuration

### `/pages/renderers/` - Output Renderers

#### `steps_renderer.php` (25KB, 650 lines)
**Steps Display Renderer**
- Renders workflow steps table
- Drag-and-drop reordering interface
- Action buttons (edit, delete, activate)
- Approver display and management
- Color-coded status indicators

#### `workflows_renderer.php` (8.1KB, 219 lines)
**Workflows Display Renderer**
- Renders workflow types table
- Active/inactive state indicators
- Step count and management links
- Bulk operations interface
- Plugin assignment display

### `/pages/actions/` - Action Handlers

#### [Not directly listed but referenced]
**Action Processing Scripts**
- `workflow_actions.php` - Workflow CRUD operations
- `step_actions.php` - Step management operations
- Handle form submissions and redirects
- Session key validation
- Error handling and user feedback

### `/lang/` - Language Strings

#### `/lang/en/` - English Language Pack

Contains `local_status.php` with all English language strings:
- Plugin interface text
- Form labels and validation messages
- Status names and descriptions
- Admin interface strings

#### `/lang/ar/` - Arabic Language Pack

Contains Arabic translations of all English strings:
- RTL language support
- Arabic terminology for departments
- Localized error messages
- Cultural adaptations

### `/classes/` - Core Classes

#### `status_workflow_manager.php` (16KB, 454 lines)
**Status Workflow Manager**
- Handles workflow status transitions
- Approval and rejection processing
- History logging and audit trails
- User permission validation
- Workflow instance management

**Key Methods:**
- `approve_workflow()` - Process workflow approval
- `reject_workflow()` - Process workflow rejection
- `start_workflow()` - Initialize workflow instance
- `get_current_status()` - Retrieve current workflow state

#### `workflow_dashboard_manager.php` (33KB, 933 lines)
**Workflow Dashboard Manager**
- Handles workflow and step management
- CRUD operations for workflows and steps
- Approver management functionality
- Step positioning and reordering
- Workflow queries and filtering

**Key Methods:**
- `create_workflow_type()` - Create new workflow
- `create_workflow_step()` - Add workflow step
- `get_workflow_steps()` - Retrieve step list
- `add_step_approver()` - Assign step approver
- `reorder_steps()` - Update step sequence

## File Dependencies and Relationships

### Core Dependencies
```
version.php (required by Moodle core)
├── lib.php (core functions)
├── settings.php (admin interface)
└── index.php (entry point)
    └── pages/dashboard.php
        ├── classes/workflow_dashboard_manager.php
        ├── pages/renderers/workflows_renderer.php
        └── pages/renderers/steps_renderer.php
```

### Database Dependencies
```
db/install.xml (schema)
├── db/install.php (initial data)
├── db/upgrade.php (migrations)
└── db/access.php (capabilities)
```

### Language Dependencies
```
All UI files depend on:
├── lang/en/local_status.php
└── lang/ar/local_status.php
```

### Form Dependencies
```
pages/dashboard.php
├── pages/forms/workflow_form.php
└── pages/forms/step_form.php
    └── classes/workflow_dashboard_manager.php
```

## Integration Points

### External Plugin Integration
```
Other plugins integrate via:
├── classes/status_workflow_manager.php (status changes)
├── classes/workflow_dashboard_manager.php (management)
└── lib.php (permission checks)
```

### Moodle Core Integration
```
Plugin integrates with:
├── Moodle capability system (access control)
├── Database API (transactions and queries)
├── Form API (UI forms)
├── Language API (multilingual support)
└── Admin settings (configuration interface)
```

## File Usage Analysis

### Actively Used Files
- **Core functionality**: All files in `/classes/`, `/db/`, `/pages/`
- **User interface**: All files in `/pages/`, `/lang/`
- **Configuration**: `settings.php`, `version.php`
- **Entry points**: `index.php`

### Diagnostic/Development Files
- `diagnostic_workflow.php` - Development troubleshooting
- Documentation files - Developer guidance

### Style and Presentation
- `styles.css` - UI styling (actively used)
- Renderer files - Output formatting (actively used)

## Performance Considerations

### High-Usage Files
- `classes/status_workflow_manager.php` - Called on every status change
- `classes/workflow_dashboard_manager.php` - Used in admin interfaces
- `lib.php` - Permission checking function used frequently

### Database-Intensive Files
- `db/install.php` - Only during installation
- `db/upgrade.php` - Only during upgrades
- `pages/dashboard.php` - Loads workflow data

### Caching Considerations
- Language strings are cached by Moodle core
- Database queries should consider caching for performance
- Workflow step data could benefit from caching

## Security Model

### Permission-Controlled Files
- All files in `/pages/` require appropriate capabilities
- `settings.php` requires `moodle/site:config`
- Action handlers validate session keys

### Database Security
- Foreign key constraints prevent orphaned records
- Transaction protection in manager classes
- Input validation in form classes

This file structure provides a robust, scalable foundation for workflow management while maintaining clear separation of concerns and following Moodle development best practices. 