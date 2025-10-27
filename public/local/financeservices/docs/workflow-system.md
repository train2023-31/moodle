# Workflow System Documentation

The Finance Services plugin implements a sophisticated multi-step approval workflow using the `local_status` plugin for status management.

## 🔄 Workflow Overview

### Workflow States
The plugin uses a 7-step approval process with the following states:

| Status ID | Status Name | Description | Next State |
|-----------|-------------|-------------|------------|
| 8 | Initial | Request submitted | Leader 1 Review (9) |
| 9 | Leader 1 Review | First-level approval | Leader 2 Review (10) |
| 10 | Leader 2 Review | Second-level approval | Leader 3 Review (11) |
| 11 | Leader 3 Review | Third-level approval | Boss Review (12) |
| 12 | Boss Review | Final approval level | Approved (13) |
| 13 | Approved | Final approved state | End state |
| 14 | Rejected | Final rejected state | End state |

### Workflow Diagram
```
[Initial] → [Leader 1] → [Leader 2] → [Leader 3] → [Boss] → [Approved]
    ↓           ↓           ↓           ↓         ↓
    └─────── [Leader 1] ←──┴───────────┴─────────┘
    ↓
[Rejected]
```

## 🛠️ Core Workflow Class

### `simple_workflow_manager.php`

This class handles all workflow operations and is the central component of the approval system.

#### Key Methods

**Status Management**:
```php
public static function get_initial_status_id()
// Returns: 8 (Initial status for new requests)

public static function get_next_status_id($current_status_id)
// Returns: Next status ID in the workflow chain

public static function get_previous_status_id($current_status_id)
// Returns: Previous status ID for rejection flow
```

**Permission Checks**:
```php
public static function can_user_approve($status_id)
// Checks if current user can approve at given status
// Returns: boolean

public static function get_required_capability($status_id)
// Returns: Required capability string for status level
```

**Action Methods**:
```php
public static function approve_request($request_id, $note = '')
// Moves request to next approval level
// Triggers status change event

public static function reject_request($request_id, $note = '')
// Moves request to rejected state or previous level
// Records rejection reason
```

#### Workflow Logic Implementation

**Approval Flow**:
```php
public static function get_next_status_id($current_status_id) {
    $workflow_map = [
        8 => 9,   // Initial → Leader 1
        9 => 10,  // Leader 1 → Leader 2
        10 => 11, // Leader 2 → Leader 3
        11 => 12, // Leader 3 → Boss
        12 => 13, // Boss → Approved
    ];
    
    return $workflow_map[$current_status_id] ?? null;
}
```

**Rejection Flow**:
```php
public static function get_previous_status_id($current_status_id) {
    $rejection_map = [
        9 => 14,  // Leader 1 → Rejected (final)
        10 => 9,  // Leader 2 → Leader 1
        11 => 9,  // Leader 3 → Leader 1
        12 => 9,  // Boss → Leader 1
    ];
    
    return $rejection_map[$current_status_id] ?? 14;
}
```

## 🔐 Permission System

### Capability-Based Access Control

Each workflow level requires specific capabilities:

```php
public static function get_required_capability($status_id) {
    $capability_map = [
        9 => 'local/financeservices:approve_level1',
        10 => 'local/financeservices:approve_level2',
        11 => 'local/financeservices:approve_level3',
        12 => 'local/financeservices:approve_boss',
    ];
    
    return $capability_map[$status_id] ?? null;
}
```

### Role-Based Workflow

**Typical Role Assignment**:
- **Staff/Teachers**: Submit requests (status 8)
- **Department Heads**: Level 1 approval (status 9)
- **Directors**: Level 2 approval (status 10)
- **Senior Management**: Level 3 approval (status 11)
- **Executive**: Final approval (status 12)

## 🎯 AJAX Workflow Processing

### Client-Side Implementation

**Approval Action**:
```javascript
function approveRequest(requestId, note) {
    // Disable button immediately to prevent double-clicks
    const button = document.querySelector(`[data-id="${requestId}"].approve-btn`);
    button.disabled = true;
    button.textContent = 'Processing...';
    
    const data = {
        action: 'approve',
        id: requestId,
        note: note || '',
        sesskey: M.cfg.sesskey
    };
    
    fetch('/local/financeservices/actions/update_request_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI or reload page
            location.reload();
        } else {
            alert(data.message);
            button.disabled = false;
            button.textContent = 'Approve';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
        button.textContent = 'Approve';
    });
}
```

**Rejection Action**:
```javascript
function rejectRequest(requestId, note) {
    if (!note || note.trim() === '') {
        alert('Rejection reason is required');
        return;
    }
    
    const data = {
        action: 'reject',
        id: requestId,
        note: note,
        sesskey: M.cfg.sesskey
    };
    
    // Similar AJAX implementation as approval
}
```

### Server-Side AJAX Handler

**`actions/update_request_status.php`**:
```php
<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/financeservices/classes/simple_workflow_manager.php');

require_login();
require_sesskey();

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    $action = $input['action'] ?? '';
    $id = (int)($input['id'] ?? 0);
    $note = trim($input['note'] ?? '');
    
    // Validate input
    if (!in_array($action, ['approve', 'reject'])) {
        throw new moodle_exception('invalid_action');
    }
    
    if ($id <= 0) {
        throw new moodle_exception('invalid_request_id');
    }
    
    // Get current request
    $request = $DB->get_record('local_financeservices', ['id' => $id], '*', MUST_EXIST);
    
    // Check permissions
    if (!simple_workflow_manager::can_user_approve($request->status_id)) {
        throw new moodle_exception('nopermission');
    }
    
    // Perform action
    if ($action === 'approve') {
        $result = simple_workflow_manager::approve_request($id, $note);
    } else {
        if (empty($note)) {
            throw new moodle_exception('rejection_note_required');
        }
        $result = simple_workflow_manager::reject_request($id, $note);
    }
    
    if ($result) {
        $response['success'] = true;
        $response['message'] = get_string($action . '_success', 'local_financeservices');
    } else {
        $response['message'] = get_string($action . '_failed', 'local_financeservices');
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
```

