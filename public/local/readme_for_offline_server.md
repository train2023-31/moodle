# Moodle Offline Server Migration Guide

This document contains everything that needs to be edited, configured, and verified when moving your Moodle installation to an offline server environment.

## 🚨 Critical External Dependencies to Address

### 1. MariaDB Installation/Upgrade Video Reference
**Important Note**: When installing MariaDB or upgrading it for the offline server, refer to this video tutorial:
- **Video URL**: https://youtu.be/-GmyjYEfuzE?si=BCDqolAXY8GMqm1P
- **Purpose**: Essential guide for proper MariaDB setup and configuration
- **When to Use**: Before or during offline server migration when database setup is required

### 2. Status Records Synchronization
**Critical Note**: When any record has been added or deleted in the system, ALL status-related records must be updated across all systems to maintain data consistency.

**Important Requirements**:
- **Cross-System Updates**: Any record addition/deletion requires status record updates in ALL related systems
- **Plugin Dependencies**: Every plugin that depends on the modified records must be updated accordingly
- **Data Consistency**: Status records must remain synchronized between online and offline servers
- **Workflow Integrity**: Status changes affect workflow states and must be properly propagated
- **Database Synchronization**: All status-related tables must be updated when records are modified

**Affected Systems**:
- **Local Status Plugin**: `mdl_local_status` table and related status records
- **Workflow Management**: Status transitions and workflow states
- **Annual Plans**: Status tracking for plan records
- **Oracle Integration**: Employee and person status records
- **Role Assignments**: User status and role-related records

**Dependent Plugins That Must Be Updated**:

**Core Workflow Plugins** (All use `simple_workflow_manager` and depend on status records):
- **local/participant** - Participant requests (Status IDs: 56-62)
- **local/residencebooking** - Residence booking requests (Status IDs: 15-21)
- **local/roombooking** - Room booking requests (Status IDs: varies)
- **local/financeservices** - Financial service requests (Status IDs: varies)
- **local/computerservice** - Computer service requests (Status IDs: varies)

**Oracle-Dependent Plugins** (Depend on Oracle database records):
- **local/oracleFetch** - Core Oracle integration (DUNIA_EMPLOYEES, DUNIA_PERSONAL_DETAILS)
- **local/participant** - Uses Oracle for employee data lookup
- **local/residencebooking** - Uses Oracle for guest/employee information
- **local/externallecturer** - Uses Oracle for lecturer data
- **local/annualplans** - Uses Oracle for beneficiary/employee data

**Status System Dependencies**:
- **local/status** - Core status management system (ALL plugins depend on this)
- **local/reports** - Reporting system that tracks status changes
- **local/requestservices** - Central request management (depends on all workflow plugins)

**Integration Plugins**:
- **local/requestservices** - Central hub that integrates with all service plugins
- **local/reports** - Cross-plugin reporting and status tracking
- **local/annualplans** - Course planning with Oracle integration

**All Local Plugins**: Any plugin in the `local/` directory that references the modified records
- **Custom Modules**: All custom modules that depend on the changed data
- **Integration Plugins**: Plugins that sync data between systems
- **Reporting Plugins**: Any reporting functionality that uses the modified records
- **Workflow Plugins**: All workflow-related plugins that process the changed records
- **Status Management**: Any plugin that manages or tracks status information

**Verification Steps**:
1. **Before Record Changes**: Document current status record states and identify all dependent plugins
2. **Plugin Dependency Check**: Identify and list all plugins that depend on the records being modified
3. **After Record Changes**: Verify all status records are updated across systems
4. **Plugin Update Verification**: Confirm all dependent plugins have been updated accordingly
5. **Cross-System Check**: Confirm status consistency between all integrated systems
6. **Workflow Testing**: Test that workflow states reflect record changes correctly
7. **Plugin Functionality Testing**: Test all dependent plugins to ensure they work with the modified records
8. **Data Integrity**: Ensure no orphaned or inconsistent status records exist

### 3. Annual Plans Plugin - Type ID Configuration
**Problem**: The `type_id` field in the annual plans plugin needs to be properly configured for offline server migration.

**Solution**: Ensure `type_id` matches with `category_id` or `level_id`:
- **For Categories**: `type_id` should match the `category_id` from the course categories table
- **For Levels**: `type_id` should match the `level_id` from the levels table  
- **For Courses**: `type_id` should be set to `null`

**Current Database Values** (from `mdl_local_annual_plan_course_codes` table):
- **Online Server**: `type_id = 22` → Management category (ادر/MGT)
- **Online Server**: `type_id = 23` → Language category (لغه/LAN)
- **Online Server**: `type_id = 24` → Intelligence category (ستخ/IST)
- **Online Server**: `type_id = 25` → Computer category (حسب/COM)

