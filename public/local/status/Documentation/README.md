# Status Plugin Documentation

## Overview

The **Status Plugin** (`local_status`) is a comprehensive workflow management system for Moodle that provides centralized approval workflows for multiple plugins. It serves as the core engine for managing multi-step approval processes across the entire Moodle installation.

## Plugin Information

- **Component**: `local_status`
- **Version**: 2025070803 (3.0.7 - PARTICIPANTS WORKFLOW ADDED)
- **Maturity**: Stable
- **Minimum Moodle Version**: 3.9+ (2020061500)
- **Release**: PARTICIPANTS WORKFLOW ADDED

## Core Purpose

The Status Plugin is designed to:
- **Centralize workflow management** across multiple Moodle plugins
- **Provide standardized approval processes** with consistent behavior
- **Support multi-language workflows** (English/Arabic)
- **Enable dynamic approver assignment** for complex organizational structures
- **Maintain audit trails** of all workflow activities

## Key Features

### 🔄 Workflow Management
- Create unlimited custom workflows
- Multi-language support (English/Arabic)
- Active/inactive workflow states
- Plugin-specific workflow assignment
- Drag-and-drop step reordering
- **Smart Rejection Logic**: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review for re-evaluation

### 👥 Dynamic Approver System
- **Person-based sequential approvals** - Specific users must approve in defined order
- **Dynamic approver management** - Add/remove approvers in real-time
- **Hybrid approval types** - Mix capability-based, user-based, and open approvals
- **Advanced sequencing** - Required vs optional approvers with complex flows

### 🏢 Department-Specific Capabilities
- **8-department structure** - Academic, Finance, Student Services, IT, Research, Training, Planning, Facilities
- **4-tier hierarchy** - Officer (ض.د), Department Head (ض.ق), Service Head (ض.ق.خ), CEO (ر.د)
- **Consistent Arabic terminology** - Standardized naming across all workflows
- **Scalable capability system** - Easy to extend to new departments

### 🔧 Advanced Step Management
- Add steps at any position in workflow
- Automatic initial/final step management
- Protected step system
- Gap-free sequence management
- Transaction-protected updates

## Supported Workflow Types

The plugin currently supports 9 different workflow types:

1. **Course Workflow** (ID: 1) - General course-related approvals
2. **Finance Services** (ID: 2) - Financial request approvals
3. **Residence Booking** (ID: 3) - Accommodation booking approvals
4. **Computer Service** (ID: 4) - IT equipment and service requests
5. **Reports** (ID: 5) - Report generation and approval workflows
6. **Training Services** (ID: 6) - Training-related request approvals
7. **Annual Plan** (ID: 7) - Annual planning workflows
8. **Classroom Booking** (ID: 8) - Room booking and facility management
9. **Participants Management** (ID: 9) - Participant request approvals

## Database Architecture

### Core Tables
- `mdl_local_status_type` - Workflow definitions and categories
- `mdl_local_status` - Individual workflow steps
- `mdl_local_status_instance` - Active workflow instances
- `mdl_local_status_history` - Complete audit trail of workflow actions
- `mdl_local_status_approvers` - Step-specific approvers for person-based workflows
- `mdl_local_status_transition` - Step transition rules and logic

## Admin Interface

The plugin provides a comprehensive admin interface accessible via:
- **Site Administration > Local Status > Workflow Dashboard**

### Available Admin Pages
- **Workflow Dashboard** - Main management interface
- **Manage Workflows** - Create and edit workflow types
- **Workflow Steps** - Manage individual workflow steps
- **Workflow History** - View complete audit trails

### Global Settings
- **Enable Audit** - Toggle audit logging functionality
- **Audit Retention Days** - Configure how long to keep audit records
- **Enable Notifications** - Control workflow notification system

## Integration with Other Plugins

The Status Plugin is designed as a shared service that other plugins integrate with. Currently integrated plugins include:

- **Finance Services** (`local_financeservices`)
- **Residence Booking** (`local_residencebooking`)
- **Computer Service** (`local_computerservice`)
- **Participant Management** (`local_participant`)
- **Room Booking** (`local_roombooking`)

## Quick Start

1. **Access Admin Interface**: Navigate to Site Administration > Local Status > Workflow Dashboard
2. **Create Workflow**: Use "Add New Workflow" to define a new approval process
3. **Add Steps**: Define the approval steps with appropriate capabilities
4. **Configure Approvers**: Set up person-based approvers if needed
5. **Integrate**: Other plugins can reference the workflow using the type_id

## Security Model

- **Capability-based permissions** for each workflow step
- **Session key validation** for all state changes
- **Transaction-protected updates** to maintain data integrity
- **Audit logging** for compliance and troubleshooting

## Support & Development

This plugin serves as the foundation for workflow management across the Moodle installation. For development guidance, integration instructions, and detailed file documentation, see the additional documentation files in this directory. 