# Database Schema Documentation

This document details the database structure of the Finance Services plugin.

## 📊 Database Overview

The Finance Services plugin uses 3 main tables to store and manage financial request data:

1. **`local_financeservices`** - Main requests table
2. **`local_financeservices_funding_type`** - Funding type configuration
3. **`local_financeservices_clause`** - Terms and conditions clauses

## 🗄️ Table Definitions

### 1. Main Requests Table

**Table Name**: `local_financeservices`

| Field | Type | Length | Null | Default | Description |
|-------|------|---------|------|---------|-------------|
| `id` | INT | 10 | NO | AUTO_INCREMENT | Primary key |
| `course_id` | INT | 10 | NO | - | Foreign key to `course.id` |
| `funding_type_id` | INT | 10 | NO | - | Foreign key to funding type |
| `price_requested` | DECIMAL | 10,2 | NO | - | Amount requested |
| `notes` | TEXT | - | YES | NULL | Request notes/justification |
| `user_id` | INT | 10 | NO | - | Foreign key to `user.id` |
| `date_time_requested` | INT | 10 | NO | - | Unix timestamp of request |
| `date_type_required` | INT | 10 | YES | NULL | Unix timestamp when funding needed |
| `status_id` | INT | 10 | NO | - | Foreign key to `local_status.id` |
| `clause_id` | INT | 10 | YES | NULL | Foreign key to clause |
| `approval_note` | TEXT | - | YES | NULL | Approval comments |
| `rejection_note` | TEXT | - | YES | NULL | Rejection reasons |
| `timemodified` | INT | 10 | YES | NULL | Last modification timestamp |

**Indexes**:
- Primary key on `id`
- Index on `course_id`
- Index on `user_id`
- Index on `status_id`
- Index on `funding_type_id`

**Sample Record**:
```sql
INSERT INTO mdl_local_financeservices (
    course_id, funding_type_id, price_requested, notes, user_id, 
    date_time_requested, status_id, clause_id
) VALUES (
    2, 1, 500.00, 'Conference attendance funding', 3, 
    1640995200, 8, 1
);
```

### 2. Funding Types Table

**Table Name**: `local_financeservices_funding_type`

| Field | Type | Length | Null | Default | Description |
|-------|------|---------|------|---------|-------------|
| `id` | INT | 10 | NO | AUTO_INCREMENT | Primary key |
| `funding_type_en` | VARCHAR | 255 | NO | - | English name |
| `funding_type_ar` | VARCHAR | 255 | NO | - | Arabic name |
| `description_en` | TEXT | - | YES | NULL | English description |
| `description_ar` | TEXT | - | YES | NULL | Arabic description |
| `active` | TINYINT | 1 | NO | 1 | Active status (0/1) |
| `timecreated` | INT | 10 | NO | - | Creation timestamp |
| `timemodified` | INT | 10 | YES | NULL | Modification timestamp |

**Indexes**:
- Primary key on `id`
- Index on `active`

**Sample Records**:
```sql
INSERT INTO mdl_local_financeservices_funding_type VALUES
(1, 'Course Materials', 'مواد الدورة', 'Funding for course-related materials', 'تمويل للمواد المتعلقة بالدورة', 1, 1640995200, NULL),
(2, 'Conference Attendance', 'حضور المؤتمر', 'Travel and registration for conferences', 'السفر والتسجيل للمؤتمرات', 1, 1640995200, NULL),
(3, 'Training Programs', 'برامج التدريب', 'Professional development training', 'تدريب التطوير المهني', 1, 1640995200, NULL);
```

### 3. Clauses Table

**Table Name**: `local_financeservices_clause`

| Field | Type | Length | Null | Default | Description |
|-------|------|---------|------|---------|-------------|
| `id` | INT | 10 | NO | AUTO_INCREMENT | Primary key |
| `clause_name_en` | VARCHAR | 255 | NO | - | English clause name |
| `clause_name_ar` | VARCHAR | 255 | NO | - | Arabic clause name |
| `clause_description_en` | TEXT | - | YES | NULL | English description |
| `clause_description_ar` | TEXT | - | YES | NULL | Arabic description |
| `active` | TINYINT | 1 | NO | 1 | Active status (0/1) |
| `timecreated` | INT | 10 | NO | - | Creation timestamp |
| `timemodified` | INT | 10 | YES | NULL | Modification timestamp |

