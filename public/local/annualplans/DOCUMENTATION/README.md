# Annual Plans Plugin Documentation

## Overview

The **Annual Plans** plugin is a comprehensive Moodle local plugin designed to manage annual training plans and courses. It provides a complete system for planning, tracking, and managing educational courses with approval workflows, beneficiary management, and administrative oversight.

## Plugin Information

- **Component**: `local_annualplans`
- **Version**: 1.9 (Build: 2025081200)
- **Requires**: Moodle 3.10 or later
- **Maturity**: MATURITY_STABLE
- **Type**: Local Plugin

## Key Features

### 1. Annual Plan Management
- Create and manage multiple annual plans
- Year-based organization
- Plan status tracking
- Plan approval workflow

### 2. Course Management
- Add courses to annual plans
- Course categorization and leveling
- Duration and scheduling management
- Beneficiary tracking
- Room/location assignment

### 3. Administrative Features
- Course code management system
- Multi-level approval process
- Excel file upload for bulk operations
- Advanced filtering and search
- User capability management

### 4. Data Management
- Import/export functionality
- CSV/Excel support
- Comprehensive reporting
- Data validation and integrity
 - Audit fields for Oracle-sourced data (beneficiaries) using `timecreated`, `timemodified`, `created_by`, `modified_by`

### 5. User Interface
- Responsive web interface
- AJAX-powered interactions
- Mustache template system
- Multi-language support (English, Arabic)

## Quick Start

### Installation
1. Extract the plugin to `/local/annualplans/`
2. Visit Site Administration to complete installation
3. Configure permissions for managers
4. Set up course codes and levels

### Basic Usage
1. Access via Site Administration → Annual Plans
2. Create a new annual plan
3. Add courses to the plan
4. Configure beneficiaries and schedules
5. Submit for approval

## Documentation Structure

This documentation is organized into the following sections:

- **[Plugin Overview](PLUGIN_OVERVIEW.md)** - Detailed plugin functionality and features
- **[File Structure](FILE_STRUCTURE.md)** - Complete breakdown of all plugin files
- **[Database Schema](DATABASE_SCHEMA.md)** - Database tables and relationships
- **[API Documentation](API_DOCUMENTATION.md)** - Classes, methods, and AJAX endpoints
- **[Development Guide](DEVELOPMENT_GUIDE.md)** - How to extend and customize the plugin

## Quick Navigation

| Function | Location | Description |
|----------|----------|-------------|
| Main Dashboard | `/local/annualplans/index.php` | Primary interface for managing plans |
| Course Management | `/local/annualplans/add_course.php` | Add new courses to plans |
| Code Management | `/local/annualplans/manage_codes.php` | Manage course codes and categories |
| Approved Courses | `/local/annualplans/approvedDisplay.php` | View approved courses |
| Admin Settings | Site Admin → Annual Plans | Plugin configuration |

## Support and Maintenance

### Requirements
- Moodle 3.10 or higher
- PHP 7.4 or higher
- MySQL/PostgreSQL database
- Web server with appropriate permissions

### Capabilities
- `local/annualplans:manage` - Full management access
- `local/annualplans:view` - Read-only access

### Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge

## Version History

- **v1.7** (2025071500) - Refactored code, improved stability
- Previous versions maintained backward compatibility

---

**Note**: This documentation covers all existing functionality of the plugin as implemented. For development and customization guidelines, see the [Development Guide](DEVELOPMENT_GUIDE.md). 