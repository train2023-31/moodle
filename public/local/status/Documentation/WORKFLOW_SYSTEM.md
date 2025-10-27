# Workflow System Documentation - Status Plugin

## Overview

The Status Plugin implements a sophisticated workflow engine that provides multi-step approval processes for Moodle plugins. This document explains the technical architecture, workflow processing logic, and internal mechanisms of the system.

## Core Architecture

### Workflow Engine Components

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Workflow Types │────│ Workflow Steps  │────│ Step Approvers  │
│ (Categories)    │    │ (Process Flow)  │    │ (User-based)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────┬───────────────┬───────────────┘
                         │               │
              ┌─────────────────┐ ┌─────────────────┐
              │ Active Instances│ │ History Log     │
              │ (Runtime State) │ │ (Audit Trail)   │
              └─────────────────┘ └─────────────────┘
```

### Database Schema Relationships

#### Primary Tables
1. **`local_status_type`** - Workflow definitions and categories
2. **`local_status`** - Individual workflow steps with sequencing
3. **`local_status_instance`** - Active workflow instances (runtime state)
4. **`local_status_history`** - Complete audit trail of all actions
5. **`local_status_approvers`** - Person-based approver assignments
6. **`local_status_transition`** - Step transition rules and conditions

#### Relationship Diagram
```sql
local_status_type (1) ──→ (many) local_status
      │                           │
      └── (1) ──→ (many) local_status_instance
                          │
                          └── (1) ──→ (many) local_status_history

local_status (1) ──→ (many) local_status_approvers
local_status (1) ──→ (many) local_status_transition
```

## Workflow Processing Engine

### Core Manager Classes

#### 1. `workflow_dashboard_manager`
**Purpose**: Workflow definition and configuration management

**Key Responsibilities:**
- Workflow type CRUD operations
- Step creation and sequencing
- Approver assignment and management
- Step positioning and reordering
- Workflow validation and integrity checks

**Core Methods:**
```php
create_workflow_type($data)           // Create new workflow
create_workflow_step($data)           // Add step to workflow
get_workflow_steps($type_id)          // Retrieve ordered steps
add_step_approver($step_id, $user_id) // Assign approver to step
reorder_steps($type_id, $sequence)    // Update step ordering
```

#### 2. `status_workflow_manager`  
**Purpose**: Runtime workflow execution and state management

**Key Responsibilities:**
- Workflow instance lifecycle management
- Status transition processing
- Permission validation
- History logging and audit trails
- Integration with external plugins

**Core Methods:**
```php
start_workflow($type_id, $record_id, $user_id)    // Initialize workflow
approve_workflow($type_id, $record_id, $user_id)  // Process approval
reject_workflow($type_id, $record_id, $user_id)   // Process rejection
get_current_status($type_id, $record_id)          // Get current state
```

### Workflow State Machine

#### State Transitions
```
[INITIAL] ──approve──→ [STEP_1] ──approve──→ [STEP_2] ──approve──→ [FINAL]
    │                      │                    │                    │
    │                      │                    │                    │
    └──reject──→ [REJECTED] └──reject──→ [STEP_1] └──reject──→ [STEP_1]
```

**Rejection Behavior:**
- **Leader 1 Rejection**: Goes directly to REJECTED status (terminal state)
- **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review (STEP_1) for re-evaluation
- **Rationale**: Allows for re-evaluation at the first review level rather than permanent rejection

#### State Management Rules
1. **Linear Progression**: Steps must be completed in sequence order
2. **Capability Validation**: Each step requires specific permissions
3. **Atomic Transitions**: Status changes are transaction-protected
4. **Audit Logging**: All transitions are recorded with full context
5. **Rollback Protection**: Failed transitions don't corrupt state

### Approval Types

#### 1. Capability-Based Approval
**Most Common Type** - Uses Moodle's capability system

```php
// Step configuration
$step->approval_type = 'capability';
$step->capability = 'local/status:finance_department_head_approve';

// Permission check
$context = context_system::instance();
$can_approve = has_capability($step->capability, $context, $user_id);
```

#### 2. User-Based Approval
**Person-Specific** - Designated users must approve in sequence

```php
// Step configuration
$step->approval_type = 'user';

// Approver assignment
workflow_dashboard_manager::add_step_approver($step_id, $user_id, $sequence_order, $is_required);

// Permission check
$approvers = get_step_approvers($step_id);
$can_approve = user_is_assigned_approver($user_id, $approvers);
```

#### 3. Open Approval
**Any User** - Any logged-in user can approve

```php
// Step configuration
$step->approval_type = 'any';

