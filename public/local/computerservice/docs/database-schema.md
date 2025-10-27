# Database Schema Documentation

This document describes the database structure, relationships, and data flow for the Computer Service plugin.

## 📊 Database Tables

### Table: `local_computerservice_requests`

Primary table storing all device requests from users.

#### Schema Definition
```sql
CREATE TABLE mdl_local_computerservice_requests (
    id                BIGINT(10) NOT NULL AUTO_INCREMENT,
    userid            BIGINT(10) NOT NULL,
    courseid          BIGINT(10) NOT NULL,
    deviceid          BIGINT(10) NOT NULL,
    status_id         BIGINT(10) NOT NULL,
    numdevices        BIGINT(5) NOT NULL,
    request_needed_by BIGINT(10) NOT NULL,
    is_urgent      TINYINT(1) DEFAULT 0,
    comments          LONGTEXT,
    rejection_note    LONGTEXT,
    approval_note     LONGTEXT,
    timecreated       BIGINT(10) NOT NULL,
    timemodified      BIGINT(10) NOT NULL,
    PRIMARY KEY (id),
    KEY mdl_locacomprequ_use_ix (userid),
    KEY mdl_locacomprequ_cou_ix (courseid),
    KEY mdl_locacomprequ_dev_ix (deviceid),
    KEY mdl_locacomprequ_sta_ix (status_id)
);
```

#### Field Descriptions

| Field | Type | Description | Constraints |
|-------|------|-------------|-------------|
| `id` | BIGINT(10) | Primary key, auto-increment | NOT NULL, AUTO_INCREMENT |
| `userid` | BIGINT(10) | References `mdl_user.id` | NOT NULL, Indexed |
| `courseid` | BIGINT(10) | References `mdl_course.id` | NOT NULL, Indexed |
| `deviceid` | BIGINT(10) | References `local_computerservice_devices.id` | NOT NULL, Indexed |
| `status_id` | BIGINT(10) | Current workflow status | NOT NULL, Indexed |
| `numdevices` | BIGINT(5) | Number of devices requested | NOT NULL, Min: 1 |
| `request_needed_by` | BIGINT(10) | Unix timestamp when devices are needed | NOT NULL |
| `is_urgent` | TINYINT(1) | 1 if request is urgent (today or tomorrow) | DEFAULT 0 |
| `comments` | LONGTEXT | Optional user comments | NULL allowed |
| `rejection_note` | LONGTEXT | Note explaining rejection (v1.3.0+) | NULL allowed |
| `approval_note` | LONGTEXT | Optional approval note (v1.3.0+) | NULL allowed |
| `timecreated` | BIGINT(10) | Unix timestamp of creation | NOT NULL |
| `timemodified` | BIGINT(10) | Unix timestamp of last modification | NOT NULL |

#### Business Rules
- `is_urgent` is automatically set to 1 if `request_needed_by` is today or tomorrow
- `rejection_note` is mandatory when request is rejected (enforced in application logic)
- `timemodified` is updated whenever workflow status changes
- Only active devices can be requested (enforced by foreign key and application logic)

### Table: `local_computerservice_devices`

Stores available device types with bilingual support.

#### Schema Definition
```sql
CREATE TABLE mdl_local_computerservice_devices (
    id             BIGINT(10) NOT NULL AUTO_INCREMENT,
    devicename_en  VARCHAR(100) NOT NULL,
    devicename_ar  VARCHAR(100) NOT NULL,
    status         VARCHAR(10) NOT NULL DEFAULT 'active',
    PRIMARY KEY (id),
    KEY mdl_locacompdevic_sta_ix (status)
);
```

#### Field Descriptions

| Field | Type | Description | Constraints |
|-------|------|-------------|-------------|
| `id` | BIGINT(10) | Primary key, auto-increment | NOT NULL, AUTO_INCREMENT |
| `devicename_en` | VARCHAR(100) | Device name in English | NOT NULL |
| `devicename_ar` | VARCHAR(100) | Device name in Arabic | NOT NULL |
| `status` | VARCHAR(10) | 'active' or 'inactive' | NOT NULL, DEFAULT 'active', Indexed |

#### Business Rules
- Both `devicename_en` and `devicename_ar` must be provided
- Only devices with `status = 'active'` appear in request forms
- Device names should be unique per language (enforced in application logic)

