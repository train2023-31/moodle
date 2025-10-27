# Department-Specific Capabilities Guide

This document explains the comprehensive department-specific capability structure implemented in the Local Status workflow system, providing clear organizational hierarchy and role-based permissions.

## 🎯 Overview

The Local Status plugin implements a sophisticated capability system that mirrors real organizational structures. Each department has specific capabilities that reflect actual roles and responsibilities within educational institutions.

## 🏢 Organizational Structure

### Department Hierarchy

Each department follows a **consistent four-tier approval structure**:

1. **Officer Level (ض.د)** - Front-line staff who handle initial reviews and coordination
2. **Head of Department (ض.ق)** - Department-level management who oversee operations  
3. **Head of Department Service (ض.ق.خ)** - Senior management who handle service oversight
4. **CEO Level (ر.د)** - Ultimate institutional authority and final approvals

### Standardized Arabic Naming
All workflows use consistent Arabic terminology:
- **قيد اعتماد ض.د** - Officer Level Review
- **قيد اعتماد ض.ق** - Head of Department Review  
- **قيد اعتماد ض.ق.خ** - Head of Department Service Review
- **قيد اعتماد ر.د** - CEO Level Review

## 📋 Department Capabilities

### 🎓 Academic Affairs Department
**Purpose**: Course development, curriculum approval, academic planning

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Academic Officer | `local/status:academic_officer_approve` | قيد اعتماد ض.د | Initial course and curriculum reviews |
| Head of Department | `local/status:academic_department_head_approve` | قيد اعتماد ض.ق | Department-level academic decisions |
| Head of Service | `local/status:academic_service_head_approve` | قيد اعتماد ض.ق.خ | Academic service oversight |
| CEO | `local/status:academic_ceo_approve` | قيد اعتماد ر.د | Final academic authority |

**Typical Workflows**: Course approval, curriculum changes, academic policy updates

### 💰 Finance Department  
**Purpose**: Budget management, financial approvals, procurement

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Finance Officer | `local/status:finance_officer_approve` | قيد اعتماد ض.د | Basic financial transaction reviews |
| Head of Department | `local/status:finance_department_head_approve` | قيد اعتماد ض.ق | Departmental budget decisions |
| Head of Service | `local/status:finance_service_head_approve` | قيد اعتماد ض.ق.خ | Financial service oversight |
| CEO | `local/status:finance_ceo_approve` | قيد اعتماد ر.د | Major financial approvals |

**Typical Workflows**: Purchase requests, budget allocations, expense approvals

### 🏠 Student Services Department
**Purpose**: Student accommodation, services, support systems  

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Residence Officer | `local/status:residence_officer_approve` | قيد اعتماد ض.د | Room booking and allocation |
| Head of Department | `local/status:residence_department_head_approve` | قيد اعتماد ض.ق | Residence facility management |
| Head of Service | `local/status:residence_service_head_approve` | قيد اعتماد ض.ق.خ | Student service oversight |
| CEO | `local/status:residence_ceo_approve` | قيد اعتماد ر.د | Overall student services authority |

**Typical Workflows**: Residence booking, student service requests, accommodation changes

### 💻 IT Department
**Purpose**: Technology services, system administration, technical support

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| IT Officer | `local/status:it_officer_approve` | قيد اعتماد ض.د | Basic technical support and maintenance |
| Head of Department | `local/status:it_department_head_approve` | قيد اعتماد ض.ق | Department technology decisions |
| Head of Service | `local/status:it_service_head_approve` | قيد اعتماد ض.ق.خ | Technology service oversight |
| CEO | `local/status:it_ceo_approve` | قيد اعتماد ر.د | Strategic technology planning |

**Typical Workflows**: Computer service requests, software installations, system upgrades

### 🔬 Research Department
**Purpose**: Research project management, academic research support

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Research Officer | `local/status:research_officer_approve` | قيد اعتماد ض.د | Research proposal initial review |
| Head of Department | `local/status:research_department_head_approve` | قيد اعتماد ض.ق | Department research oversight |
| Head of Service | `local/status:research_service_head_approve` | قيد اعتماد ض.ق.خ | Research service oversight |
| CEO | `local/status:research_ceo_approve` | قيد اعتماد ر.د | Institution research strategy |

**Typical Workflows**: Research proposals, report submissions, research resource allocation

