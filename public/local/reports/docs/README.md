# Student Reports Plugin Documentation

## Overview

The **Student Reports** plugin (`local_reports`) is a Moodle local plugin designed to manage student reports with an approval workflow system. This plugin enables teachers and managers to create, review, approve, and disapprove student reports within course contexts.

## Key Features

- **Student Report Management**: Create and edit reports for individual students
- **Workflow System**: Multi-step approval process for reports
- **AJAX Interface**: Modern, responsive user interface with modal dialogs
- **PDF Generation**: Generate PDF reports in vertical and horizontal formats
- **Multi-language Support**: Available in English and Arabic
- **Tab-based Interface**: Separate views for pending and approved reports
- **System-wide Overview**: View all reports across all courses
- **Permission-based Access**: Role-based capabilities for different user types

## Documentation Structure

This documentation folder contains:

- [`README.md`](README.md) - This overview file
- [`INSTALLATION.md`](INSTALLATION.md) - Installation and setup guide
- [`ROLES_AND_CAPABILITIES.md`](ROLES_AND_CAPABILITIES.md) - Comprehensive roles and permissions guide
- [`DEVELOPMENT.md`](DEVELOPMENT.md) - Development workflow and guidelines
- [`FILE_STRUCTURE.md`](FILE_STRUCTURE.md) - Complete file structure explanation
- [`DATABASE.md`](DATABASE.md) - Database schema and data flow
- [`API.md`](API.md) - AJAX endpoints and API documentation
- [`WORKFLOW.md`](WORKFLOW.md) - Report approval workflow explanation
- [`CUSTOMIZATION.md`](CUSTOMIZATION.md) - How to customize and extend the plugin

## Quick Start

1. Install the plugin in your Moodle's `local/reports` directory
2. Visit **Site Administration > Notifications** to run the database installation
3. Configure user capabilities in **Site Administration > Users > Permissions > Define roles**
4. Access the plugin through any course where you have the required permissions

## Requirements

- **Moodle Version**: 4.0 or later
- **PHP Version**: As required by your Moodle installation
- **Required Capabilities**: 
  - `local/reports:manage` - For creating and managing reports
  - `local/reports:viewall` - For viewing reports
  - `local/status:reports_workflow_step1` - First approval step
  - `local/status:reports_workflow_step2` - Second approval step
  - `local/status:reports_workflow_step3` - Final approval step

## Permission System

The plugin uses a sophisticated permission system with:

- **Core Capabilities**: Basic report management permissions
- **Workflow Capabilities**: Multi-step approval process permissions
- **Context-Based Access**: Course-level and system-level permissions
- **Role Integration**: Standard Moodle role system integration

For detailed information about roles, capabilities, and permission configuration, see [`ROLES_AND_CAPABILITIES.md`](ROLES_AND_CAPABILITIES.md).

## Support

For development questions, refer to the detailed documentation files in this folder. Each file provides specific information about different aspects of the plugin. 