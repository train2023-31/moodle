# Dynamic Approver Management System

## Overview

The Local Status plugin includes a dynamic approver management framework that provides person-based approval workflows beyond traditional capability-based systems.

## 🎯 Features

### 1. **Person-Based Sequential Approvals**
Specific users must approve in defined order

### 2. **Dynamic Approver Management**
Add/remove approvers in real-time through admin interface

### 3. **Hybrid Approval Types**
Mix capability-based, user-based, and open approvals

### 4. **Simple Plugin API**
Easy integration for other Moodle plugins

### 5. **Advanced Sequencing**
Required vs optional approvers with complex flows

## Approval Types

### 1. Capability-Based Approvals
Traditional role-based approvals using Moodle capabilities:
```php
$step->approval_type = 'capability';
$step->capability = 'local/status:academic_department_head_approve';
```

### 2. User-Based Approvals
Specific people must approve in sequence:
```php
$step->approval_type = 'user';
$approvers = [
    ['user_id' => 123, 'sequence_order' => 1, 'is_required' => true],
    ['user_id' => 456, 'sequence_order' => 2, 'is_required' => true],
    ['user_id' => 789, 'sequence_order' => 3, 'is_required' => false]
];
workflow_manager::set_step_approvers($step_id, $approvers);
```

### 3. Any User Approvals
Any logged-in user can approve:
```php
$step->approval_type = 'any';
```

## Database Schema

### **Core Tables**

#### `local_status_approvers`
```sql
CREATE TABLE local_status_approvers (
    id bigint(10) NOT NULL AUTO_INCREMENT,
    step_id bigint(10) NOT NULL,
    user_id bigint(10) NOT NULL,
    sequence_order int(11) NOT NULL DEFAULT 1,
    is_required tinyint(1) NOT NULL DEFAULT 1,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    created_by bigint(10) NOT NULL,
    created_time bigint(10) NOT NULL,
    modified_time bigint(10) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY step_user_unique (step_id, user_id),
    KEY idx_step_sequence (step_id, sequence_order)
);
```

#### `local_status_instance`
```sql
CREATE TABLE local_status_instance (
    id bigint(10) NOT NULL AUTO_INCREMENT,
    type_id bigint(10) NOT NULL,
    record_id bigint(10) NOT NULL,
    current_status_id bigint(10) NOT NULL,
    current_approver_sequence int(11) DEFAULT 1,
    is_completed tinyint(1) NOT NULL DEFAULT 0,
    is_rejected tinyint(1) NOT NULL DEFAULT 0,
    created_by bigint(10) NOT NULL,
    created_time bigint(10) NOT NULL,
    modified_time bigint(10) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY type_record_unique (type_id, record_id)
);
```

## API Methods

### **Approver Management**
```php
// Add approver to step
workflow_manager::add_step_approver($step_id, $user_id, $sequence_order, $is_required);

// Remove approver from step
workflow_manager::remove_step_approver($step_id, $user_id);

// Get step approvers
$approvers = workflow_manager::get_step_approvers($step_id, $active_only);

// Reorder approvers
workflow_manager::reorder_step_approvers($step_id, $user_ids);
```

### **Workflow Instance Management**
```php
// Reject workflow
workflow_manager::reject_workflow($type_id, $record_id, $user_id, $note);

// Start workflow instance
workflow_manager::start_workflow_instance($type_id, $record_id, $created_by);

// Check approval permissions
$can_approve = workflow_manager::can_user_approve($type_id, $record_id, $user_id);

// Get next approver
$next_approver = workflow_manager::get_next_approver($type_id, $record_id);

// Process approval
$moved = workflow_manager::approve_step($type_id, $record_id, $user_id, $note);
```

### **Simple API**
```php
// Create simple sequential workflow
$workflow_id = workflow_manager::create_simple_sequential_workflow($name, $display, $approvers, $plugin);

// Start workflow by name
workflow_manager::start_workflow($workflow_name, $record_id, $user_id);

// Get status by name
$status = workflow_manager::get_workflow_status($workflow_name, $record_id);

// Approve by name
$success = workflow_manager::approve_workflow($workflow_name, $record_id, $user_id, $note);
```