### 📚 Training Department
**Purpose**: Staff development, professional training programs

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Training Officer | `local/status:training_officer_approve` | قيد اعتماد ض.د | Training program coordination |
| Head of Department | `local/status:training_department_head_approve` | قيد اعتماد ض.ق | Department training oversight |
| Head of Service | `local/status:training_service_head_approve` | قيد اعتماد ض.ق.خ | Training service oversight |
| CEO | `local/status:training_ceo_approve` | قيد اعتماد ر.د | Institutional training strategy |

**Typical Workflows**: Training requests, professional development plans, certification programs

### 📋 Planning Department  
**Purpose**: Strategic planning, institutional development, annual planning

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Planning Officer | `local/status:planning_officer_approve` | قيد اعتماد ض.د | Plan development and coordination |
| Head of Department | `local/status:planning_department_head_approve` | قيد اعتماد ض.ق | Department planning oversight |
| Head of Service | `local/status:planning_service_head_approve` | قيد اعتماد ض.ق.خ | Planning service oversight |
| CEO | `local/status:planning_ceo_approve` | قيد اعتماد ر.د | Strategic institutional planning |

**Typical Workflows**: Annual plans, strategic initiatives, institutional development projects

### 🏢 Facilities Department
**Purpose**: Physical infrastructure, classroom management, space allocation

| Role | Capability | Arabic Description | English Description |
|------|------------|-------------------|---------------------|
| Facilities Officer | `local/status:facilities_officer_approve` | قيد اعتماد ض.د | Daily facility operations |
| Head of Department | `local/status:facilities_department_head_approve` | قيد اعتماد ض.ق | Department facility management |
| Head of Service | `local/status:facilities_service_head_approve` | قيد اعتماد ض.ق.خ | Facility service oversight |
| CEO | `local/status:facilities_ceo_approve` | قيد اعتماد ر.د | Infrastructure planning and oversight |

**Typical Workflows**: Classroom booking, facility maintenance, space allocation

### 🔧 System Administration
**Purpose**: Workflow system management and technical oversight

| Role | Capability | Description |
|------|------------|-------------|
| Workflow Manager | `local/status:manage_workflows` | Workflow configuration and management |
| System Viewer | `local/status:view_all_requests` | System-wide request visibility |

## 🛠️ Database Implementation

### Defined in `db/access.php`
```php
$capabilities = [
    // Academic Affairs Department - Standardized Hierarchy
    'local/status:academic_officer_approve' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => []
    ],
    'local/status:academic_department_head_approve' => [
        'captype' => 'write', 
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => ['editingteacher' => CAP_ALLOW]
    ],
    'local/status:academic_service_head_approve' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => []
    ],
    'local/status:academic_ceo_approve' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => []
    ],
    
    // Finance Department - Same Pattern
    'local/status:finance_officer_approve' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => []
    ],
    // ... additional capabilities follow same pattern
];
```

### Language Support

**English** (`lang/en/local_status.php`):
```php
$string['academic_officer_approve'] = 'Academic Officer Approval';
$string['academic_department_head_approve'] = 'Academic Department Head Approval'; 
$string['academic_service_head_approve'] = 'Academic Service Head Approval';
$string['academic_ceo_approve'] = 'Academic CEO Approval';

$string['finance_officer_approve'] = 'Finance Officer Approval';
$string['finance_department_head_approve'] = 'Finance Department Head Approval';
```

**Arabic** (`lang/ar/local_status.php`):
```php
$string['academic_officer_approve'] = 'موافقة موظف أكاديمي';
$string['academic_department_head_approve'] = 'موافقة رئيس قسم أكاديمي';
$string['academic_service_head_approve'] = 'موافقة رئيس خدمة أكاديمية';
$string['academic_ceo_approve'] = 'موافقة رئيس تنفيذي أكاديمي';
```

## 🎯 Usage Examples

### Assigning Capabilities to Users

#### Through Role Management
1. Go to **Site Administration > Users > Permissions > Define roles**
2. Create or edit a role (e.g., "Finance Department Head")  
3. Add the capability `local/status:finance_department_head_approve`
4. Assign users to this role in appropriate contexts

#### Through Code
```php
// Assign capability to specific user
$context = context_system::instance();
$roleid = $DB->get_field('role', 'id', ['shortname' => 'finance_department_head']);

role_assign($roleid, $userid, $context->id);

// Check if user has capability
if (has_capability('local/status:finance_department_head_approve', $context, $userid)) {
    // User can approve finance workflows at department head level
}
```