## 🔗 Relationships and Foreign Keys

### Entity Relationship Diagram
```
┌─────────────┐         ┌──────────────────────────┐         ┌─────────────────────┐
│   mdl_user  │         │ local_computerservice_   │         │ local_computerservice_ │
│             │◄────────┤      requests            │────────►│     devices         │
│ id          │ 1    N  │                          │ N    1  │                     │
│ username    │         │ userid (FK)              │         │ id                  │
│ firstname   │         │ courseid (FK)            │         │ devicename_en       │
│ lastname    │         │ deviceid (FK)            │         │ devicename_ar       │
└─────────────┘         │ status_id                │         │ status              │
                        │ numdevices               │         └─────────────────────┘
┌─────────────┐         │ request_needed_by        │
│ mdl_course  │         │ is_urgent                │
│             │◄────────┤ comments                 │
│ id          │ 1    N  │ rejection_note           │
│ fullname    │         │ approval_note            │
│ shortname   │         │ timecreated              │
└─────────────┘         │ timemodified             │
                        └──────────────────────────┘
```

### Foreign Key Relationships

1. **requests.userid → mdl_user.id**
   - One user can have multiple requests
   - User information displayed in management interface

2. **requests.courseid → mdl_course.id**
   - Each request is associated with a specific course
   - Users can only select courses they're enrolled in

3. **requests.deviceid → devices.id**
   - Each request specifies one device type
   - Multiple requests can reference the same device type

## 📈 Workflow Status Integration

### Status ID Mapping
The plugin integrates with the `local_status` plugin for workflow management:

```php
// Status constants (defined in simple_workflow_manager.php)
const STATUS_INITIAL        = 15;  // Initial request
const STATUS_LEADER1_REVIEW = 16;  // First leader review
const STATUS_LEADER2_REVIEW = 17;  // Second leader review  
const STATUS_LEADER3_REVIEW = 18;  // Third leader review
const STATUS_BOSS_REVIEW    = 19;  // Final boss review
const STATUS_APPROVED       = 20;  // Approved
const STATUS_REJECTED       = 21;  // Legacy rejected status (rejections now go to initial)
```

### Workflow Progression
```
15 (Initial) → 16 (Leader1) → 17 (Leader2) → 18 (Leader3) → 19 (Boss) → 20 (Approved)
     ↑              ↑             ↑             ↑            ↑
     └──────────────┴─────────────┴─────────────┴────────────┘
                    (All rejections return to Initial)
```

## 📝 Data Operations

### Common Queries

#### Fetch All Requests with User and Course Information
```sql
SELECT 
    r.id,
    r.numdevices,
    r.request_needed_by,
    r.is_urgent,
    r.comments,
    r.rejection_note,
    r.approval_note,
    r.status_id,
    r.timecreated,
    u.firstname,
    u.lastname,
    c.fullname as coursename,
    d.devicename_en,
    d.devicename_ar
FROM mdl_local_computerservice_requests r
JOIN mdl_user u ON r.userid = u.id
JOIN mdl_course c ON r.courseid = c.id
JOIN mdl_local_computerservice_devices d ON r.deviceid = d.id
ORDER BY r.timecreated DESC;
```

#### Get Active Devices for Form
```sql
SELECT 
    id,
    devicename_en,
    devicename_ar
FROM mdl_local_computerservice_devices
WHERE status = 'active'
ORDER BY devicename_en;
```

#### Urgent Requests Report
```sql
SELECT 
    r.id,
    CONCAT(u.firstname, ' ', u.lastname) as username,
    c.fullname as coursename,
    r.numdevices,
    FROM_UNIXTIME(r.request_needed_by) as needed_date,
    r.status_id
FROM mdl_local_computerservice_requests r
JOIN mdl_user u ON r.userid = u.id
JOIN mdl_course c ON r.courseid = c.id
WHERE r.is_urgent = 1
  AND r.status_id NOT IN (20, 21)  -- Not approved or rejected
ORDER BY r.request_needed_by ASC;
```