## Admin Interface

### **Managing Approvers**

1. **Access Workflow Management**
   - Go to **Site Administration → Plugins → Local plugins → Workflow Management**

2. **Navigate to Steps**
   - Click **Workflow Steps** tab
   - Select your workflow from dropdown

3. **Manage Step Approvers**
   - For user-based steps, click the **👥 Manage Approvers** icon
   - Add users by searching and selecting
   - Set sequence order (1, 2, 3...)
   - Mark as required or optional
   - Drag to reorder

4. **Step Configuration**
   - **Capability-based**: Choose capability from dropdown
   - **User-based**: Manage specific approvers list
   - **Any user**: No additional configuration needed

### Step Types Configuration

| Type | Configuration | Use Case |
|------|---------------|----------|
| **Capability** | Select Moodle capability | Role-based approvals |
| **User** | Manage approvers list | Specific person approvals |
| **Any** | No configuration | Open approval steps |

## Integration Examples

### Residence Booking Plugin

```php
<?php
use local_status\workflow_manager;

class booking_manager {
    
    public static function setup_workflow() {
        // Get key staff user IDs
        $coordinator_id = self::get_user_by_username('residence_coordinator')->id;
        $manager_id = self::get_user_by_username('residence_manager')->id;
        $director_id = self::get_user_by_username('services_director')->id;
        
        // Create sequential workflow
        workflow_manager::create_simple_sequential_workflow(
            'residence_booking',
            'Residence Booking Approval',
            [$coordinator_id, $manager_id, $director_id],
            'local_residencebooking'
        );
    }
    
    public static function submit_booking($form_data) {
        global $DB, $USER;
        
        // Create booking record
        $booking = new stdClass();
        $booking->student_id = $USER->id;
        $booking->room_type = $form_data->room_type;
        $booking->start_date = $form_data->start_date;
        $booking->end_date = $form_data->end_date;
        $booking->notes = $form_data->notes;
        $booking->status = 'pending';
        $booking->created_time = time();
        
        $booking_id = $DB->insert_record('local_residencebooking_request', $booking);
        
        // Start approval workflow
        workflow_manager::start_workflow('residence_booking', $booking_id, $USER->id);
        
        return $booking_id;
    }
    
    public static function process_approval($booking_id, $action, $note = '') {
        global $USER, $DB;
        
        if ($action === 'approve') {
            $success = workflow_manager::approve_workflow(
                'residence_booking', 
                $booking_id, 
                $USER->id, 
                $note
            );
            
            if ($success) {
                $status = workflow_manager::get_workflow_status('residence_booking', $booking_id);
                
                if ($status->is_final) {
                    self::allocate_room($booking_id);
                    $DB->set_field('local_residencebooking_request', 'status', 'approved', ['id' => $booking_id]);
                }
            }
            
        } else if ($action === 'reject') {
            workflow_manager::reject_workflow('residence_booking', $booking_id, $USER->id, $note);
            $DB->set_field('local_residencebooking_request', 'status', 'rejected', ['id' => $booking_id]);
        }
    }
    
    public static function can_approve_booking($booking_id, $user_id = null) {
        global $USER;
        $user_id = $user_id ?: $USER->id;
        
        return workflow_manager::can_user_approve('residence_booking', $booking_id, $user_id);
    }
    
    public static function get_booking_status($booking_id) {
        $status = workflow_manager::get_workflow_status('residence_booking', $booking_id);
        $next_approver = workflow_manager::get_next_approver('residence_booking', $booking_id);
        
        return [
            'current_step' => $status->current_step_name,
            'is_final' => $status->is_final,
            'next_approver' => $next_approver ? $next_approver->firstname . ' ' . $next_approver->lastname : null
        ];
    }
}
```

### Course Approval Plugin

