# Plugin Interactions Documentation - Status Plugin

## Overview

The Status Plugin (`local_status`) serves as a centralized workflow engine for multiple Moodle plugins. This document details how other plugins integrate with the Status Plugin and the patterns used for this integration.

## Integration Architecture

### Core Integration Pattern

Other plugins integrate with the Status Plugin by:
1. **Referencing workflow types** by `type_id` in the `local_status_type` table
2. **Storing status references** as foreign keys to `local_status` table 
3. **Using workflow manager classes** for status transitions
4. **Implementing simple workflow managers** for plugin-specific logic

## Currently Integrated Plugins

### 1. Finance Services (`local_financeservices`)

**Integration Type**: Financial request approvals  
**Workflow Type ID**: 2  
**Workflow Name**: `finance_workflow`

#### Database Integration
```sql
-- Stores status_id as foreign key
ALTER TABLE mdl_local_financeservices_requests 
ADD COLUMN status_id INT(10) REFERENCES local_status(id);
```

#### Class Integration
```php
// File: local/financeservices/classes/simple_workflow_manager.php
class simple_workflow_manager {
    const WORKFLOW_TYPE_ID = 2; // Finance workflow
    
    public static function get_workflow_statuses() {
        global $DB;
        return $DB->get_records('local_status', ['type_id' => 2], 'seq ASC');
    }
}
```

#### UI Integration
```php
// File: local/financeservices/index.php
// Joins local_status for translated status names
JOIN {local_status} ls ON fs.status_id = ls.id
```

### 2. Residence Booking (`local_residencebooking`)

**Integration Type**: Accommodation booking approvals  
**Workflow Type ID**: 3  
**Workflow Name**: `residence_workflow`

#### Key Features
- **Multi-stage approval**: Initial → Leader1 → Leader2 → Leader3 → Boss → Approved/Rejected
- **Capability-based approvals**: Each stage requires specific capabilities
- **Status tracking**: Full integration with status display

#### Database Integration
```sql
-- Stores workflow status for each booking request
-- Status IDs: 15(Initial), 16(Leader1), 17(Leader2), 18(Leader3), 19(Boss), 20(Approved), 21(Rejected)
```

#### Workflow Implementation
```php
// File: local/residencebooking/classes/simple_workflow_manager.php
public static function can_user_transition_to($userid, $current_status, $target_status) {
    // Uses local_status capabilities to check permissions
}
```

### 3. Computer Service (`local_computerservice`)

**Integration Type**: IT equipment and service requests  
**Workflow Type ID**: 4  
**Workflow Name**: `computer_workflow`

#### Integration Features
- **AJAX-based workflow**: Real-time status updates
- **Equipment-specific approvals**: Different workflows for different device types
- **Enhanced transparency**: Shows approval/rejection notes

#### Database Schema
```sql
-- Foreign key to local_status_type (not local_status directly)
ALTER TABLE mdl_local_computerservice_requests 
ADD FOREIGN KEY (status_id) REFERENCES local_status_type(id);
```

#### Status Management
```php
// File: local/computerservice/classes/simple_workflow_manager.php
return $DB->get_records('local_status', ['type_id' => 4], 'seq ASC');
```

### 4. Participant Management (`local_participant`)

**Integration Type**: Participant request approvals  
**Workflow Type ID**: 9  
**Workflow Name**: `participants_workflow`

#### Migration Features
- **Legacy system migration**: Migrated from custom status system to local_status
- **Workflow consistency**: Standardized approval process
- **Capability integration**: Uses status plugin capabilities

#### Database Migration
```php
// File: local/participant/db/upgrade.php
// Migrate to new workflow system using local_status
$key = new xmldb_key('request_status_workflow_fk', XMLDB_KEY_FOREIGN, 
    array('request_status_id'), 'local_status', array('id'));
```

