# Workflow Documentation

This document explains the report approval workflow system and how it operates within the Student Reports plugin.

## Workflow Overview

The Student Reports plugin implements a multi-step approval workflow that guides reports through various stages from creation to final approval. The workflow is capability-based, meaning different user roles can perform different actions at each stage.

## Workflow States

### Status Definitions

| Status ID | Status Name | Description | Capabilities Required |
|-----------|-------------|-------------|----------------------|
| 0 | Draft | Report is being created or edited | `local/reports:manage` |
| 30 | Pending | Submitted for initial review | `local/status:reports_workflow_step1` |
| 31 | Under Review | Being reviewed by authorized personnel | `local/status:reports_workflow_step2` |
| 32 | Approved | Approved but not yet final | `local/status:reports_workflow_step3` |
| 50 | Final | Fully approved and locked | System-generated |

### Workflow Progression

```
Draft (0) → Pending (30) → Under Review (31) → Approved (32) → Final (50)
              ↓              ↓                    ↓
           [Can be disapproved and returned to previous state]
```

## Workflow Configuration

### Current Workflow Map
The workflow is defined in the main interface files (`index.php`, `allreports.php`):

```php
$workflow = [
    30 => ['next' => 31, 'capability' => 'local/status:reports_workflow_step1'],
    31 => ['next' => 32, 'capability' => 'local/status:reports_workflow_step2'],
    32 => ['next' => 50, 'capability' => 'local/status:reports_workflow_step3'],
];
```

### Legacy Workflow (Commented Out)
The code shows evidence of a previous workflow system:
```php
// OLD Capability‐based workflow map
// $workflow = [
//     41 => ['next' => 42, 'capability' => 'local/status:type5_step1'],
//     42 => ['next' => 44, 'capability' => 'local/status:type5_step2'],
//     44 => ['next' => 50, 'capability' => 'local/status:type5_step3'],
// ];
```

## User Roles and Capabilities

> **📋 Comprehensive Guide**: For detailed information about all roles, capabilities, and permission configurations, see [`ROLES_AND_CAPABILITIES.md`](ROLES_AND_CAPABILITIES.md)

### Report Management Capabilities

#### `local/reports:manage`
- **Purpose**: Create and edit reports
- **Context**: Course level
- **Default Roles**: Editing Teacher, Manager
- **Risk Level**: RISK_SPAM | RISK_XSS

#### `local/reports:viewall`
- **Purpose**: View all reports in a course
- **Context**: Course level
- **Default Roles**: Editing Teacher, Manager
- **Risk Level**: Read-only

### Workflow Capabilities

#### `local/status:reports_workflow_step1`
- **Purpose**: Can approve reports from Pending (30) to Under Review (31)
- **Typical Role**: Department reviewers

#### `local/status:reports_workflow_step2`
- **Purpose**: Can approve reports from Under Review (31) to Approved (32)
- **Typical Role**: Senior staff or administrators

#### `local/status:reports_workflow_step3`
- **Purpose**: Can approve reports from Approved (32) to Final (33)
- **Typical Role**: Final authorities or system administrators
- **Special Note**: Required for accessing `allreports.php`

## Workflow Actions

### Report Creation
1. **Initial State**: Reports start in Draft (0) status
2. **Creator**: Any user with `local/reports:manage` capability
3. **Context**: Course-specific
4. **Data**: Report content fields are populated

### Approval Process

#### Single Report Approval
Handled by `approve_ajax.php`:

1. **Capability Check**: Verify user has required workflow capability
2. **Status Validation**: Ensure report is in correct current state
3. **Status Update**: Move to next workflow stage
4. **Audit Trail**: Update `timemodified` and `modifiedby`
5. **Notification**: May trigger notifications (implementation dependent)

#### Bulk Approval
The system supports approving multiple reports simultaneously:
- **UI Element**: "Approve All Reports" button
- **Confirmation**: Requires user confirmation
- **Process**: Iterates through eligible reports
- **Capability**: User must have capability for the workflow step

### Disapproval Process

#### Single Report Disapproval
Handled by `disapprove_ajax.php`:

1. **Reason Required**: User must provide disapproval reason
2. **Status Rollback**: May return report to previous workflow stage
3. **Audit Trail**: Record disapproval action and reason
4. **Notification**: Inform relevant parties of disapproval

