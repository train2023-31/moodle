# Participant Management Plugin

## Overview

The **Participant Management Plugin** (`local_participant`) is a Moodle local plugin designed to manage participant requests with a sophisticated workflow approval system. This plugin allows users to create, view, and manage requests for both internal and external participants in courses.

## Key Features

- **Request Management**: Add and view participant requests
- **Workflow System**: Multi-level approval process with leader and boss reviews
- **Bilingual Support**: English and Arabic language support
- **Permission System**: Role-based access control with specific capabilities
- **User Interface**: Tab-based navigation with filtering capabilities
- **Database Integration**: Custom tables for storing request data
 - **Audit Trail**: Tracks creator and last modifier with timestamps

## Plugin Information

- **Component**: `local_participant`
 - **Version**: 2.2 (2025081800)
- **Maturity**: Stable
- **Moodle Requirements**: 4.1 or later
- **Release Notes**: Completed workflow system migration

## Quick Start

1. **View Requests**: Navigate to `/local/participant/index.php`
2. **Add Request**: Navigate to `/local/participant/add_request.php`
3. **Permissions**: Ensure users have appropriate capabilities (`local/participant:view`, `local/participant:addrequest`)

## Main Capabilities

- `local/participant:view` - View participant requests
- `local/participant:addrequest` - Add new participant requests

## File Structure Overview

```
local/participant/
├── Documentation/          # Plugin documentation (this folder)
├── actions/               # Action handlers for workflow operations
├── classes/              # PHP classes and forms
├── db/                   # Database definitions and upgrades
├── js/                   # JavaScript files
├── lang/                 # Language strings (en, ar)
├── templates/            # Mustache templates
├── add_request.php       # Add new request page
├── index.php            # Main view requests page
└── version.php          # Plugin version information
```

For detailed information about each component, see the specific documentation files in this folder. 

## Recent changes

- 2025-09-03: **JavaScript Integration Fixed** - AJAX URLs updated to work correctly from any plugin context, enabling seamless integration with requestservices plugin
- 2025-08-18: **Workflow System Updated** - Rejection behavior modified so that rejections from Leader 2, 3, and Boss levels now return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status
- 2025-08-18: Upgrade step 2025081800 added. This upgrade inserts three new request-type records (First Period, Second Period, Third Period) for the Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor family. The upgrade also removes the legacy single lecturer entry (previously present as id=4) to avoid duplication. Run the Moodle admin Notifications page to apply this change.

Notes for administrators:
- If you have existing custom request types that match the old lecturer entry, verify duplicates after running the upgrade.
- The install routine (`db/install.php`) was adjusted to no longer include a commented legacy lecturer row and now relies on the new period-based types.

Runtime behaviour change (hardcoded ID):
- The application now treats participant type id `7` as the canonical External Lecturer type at runtime. This is a hardcoded change in the plugin code (front-end and back-end). The plugin will no longer interpret the database field `calculation_type = 'dynamic'` or the legacy id `5` as the external-lecturer trigger.

Recommended action:
- If your database currently contains an External Lecturer record with id `5` (or requests using participant_type_id = 5), migrate those rows to id `7` to preserve behaviour. A safe approach:
	1. Backup your database.
	2. Insert a new record in `local_participant_request_types` for id=7 copying the data from id=5.
	3. Update all `local_participant_requests` rows where `participant_type_id = 5` to `participant_type_id = 7`.
	4. Optionally remove the old id=5 row when verified.

If you want, we can add a migration step to `db/upgrade.php` to perform this copy-and-update automatically during the next upgrade. Request me to implement that and I will add it to upgrade `2025081800`.