#### Status Constants
```php
// Status IDs for participants workflow (type_id = 9)
const STATUS_INITIAL = 56;        // request_submitted
const STATUS_LEADER1_REVIEW = 57; // leader1_review  
const STATUS_LEADER2_REVIEW = 58; // leader2_review
const STATUS_LEADER3_REVIEW = 59; // leader3_review
const STATUS_BOSS_REVIEW = 60;    // boss_review
const STATUS_APPROVED = 61;       // approved
const STATUS_REJECTED = 62;       // rejected
```

### 5. Room Booking (`local_roombooking`)

**Integration Type**: Facility and room booking approvals  
**Workflow Type ID**: 8  
**Workflow Name**: `classroom_workflow`

#### Integration Pattern
```php
// File: local/roombooking/pages/bookings.php
LEFT JOIN {local_status} s ON bc.status_id = s.id
```

#### Workflow Manager Usage
```php
// Uses both workflow dashboard manager and status workflow manager
use local_status\workflow_dashboard_manager;
use local_status\status_workflow_manager;
```

### 6. Reports System (`local_reports`)

**Integration Type**: Report generation and approval workflows  
**Workflow Type ID**: 5  
**Workflow Name**: `reports_workflow`

#### Status Display Integration
```php
// File: local/reports/index.php
'local_status', 'display_name_ar', // Arabic status names
```

#### AJAX Integration
```php
// File: local/reports/approve_ajax.php
$status = $DB->get_record('local_status', ['id' => $status_id]);
```

## Integration Patterns

### Pattern 1: Direct Foreign Key Reference

**Most Common Pattern** - Store `status_id` directly referencing `local_status.id`

```sql
CREATE TABLE plugin_requests (
    id INT PRIMARY KEY,
    status_id INT REFERENCES local_status(id),
    -- other fields
);
```

**Benefits:**
- Simple and direct relationship
- Efficient JOIN operations
- Automatic constraint enforcement

### Pattern 2: Workflow Type Reference

**Alternative Pattern** - Reference workflow type and manage steps internally

```sql
CREATE TABLE plugin_requests (
    id INT PRIMARY KEY,
    workflow_type_id INT REFERENCES local_status_type(id),
    current_step INT,
    -- other fields
);
```

**Use Cases:**
- Complex workflow logic
- Custom step management
- Non-linear workflows

### Pattern 3: Hybrid Integration

**Advanced Pattern** - Combine direct status reference with workflow management

```sql
CREATE TABLE plugin_requests (
    id INT PRIMARY KEY,
    status_id INT REFERENCES local_status(id),
    workflow_instance_id INT REFERENCES local_status_instance(id),
    -- other fields
);
```

## Common Integration Code

### Simple Workflow Manager Template

Most integrated plugins implement a `simple_workflow_manager` class:

```php
namespace local_yourplugin;

class simple_workflow_manager {
    const WORKFLOW_TYPE_ID = X; // Your assigned workflow type ID
    
    // Status constants matching local_status records
    const STATUS_INITIAL = XX;
    const STATUS_APPROVED = XX;
    const STATUS_REJECTED = XX;
    
    public static function get_workflow_statuses() {
        global $DB;
        return $DB->get_records('local_status', 
            ['type_id' => self::WORKFLOW_TYPE_ID], 'seq ASC');
    }
    
    public static function get_status_name($status_id) {
        global $DB;
        $lang_field = (current_language() === 'ar') ? 
            'status_name_ar' : 'status_name_en';
        return $DB->get_field('local_status', $lang_field, 
            ['id' => $status_id]);
    }
    
    public static function can_user_transition_to($userid, $current_status, $target_status) {
        global $DB;
        $target_step = $DB->get_record('local_status', ['id' => $target_status]);
        if (!$target_step || empty($target_step->capability)) {
            return false;
        }
        $context = context_system::instance();
        return has_capability($target_step->capability, $context, $userid);
    }
}
```

### Status Display Pattern

Most plugins use this pattern for status display:

```php
// SQL Query with JOIN
SELECT r.*, 
       CASE WHEN ? = 'ar' THEN ls.status_name_ar 
            ELSE ls.status_name_en END as status_name
FROM {plugin_table} r
JOIN {local_status} ls ON ls.id = r.status_id

// PHP Display Logic
$status_class = 'status-' . strtolower($record->status_name);
echo '<span class="' . $status_class . '">' . $record->status_name . '</span>';
```

