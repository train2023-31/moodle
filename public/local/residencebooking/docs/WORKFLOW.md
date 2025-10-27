# Workflow Documentation

This document explains the approval workflow system used in the Residence Booking plugin.

## Overview

The Residence Booking plugin uses a multi-stage approval workflow integrated with the `local_status` plugin. Each booking request progresses through multiple review stages before final approval or rejection. Requests include audit fields (`timecreated`, `timemodified`, `created_by`, `modified_by`) populated on create/update.

## Workflow Stages

### Stage Flow Diagram

```
[INITIAL] → [LEADER1_REVIEW] → [LEADER2_REVIEW] → [LEADER3_REVIEW] → [BOSS_REVIEW] → [APPROVED]
    ↓               ↓                ↓                ↓               ↓             
[REJECTED]     [REJECTED]      [LEADER1_REVIEW]  [LEADER1_REVIEW]  [LEADER1_REVIEW]      
```

**Note**: Rejections from Leader 2, 3, and Boss levels return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status.

### Status Definitions

| Status ID | Status Name | Description | Required Capability |
|-----------|-------------|-------------|-------------------|
| 15 | INITIAL | Request submitted, awaiting first review | - |
| 16 | LEADER1_REVIEW | Under review by first leader | `local/status:residence_workflow_step1` |
| 17 | LEADER2_REVIEW | Under review by second leader | `local/status:residence_workflow_step2` |
| 18 | LEADER3_REVIEW | Under review by third leader | `local/status:residence_workflow_step3` |
| 19 | BOSS_REVIEW | Under review by supervisor/boss | `local/status:residence_workflow_step4` |
| 20 | APPROVED | Request approved | `local/status:residence_workflow_step4` |
| 21 | REJECTED | Request rejected | Any review capability |

## Workflow Implementation

### Class: `simple_workflow_manager`

The workflow is managed by the `simple_workflow_manager` class located in `/classes/simple_workflow_manager.php`.

#### Key Methods

**`can_user_transition_to($userid, $current_status, $target_status)`**
- Checks if a user can transition a request from current to target status
- Validates capabilities and workflow rules
- Returns boolean result

**`get_next_status($current_status)`**
- Returns the next logical status in the workflow
- Used for "approve" actions
- Returns null if no next status exists

**`get_available_actions($userid, $current_status)`**
- Returns array of actions available to user for current status
- Actions: 'approve', 'reject', 'delete'
- Considers user capabilities and workflow state

### Workflow Rules

#### Forward Progression
1. **INITIAL → LEADER1_REVIEW**: Automatic on submission
2. **LEADER1_REVIEW → LEADER2_REVIEW**: Requires `residence_workflow_step1` capability
3. **LEADER2_REVIEW → LEADER3_REVIEW**: Requires `residence_workflow_step2` capability
4. **LEADER3_REVIEW → BOSS_REVIEW**: Requires `residence_workflow_step3` capability
5. **BOSS_REVIEW → APPROVED**: Requires `residence_workflow_step4` capability

#### Rejection Rules
- **Leader 1 Rejection**: Goes directly to REJECTED status (terminal state)
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review for re-evaluation
- Rejection requires same capability as approval for that stage
- Only Leader 1 rejections are terminal; higher-level rejections allow for re-evaluation

#### Special Rules
- **Initial status**: Only automatic system transitions
- **Approved/Rejected**: Terminal states (no further transitions)
- **Backward transitions**: Not supported in current implementation

## Integration with local_status Plugin

### Configuration Requirements

The workflow requires the following status records in the `local_status` table:

```sql
INSERT INTO mdl_local_status (id, type_id, status_name_en, status_name_ar) VALUES
(15, 3, 'Initial', 'مبدئي'),
(16, 3, 'Leader 1 Review', 'مراجعة القائد الأول'),
(17, 3, 'Leader 2 Review', 'مراجعة القائد الثاني'),
(18, 3, 'Leader 3 Review', 'مراجعة القائد الثالث'),
(19, 3, 'Boss Review', 'مراجعة المدير'),
(20, 3, 'Approved', 'موافق عليه'),
(21, 3, 'Rejected', 'مرفوض');
```

**Note**: `type_id = 3` indicates "Residence Booking" workflow type.

### Status Display

Status names are retrieved from `local_status` table with multilingual support:
- English: `status_name_en`
- Arabic: `status_name_ar`

## Capabilities System

### Required Capabilities

#### Core Plugin Capabilities
- `local/residencebooking:submitrequest` - Submit booking requests
- `local/residencebooking:viewbookings` - View booking interface
- `local/residencebooking:manage` - Manage all bookings (admin)

#### Workflow-Specific Capabilities
- `local/status:residence_workflow_step1` - Leader 1 review permissions
- `local/status:residence_workflow_step2` - Leader 2 review permissions  
- `local/status:residence_workflow_step3` - Leader 3 review permissions
- `local/status:residence_workflow_step4` - Boss review permissions

### Role Assignment Example