## 🔄 Status Transitions

### Forward Movement (Approval)

When a request is approved:
1. **Permission Check**: Verify user can approve at current status
2. **Status Update**: Move to next status in workflow
3. **Note Recording**: Store approval note if provided
4. **Event Trigger**: Log status change event
5. **Response**: Return success message

```php
public static function approve_request($request_id, $note = '') {
    global $DB, $USER;
    
    $request = $DB->get_record('local_financeservices', ['id' => $request_id], '*', MUST_EXIST);
    
    // Check permissions
    if (!self::can_user_approve($request->status_id)) {
        return false;
    }
    
    $next_status = self::get_next_status_id($request->status_id);
    if (!$next_status) {
        return false;
    }
    
    // Update request
    $request->status_id = $next_status;
    $request->timemodified = time();
    
    if (!empty($note)) {
        $request->approval_note = $note;
    }
    
    $success = $DB->update_record('local_financeservices', $request);
    
    if ($success) {
        // Trigger event
        $event = \local_financeservices\event\request_status_changed::create([
            'context' => context_system::instance(),
            'objectid' => $request_id,
            'other' => [
                'old_status' => $request->status_id,
                'new_status' => $next_status,
                'action' => 'approve',
                'note' => $note
            ]
        ]);
        $event->trigger();
    }
    
    return $success;
}
```

### Backward Movement (Rejection)

When a request is rejected:
1. **Permission Check**: Verify user can reject at current status
2. **Rejection Logic**: Determine target status (previous level or final rejection)
3. **Note Requirement**: Ensure rejection reason is provided
4. **Status Update**: Move to target status
5. **Event Trigger**: Log rejection event

```php
public static function reject_request($request_id, $note) {
    global $DB, $USER;
    
    if (empty($note)) {
        throw new moodle_exception('rejection_note_required');
    }
    
    $request = $DB->get_record('local_financeservices', ['id' => $request_id], '*', MUST_EXIST);
    
    // Check permissions
    if (!self::can_user_approve($request->status_id)) {
        return false;
    }
    
    $target_status = self::get_previous_status_id($request->status_id);
    
    // Update request
    $request->status_id = $target_status;
    $request->rejection_note = $note;
    $request->timemodified = time();
    
    $success = $DB->update_record('local_financeservices', $request);
    
    if ($success) {
        // Trigger event
        $event = \local_financeservices\event\request_status_changed::create([
            'context' => context_system::instance(),
            'objectid' => $request_id,
            'other' => [
                'old_status' => $request->status_id,
                'new_status' => $target_status,
                'action' => 'reject',
                'note' => $note
            ]
        ]);
        $event->trigger();
    }
    
    return $success;
}
```

## 🎨 UI Integration

### Status Display

**Color-Coded Status**:
```php
// In classes/output/tab_list.php
private function get_status_class($status_id) {
    switch ($status_id) {
        case 13: // Approved
            return 'success';
        case 14: // Rejected
            return 'danger';
        default: // Pending
            return 'warning';
    }
}
```

**Action Buttons**:
```mustache
{{! In templates/list.mustache }}
{{#can_approve}}
<button class="btn btn-success btn-sm approve-btn" data-id="{{id}}">
    {{approve_label}}
</button>
<button class="btn btn-danger btn-sm reject-btn" data-id="{{id}}">
    {{reject_label}}
</button>
{{/can_approve}}

{{#is_final_state}}
<span class="badge badge-{{status_class}}">{{status_display}}</span>
{{/is_final_state}}
```

### Note Display

**Rejection Notes**:
```php
// Show rejection notes when request was previously rejected
if ($request->status_id == 14) {
    $display_note = "Rejection Reason: " . $request->rejection_note;
} else if (self::has_rejection_note($request->status_id)) {
    $display_note = "Previously Rejected - " . $request->rejection_note;
}
```

**Approval Notes**:
```php
// Show approval notes when available
if (!empty($request->approval_note) && $request->status_id == 13) {
    $display_note = "Approval Note: " . $request->approval_note;
}
```

## 🛡️ Security Features

### Race Condition Prevention
- **Immediate button disabling** on client-side
- **Server-side status validation** before updates
- **Transaction locking** for critical updates

### Session Security
- **Session key validation** for all AJAX requests
- **CSRF token verification** in forms
- **User authentication** required for all actions

### Permission Validation
- **Capability checks** at every workflow step
- **Context-aware permissions** for system-level operations
- **Record ownership validation** where applicable

## 📊 Workflow Analytics

### Status History Tracking
The workflow system automatically tracks:
- **Status changes** with timestamps
- **User actions** with approver information
- **Notes and comments** at each step
- **Time spent** at each workflow level

### Event Logging
All workflow actions trigger Moodle events:
- `request_created` - New request submitted
- `request_status_changed` - Status transition occurred
- **Log data includes**: User ID, request ID, old/new status, action type, notes

### Reporting Capabilities
The workflow data enables reporting on:
- **Approval times** by workflow level
- **Rejection rates** by department/user
- **Bottlenecks** in the approval process
- **User activity** in the workflow system

This workflow system provides a robust, secure, and auditable approval process that can be easily extended for additional workflow steps or customized business rules. 