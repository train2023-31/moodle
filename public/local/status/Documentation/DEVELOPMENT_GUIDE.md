# Development Guide - Status Plugin

## Overview

This guide explains how to work with and develop using the Status Plugin. It covers integration patterns, development workflows, and best practices for both maintaining the plugin and integrating it with other plugins.

## Development Environment Setup

### Prerequisites
- Moodle 3.9+ development environment
- MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 9.6+
- PHP 7.4+
- Database transaction support (InnoDB recommended)

### Installation for Development
1. Clone or extract the plugin to `/local/status/`
2. Run database upgrade: `php admin/cli/upgrade.php`
3. Enable developer debugging in Moodle settings
4. Verify installation in Site Administration > Plugins > Local plugins

## Core Architecture

### Class Structure

#### Main Manager Classes
- **`workflow_dashboard_manager`** - Handles workflow and step management operations
- **`status_workflow_manager`** - Handles status transitions and workflow execution

#### Usage Pattern
```php
// For dashboard/management operations
use local_status\workflow_dashboard_manager;

// For status change operations  
use local_status\status_workflow_manager;
```

### Database Schema Understanding

#### Core Tables Relationships
```
local_status_type (workflow definitions)
    ↓ (1:many)
local_status (workflow steps)
    ↓ (1:many) 
local_status_instance (active workflows)
    ↓ (1:many)
local_status_history (action log)
```

## Integration with Other Plugins

### Step 1: Define Your Workflow Type

In your plugin's `db/install.php`, ensure you have a workflow type:

```php
function xmldb_local_yourplugin_install() {
    global $DB;
    
    // Check if workflow type exists
    $workflow_type = $DB->get_record('local_status_type', ['name' => 'your_workflow']);
    if (!$workflow_type) {
        $type = new stdClass();
        $type->name = 'your_workflow';
        $type->display_name_en = 'Your Workflow';
        $type->display_name_ar = 'سير العمل الخاص بك';
        $type->plugin_name = 'local_yourplugin';
        $type->is_active = 1;
        $type->sort_order = 10;
        $type->timecreated = time();
        
        $type_id = $DB->insert_record('local_status_type', $type);
    }
}
```

### Step 2: Add Foreign Key to Status

In your main table, add a foreign key to `local_status`:

```xml
<!-- In db/install.xml -->
<FIELD NAME="status_id" TYPE="int" LENGTH="10" NOTNULL="false" COMMENT="Current workflow status"/>

<!-- Add foreign key -->
<KEY NAME="status_fk" TYPE="foreign" FIELDS="status_id" REFTABLE="local_status" REFFIELDS="id"/>
```

### Step 3: Create a Simple Workflow Manager

Create a workflow manager class in your plugin:

```php
// classes/simple_workflow_manager.php
namespace local_yourplugin;

defined('MOODLE_INTERNAL') || die();

class simple_workflow_manager {
    const WORKFLOW_TYPE_ID = 10; // Your workflow type ID
    
    // Define your status constants
    const STATUS_INITIAL = 71;
    const STATUS_REVIEW = 72;
    const STATUS_APPROVED = 73;
    const STATUS_REJECTED = 74;
    
    /**
     * Get all workflow statuses for this plugin
     */
    public static function get_workflow_statuses() {
        global $DB;
        return $DB->get_records('local_status', ['type_id' => self::WORKFLOW_TYPE_ID], 'seq ASC');
    }
    
    /**
     * Get status name in current language
     */
    public static function get_status_name($status_id) {
        global $DB;
        $lang_field = (current_language() === 'ar') ? 'status_name_ar' : 'status_name_en';
        return $DB->get_field('local_status', $lang_field, ['id' => $status_id]);
    }
    
    /**
     * Check if user can transition to target status
     */
    public static function can_user_transition_to($userid, $current_status, $target_status) {
        global $DB;
        
        $target_step = $DB->get_record('local_status', ['id' => $target_status]);
        if (!$target_step || empty($target_step->capability)) {
            return false;
        }
        
        $context = context_system::instance();
        return has_capability($target_step->capability, $context, $userid);
    }
    
    /**
     * Start workflow for a record
     */
    public static function start_workflow($record_id, $user_id, $note = '') {
        require_once($CFG->dirroot . '/local/status/classes/status_workflow_manager.php');
        
        return \local_status\status_workflow_manager::start_workflow(
            self::WORKFLOW_TYPE_ID, 
            $record_id, 
            $user_id, 
            $note
        );
    }
    
    /**
     * Approve workflow step
     */
    public static function approve_workflow($record_id, $user_id, $note = '') {
        require_once($CFG->dirroot . '/local/status/classes/status_workflow_manager.php');
        
        return \local_status\status_workflow_manager::approve_workflow(
            self::WORKFLOW_TYPE_ID, 
            $record_id, 
            $user_id, 
            $note
        );
    }
    
    /**
     * Reject workflow
     * NEW BEHAVIOR: Leader 1 rejections go to final rejection, 
     * while higher-level rejections return to Leader 1 Review for re-evaluation
     */
    public static function reject_workflow($record_id, $user_id, $note = '') {
        require_once($CFG->dirroot . '/local/status/classes/status_workflow_manager.php');
        
        return \local_status\status_workflow_manager::reject_workflow(
            self::WORKFLOW_TYPE_ID, 
            $record_id, 
            $user_id, 
            $note
        );
    }
}
```

### Step 4: Implement UI Integration

In your plugin's main page, integrate status display and actions:

