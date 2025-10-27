# Workflow System Documentation

This document explains the workflow approval system used in the participant plugin for managing request approvals.

## Overview

The participant plugin uses a sophisticated multi-level approval workflow system managed by the `simple_workflow_manager` class. This system ensures that participant requests go through proper review and approval processes before being finalized. Each request record maintains an audit trail via `created_by`, `time_created`, `time_modified`, and `modified_by`.

## Workflow Architecture

### Core Components

1. **Status System**: Based on the `local/status` plugin
2. **Workflow Manager**: `classes/simple_workflow_manager.php`
3. **Action Handlers**: Files in `/actions/` directory
4. **Capability System**: Role-based permissions for each workflow step

### Dependencies

- **local/status plugin**: Provides the underlying status framework
- **Moodle capabilities**: Controls who can perform actions at each step
- **Database tables**: Stores request data and status information

## Workflow Stages

### Status Definitions

The workflow consists of 7 distinct statuses with specific IDs:

```php
STATUS_INITIAL        = 56  // request_submitted (sequence: 1)
STATUS_LEADER1_REVIEW = 57  // leader1_review (sequence: 2)
STATUS_LEADER2_REVIEW = 58  // leader2_review (sequence: 3)
STATUS_LEADER3_REVIEW = 59  // leader3_review (sequence: 4)
STATUS_BOSS_REVIEW    = 60  // boss_review (sequence: 5)
STATUS_APPROVED       = 61  // approved (sequence: 6)
STATUS_REJECTED       = 62  // rejected (sequence: 7)
```

### Workflow Flow Diagram

```
┌─────────────────┐
│   Initial (56)  │ ──────┐
│  New Request    │       │
└─────────────────┘       │
          │               │
          ▼               │
┌─────────────────┐       │
│ Leader1 Rev(57) │ ◄─────┼─────┐
│  First Review   │       │     │
└─────────────────┘       │     │
          │               │     │
          ▼               │     │
┌─────────────────┐       │     │
│ Leader2 Rev(58) │ ──────┘     │
│ Second Review   │             │
└─────────────────┘             │
          │                     │
          ▼                     │
┌─────────────────┐             │
│ Leader3 Rev(59) │ ────────────┘
│  Third Review   │
└─────────────────┘
          │
          ▼
┌─────────────────┐
│ Boss Review(60) │ ────────────┐
│  Final Review   │             │
└─────────────────┘             │
          │                     │
          ▼                     │
┌─────────────────┐             │
│  Approved (61)  │             │
│ Final Approval  │             │
└─────────────────┘             │
                                │
                                ▼
                ┌─────────────────┐
                │  Rejected (62)  │
                │ Can occur from  │
                │   any status    │
                └─────────────────┘
```

## Workflow Transitions

### Forward Progression

Each status has a defined next step and required capability:

| Current Status | Next Status | Required Capability |
|----------------|-------------|-------------------|
| Initial (56) | Leader1 Review (57) | `local/status:participants_workflow_step1` |
| Leader1 Review (57) | Leader2 Review (58) | `local/status:participants_workflow_step2` |
| Leader2 Review (58) | Leader3 Review (59) | `local/status:participants_workflow_step3` |
| Leader3 Review (59) | Boss Review (60) | `local/status:participants_workflow_step4` |
| Boss Review (60) | Approved (61) | `local/status:participants_workflow_step4` |

### Rejection Path

From any status (except final states), requests can be rejected:

- **Leader 1 Rejection**: Goes directly to Rejected (62) status
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review (57) for re-evaluation
- **Capability Required**: Each level requires its respective workflow step capability
- **Available From**: All intermediate statuses

**Rejection Flow**:
```php
public static function get_rejection_map(): array {
    return [
        self::STATUS_LEADER1_REVIEW => [
            'next'       => self::STATUS_REJECTED,
            'capability' => 'local/status:participants_workflow_step1',
        ],
        self::STATUS_LEADER2_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step2',
        ],
        self::STATUS_LEADER3_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step3',
        ],
        self::STATUS_BOSS_REVIEW => [
            'next'       => self::STATUS_LEADER1_REVIEW,
            'capability' => 'local/status:participants_workflow_step4',
        ],
    ];
}
```

## Capability System

### Required Capabilities

The workflow system relies on specific capabilities defined in the status plugin:

```php
// Workflow step capabilities
'local/status:participants_workflow_step1' // Leader 1 approval
'local/status:participants_workflow_step2' // Leader 2 approval
'local/status:participants_workflow_step3' // Leader 3 approval
'local/status:participants_workflow_step4' // Boss approval/rejection
```

### Permission Hierarchy