**Offline Server Configuration** (MUST be updated to):
- **Offline Server**: `type_id = 2` → Management category (ادر/MGT)
- **Offline Server**: `type_id = 3` → Language category (لغه/LAN)
- **Offline Server**: `type_id = 4` → Intelligence category (ستخ/IST)
- **Offline Server**: `type_id = 5` → Computer category (حسب/COM)

**Configuration Location**:
- File: `local/annualplans/db/install.php`
- Line 174: `'type_id' => 7, // This type_id depends on the course_categories table rows`
- Database Table: `mdl_local_annual_plan_course_codes`

**Database Update Required for Offline Server**:
```sql
-- Update type_id values in mdl_local_annual_plan_course_codes table
UPDATE mdl_local_annual_plan_course_codes SET type_id = 2 WHERE type_id = 22; -- Management
UPDATE mdl_local_annual_plan_course_codes SET type_id = 3 WHERE type_id = 23; -- Language  
UPDATE mdl_local_annual_plan_course_codes SET type_id = 4 WHERE type_id = 24; -- Intelligence
UPDATE mdl_local_annual_plan_course_codes SET type_id = 5 WHERE type_id = 25; -- Computer
```

**Verification Steps**:
1. **Before Migration**: Note current `type_id` values (22, 23, 24, 25) on online server
2. **After Migration**: Update `type_id` values to (2, 3, 4, 5) on offline server
3. Check that new `type_id` values (2, 3, 4, 5) correspond to existing category IDs in the course categories table
4. Ensure courses have `type_id` set to `null`
5. Verify data integrity after migration
6. Confirm all entries in `mdl_local_annual_plan_course_codes` have correct `type_id` mappings

### 4. Oracle Database Configuration - Oracle Fetch Plugin
**Problem**: The Oracle database connection settings and table structures are different between online and offline servers, requiring configuration updates for the Oracle Fetch plugin.

**Solution**: Update Oracle database connection settings and verify table structures for offline server migration:
- **Database Connection**: Oracle server host, port, and service names differ between environments
- **Table Structure**: Ensure Oracle tables exist and have correct data for offline environment
- **Character Encoding**: Proper UTF-8 support for Arabic text handling

**Current Oracle Configuration** (Online Server):
- **Database Host**: Online Oracle server (e.g., `//online-oracle-server:1521/ONLINE_SERVICE`)
- **Database User**: `moodleuser` (or configured user)
- **Database Password**: `moodle` (or configured password)
- **Tables**: `DUNIA_EMPLOYEES`, `DUNIA_PERSONAL_DETAILS`
- **Key Fields**: `aaa_emp_civil_number_ar`, `nationality_arabic_SYS`

**Offline Server Configuration** (MUST be updated to):
- **Database Host**: Offline Oracle server (e.g., `//offline-oracle-server:1521/OFFLINE_SERVICE`)
- **Database User**: `moodleuser` (or offline-specific user)
- **Database Password**: `moodle` (or offline-specific password)
- **Tables**: **COMPLETELY DIFFERENT TABLE NAMES** - Not `DUNIA_EMPLOYEES` & `DUNIA_PERSONAL_DETAILS`
- **Key Fields**: **DIFFERENT FIELD NAMES** - `aaa_emp_civil_number_ar` & `nationality_arabic_SYS` (may have different names)

**Configuration Location**:
- File: `config.php` (Moodle configuration)
- Plugin: `local/oracleFetch/classes/oracle_manager.php`
- Configuration Variables:
  ```php
  $CFG->local_oraclefetch_dbuser = 'YOUR_OFFLINE_USER';
  $CFG->local_oraclefetch_dbpass = 'YOUR_OFFLINE_PASS';
  $CFG->local_oraclefetch_dsn    = '//OFFLINE_HOST:1521/OFFLINE_SERVICE';
  ```

**Database Tables Required** (Offline Server):
```sql
-- NOTE: Table names are COMPLETELY DIFFERENT on offline server
-- These are examples - actual table names need to be verified on offline server

-- Table 1: [ACTUAL_OFFLINE_EMPLOYEE_TABLE_NAME] (replace with real name)
CREATE TABLE [ACTUAL_OFFLINE_EMPLOYEE_TABLE_NAME] (
    pf_number                VARCHAR2(20)   PRIMARY KEY,
    prs_name1_a              VARCHAR2(100),
    prs_name2_a              VARCHAR2(100),
    prs_name3_a              VARCHAR2(100),
    prs_tribe_a              VARCHAR2(100),
    [ACTUAL_CIVIL_NUMBER_FIELD] VARCHAR2(20)   -- Field name may be different
);

-- Table 2: [ACTUAL_OFFLINE_PERSONAL_TABLE_NAME] (replace with real name)
CREATE TABLE [ACTUAL_OFFLINE_PERSONAL_TABLE_NAME] (
    civil_number         NUMBER        PRIMARY KEY,
    passport_number      VARCHAR2(20),
    name_arabic_1        VARCHAR2(100),
    name_arabic_2        VARCHAR2(100),
    name_arabic_3        VARCHAR2(100),
    name_arabic_6        VARCHAR2(100),
    [ACTUAL_NATIONALITY_FIELD] VARCHAR2(100)   -- Field name may be different
);
```

