# Residence Booking Plugin Documentation

## Overview

The **Residence Booking Plugin** (`local_residencebooking`) is a Moodle local plugin that provides an accommodation booking service for educational institutions. It allows users to submit accommodation requests, administrators to manage these requests through a workflow system, and supports multilingual content in English and Arabic.

## Features

- **Request Submission**: Users can submit accommodation requests with details like guest name, service number, accommodation type, dates, purpose, and additional notes
- **Workflow Management**: Built-in approval workflow with multiple review stages (Leader1 → Leader2 → Leader3 → Boss → Approved/Rejected)
- **Multilingual Support**: Full support for English and Arabic languages
- **Admin Management**: Complete administrative interface for managing accommodation types, purposes, and requests
- **AJAX Integration**: Real-time guest search functionality
- **Soft Delete**: Support for hiding/restoring data instead of permanent deletion
- **Status Tracking**: Integration with local_status plugin for workflow management

## Current Version

**Version**: 2025081200 (v0.6-audit-fields)
**Maturity**: ALPHA
**Requires**: Moodle 4.0+ (2022041900)

## Recent Updates

- **Workflow System Updated**: Rejection behavior modified so that rejections from Leader 2, 3, and Boss levels return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status

## Quick Start

1. **Installation**: Place the plugin in `/local/residencebooking/` directory
2. **Database**: Run Moodle upgrade to install database tables
3. **Permissions**: Configure user capabilities for different roles
4. **Settings**: Configure plugin settings via Site Administration
5. **Usage**: Access via `/local/residencebooking/index.php`

## Database Tables

- `local_residencebooking_request` - Main booking requests with audit fields (`timecreated`, `timemodified`, `created_by`, `modified_by`)
- `local_residencebooking_types` - Accommodation types (multilingual)
- `local_residencebooking_purpose` - Booking purposes (multilingual)

## Integration

- **local_status**: Workflow management integration (type_id = 3)
- **Moodle Core**: User management, course integration, capabilities system
- **Theme Stream**: Custom dialog and export functionality

## Documentation Structure

- [`README.md`](README.md) - This overview document
- [`DEVELOPMENT.md`](DEVELOPMENT.md) - Development guidelines and best practices
- [`FILE_STRUCTURE.md`](FILE_STRUCTURE.md) - Detailed explanation of all files
- [`API.md`](API.md) - API documentation and web services
- [`WORKFLOW.md`](WORKFLOW.md) - Workflow system documentation
- [`INSTALLATION.md`](INSTALLATION.md) - Installation and configuration guide

## Support

For technical support or feature requests, consult the development team or refer to the detailed documentation files in this directory. 