1. **Step 1 (Leader1)**: First level reviewers
2. **Step 2 (Leader2)**: Second level reviewers
3. **Step 3 (Leader3)**: Third level reviewers
4. **Step 4 (Boss)**: Final approval authority and rejection capability

## Implementation Details

### Workflow Manager Class

The `simple_workflow_manager` provides several key methods:

#### Status Checking Methods
```php
// Check if user can approve from current status
can_user_approve($userid, $current_status)

// Check if user can reject from current status
can_user_reject($userid, $current_status)

// Get next status in approval flow
get_next_approval_status($current_status)
```

#### Workflow Action Methods
```php
// Approve a request (move to next status)
approve_request($request_id, $user_id)

// Reject a request (move to rejected status)
reject_request($request_id, $user_id)
```

### Action Handlers

#### Approve Request (`actions/approve_request.php`)
- Validates user permissions
- Processes approval transition
- Updates request status and audit fields (`time_modified`, `modified_by`)
- Logs the action
- Redirects back to main page

#### Reject Request (`actions/reject_request.php`)
- Validates user permissions
- Processes rejection transition
- Updates request status to rejected and audit fields (`time_modified`, `modified_by`)
- Logs the action
- Redirects back to main page

## Usage Examples

### Processing an Approval

```php
// Example from approve_request.php
$workflow_manager = new \local_participant\simple_workflow_manager();

// Check if user can approve
if ($workflow_manager->can_user_approve($USER->id, $current_status)) {
    // Process the approval
    $result = $workflow_manager->approve_request($request_id, $USER->id);
    
    if ($result) {
        \core\notification::success(get_string('request_approved', 'local_participant'));
    }
}
```

### Processing a Rejection

```php
// Example from reject_request.php
$workflow_manager = new \local_participant\simple_workflow_manager();

// Check if user can reject
if ($workflow_manager->can_user_reject($USER->id, $current_status)) {
    // Process the rejection
    $result = $workflow_manager->reject_request($request_id, $USER->id);
    
    if ($result) {
        \core\notification::success(get_string('request_rejected', 'local_participant'));
    }
}
```

## User Interface Integration

### Status Display

The workflow status is displayed in the main requests view:
- Shows current status name
- Displays appropriate action buttons based on user permissions
- Uses color coding for different status types

### Action Buttons

Dynamic buttons appear based on:
- Current request status
- User's capabilities
- Available workflow transitions

```php
// Example button logic
if ($workflow_manager->can_user_approve($USER->id, $request->status)) {
    // Show approve button
    echo $OUTPUT->single_button(
        new moodle_url('/local/participant/actions/approve_request.php', ['id' => $request->id]),
        get_string('approve', 'local_participant'),
        'post'
    );
}
```

## Error Handling

### Common Error Scenarios

1. **Insufficient Permissions**: User lacks required capability
2. **Invalid Status Transition**: Attempting illegal status change
3. **Database Errors**: Issues updating request status
4. **Workflow Conflicts**: Multiple simultaneous actions

### Error Messages

The system provides specific error messages for different scenarios:
- Permission denied errors
- Invalid transition errors
- Database operation failures
- General workflow errors

## Logging and Auditing

### Action Logging

All workflow actions are logged:
- User who performed the action
- Timestamp of the action
- Previous and new status
- Request ID affected

### Audit Trail

The system maintains a complete audit trail:
- Status change history
- User actions and timestamps
- Approval/rejection reasons (if implemented)

## Customization Guide

### Adding New Workflow Steps

To add a new step in the workflow:

1. **Define new status constant**:
   ```php
   public const STATUS_NEW_STEP = 63;
   ```

2. **Update workflow map**:
   ```php
   self::STATUS_PREVIOUS => [
       'next' => self::STATUS_NEW_STEP,
       'capability' => 'local/status:participants_workflow_new_step',
   ],
   ```

3. **Update capability definitions** in the status plugin

4. **Test all workflow paths**

### Modifying Approval Logic

To change the approval logic:

1. **Update workflow map** in `simple_workflow_manager.php`
2. **Modify action handlers** as needed
3. **Update capability requirements**
4. **Test thoroughly**

### Adding Custom Actions

To add new workflow actions:

1. **Create new action file** in `/actions/` directory
2. **Implement proper capability checks**
3. **Use workflow manager methods**
4. **Update UI to show new action buttons**

## Troubleshooting

### Common Issues

1. **Permissions not working**: Check capability assignments in user roles
2. **Status not updating**: Verify workflow map configuration
3. **Buttons not showing**: Check capability checks in UI code
4. **Database errors**: Verify table structure and foreign keys

### Debug Steps

1. **Enable debug mode** in Moodle
2. **Check error logs** for specific error messages
3. **Verify user capabilities** using role assignments
4. **Test workflow transitions** manually
5. **Check database integrity** for status records 