```php
// Manager role gets full workflow access
$managerrole = $DB->get_record('role', array('shortname' => 'manager'));
assign_capability('local/status:residence_workflow_step1', CAP_ALLOW, $managerrole->id, $systemcontext);
assign_capability('local/status:residence_workflow_step2', CAP_ALLOW, $managerrole->id, $systemcontext);
assign_capability('local/status:residence_workflow_step3', CAP_ALLOW, $managerrole->id, $systemcontext);
assign_capability('local/status:residence_workflow_step4', CAP_ALLOW, $managerrole->id, $systemcontext);

// Department head gets limited access
$deptheadrole = $DB->get_record('role', array('shortname' => 'depthead'));
assign_capability('local/status:residence_workflow_step1', CAP_ALLOW, $deptheadrole->id, $systemcontext);
assign_capability('local/status:residence_workflow_step2', CAP_ALLOW, $deptheadrole->id, $systemcontext);
```

## User Interface Integration

### Request Management Table

The workflow integrates with the management interface through `manage_requests_table.php`:

#### Action Buttons
- **Approve**: Advances to next workflow stage (if user has capability)
- **Reject**: Sets status to REJECTED (if user has capability)
- **Delete**: Removes request (admin only)

#### Status Display
- Shows current status name in user's language
- Color-coded status indicators
- Workflow stage progression indicators

### Form Integration

#### Submission Process
1. User submits request via `residencebooking_form.php`
2. Request created with `status_id = STATUS_INITIAL`
3. System automatically assigns to first reviewer
4. Email notifications sent (if configured)

## Workflow Customization

### Adding New Workflow Steps

To add a new workflow step:

1. **Add status record** to `local_status` table
2. **Update workflow constants** in `simple_workflow_manager.php`
3. **Add capability** to `db/access.php`
4. **Update workflow map** in `get_workflow_map()`
5. **Add language strings** for new status

Example:
```php
// Add new constant
public const STATUS_FINANCE_REVIEW = 22;

// Update workflow map
self::STATUS_LEADER3_REVIEW => [
    'next'       => self::STATUS_FINANCE_REVIEW,  // New step
    'capability' => 'local/status:residence_workflow_step3',
],
self::STATUS_FINANCE_REVIEW => [
    'next'       => self::STATUS_BOSS_REVIEW,
    'capability' => 'local/status:residence_workflow_step_finance',
],
```

### Conditional Workflow

For business logic that requires conditional workflows:

```php
public function get_next_status($current_status, $request_data = null) {
    // Standard workflow map
    $map = $this->get_workflow_map();
    
    // Business logic override
    if ($current_status == self::STATUS_LEADER2_REVIEW) {
        // Skip Leader3 for certain conditions
        if ($request_data && $request_data->amount < 1000) {
            return self::STATUS_BOSS_REVIEW;
        }
    }
    
    return isset($map[$current_status]) ? $map[$current_status]['next'] : null;
}
```

### Parallel Approval Workflows

For parallel approvals, consider extending the workflow:

```php
// Multiple parallel paths
const STATUS_FINANCE_PARALLEL = 23;
const STATUS_SECURITY_PARALLEL = 24;
const STATUS_PARALLEL_COMPLETE = 25;

// Logic to handle parallel completion
private function check_parallel_completion($request_id) {
    // Check if both finance and security have approved
    // Move to final stage if both complete
}
```

## Notification System

### Email Notifications

The workflow can be extended with email notifications:

```php
// In workflow transition
public function transition_status($request_id, $new_status, $user_id) {
    // Update status
    $this->update_request_status($request_id, $new_status);
    
    // Send notification
    $this->send_workflow_notification($request_id, $new_status);
}

private function send_workflow_notification($request_id, $status) {
    // Get next reviewers
    $reviewers = $this->get_next_reviewers($status);
    
    // Send email to reviewers
    foreach ($reviewers as $reviewer) {
        email_to_user($reviewer, $request_details, $subject, $message);
    }
}
```

### Notification Types
- **Request Submitted**: Notify first reviewer
- **Status Changed**: Notify next reviewer
- **Request Approved**: Notify requester
- **Request Rejected**: Notify requester with reason

## Reporting and Analytics

### Workflow Metrics

Track workflow performance:

```sql
-- Average approval time by stage
SELECT 
    status_name_en,
    AVG(DATEDIFF(next_status_date, current_status_date)) as avg_days
FROM workflow_history 
GROUP BY status_id;

-- Rejection rates by stage
SELECT 
    status_name_en,
    COUNT(*) as total_at_stage,
    SUM(CASE WHEN final_status = 21 THEN 1 ELSE 0 END) as rejected_at_stage
FROM workflow_history 
GROUP BY status_id;
```

### Performance Monitoring
- Track average time per workflow stage
- Monitor rejection rates by reviewer
- Identify workflow bottlenecks
- Generate approval statistics

## Troubleshooting

### Common Issues

**Request stuck in workflow**
- Check user capabilities for the current stage
- Verify status exists in local_status table
- Confirm workflow map configuration

**Users cannot approve/reject**
- Verify capability assignments
- Check role context and inheritance
- Confirm user is logged in with correct role

**Status not displaying correctly**
- Check local_status table records
- Verify type_id = 3 for residence booking
- Confirm multilingual fields are populated

### Debug Queries

```sql
-- Check request status
SELECT r.id, r.guest_name, s.status_name_en, s.status_name_ar 
FROM local_residencebooking_request r
JOIN local_status s ON r.status_id = s.id;

-- Check user capabilities
SELECT rc.userid, rc.capability, rc.permission 
FROM role_capabilities rc 
WHERE rc.capability LIKE '%residence_workflow%';
``` 