// Permission check (always true for logged-in users)
$can_approve = isloggedin() && !isguestuser();
```

## Workflow Processing Logic

### Workflow Initialization

```php
public static function start_workflow($type_id, $record_id, $user_id, $note = '') {
    global $DB;
    
    // 1. Get initial step for workflow type
    $initial_step = $DB->get_record('local_status', [
        'type_id' => $type_id,
        'is_initial' => 1
    ]);
    
    // 2. Create workflow instance
    $instance = new stdClass();
    $instance->type_id = $type_id;
    $instance->record_id = $record_id;
    $instance->current_status_id = $initial_step->id;
    $instance->started_by = $user_id;
    $instance->timestarted = time();
    $instance_id = $DB->insert_record('local_status_instance', $instance);
    
    // 3. Log initial state
    self::log_history($instance_id, $initial_step->id, $user_id, 'started', $note);
    
    return $initial_step->id;
}
```

### Approval Processing

```php
public static function approve_workflow($type_id, $record_id, $user_id, $note = '') {
    global $DB;
    
    // 1. Get current workflow state
    $instance = self::get_workflow_instance($type_id, $record_id);
    $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id]);
    
    // 2. Validate user permissions
    if (!self::can_user_approve($user_id, $current_step)) {
        throw new moodle_exception('insufficient_permissions');
    }
    
    // 3. Get next step in sequence
    $next_step = $DB->get_record('local_status', [
        'type_id' => $type_id,
        'seq' => $current_step->seq + 1
    ]);
    
    // 4. Process transition
    $transaction = $DB->start_delegated_transaction();
    try {
        // Update instance state
        $instance->current_status_id = $next_step ? $next_step->id : $current_step->id;
        $instance->timemodified = time();
        $DB->update_record('local_status_instance', $instance);
        
        // Log approval action
        self::log_history($instance->id, $next_step->id, $user_id, 'approved', $note);
        
        // Update external plugin record
        self::update_external_record($type_id, $record_id, $next_step->id);
        
        $transaction->allow_commit();
        return $next_step->id;
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}
```

### Rejection Processing

```php
public static function reject_workflow($type_id, $record_id, $user_id, $note = '') {
    global $DB;
    
    // 1. Get current workflow state
    $instance = self::get_workflow_instance($type_id, $record_id);
    $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id]);
    
    // 2. Validate user permissions
    if (!self::can_user_approve($user_id, $current_step)) {
        throw new moodle_exception('insufficient_permissions');
    }
    
    // 3. Determine rejection target based on current step
    $rejection_target = self::get_rejection_target($type_id, $current_step);
    
    // 4. Process rejection transition
    $transaction = $DB->start_delegated_transaction();
    try {
        // Update instance state
        $instance->current_status_id = $rejection_target->id;
        $instance->timemodified = time();
        $DB->update_record('local_status_instance', $instance);
        
        // Log rejection action
        self::log_history($instance->id, $rejection_target->id, $user_id, 'rejected', $note);
        
        // Update external plugin record
        self::update_external_record($type_id, $record_id, $rejection_target->id);
        
        $transaction->allow_commit();
        return $rejection_target->id;
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}

/**
 * Determine rejection target based on current step
 * NEW BEHAVIOR: All rejections go back to Leader 1 Review except Leader 1 which goes to final rejection
 */