**Indexes**:
- Primary key on `id`
- Index on `active`

**Sample Records**:
```sql
INSERT INTO mdl_local_financeservices_clause VALUES
(1, 'Standard Terms', 'الشروط القياسية', 'Standard funding terms and conditions', 'شروط وأحكام التمويل القياسية', 1, 1640995200, NULL),
(2, 'Urgent Request', 'طلب عاجل', 'For urgent funding requests', 'للطلبات التمويلية العاجلة', 1, 1640995200, NULL),
(3, 'Research Project', 'مشروع بحثي', 'Research-related funding clause', 'بند التمويل المتعلق بالبحث', 1, 1640995200, NULL);
```

## 🔗 Relationships

### Entity Relationship Diagram

```
┌─────────────────────┐         ┌─────────────────────┐
│       course        │         │        user         │
│ ─────────────────── │         │ ─────────────────── │
│ id (PK)            │         │ id (PK)            │
│ fullname           │         │ firstname          │
│ shortname          │         │ lastname           │
└─────────────────────┘         └─────────────────────┘
           │                              │
           │ 1:N                          │ 1:N
           ▼                              ▼
┌─────────────────────────────────────────────────────┐
│              local_financeservices                   │
│ ─────────────────────────────────────────────────── │
│ id (PK)                                            │
│ course_id (FK) ────────────────────────────────────┼─► course.id
│ user_id (FK) ──────────────────────────────────────┼─► user.id
│ funding_type_id (FK) ──────────────────────────────┼─► funding_type.id
│ status_id (FK) ────────────────────────────────────┼─► local_status.id
│ clause_id (FK) ────────────────────────────────────┼─► clause.id
│ price_requested                                     │
│ notes                                              │
│ date_time_requested                                │
│ date_type_required                                 │
│ approval_note                                      │
│ rejection_note                                     │
│ timemodified                                       │
└─────────────────────────────────────────────────────┘
           │                              │
           │ N:1                          │ N:1
           ▼                              ▼
┌─────────────────────┐         ┌─────────────────────┐
│ funding_type        │         │      clause         │
│ ─────────────────── │         │ ─────────────────── │
│ id (PK)            │         │ id (PK)            │
│ funding_type_en    │         │ clause_name_en     │
│ funding_type_ar    │         │ clause_name_ar     │
│ description_en     │         │ clause_description_en │
│ description_ar     │         │ clause_description_ar │
│ active             │         │ active             │
│ timecreated        │         │ timecreated        │
│ timemodified       │         │ timemodified       │
└─────────────────────┘         └─────────────────────┘
```

### External Dependencies

**Status Management** (via `local_status` plugin):
```
┌─────────────────────┐
│    local_status     │
│ ─────────────────── │
│ id (PK)            │ ◄─── status_id (FK)
│ status_string_en   │
│ display_name_en    │
│ display_name_ar    │
│ status_position    │
└─────────────────────┘
```

## 📝 SQL Queries

### Common Query Patterns

**1. Get All Requests with Details**:
```sql
SELECT 
    fs.*,
    c.fullname AS course_name,
    u.firstname,
    u.lastname,
    ft.funding_type_en,
    ft.funding_type_ar,
    cl.clause_name_en,
    cl.clause_name_ar,
    ls.display_name_en AS status_en,
    ls.display_name_ar AS status_ar
FROM {local_financeservices} fs
JOIN {course} c ON fs.course_id = c.id
JOIN {user} u ON fs.user_id = u.id
JOIN {local_financeservices_funding_type} ft ON fs.funding_type_id = ft.id
LEFT JOIN {local_financeservices_clause} cl ON fs.clause_id = cl.id
JOIN {local_status} ls ON fs.status_id = ls.id
ORDER BY fs.date_time_requested DESC;
```

**2. Language-Aware Query**:
```sql
SELECT 
    fs.*,
    c.fullname AS course_name,
    CASE 
        WHEN :lang = 'ar' THEN ft.funding_type_ar 
        ELSE ft.funding_type_en 
    END AS funding_type,
    CASE 
        WHEN :lang = 'ar' THEN ls.display_name_ar 
        ELSE ls.display_name_en 
    END AS status_display
FROM {local_financeservices} fs
JOIN {course} c ON fs.course_id = c.id
JOIN {local_financeservices_funding_type} ft ON fs.funding_type_id = ft.id
JOIN {local_status} ls ON fs.status_id = ls.id
WHERE fs.status_id = :status_id;
```