#### Bulk Disapproval
Similar to bulk approval but with reason requirement:
- **UI Element**: "Disapprove All Reports" button
- **Reason Field**: Required text input for disapproval reason
- **Confirmation**: Enhanced confirmation dialog

## User Interface Elements

### Tab Interface
The workflow is reflected in the tab-based interface:

#### Pending Reports Tab
- Shows reports in workflow states 30-32
- Available actions depend on user capabilities
- Bulk actions available

#### Approved Reports Tab
- Shows reports in final state (50)
- Limited actions (view, potentially reopen)
- Historical record

### Action Buttons
Dynamic button display based on:
- Current report status
- User capabilities
- Workflow configuration

Common buttons:
- **Approve**: Move to next workflow stage
- **Disapprove**: Reject and provide reason
- **Edit**: Modify report content (if permitted)
- **Preview**: View report in PDF format

## JavaScript Integration

### Workflow JavaScript Files
The workflow integrates with several JavaScript modules:

#### `approve.js`
- Handles approval confirmations
- AJAX communication with `approve_ajax.php`
- UI updates after successful operations
- Error handling and user feedback

#### `modal.js`
- Confirmation dialogs for workflow actions
- Bulk operation confirmations
- Disapproval reason collection

### AJAX Communication
Workflow actions use AJAX for seamless user experience:
- **Endpoints**: `approve_ajax.php`, `disapprove_ajax.php`
- **Data Format**: JSON requests and responses
- **Error Handling**: User-friendly error messages
- **UI Updates**: Dynamic page updates without refresh

## Configuration and Customization

### Adding New Workflow Steps
1. **Define New Capabilities**: Add to `db/access.php`
2. **Update Workflow Map**: Modify workflow arrays in interface files
3. **Add Language Strings**: Create user-friendly status names
4. **Update AJAX Handlers**: Modify approval/disapproval logic
5. **Update Templates**: Add UI elements for new states

### Modifying Workflow Logic
The workflow system is designed to be flexible:
- **Capability-Based**: Easy to reassign who can perform actions
- **Status-Driven**: Clear state progression
- **Configurable**: Workflow maps can be modified

### Custom Workflow Implementation
For organizations needing different workflows:
1. **Copy Current Implementation**: Use as starting point
2. **Define New Status Values**: Create custom status ID scheme
3. **Update Capability Definitions**: Create organization-specific capabilities
4. **Modify Templates**: Update UI to reflect new workflow
5. **Test Thoroughly**: Ensure all edge cases are handled

## Error Handling

### Common Workflow Errors
- **Insufficient Capabilities**: User lacks required permissions
- **Invalid State Transition**: Attempting impossible status change
- **Missing Report**: Report ID not found
- **Concurrent Modification**: Report changed by another user

### Error Recovery
- **Graceful Degradation**: UI disables unavailable actions
- **Clear Messages**: User-friendly error explanations
- **Audit Trail**: All errors logged for troubleshooting
- **Rollback Capability**: Failed operations don't leave inconsistent state

## Monitoring and Reporting

### Workflow Metrics
Track workflow performance:
- **Processing Time**: Time between workflow stages
- **Bottlenecks**: Stages where reports accumulate
- **User Activity**: Who is performing workflow actions
- **Approval Rates**: Success/failure ratios

### Audit Trail
Every workflow action is recorded:
- **Timestamp**: When action occurred
- **User**: Who performed the action
- **Status Change**: From/to status values
- **Reason**: For disapprovals or special actions

## Best Practices

### For Administrators
1. **Clear Role Definitions**: Ensure users understand their workflow responsibilities
2. **Regular Review**: Monitor workflow efficiency and bottlenecks
3. **Training**: Provide training on workflow procedures
4. **Documentation**: Maintain organization-specific workflow documentation

### For Developers
1. **Capability Checks**: Always verify permissions before workflow actions
2. **Atomic Operations**: Ensure workflow changes are consistent
3. **Error Handling**: Provide clear feedback for all failure modes
4. **Testing**: Test all workflow transitions thoroughly
5. **Logging**: Implement comprehensive audit logging

### For Users
1. **Timely Action**: Process workflow items promptly
2. **Clear Reasons**: Provide detailed disapproval reasons
3. **Communication**: Coordinate with other workflow participants
4. **Documentation**: Follow organizational workflow procedures 