**⚠️ IMPORTANT: Table and Field Name Differences**:
- **Online Server**: `DUNIA_EMPLOYEES`, `DUNIA_PERSONAL_DETAILS`
- **Offline Server**: **COMPLETELY DIFFERENT TABLE NAMES** (not just case changes)
- **Field Names**: `aaa_emp_civil_number_ar` and `nationality_arabic_SYS` may have different names on offline server
- **Action Required**: Must identify actual table and field names on offline server before migration

**Code Updates Required**:
The Oracle manager class (`local/oracleFetch/classes/oracle_manager.php`) needs to be updated to use the correct table and field names for the offline server:

1. **Identify Actual Table Names** on offline server:
   - Connect to offline Oracle database
   - List all tables to find the equivalent of `DUNIA_EMPLOYEES`
   - List all tables to find the equivalent of `DUNIA_PERSONAL_DETAILS`

2. **Update Table Names** in all SQL queries:
   - Change `DUNIA_EMPLOYEES` → `[ACTUAL_OFFLINE_EMPLOYEE_TABLE_NAME]`
   - Change `DUNIA_PERSONAL_DETAILS` → `[ACTUAL_OFFLINE_PERSONAL_TABLE_NAME]`

3. **Update Field Names** (if different on offline server):
   - Verify `aaa_emp_civil_number_ar` field name
   - Verify `nationality_arabic_SYS` field name
   - Update all SQL queries accordingly

4. **Example SQL Query Updates**:
   ```sql
   -- Online Server Query
   SELECT pf_number, prs_name1_a, aaa_emp_civil_number_ar FROM DUNIA_EMPLOYEES
   
   -- Offline Server Query (updated with actual table name)
   SELECT pf_number, prs_name1_a, [ACTUAL_CIVIL_NUMBER_FIELD] FROM [ACTUAL_OFFLINE_EMPLOYEE_TABLE_NAME]
   ```

**Verification Steps**:
1. **Before Migration**: Note current Oracle connection settings and table/field names on online server
2. **After Migration**: Update Oracle connection settings in `config.php`
3. **Database Connectivity**: Test connection using `/local/oracleFetch/tools/health.php?html=1`
4. **Table Discovery**: Connect to offline Oracle database and identify actual table names (not `DUNIA_EMPLOYEES`/`DUNIA_PERSONAL_DETAILS`)
5. **Field Name Verification**: Confirm field names `aaa_emp_civil_number_ar` and `nationality_arabic_SYS` exist or identify correct field names
6. **Code Updates**: Update Oracle manager class with correct table and field names
7. **Data Integrity**: Verify employee and person data is available and properly formatted
8. **Character Encoding**: Test Arabic text display and search functionality
9. **AJAX Endpoints**: Test all Oracle Fetch AJAX endpoints for proper functionality

### 5. Workflow Management - Simple Workflow Manager
**Problem**: The workflow system is exclusively managed through a single file and needs to be properly configured for offline server migration.

**Solution**: Ensure the workflow management is properly handled through the centralized manager:
- **Central Management**: All workflow operations are managed exclusively at `simple_workflow_manager.php`
- **Single Point of Control**: This file serves as the central hub for all workflow-related functionality

**Workflow Configuration Details**:
- **Status IDs**: The workflow uses specific status IDs (56-62) that must match the `{local_status}` plugin
- **Forward Transitions**: Defines progression through workflow steps (Initial → Leader1 → Leader2 → Leader3 → Boss → Approved)
- **Rejection Handling**: Manages rejection flows and fallback to previous steps
- **Capability Requirements**: Each step requires specific capabilities (`local/status:participants_workflow_step1-4`)

**Current Workflow Steps**:
1. **STATUS_INITIAL (56)**: Request submitted
2. **STATUS_LEADER1_REVIEW (57)**: First leader review
3. **STATUS_LEADER2_REVIEW (58)**: Second leader review  
4. **STATUS_LEADER3_REVIEW (59)**: Third leader review
5. **STATUS_BOSS_REVIEW (60)**: Boss review
6. **STATUS_APPROVED (61)**: Final approval
7. **STATUS_REJECTED (62)**: Rejection state

**Configuration Location**:
- File: `local/participant/classes/simple_workflow_manager.php`
- Purpose: Centralized workflow management system
- Scope: All workflow operations, transitions, and status configurations

