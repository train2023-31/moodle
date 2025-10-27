# Roles and Capabilities Guide

This document provides a comprehensive overview of the roles and capabilities required for the Student Reports plugin system. It covers all permission structures, workflow capabilities, and role assignments needed for proper system operation.

## Table of Contents

1. [System Overview](#system-overview)
2. [Core Capabilities](#core-capabilities)
3. [Workflow Capabilities](#workflow-capabilities)
4. [Role Definitions](#role-definitions)
5. [Permission Matrix](#permission-matrix)
6. [Configuration Guide](#configuration-guide)
7. [Troubleshooting](#troubleshooting)

## System Overview

The Student Reports plugin uses a multi-layered permission system that combines:

- **Core Plugin Capabilities**: Basic functionality permissions
- **Workflow Capabilities**: Multi-step approval process permissions
- **Context-Based Access**: Course-level and system-level permissions
- **Role-Based Assignment**: Standard Moodle role integration

### Permission Architecture

```
User Role → Capabilities → Context → Actions
    ↓           ↓           ↓         ↓
Manager → local/reports:manage → Course → Create/Edit Reports
Teacher → local/status:reports_workflow_step1 → Course → Approve Step 1
Admin → local/status:reports_workflow_step3 → System → Final Approval
```

## Core Capabilities

### `local/reports:manage`

**Purpose**: Primary capability for report management operations

**Context Level**: `CONTEXT_COURSE`

**Risk Level**: `RISK_SPAM | RISK_XSS`

**Default Role Assignments** (from `db/access.php`):
- `editingteacher` → `CAP_ALLOW`
- `manager` → `CAP_ALLOW`

**Actions Enabled**:
- Create new student reports
- Edit existing reports (when in draft status)
- Submit reports for workflow processing
- Access report management interface
- View and modify report content

**Usage Locations**:
- `index.php` - Main course interface access
- `form_ajax.php` - Report creation and editing
- `approve_ajax.php` - Single course report management
- `disapprove_ajax.php` - Single course disapproval actions

### `local/reports:viewall`

**Purpose**: Read-only access to view all reports in a course

**Context Level**: `CONTEXT_COURSE`

**Risk Level**: Read-only (no risk flags)

**Default Role Assignments** (from `db/access.php`):
- `manager` → `CAP_ALLOW`
- `editingteacher` → `CAP_ALLOW`

**Actions Enabled**:
- View all reports in a course
- Access pending and approved reports tabs
- Preview reports (read-only)
- View report status and workflow information
- Access course-level report listings

**Usage Locations**:
- `lib.php` - Navigation menu display
- `allreports.php` - System-wide report viewing
- `approve_ajax.php` - Bulk operations across courses
- `disapprove_ajax.php` - System-wide disapproval actions

## Workflow Capabilities

The workflow system uses a series of step-specific capabilities that control the multi-stage approval process.

### Workflow Status Flow

```
Draft (0) → Pending (30) → Under Review (31) → Approved (32) → Final (33)
              ↓              ↓                    ↓
           [Disapproval can return to previous state]
```

### `local/status:reports_workflow_step1`

**Purpose**: First approval step - moves reports from Pending (30) to Under Review (31)

**Context Level**: `CONTEXT_COURSE`

**Typical Role**: Department reviewers, senior teachers, course coordinators

**Actions Enabled**:
- Approve reports from Pending status
- Move reports to Under Review status
- Access reports in Pending state for review
- Perform bulk approval operations for Step 1

**Workflow Configuration**:
```php
30 => ['next' => 31, 'capability' => 'local/status:reports_workflow_step1']
```

### `local/status:reports_workflow_step2`

**Purpose**: Second approval step - moves reports from Under Review (31) to Approved (32)

**Context Level**: `CONTEXT_COURSE`

**Typical Role**: Department heads, senior administrators, quality assurance staff

**Actions Enabled**:
- Approve reports from Under Review status
- Move reports to Approved status
- Access reports in Under Review state
- Perform bulk approval operations for Step 2
- Disapprove reports back to Pending status

**Workflow Configuration**:
```php
31 => ['next' => 32, 'capability' => 'local/status:reports_workflow_step2']
```

### `local/status:reports_workflow_step3`

**Purpose**: Final approval step - moves reports from Approved (32) to Final (33)

**Context Level**: `CONTEXT_COURSE` and `CONTEXT_SYSTEM`

**Typical Role**: Final authorities, system administrators, academic directors

**Actions Enabled**:
- Approve reports from Approved status to Final
- Access system-wide reports interface (`allreports.php`)
- Perform final approval operations
- Disapprove reports back to Under Review status
- Access all reports across all courses

**Workflow Configuration**:
```php
32 => ['next' => 33, 'capability' => 'local/status:reports_workflow_step3']
```

**Special Requirements**:
- Required for accessing `allreports.php` (system-wide interface)
- Must be assigned at system context level for cross-course operations

## Complete Role Inventory

This section documents **every role** that exists and is used within the reports plugin system.

### All Roles in the Reports Plugin System

#### 1. **Trainee Role (المتدرب)** - Custom Role
- **Role Shortname**: `trainee`
- **Arabic Translation**: `المتدرب`
- **Type**: Custom role (not standard Moodle)
- **Purpose**: Primary role for students/trainees who are subjects of reports
- **Capabilities**: None (subjects only)
- **Access Level**: No direct plugin access
- **Implementation**: Used for filtering report subjects in `index.php`

#### 2. **Manager Role** - Standard Moodle Role
- **Role Shortname**: `manager`
- **Type**: Standard Moodle role
- **Purpose**: Complete system administration and oversight
- **Capabilities**: All reports capabilities + all workflow steps
- **Access Level**: System-wide and course-specific
- **Default Assignment**: `local/reports:manage` and `local/reports:viewall`

#### 3. **Editing Teacher Role** - Standard Moodle Role
- **Role Shortname**: `editingteacher`
- **Type**: Standard Moodle role
- **Purpose**: Course-level report management and initial approvals
- **Capabilities**: Report management + optional workflow step 1
- **Access Level**: Course-specific
- **Default Assignment**: `local/reports:manage` and `local/reports:viewall`

#### 4. **Teacher Role** - Standard Moodle Role
- **Role Shortname**: `teacher`
- **Type**: Standard Moodle role
- **Purpose**: Limited report viewing and basic operations
- **Capabilities**: Read-only access to view reports
- **Access Level**: Course-specific (read-only)
- **Default Assignment**: None (must be manually assigned)

#### 5. **Student Role** - Standard Moodle Role
- **Role Shortname**: `student`
- **Type**: Standard Moodle role
- **Purpose**: Not used in this system (replaced by Trainee role)
- **Capabilities**: None
- **Access Level**: No access to plugin
- **Note**: This role exists in Moodle but is not used by the reports plugin

### Role Usage Summary

| Role | Type | Used in Reports Plugin | Primary Function |
|------|------|----------------------|------------------|
| **trainee** | Custom | ✅ Yes | Subject of reports |
| **manager** | Standard | ✅ Yes | System administration |
| **editingteacher** | Standard | ✅ Yes | Report creation/management |
| **teacher** | Standard | ✅ Yes | Report viewing |
| **student** | Standard | ❌ No | Not used (replaced by trainee) |

### Role Implementation Details

#### Custom Roles

##### Trainee Role (المتدرب)

**Role Shortname**: `trainee`  
**Arabic Translation**: `المتدرب`  
**Purpose**: Primary custom role for students/trainees in the reports system

**Key Characteristics**:
- **Subject Role**: Trainees are the subjects of reports, not the creators
- **Filtering Logic**: The system specifically filters to show only users with the "trainee" role
- **No Direct Access**: Trainees don't have direct access to the reports plugin interface
- **Report Targets**: Teachers create reports about trainees

**Implementation in Code**:
```php
// Only include users with role "trainee"
if (!user_has_role_assignment($u->id, $DB->get_field('role', 'id', ['shortname' => 'trainee']), $context->id)) {
    continue;
}
```

**Required Capabilities**: None (subjects of reports only)

**Access Level**: No direct access to plugin

**Typical Use Cases**:
- Students/trainees are the subjects of reports created by teachers
- No direct interaction with the plugin interface
- Reports are created about trainees, not by them

#### Standard Moodle Roles

### Manager Role

**Primary Responsibilities**: Complete system administration and oversight

**Required Capabilities**:
- `local/reports:manage` → `CAP_ALLOW`
- `local/reports:viewall` → `CAP_ALLOW`
- `local/status:reports_workflow_step1` → `CAP_ALLOW`
- `local/status:reports_workflow_step2` → `CAP_ALLOW`
- `local/status:reports_workflow_step3` → `CAP_ALLOW`

**Access Level**: System-wide and course-specific

**Typical Use Cases**:
- System administration and configuration
- Final approval authority
- Cross-course report management
- Troubleshooting and support

### Editing Teacher Role

**Primary Responsibilities**: Course-level report management and initial approvals

**Required Capabilities**:
- `local/reports:manage` → `CAP_ALLOW`
- `local/reports:viewall` → `CAP_ALLOW`
- `local/status:reports_workflow_step1` → `CAP_ALLOW` (optional)

**Access Level**: Course-specific

**Typical Use Cases**:
- Creating and editing student reports
- Initial report review and approval
- Course-specific report management
- Student guidance and support

### Teacher Role

**Primary Responsibilities**: Limited report viewing and basic operations

**Required Capabilities**:
- `local/reports:viewall` → `CAP_ALLOW`

**Access Level**: Course-specific (read-only)

**Typical Use Cases**:
- Viewing student reports
- Monitoring report progress
- Basic report information access

### Student Role

**Primary Responsibilities**: Subject of reports (not report creators)

**Required Capabilities**: None

**Access Level**: No direct access to plugin

**Typical Use Cases**:
- Students are the subjects of reports created by teachers
- No direct interaction with the plugin interface

**Note**: In this system, the custom "Trainee" role is used instead of the standard "Student" role for filtering report subjects.

## Permission Matrix

| Action | Manager | Editing Teacher | Teacher | Trainee | Student |
|--------|---------|-----------------|---------|---------|---------|
| **Create Reports** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Edit Reports** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **View All Reports** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Approve Step 1** | ✅ | ✅* | ❌ | ❌ | ❌ |
| **Approve Step 2** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Approve Step 3** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **System-wide Access** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Bulk Operations** | ✅ | ✅* | ❌ | ❌ | ❌ |
| **Disapprove Reports** | ✅ | ✅* | ❌ | ❌ | ❌ |
| **Subject of Reports** | ❌ | ❌ | ❌ | ✅ | ❌ |

*Optional - depends on specific role configuration

**Note**: The "Trainee" role is specifically used as the subject of reports, while the standard "Student" role is not used in this system.

## Role Archetypes and Default Assignments

### Capability Archetype Definitions

The reports plugin defines capabilities with specific archetype assignments in `db/access.php`:

#### `local/reports:manage` Capability
```php
'local/reports:manage' => [
    'riskbitmask' => RISK_SPAM | RISK_XSS,
    'captype'     => 'write',
    'contextlevel'=> CONTEXT_COURSE,
    'archetypes'  => [
        'editingteacher' => CAP_ALLOW,
        'manager'        => CAP_ALLOW,
    ],
],
```

**Archetype Assignments**:
- **editingteacher**: `CAP_ALLOW` - Can create and edit reports
- **manager**: `CAP_ALLOW` - Can create and edit reports
- **teacher**: Not assigned (no default access)
- **student**: Not assigned (no default access)

#### `local/reports:viewall` Capability
```php
'local/reports:viewall' => [
    'captype'     => 'read',
    'contextlevel'=> CONTEXT_COURSE,
    'archetypes'  => [
        'manager' => CAP_ALLOW,
        'editingteacher' => CAP_ALLOW,
    ],
],
```

**Archetype Assignments**:
- **manager**: `CAP_ALLOW` - Can view all reports
- **editingteacher**: `CAP_ALLOW` - Can view all reports
- **teacher**: Not assigned (no default access)
- **student**: Not assigned (no default access)

### Role Archetype Summary

| Role Archetype | local/reports:manage | local/reports:viewall | Notes |
|----------------|---------------------|----------------------|-------|
| **manager** | ✅ CAP_ALLOW | ✅ CAP_ALLOW | Full access |
| **editingteacher** | ✅ CAP_ALLOW | ✅ CAP_ALLOW | Full access |
| **teacher** | ❌ Not assigned | ❌ Not assigned | Must be manually assigned |
| **student** | ❌ Not assigned | ❌ Not assigned | Not used in this system |

### Custom Role Considerations

#### Trainee Role
- **Not a standard Moodle archetype**
- **No capability assignments** (subjects of reports only)
- **Must be created manually** in Moodle
- **Used for filtering** report subjects in the interface

## Integration with Broader Role System

### Multi-Plugin Role Architecture

The reports system integrates with a broader organizational role system that includes multiple plugins with consistent role patterns:

#### 4-Tier Department Hierarchy
Each department follows a consistent hierarchy across all plugins:

1. **Officer Level (ض.د)** - `{department}_officer_approve`
2. **Department Head (ض.ق)** - `{department}_department_head_approve`  
3. **Service Head (ض.ق.خ)** - `{department}_service_head_approve`
4. **CEO Level (ر.د)** - `{department}_ceo_approve`

#### Department-Specific Capabilities
| Department | Capabilities Prefix | Use Cases |
|------------|-------------------|-----------|
| **Academic Affairs** | `academic_*` | Course approval, curriculum changes |
| **Finance** | `finance_*` | Budget approvals, purchases |
| **Student Services** | `residence_*` | Accommodation, student services |
| **IT** | `it_*` | System requests, technical support |
| **Research** | `research_*` | Research proposals, reports |
| **Training** | `training_*` | Professional development |
| **Planning** | `planning_*` | Strategic planning, development |
| **Facilities** | `facilities_*` | Space allocation, maintenance |

#### Cross-Plugin Integration
The reports system works alongside other plugins with similar role patterns:

- **Computer Service Plugin**: Uses `local/computerservice:*` capabilities
- **Residence Booking Plugin**: Uses `local/status:residence_workflow_step*` capabilities
- **Participant Management Plugin**: Uses status IDs 56-62 for workflow stages
- **Status Plugin**: Provides the underlying workflow framework for all plugins

### Bilingual Role Support

The system supports **bilingual implementation**:
- **English**: Standard role names and capabilities
- **Arabic**: Full RTL support with Arabic translations
- **Consistent Terminology**: Standardized Arabic terms across all plugins
- **Language Files**: Separate language files for each plugin with role-specific strings

## Configuration Guide

### Step 1: Access Role Management

1. **Navigate to Role Management**:
   - Go to **Site Administration > Users > Permissions > Define roles**
   - Or **Site Administration > Users > Permissions > Define roles**

2. **Select Role to Configure**:
   - Choose the role you want to modify (e.g., "Manager", "Editing Teacher")
   - Click **Edit** to modify the role

### Step 2: Configure Core Capabilities

#### For Manager Role:
```
Search for: "local/reports"
Set the following:
- local/reports:manage → Allow
- local/reports:viewall → Allow
```

#### For Editing Teacher Role:
```
Search for: "local/reports"
Set the following:
- local/reports:manage → Allow
- local/reports:viewall → Allow
```

#### For Teacher Role:
```
Search for: "local/reports"
Set the following:
- local/reports:viewall → Allow
```

#### For Trainee Role:
```
No capabilities needed - trainees are subjects of reports only
Trainees should not have any local/reports capabilities
```

### Step 3: Configure Workflow Capabilities

#### For Manager Role:
```
Search for: "local/status"
Set the following:
- local/status:reports_workflow_step1 → Allow
- local/status:reports_workflow_step2 → Allow
- local/status:reports_workflow_step3 → Allow
```

#### For Editing Teacher Role (Optional):
```
Search for: "local/status"
Set the following:
- local/status:reports_workflow_step1 → Allow (if desired)
```

### Step 4: Context-Level Assignments

#### Course-Level Assignments:
1. **Navigate to Course**:
   - Go to the specific course
   - Click **Course Administration > Users > Permissions > Assign roles**

2. **Assign Roles**:
   - Select appropriate role (Manager, Editing Teacher, Trainee, etc.)
   - Assign to specific users
   - Verify context is set to "Course"

3. **Trainee Role Assignment**:
   - Assign "Trainee" role to students who will be subjects of reports
   - Ensure trainees are enrolled in the course
   - Verify the role shortname is exactly "trainee"

#### System-Level Assignments (for Managers):
1. **Navigate to System Context**:
   - Go to **Site Administration > Users > Permissions > Assign roles**
   - Select "System" context

2. **Assign Manager Role**:
   - Assign Manager role to system administrators
   - This enables system-wide report access

### Step 5: Verify Configuration

#### Test Access Levels:
1. **Login as Different Users**:
   - Test with Manager role
   - Test with Editing Teacher role
   - Test with Teacher role

2. **Verify Functionality**:
   - Check report creation access
   - Test workflow approval steps
   - Verify system-wide access (for managers)

3. **Check Error Messages**:
   - Ensure appropriate "permission denied" messages
   - Verify graceful degradation of unavailable features

## Troubleshooting

### Common Permission Issues

#### Issue: "You do not have permission to access this page"

**Symptoms**:
- Users cannot access the reports interface
- Permission denied errors appear

**Solutions**:
1. **Check Role Assignment**:
   - Verify user has appropriate role assigned
   - Check role is assigned at correct context level

2. **Verify Capabilities**:
   - Ensure required capabilities are set to "Allow"
   - Check capability inheritance from parent contexts

3. **Clear Caches**:
   - Go to **Site Administration > Development > Purge caches**
   - Clear all caches, especially "Language strings" and "Theme"

#### Issue: Workflow buttons not appearing

**Symptoms**:
- Approve/Disapprove buttons missing
- Users cannot perform workflow actions

**Solutions**:
1. **Check Workflow Capabilities**:
   - Verify user has appropriate workflow step capability
   - Ensure capability is assigned at course context level

2. **Verify Report Status**:
   - Check report is in correct status for the action
   - Ensure workflow configuration is correct

3. **Review JavaScript**:
   - Check browser console for JavaScript errors
   - Verify theme includes required JavaScript files

#### Issue: Cannot access system-wide reports

**Symptoms**:
- `allreports.php` shows permission denied
- Cannot view reports across courses

**Solutions**:
1. **Check System Context**:
   - Verify user has `local/status:reports_workflow_step3` capability
   - Ensure capability is assigned at system context level

2. **Verify Role Assignment**:
   - Check user has Manager role at system level
   - Confirm role includes all required capabilities

#### Issue: Bulk operations not working

**Symptoms**:
- "Approve All" or "Disapprove All" buttons not functional
- Bulk operations fail silently

**Solutions**:
1. **Check Capability Scope**:
   - Verify user has appropriate workflow capability
   - Ensure capability covers all reports in the operation

2. **Review AJAX Endpoints**:
   - Check `approve_ajax.php` and `disapprove_ajax.php` access
   - Verify session keys and parameters

#### Issue: No trainees showing in reports interface

**Symptoms**:
- Empty trainee list in "Add Report" tab
- "All students have reports" message when trainees exist

**Solutions**:
1. **Check Trainee Role Assignment**:
   - Verify users have "Trainee" role assigned (shortname: `trainee`)
   - Ensure role is assigned at course context level
   - Check role assignment is active and not expired

2. **Verify Role Shortname**:
   - Confirm role shortname is exactly "trainee" (case-sensitive)
   - Check for typos in role shortname
   - Verify role exists in the system

3. **Check Course Enrollment**:
   - Ensure trainees are enrolled in the course
   - Verify enrollment is active
   - Check course context permissions

4. **Debug Role Assignment**:
   ```php
   // Debug code to check trainee role assignments
   global $DB;
   $context = context_course::instance($courseid);
   $trainee_role_id = $DB->get_field('role', 'id', ['shortname' => 'trainee']);
   $enrolled_users = get_enrolled_users($context);
   
   foreach ($enrolled_users as $user) {
       $has_trainee_role = user_has_role_assignment($user->id, $trainee_role_id, $context->id);
       echo "User: " . fullname($user) . " - Trainee Role: " . ($has_trainee_role ? 'YES' : 'NO') . "<br>";
   }
   ```

### Permission Debugging

#### Enable Debug Mode:
```php
// In config.php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
$CFG->debugdeveloper = true;
```

#### Check Capability Assignment:
```php
// Debug code to check capabilities
global $USER, $DB;
$context = context_course::instance($courseid);
$capabilities = [
    'local/reports:manage',
    'local/reports:viewall',
    'local/status:reports_workflow_step1',
    'local/status:reports_workflow_step2',
    'local/status:reports_workflow_step3'
];

foreach ($capabilities as $cap) {
    $has_cap = has_capability($cap, $context, $USER);
    echo "Capability: $cap - " . ($has_cap ? 'YES' : 'NO') . "<br>";
}
```

#### Review Role Assignments:
1. **Check User Roles**:
   - Go to **Site Administration > Users > Accounts > Browse list of users**
   - Find specific user and check assigned roles

2. **Review Context Assignments**:
   - Check both course-level and system-level assignments
   - Verify role assignments are not conflicting

### Best Practices

#### For Administrators:
1. **Principle of Least Privilege**:
   - Grant only necessary capabilities
   - Regularly review and audit permissions

2. **Clear Role Definitions**:
   - Document role responsibilities
   - Provide training on workflow procedures

3. **Regular Audits**:
   - Periodically review capability assignments
   - Remove unnecessary permissions

#### For Developers:
1. **Always Check Capabilities**:
   - Verify permissions before any action
   - Provide clear error messages for permission failures

2. **Context Awareness**:
   - Use appropriate context levels
   - Consider inheritance and overrides

3. **Graceful Degradation**:
   - Hide unavailable features
   - Provide helpful error messages

This completes the comprehensive roles and capabilities guide. For additional information, refer to the other documentation files in this folder.