### AJAX Action Pattern

Common AJAX pattern for workflow actions:

```php
if ($action === 'approve') {
    require_sesskey();
    
    $result = local_yourplugin\simple_workflow_manager::approve_workflow(
        $record_id, $USER->id, $note
    );
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Approved']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed']);
    }
    exit;
}
```

## Capability Integration

### Department-Based Capabilities

Most plugins use the standard department capability pattern:

```php
// Finance workflow capabilities
'local/status:finance_officer_approve'
'local/status:finance_department_head_approve'  
'local/status:finance_service_head_approve'
'local/status:finance_ceo_approve'

// Computer service workflow capabilities
'local/status:it_officer_approve'
'local/status:it_department_head_approve'
'local/status:it_service_head_approve'
'local/status:it_ceo_approve'
```

### Plugin-Specific Capabilities

Some plugins define their own capabilities:

```php
// Participant workflow
'local/status:participants_workflow_step1'
'local/status:participants_workflow_step2'
'local/status:participants_workflow_step3'
'local/status:participants_workflow_step4'

// Residence workflow
'local/status:residence_workflow_step1'
'local/status:residence_workflow_step2'
'local/status:residence_workflow_step3'
'local/status:residence_workflow_step4'
```

## Installation and Upgrade Integration

### Install Script Integration

```php
// In plugin's db/install.php
function xmldb_local_yourplugin_install() {
    global $DB;
    
    // The workflow type is automatically created by local_status
    // Just reference it by type_id in your code
    
    // Add foreign key constraint
    $key = new xmldb_key('status_fk', XMLDB_KEY_FOREIGN, 
        ['status_id'], 'local_status', ['id']);
    // Add to table...
}
```

### Upgrade Script Integration

```php
// In plugin's db/upgrade.php
if ($oldversion < 2025070800) {
    // Migrate from old status system to local_status
    $initial_status = $DB->get_record('local_status', [
        'type_id' => YOUR_WORKFLOW_TYPE_ID,
        'is_initial' => 1
    ]);
    
    // Update all records to use new status system
    $DB->execute("UPDATE {plugin_table} SET status_id = ? WHERE old_status = 'new'", 
        [$initial_status->id]);
        
    upgrade_plugin_savepoint(true, 2025070800, 'local', 'yourplugin');
}
```

## Benefits of Integration

### For Plugin Developers
1. **Standardized workflow logic** - No need to implement custom approval systems
2. **Multilingual support** - Automatic English/Arabic status names
3. **Audit trails** - Built-in history logging
4. **Permission management** - Leverage Moodle capability system
5. **Consistent UI patterns** - Reusable interface components

### For System Administrators
1. **Centralized workflow management** - Single interface for all workflows
2. **Consistent permissions** - Unified capability structure
3. **Better oversight** - Complete audit trails across all systems
4. **Easier maintenance** - Single plugin to update for workflow improvements

### For End Users
1. **Consistent experience** - Similar workflow patterns across plugins
2. **Clear status indication** - Standardized status names and colors
3. **Transparent process** - Visible workflow history
4. **Multilingual support** - Status names in preferred language

## Best Practices for Integration

### 1. Follow Standard Patterns
- Use the `simple_workflow_manager` class pattern
- Implement standard status constants
- Follow naming conventions for capabilities

### 2. Database Design
- Always use foreign keys to `local_status`
- Include proper indexes for performance
- Plan for future workflow changes

### 3. User Interface
- Join with `local_status` for status display
- Implement AJAX for smooth transitions
- Use consistent styling and colors

### 4. Error Handling
- Validate status IDs before assignment
- Handle workflow transition failures gracefully
- Provide meaningful error messages

### 5. Testing
- Test all workflow transitions
- Verify capability requirements
- Check multilingual status display

This integration system provides a powerful, flexible foundation for workflow management while maintaining consistency and reducing development overhead for individual plugins. 