**Adding New Workflow Steps**:
If you need to add new workflow steps:
1. **Add New Status ID**: Define new constant (e.g., `STATUS_NEW_STEP = 63`)
2. **Update Forward Map**: Add transition in `get_workflow_map()` method
3. **Update Rejection Map**: Add rejection handling in `get_rejection_map()` method
4. **Define Capability**: Create new capability in `local/status` plugin
5. **Update Database**: Ensure status IDs exist in the status table

**Verification Steps**:
1. **Before Migration**: Test workflow functionality on online server
2. **After Migration**: Verify `simple_workflow_manager.php` is accessible and functional
3. **Status ID Validation**: Confirm all status IDs (56-62) exist in the database
4. **Capability Check**: Verify all required capabilities are properly configured
5. **Transition Testing**: Test forward and rejection transitions work correctly
6. **Dependencies**: Confirm all required dependencies for workflow management are available
7. **Data Integrity**: Verify workflow data and configurations are properly migrated

### 6. System Roles Configuration
**Problem**: The system may have custom roles that need to be properly configured and verified during offline server migration.

**Solution**: Ensure all custom roles are properly migrated and configured for the offline server environment:
- **Role Definitions**: All custom roles must be imported and configured correctly
- **Role Assignments**: User role assignments must be preserved during migration
- **Role Capabilities**: All role capabilities and permissions must be properly set

**System Roles Available** (Located in `Rules/Current Roles (new once)/`):
- **trainer.xml** - مدرب (Trainer role with teacher archetype)
- **trainee.xml** - متدرب (Trainee role with student archetype)  
- **presidency.xml** - الرئاسة (Presidency role with manager archetype)
- **participant.xml** - مشارك (Participant role with teacher archetype)
- **departmentofficer.xml** - Department Officer role
- **departmentofficer_mgt.xml** - Department Officer Management role
- **departmentofficer_lan.xml** - Department Officer Language role
- **departmentofficer_ist.xml** - Department Officer Intelligence role
- **departmentofficer_com.xml** - Department Officer Computer role
- **dep55.xml** - Department 55 role
- **courseofficer.xml** - Course Officer role
- **administration.xml** - Administration role

**Role Configuration Details**:
- **Archetypes**: Roles inherit from standard Moodle archetypes (teacher, student, manager)
- **Context Levels**: Roles are assigned at system, course, and module levels
- **Permissions**: Each role has specific capabilities and permissions defined
- **Workflow Integration**: Roles may have workflow-specific capabilities for the local status system

**Configuration Location**:
- **Role Files**: `Rules/Current Roles (new once)/*.xml`
- **Import Method**: Use Moodle's role import functionality
- **Database Tables**: `mdl_role`, `mdl_role_capabilities`, `mdl_role_context_levels`

**Migration Steps**:
1. **Before Migration**: Export current role definitions from online server
2. **Role Import**: Import all custom roles using the XML files
3. **Capability Verification**: Ensure all role capabilities are properly assigned
4. **User Assignment**: Verify user role assignments are preserved
5. **Workflow Testing**: Test role-based workflow functionality
6. **Permission Validation**: Confirm all role permissions work correctly

**Verification Steps**:
1. **Role Import**: Successfully import all custom roles from XML files
2. **Capability Check**: Verify all role capabilities are properly configured
3. **User Assignment**: Confirm user role assignments are maintained
4. **Workflow Integration**: Test role-based workflow step permissions
5. **Context Levels**: Verify roles work at appropriate context levels
6. **Permission Testing**: Test role-specific functionality and access controls
7. **Arabic Support**: Ensure Arabic role names and descriptions display correctly

---

## 🔧 **Additional Configuration Notes**

### **Oracle Database Migration Checklist**
When migrating to offline server, ensure the following Oracle-related items are addressed:

1. **Connection String Updates**: Update all Oracle connection strings in `config.php`
2. **Table Name Discovery**: Connect to offline Oracle database and identify actual table names (completely different from online)
3. **Field Name Verification**: Check if `aaa_emp_civil_number_ar` and `nationality_arabic_SYS` field names are correct
4. **Code Updates**: Modify Oracle manager class SQL queries for offline server table/field names
5. **Database User Permissions**: Verify offline Oracle user has proper permissions
6. **Table Data Migration**: Ensure employee and person data is properly migrated
7. **Character Encoding**: Test Arabic text handling in offline environment
8. **Network Connectivity**: Verify network access between Moodle and Oracle servers
9. **Firewall Rules**: Ensure Oracle port (1521) is accessible in offline environment

---

**Last Updated**: 2025-09-11
**Version**: 1.1
**Status**: Ready for offline server deployment
**Changes**: Added System Roles Configuration section for custom role migration