### Checking Approval Permissions

```php
// Using current implemented function
if (local_status_can_approve($status_id)) {
    // Show approval button
    echo '<button class="btn btn-success">Approve</button>';
}
```

### Creating Department-Specific Workflows

```php
use local_status\workflow_manager;

// Create workflow type
$workflow_data = [
    'name' => 'finance_approval',
    'display_name_en' => 'Finance Approval Process',
    'display_name_ar' => 'عملية الموافقة المالية',
    'plugin_name' => 'local_finance',
    'is_active' => 1
];
$type_id = workflow_manager::create_workflow_type($workflow_data);

// Create standardized steps
$steps = [
    [
        'type_id' => $type_id,
        'name' => 'officer_review',
        'display_name_en' => 'Finance Officer Review',
        'display_name_ar' => 'قيد اعتماد ض.د',
        'approval_type' => 'capability',
        'capability' => 'local/status:finance_officer_approve',
        'seq' => 1,
        'color' => '#17a2b8'
    ],
    [
        'type_id' => $type_id,
        'name' => 'department_head_review',
        'display_name_en' => 'Head of Department Review',
        'display_name_ar' => 'قيد اعتماد ض.ق',
        'approval_type' => 'capability',
        'capability' => 'local/status:finance_department_head_approve',
        'seq' => 2,
        'color' => '#ffc107'
    ],
    [
        'type_id' => $type_id,
        'name' => 'service_head_review',
        'display_name_en' => 'Head of Service Review',
        'display_name_ar' => 'قيد اعتماد ض.ق.خ',
        'approval_type' => 'capability',
        'capability' => 'local/status:finance_service_head_approve',
        'seq' => 3,
        'color' => '#fd7e14'
    ],
    [
        'type_id' => $type_id,
        'name' => 'ceo_review',
        'display_name_en' => 'CEO Review',
        'display_name_ar' => 'قيد اعتماد ر.د',
        'approval_type' => 'capability',
        'capability' => 'local/status:finance_ceo_approve',
        'seq' => 4,
        'color' => '#6f42c1'
    ],
    [
        'type_id' => $type_id,
        'name' => 'approved',
        'display_name_en' => 'Approved',
        'display_name_ar' => 'تم الإعتماد',
        'approval_type' => 'any',
        'seq' => 5,
        'color' => '#28a745',
        'is_final' => 1
    ]
];

foreach ($steps as $step_data) {
    workflow_manager::create_workflow_step($step_data);
}
```

## 🔍 Benefits

### ✅ **Clear Organizational Hierarchy**
- **Standardized Structure**: Every department follows the same four-tier approval pattern
- **Consistent Arabic Names**: Same terminology across all workflows (ض.د, ض.ق, ض.ق.خ, ر.د)
- **Predictable Capabilities**: `{department}_{level}_approve` pattern throughout
- **Clear Role Definition**: Each level has distinct responsibilities

### ✅ **Improved User Experience**  
- **Familiar Process**: Users know what to expect regardless of department
- **Consistent Interface**: Same approval screens and actions across all workflows
- **Multilingual Support**: Arabic and English names for all levels

### ✅ **Easy Permission Management**
- **Standard Moodle Integration**: Uses native role/capability system
- **Scalable Assignment**: Easy to create department-specific roles
- **Hierarchical Permissions**: Higher levels can have lower-level capabilities too
- **Bulk Management**: Assign multiple users to department roles quickly

### ✅ **Developer Benefits**
- **Predictable API**: Same patterns work across all departments
- **Easy Integration**: Simple capability checks for all workflows
- **Reusable Code**: Common functions work for any department
- **Future-Proof**: New departments automatically fit the pattern

## 🛠️ Troubleshooting

### **Capability Assignment Issues**
```php
// Debug department capability assignments
function debug_department_capabilities($user_id, $department) {
    $context = context_system::instance();
    $levels = ['officer', 'department_head', 'service_head', 'ceo'];
    
    foreach ($levels as $level) {
        $capability = "local/status:{$department}_{$level}_approve";
        $has_cap = has_capability($capability, $context, $user_id);
        debugging("User {$user_id} can approve {$department} {$level}: " . ($has_cap ? 'YES' : 'NO'));
    }
}
```

---

**Version**: 3.0.1 - Department Capabilities System  
**Last Updated**: January 2025  
**Compatibility**: Moodle 3.9+