**3. Filter by User and Status**:
```sql
SELECT fs.*, c.fullname AS course_name
FROM {local_financeservices} fs
JOIN {course} c ON fs.course_id = c.id
WHERE fs.user_id = :userid 
  AND fs.status_id IN (8, 9, 10, 11, 12)
ORDER BY fs.date_time_requested DESC;
```

**Note**: Status transitions follow the workflow where rejections from any level (except Leader 1) return to Leader 1 Review (status 9), while Leader 1 rejections go to final rejection (status 14).

**4. Aggregate Statistics**:
```sql
SELECT 
    ls.display_name_en AS status,
    COUNT(*) AS request_count,
    AVG(fs.price_requested) AS avg_amount,
    SUM(fs.price_requested) AS total_amount
FROM {local_financeservices} fs
JOIN {local_status} ls ON fs.status_id = ls.id
WHERE fs.date_time_requested >= :start_date
GROUP BY fs.status_id, ls.display_name_en
ORDER BY request_count DESC;
```

## 🛠️ Database Migrations

### Upgrade Handling

The plugin uses `upgrade.php` for database schema changes:

```php
function xmldb_local_financeservices_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025061901) {
        // Add new field example
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('approval_note', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'rejection_note');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2025061901, 'local', 'financeservices');
    }

    if ($oldversion < 2025061902) {
        // Add index example
        $table = new xmldb_table('local_financeservices');
        $index = new xmldb_index('idx_date_status', XMLDB_INDEX_NOTUNIQUE, ['date_time_requested', 'status_id']);
        
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        
        upgrade_plugin_savepoint(true, 2025061902, 'local', 'financeservices');
    }

    return true;
}
```

### Data Migrations

**Example: Migrating Status Values**:
```php
if ($oldversion < 2025061903) {
    // Update old status values to new workflow
    $DB->execute("UPDATE {local_financeservices} 
                  SET status_id = 8 
                  WHERE status_id = 1"); // Old 'pending' to new 'initial'
    
    upgrade_plugin_savepoint(true, 2025061903, 'local', 'financeservices');
}
```

## 🔧 Database Maintenance

### Performance Optimization

**1. Regular Index Analysis**:
```sql
-- Check index usage
EXPLAIN SELECT * FROM mdl_local_financeservices 
WHERE status_id = 8 AND user_id = 123;

-- Add composite index if needed
CREATE INDEX idx_user_status ON mdl_local_financeservices (user_id, status_id);
```

**2. Data Cleanup**:
```sql
-- Archive old completed requests (older than 2 years)
CREATE TABLE mdl_local_financeservices_archive LIKE mdl_local_financeservices;

INSERT INTO mdl_local_financeservices_archive
SELECT * FROM mdl_local_financeservices 
WHERE status_id IN (13, 14) 
  AND date_time_requested < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 YEAR));

DELETE FROM mdl_local_financeservices 
WHERE status_id IN (13, 14) 
  AND date_time_requested < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 YEAR));
```

### Backup Considerations

**Important Tables to Backup**:
1. `local_financeservices` - All request data
2. `local_financeservices_funding_type` - Configuration
3. `local_financeservices_clause` - Terms and conditions
4. `local_status` - Status definitions (if customized)

**Backup Script**:
```bash
# Backup finance services data
mysqldump -u username -p database_name \
  mdl_local_financeservices \
  mdl_local_financeservices_funding_type \
  mdl_local_financeservices_clause \
  > financeservices_backup_$(date +%Y%m%d).sql
```

## 📊 Database Monitoring

### Key Metrics to Monitor

**1. Table Sizes**:
```sql
SELECT 
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES 
WHERE table_schema = 'moodle' 
  AND table_name LIKE 'mdl_local_financeservices%';
```

**2. Query Performance**:
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Monitor for slow queries involving finance tables
```

**3. Growth Trends**:
```sql
SELECT 
    DATE(FROM_UNIXTIME(date_time_requested)) AS request_date,
    COUNT(*) AS daily_requests,
    AVG(price_requested) AS avg_amount
FROM mdl_local_financeservices 
WHERE date_time_requested >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
GROUP BY DATE(FROM_UNIXTIME(date_time_requested))
ORDER BY request_date;
```

This database schema provides a robust foundation for the Finance Services plugin with proper relationships, indexing, and maintainability considerations. 