private static function get_rejection_target($type_id, $current_step) {
    global $DB;
    
    // Get all active steps in sequence order
    $all_steps = $DB->get_records('local_status', [
        'type_id' => $type_id,
        'is_active' => 1
    ], 'seq ASC');
    
    // Find initial step and first step after initial
    $initial_step = null;
    $first_after_initial = null;
    $found_initial = false;
    
    foreach ($all_steps as $step) {
        if ($step->is_initial) {
            $initial_step = $step;
            $found_initial = true;
            continue;
        }
        if ($found_initial && !$first_after_initial) {
            $first_after_initial = $step;
            break;
        }
    }
    
    // Check if current step is the first step after initial (Leader 1)
    if ($current_step->id == $first_after_initial->id) {
        // Leader 1 rejection = go to final rejection
        $rejected_step = $DB->get_record('local_status', [
            'type_id' => $type_id,
            'name' => 'rejected',
            'is_active' => 1
        ]);
        return $rejected_step;
    } else {
        // All other rejections = go back to Leader 1 Review
        return $first_after_initial;
    }
}
```

### Step Sequencing System

#### Automatic Sequence Management
```php
public static function add_step_at_position($type_id, $position, $step_data) {
    global $DB;
    
    // 1. Validate position
    $max_seq = $DB->get_field_sql(
        "SELECT MAX(seq) FROM {local_status} WHERE type_id = ?", 
        [$type_id]
    );
    
    if ($position > $max_seq + 1) {
        $position = $max_seq + 1; // Add at end
    }
    
    // 2. Shift existing steps (gap-free sequence)
    $transaction = $DB->start_delegated_transaction();
    try {
        // Move existing steps to make room
        $DB->execute(
            "UPDATE {local_status} SET seq = seq + 1 
             WHERE type_id = ? AND seq >= ?",
            [$type_id, $position]
        );
        
        // Insert new step
        $step_data->seq = $position;
        $step_data->type_id = $type_id;
        $step_id = $DB->insert_record('local_status', $step_data);
        
        $transaction->allow_commit();
        return $step_id;
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}
```

#### Step Reordering Logic
```php
public static function reorder_steps($type_id, $step_sequence) {
    global $DB;
    
    // Two-phase update to prevent constraint violations
    $transaction = $DB->start_delegated_transaction();
    try {
        // Phase 1: Set temporary negative sequences
        foreach ($step_sequence as $index => $step_id) {
            $DB->execute(
                "UPDATE {local_status} SET seq = ? WHERE id = ?",
                [-(1000 + $index), $step_id]
            );
        }
        
        // Phase 2: Set final positive sequences
        foreach ($step_sequence as $index => $step_id) {
            $DB->execute(
                "UPDATE {local_status} SET seq = ? WHERE id = ?",
                [$index + 1, $step_id]
            );
        }
        
        $transaction->allow_commit();
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}
```

## Department-Specific Workflow System

### 8-Department Structure
The system implements a standardized organizational structure:

1. **Academic** (أكاديمي)
2. **Finance** (مالية)  
3. **Student Services** (خدمات الطلاب)
4. **IT** (تقنية المعلومات)
5. **Research** (البحث العلمي)
6. **Training** (التدريب)
7. **Planning** (التخطيط)
8. **Facilities** (المرافق)

### 4-Tier Hierarchy
Each department follows a consistent approval hierarchy:

1. **Officer** (ض.د) - `department_officer_approve`
2. **Department Head** (ض.ق) - `department_department_head_approve`
3. **Service Head** (ض.ق.خ) - `department_service_head_approve`
4. **CEO** (ر.د) - `department_ceo_approve`

### Capability Generation Pattern
```php
// Auto-generated capabilities for each department
foreach ($departments as $dept) {
    $capabilities = [
        "local/status:{$dept}_officer_approve",
        "local/status:{$dept}_department_head_approve", 
        "local/status:{$dept}_service_head_approve",
        "local/status:{$dept}_ceo_approve"
    ];
}

// Example: Finance department capabilities
'local/status:finance_officer_approve'
'local/status:finance_department_head_approve'
'local/status:finance_service_head_approve'
'local/status:finance_ceo_approve'
```

## Advanced Features

### Dynamic Approver Management

#### Approver Assignment
```php
public static function add_step_approver($step_id, $user_id, $sequence_order = 1, $is_required = true) {
    global $DB;
    
    $approver = new stdClass();
    $approver->step_id = $step_id;
    $approver->user_id = $user_id;
    $approver->sequence_order = $sequence_order;
    $approver->is_required = $is_required;
    $approver->timecreated = time();
    
    return $DB->insert_record('local_status_approvers', $approver);
}
```

#### Sequential Approval Logic
```php
public static function can_user_approve_sequential($user_id, $step_id, $instance_id) {
    global $DB;
    
    // Get approvers for this step
    $approvers = $DB->get_records('local_status_approvers', 
        ['step_id' => $step_id], 'sequence_order ASC');
    
    // Check if user is assigned as approver
    $user_approver = null;
    foreach ($approvers as $approver) {
        if ($approver->user_id == $user_id) {
            $user_approver = $approver;
            break;
        }
    }
    
    if (!$user_approver) {
        return false; // User not assigned as approver
    }
    
    // Check if previous required approvers have completed
    foreach ($approvers as $approver) {
        if ($approver->sequence_order >= $user_approver->sequence_order) {
            break; // Only check previous approvers
        }
        
        if ($approver->is_required) {
            $has_approved = $DB->record_exists('local_status_history', [
                'instance_id' => $instance_id,
                'step_id' => $step_id,
                'user_id' => $approver->user_id,
                'action' => 'approved'
            ]);
            
            if (!$has_approved) {
                return false; // Required previous approver hasn't approved
            }
        }
    }
    
    return true;
}
```

### Audit and History System

#### Complete History Logging
```php
public static function log_history($instance_id, $status_id, $user_id, $action, $note = '') {
    global $DB;
    
    $history = new stdClass();
    $history->instance_id = $instance_id;
    $history->status_id = $status_id;
    $history->user_id = $user_id;
    $history->action = $action; // 'started', 'approved', 'rejected', 'cancelled'
    $history->note = $note;
    $history->timecreated = time();
    $history->ip_address = getremoteaddr();
    $history->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    return $DB->insert_record('local_status_history', $history);
}
```

#### History Retrieval
```php
public static function get_workflow_history($type_id, $record_id) {
    global $DB;
    
    $sql = "SELECT h.*, u.firstname, u.lastname, s.status_name_en, s.status_name_ar
            FROM {local_status_history} h
            JOIN {local_status_instance} i ON i.id = h.instance_id
            JOIN {user} u ON u.id = h.user_id
            JOIN {local_status} s ON s.id = h.status_id
            WHERE i.type_id = ? AND i.record_id = ?
            ORDER BY h.timecreated ASC";
            
    return $DB->get_records_sql($sql, [$type_id, $record_id]);
}
```

## Performance Optimization

### Database Indexing Strategy
```sql
-- Workflow instance lookups
CREATE INDEX idx_status_instance_lookup ON local_status_instance (type_id, record_id);

-- Step sequencing
CREATE INDEX idx_status_sequence ON local_status (type_id, seq);

-- History queries  
CREATE INDEX idx_history_instance ON local_status_history (instance_id, timecreated);

-- Approver lookups
CREATE INDEX idx_approvers_step ON local_status_approvers (step_id, sequence_order);
```

### Query Optimization
```php
// Efficient status lookup with caching
public static function get_current_status_cached($type_id, $record_id) {
    static $cache = [];
    $cache_key = "{$type_id}_{$record_id}";
    
    if (!isset($cache[$cache_key])) {
        $cache[$cache_key] = self::get_current_status($type_id, $record_id);
    }
    
    return $cache[$cache_key];
}

// Batch status retrieval for lists
public static function get_batch_statuses($type_id, $record_ids) {
    global $DB;
    
    list($in_sql, $params) = $DB->get_in_or_equal($record_ids);
    $params[] = $type_id;
    
    $sql = "SELECT i.record_id, s.status_name_en, s.status_name_ar
            FROM {local_status_instance} i
            JOIN {local_status} s ON s.id = i.current_status_id
            WHERE i.record_id $in_sql AND i.type_id = ?";
            
    return $DB->get_records_sql($sql, $params);
}
```

## Error Handling and Recovery

### Transaction Protection
```php
public static function safe_workflow_transition($type_id, $record_id, $user_id, $action, $note = '') {
    global $DB;
    
    $transaction = $DB->start_delegated_transaction();
    try {
        // Validate current state
        $instance = self::get_workflow_instance($type_id, $record_id);
        if (!$instance) {
            throw new moodle_exception('workflow_not_found');
        }
        
        // Check for race conditions
        $last_modified = $DB->get_field('local_status_instance', 'timemodified', 
            ['id' => $instance->id]);
        if ($last_modified > $instance->timemodified) {
            throw new moodle_exception('workflow_state_changed');
        }
        
        // Process action
        switch ($action) {
            case 'approve':
                $result = self::approve_workflow($type_id, $record_id, $user_id, $note);
                break;
            case 'reject':
                $result = self::reject_workflow($type_id, $record_id, $user_id, $note);
                break;
            default:
                throw new moodle_exception('invalid_action');
        }
        
        $transaction->allow_commit();
        return $result;
        
    } catch (Exception $e) {
        $transaction->rollback($e);
        
        // Log error for debugging
        error_log("Workflow transition failed: " . $e->getMessage());
        
        throw $e;
    }
}
```

### Consistency Validation
```php
public static function validate_workflow_integrity($type_id) {
    global $DB;
    
    $issues = [];
    
    // Check for gaps in sequence
    $steps = $DB->get_records('local_status', ['type_id' => $type_id], 'seq ASC');
    $expected_seq = 1;
    foreach ($steps as $step) {
        if ($step->seq != $expected_seq) {
            $issues[] = "Sequence gap detected: expected {$expected_seq}, found {$step->seq}";
        }
        $expected_seq++;
    }
    
    // Check for orphaned instances
    $orphaned = $DB->get_records_sql(
        "SELECT i.* FROM {local_status_instance} i
         LEFT JOIN {local_status} s ON s.id = i.current_status_id
         WHERE i.type_id = ? AND s.id IS NULL",
        [$type_id]
    );
    
    if ($orphaned) {
        $issues[] = count($orphaned) . " orphaned workflow instances found";
    }
    
    return $issues;
}
```

This workflow system provides a robust, scalable foundation for complex approval processes while maintaining data integrity, audit trails, and performance across the entire Moodle installation. 