```php
// Display current status
$status_name = \local_yourplugin\simple_workflow_manager::get_status_name($record->status_id);

// Check available actions
$can_approve = \local_yourplugin\simple_workflow_manager::can_user_transition_to($USER->id, $current_status, $next_status);

// AJAX action handling
if ($action === 'approve') {
    require_sesskey();
    $result = \local_yourplugin\simple_workflow_manager::approve_workflow($record_id, $USER->id, $note);
    echo json_encode(['success' => $result]);
    exit;
}

if ($action === 'reject') {
    require_sesskey();
    $result = \local_yourplugin\simple_workflow_manager::reject_workflow($record_id, $USER->id, $note);
    echo json_encode(['success' => $result]);
    exit;
}
```

## Workflow Rejection Behavior

### New Rejection Logic

The Status Plugin implements a smart rejection system that provides better workflow management:

#### Rejection Rules
1. **Leader 1 Rejection**: Goes directly to REJECTED status (terminal state)
2. **Leader 2, 3, Boss Rejections**: Return to Leader 1 Review for re-evaluation
3. **Rationale**: Allows for re-evaluation at the first review level rather than permanent rejection

#### Implementation Details
```php
// The rejection logic is handled automatically by the status_workflow_manager
// No changes needed in your plugin code - the behavior is consistent across all workflows

// Example rejection flow:
// Initial → Leader 1 → Leader 2 → Leader 3 → Boss → Approved
//    ↓         ↓          ↓          ↓        ↓
// Rejected  Rejected  Leader 1   Leader 1  Leader 1
```

#### Benefits
- **Reduced Permanent Rejections**: Higher-level rejections allow for re-evaluation
- **Consistent Behavior**: Same logic across all integrated plugins
- **Better User Experience**: Users can address issues and resubmit
- **Audit Trail**: Complete history of all rejection and re-evaluation attempts

## Development Workflow

### 1. Setting Up New Workflow Types

When adding a new workflow type:

1. **Define constants** in your workflow manager
2. **Add database records** in install.php
3. **Create upgrade path** in upgrade.php if needed
4. **Add language strings** for status names
5. **Define capabilities** in db/access.php

### 2. Testing Workflows

Use the diagnostic script provided:

```bash
php local/status/diagnostic_workflow.php
```

This script helps identify:
- Missing workflow steps
- Capability issues
- Database inconsistencies

### 3. Debugging Common Issues

#### Missing Capabilities
Check that your workflow steps have proper capabilities defined:

```php
// In db/access.php
$capabilities = array(
    'local/yourplugin:workflow_step1' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        )
    ),
);
```

#### Foreign Key Violations
Ensure status_id references valid records in local_status:

```php
// Always validate before setting status_id
$status = $DB->get_record('local_status', ['id' => $status_id]);
if (!$status) {
    throw new moodle_exception('Invalid status ID');
}
```

### 4. Performance Considerations

#### Efficient Status Queries
Use JOINs to get status information:

```sql
SELECT r.*, ls.status_name_en, ls.status_name_ar
FROM {your_plugin_table} r
JOIN {local_status} ls ON ls.id = r.status_id
WHERE r.some_condition = ?
```

#### Batch Operations
For bulk status updates, use transactions:

```php
$transaction = $DB->start_delegated_transaction();
try {
    foreach ($records as $record) {
        // Update status
    }
    $transaction->allow_commit();
} catch (Exception $e) {
    $transaction->rollback($e);
}
```

## Best Practices

### 1. Error Handling
Always wrap workflow operations in try-catch blocks:

```php
try {
    $result = simple_workflow_manager::approve_workflow($record_id, $USER->id, $note);
    if ($result) {
        redirect($return_url, get_string('approved', 'local_yourplugin'));
    } else {
        throw new moodle_exception('approval_failed', 'local_yourplugin');
    }
} catch (Exception $e) {
    redirect($return_url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
}
```

### 2. Security
- Always validate user capabilities before showing action buttons
- Use sesskey validation for all state-changing operations
- Validate record ownership before allowing actions

### 3. UI/UX
- Show clear status indicators with appropriate colors
- Display workflow history when relevant
- Provide meaningful error messages
- Use AJAX for seamless workflow transitions

### 4. Multilingual Support
Always support both English and Arabic:

```php
// Get localized status name
$lang_field = (current_language() === 'ar') ? 'status_name_ar' : 'status_name_en';
$status_name = $DB->get_field('local_status', $lang_field, ['id' => $status_id]);
```

## Maintenance and Updates

### Regular Tasks
1. **Monitor workflow performance** using built-in history logs
2. **Update capabilities** as organizational structure changes
3. **Archive old workflow instances** based on retention policies
4. **Review and update status translations** for accuracy

### Upgrade Procedures
When updating workflow definitions:

1. **Backup database** before schema changes
2. **Test upgrade scripts** on development environment
3. **Document breaking changes** for dependent plugins
4. **Coordinate updates** with other plugin maintainers

## Common Integration Patterns

### Pattern 1: Simple Linear Workflow
```
Initial → Review → Approved/Rejected
```

### Pattern 2: Multi-Level Approval
```
Initial → Level1 → Level2 → Level3 → Boss → Approved/Rejected
```

### Pattern 3: Branch-based Workflow
```
Initial → Department Review → {Finance/IT/Academic} → Final Approval
```

## Troubleshooting

### Issue: Workflow stuck in status
**Cause**: User missing required capability
**Solution**: Check capability assignments and workflow step configuration

### Issue: Foreign key violations
**Cause**: Trying to reference non-existent status ID
**Solution**: Validate status exists before assignment

### Issue: Inconsistent status display
**Cause**: Missing language strings or incorrect field references
**Solution**: Verify language files and database field names

## Support and Resources

- **Status Plugin Documentation**: See other files in this Documentation folder
- **Moodle Development Docs**: https://moodledev.io/
- **Workflow Examples**: Check existing integrations (financeservices, participant, etc.)
- **Database Schema**: See `db/install.xml` for complete table definitions 