```php
<?php
use local_status\workflow_manager;

class course_workflow {
    
    public static function setup_course_approval() {
        $type_data = [
            'name' => 'course_approval',
            'display_name_en' => 'Course Approval Process',
            'plugin_name' => 'local_courseapproval',
            'is_active' => 1
        ];
        $type_id = workflow_manager::create_workflow_type($type_data);
        
        // Step 1: Academic Coordinator (specific person)
        $step1 = workflow_manager::create_workflow_step([
            'type_id' => $type_id,
            'name' => 'coordinator_review',
            'display_name_en' => 'Academic Coordinator Review',
            'approval_type' => 'user',
            'sort_order' => 1,
            'color' => '#17a2b8'
        ]);
        
        // Add specific coordinator
        $coordinator = get_user_by_username('academic_coordinator');
        workflow_manager::add_step_approver($step1, $coordinator->id, 1, true);
        
        // Step 2: Any Academic Manager (capability-based)
        workflow_manager::create_workflow_step([
            'type_id' => $type_id,
            'name' => 'manager_approval',
            'display_name_en' => 'Manager Approval',
            'approval_type' => 'capability',
            'capability' => 'local/status:academic_department_head_approve',
            'sort_order' => 2,
            'color' => '#ffc107'
        ]);
        
        return $type_id;
    }
    
    public static function submit_course_proposal($course_data) {
        global $DB, $USER;
        
        $proposal_id = $DB->insert_record('local_courseapproval_proposal', $course_data);
        workflow_manager::start_workflow('course_approval', $proposal_id, $USER->id);
        
        return $proposal_id;
    }
}
```

## Advanced Features

### Mixed Approval Types in Single Workflow

```php
// Step 1: Specific person must approve
$step1_id = workflow_manager::create_workflow_step([
    'type_id' => $type_id,
    'approval_type' => 'user'
]);
workflow_manager::add_step_approver($step1_id, 123, 1, true);

// Step 2: Anyone with capability can approve  
workflow_manager::create_workflow_step([
    'type_id' => $type_id,
    'approval_type' => 'capability',
    'capability' => 'local/status:manager_approve'
]);

// Step 3: Any user can approve (final confirmation)
workflow_manager::create_workflow_step([
    'type_id' => $type_id,
    'approval_type' => 'any',
    'is_final' => 1
]);
```

### Required vs Optional Approvers

```php
$approvers = [
    ['user_id' => 123, 'sequence_order' => 1, 'is_required' => true],   // Must approve
    ['user_id' => 456, 'sequence_order' => 2, 'is_required' => true],   // Must approve  
    ['user_id' => 789, 'sequence_order' => 3, 'is_required' => false],  // Optional
    ['user_id' => 101, 'sequence_order' => 4, 'is_required' => false],  // Optional
];
workflow_manager::set_step_approvers($step_id, $approvers);
```

### Conditional Workflow Advancement

```php
// Check if all required approvers have approved
$can_advance = workflow_manager::can_advance_step($type_id, $record_id);

// Manual advancement after custom logic
if ($can_advance && custom_conditions_met($record_id)) {
    workflow_manager::advance_to_next_step($type_id, $record_id, $user_id);
}
```

## Best Practices

### 1. Workflow Design
- **Keep it simple**: Avoid too many approval steps
- **Clear naming**: Use descriptive step and workflow names
- **Logical flow**: Ensure approval sequence makes organizational sense
- **Backup plans**: Consider what happens if approvers are unavailable

### 2. Approver Management
- **Regular audits**: Review and update approver lists regularly
- **Role coverage**: Ensure critical steps have backup approvers
- **Active users only**: Remove inactive/deleted users from approver lists
- **Clear documentation**: Document approval processes for users

### 3. Performance
- **Cache workflow data**: Steps and transitions don't change often
- **Index properly**: Ensure database indexes on frequently queried fields
- **Limit history**: Set appropriate audit retention periods
- **Batch operations**: Use bulk methods for multiple changes

### 4. Security
- **Validate transitions**: Always check capabilities before allowing transitions
- **Audit everything**: Enable audit trail for compliance
- **Use capabilities**: Leverage Moodle's capability system
- **Protect sensitive data**: Careful with approval notes and history

---

**Version**: 3.0.0 - Dynamic Approver Management System  
**Last Updated**: January 2025  
**Compatibility**: Moodle 3.9+ 