#### Filtered Requests by Urgency
```sql
-- Get urgent requests only
SELECT * FROM mdl_local_computerservice_requests 
WHERE is_urgent = 1;

-- Get non-urgent requests only  
SELECT * FROM mdl_local_computerservice_requests 
WHERE is_urgent = 0;

-- Get all requests (for filter reset)
SELECT * FROM mdl_local_computerservice_requests;
```

### Data Manipulation Operations

#### Insert New Request
```php
$record = (object)[
    'userid'            => $USER->id,
    'courseid'          => $data->courseid,
    'deviceid'          => $data->deviceid,
    'numdevices'        => $data->numdevices,
    'comments'          => $data->comments,
    'status_id'         => 15, // STATUS_INITIAL
    'timecreated'       => time(),
    'timemodified'      => time(),
    'request_needed_by' => $data->request_needed_by,
    'is_urgent'      => (($data->request_needed_by - time()) < DAYSECS) ? 1 : 0,
];

$requestid = $DB->insert_record('local_computerservice_requests', $record);
```

#### Update Request Status
```php
$record = (object)[
    'id'           => $requestid,
    'status_id'    => $new_status,
    'timemodified' => time(),
];

// Add rejection note if rejecting
if ($action === 'reject') {
    $record->rejection_note = $rejection_note;
}

// Add approval note if approving
if ($action === 'approve' && !empty($approval_note)) {
    $record->approval_note = $approval_note;
}

$DB->update_record('local_computerservice_requests', $record);
```

## 🛡️ Database Security

### SQL Injection Prevention
- All database operations use Moodle's `$DB` object
- Parameters are properly typed and validated
- No direct SQL concatenation with user input

### Data Validation
```php
// Example parameter validation
$courseid = required_param('courseid', PARAM_INT);
$deviceid = required_param('deviceid', PARAM_INT);
$comments = optional_param('comments', '', PARAM_TEXT);
```

### Access Control
- Database operations are protected by Moodle capability checks
- Users can only access data they have permission to see
- Course enrollment is validated before allowing requests

## 📊 Performance Considerations

### Indexing Strategy
- Primary keys on all tables for unique identification
- Foreign key columns are indexed for join performance
- Status field indexed for filtering operations
- Composite indexes could be added for complex queries

### Query Optimization
- Use specific field lists instead of `SELECT *`
- Leverage Moodle's caching mechanisms
- Consider pagination for large result sets
- Use prepared statements (handled by Moodle's `$DB`)

### Recommended Indexes (Future Enhancement)
```sql
-- Composite index for common filter combinations
CREATE INDEX mdl_locacomprequ_cou_sta_ix 
ON mdl_local_computerservice_requests (courseid, status_id);

-- Index for time-based queries
CREATE INDEX mdl_locacomprequ_tim_ix 
ON mdl_local_computerservice_requests (timecreated);

-- Index for urgent requests
CREATE INDEX mdl_locacomprequ_eme_ix 
ON mdl_local_computerservice_requests (is_urgent, status_id);
```

## 🔄 Migration and Upgrades

### Version History

#### Version 1.3.0 (2025061901)
- Added `rejection_note` field to requests table
- Added `approval_note` field to requests table
- Removed deprecated `unapprove_note` field

#### Version 1.3.1 (2025061904)
- Fixed database column naming issues
- Enhanced data validation

### Upgrade Scripts Location
All database upgrades are handled in `/db/upgrade.php`:

```php
function xmldb_local_computerservice_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025061901) {
        // Add rejection_note field
        $table = new xmldb_table('local_computerservice_requests');
        $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'comments');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2025061901, 'local', 'computerservice');
    }

    return true;
}
```

## 🧹 Data Maintenance

### Cleanup Procedures
```sql
-- Remove old completed requests (older than 1 year)
DELETE FROM mdl_local_computerservice_requests 
WHERE status_id IN (20, 21) 
  AND timecreated < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 YEAR));

-- Inactive device cleanup
UPDATE mdl_local_computerservice_devices 
SET status = 'inactive' 
WHERE id NOT IN (
    SELECT DISTINCT deviceid 
    FROM mdl_local_computerservice_requests 
    WHERE timecreated > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 6 MONTH))
);
```

### Backup Recommendations
- Regular backups of both tables
- Include related Moodle tables (users, courses)
- Test restore procedures
- Archive old requests before deletion 