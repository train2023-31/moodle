# Database Schema Documentation - Annual Report Block

## Overview

The Annual Report Block depends on several custom database tables to provide comprehensive reporting functionality. This document outlines the database schema requirements, table relationships, and data flow patterns.

## Required Database Tables

### `local_annual_plan_course_level`
**Purpose**: Defines course levels and categories (internal vs external)

#### Expected Fields:
```sql
id                 INT(10)      PRIMARY KEY, AUTO_INCREMENT
name              VARCHAR(255)  NOT NULL -- Level name/identifier
description_en    TEXT         -- English description  
description_ar    TEXT         -- Arabic description
is_internal       INT(1)       DEFAULT 1 -- 1=internal, 0=external
```

#### Usage in Plugin:
- Determines internal vs external course categorization
- Provides multilingual display names for course types
- Used in JOIN operations with course data

#### Sample Data:
```sql
INSERT INTO local_annual_plan_course_level VALUES
(1, 'Basic Training', 'Basic level training courses', 'دورات التدريب الأساسية', 1),
(2, 'Advanced Training', 'Advanced level training', 'دورات التدريب المتقدمة', 1),
(6, 'External Basic', 'External entity basic training', 'تدريب أساسي للجهات الخارجية', 0);
```

### `local_annual_plan_course`
**Purpose**: Stores individual course planning and execution data

#### Expected Fields:
```sql
id                     INT(10)      PRIMARY KEY, AUTO_INCREMENT
courselevelid         INT(10)      -- FK to local_annual_plan_course_level.id
coursedate            TIMESTAMP    -- Course date/time
numberofbeneficiaries INT(10)      -- Number of people who benefited
approve               INT(1)       DEFAULT 0 -- Approval status (1=approved)
-- Additional fields may exist
```

#### Usage in Plugin:
- Primary source for course statistics
- Links to course levels for categorization
- Provides beneficiary counts and approval status
- Filtered by current year date range

#### Key Relationships:
```sql
-- Join pattern used in plugin
SELECT ac.*, cl.description_en, cl.is_internal
FROM local_annual_plan_course ac
JOIN local_annual_plan_course_level cl ON ac.courselevelid = cl.id
WHERE ac.coursedate BETWEEN ? AND ?
```

### `local_financeservices_clause`
**Purpose**: Stores approved budget amounts for different financial clauses

#### Expected Fields:
```sql
id      INT(10)     PRIMARY KEY
amount  DECIMAL     -- Approved budget amount
-- Additional metadata fields
```

#### Usage in Plugin:
- Retrieves approved budget for clauses 802 and 811
- Used for financial calculations (remaining = approved - spent)

#### Sample Data:
```sql
-- The plugin specifically looks for IDs 1 and 2
INSERT INTO local_financeservices_clause VALUES
(1, 50000.00),  -- Clause 802 budget
(2, 75000.00);  -- Clause 811 budget
```

### `local_financeservices`
**Purpose**: Contains financial service requests and expenditures

#### Expected Fields:
```sql
id              INT(10)     PRIMARY KEY, AUTO_INCREMENT
clause_id       INT(10)     -- FK to local_financeservices_clause.id
price_requested DECIMAL     -- Amount spent/requested
-- Additional fields for service details
```

#### Usage in Plugin:
- Calculates total spent amount per clause
- Aggregated using SUM() for budget calculations

#### Key Query Pattern:
```sql
SELECT SUM(price_requested) AS total_spent
FROM local_financeservices
WHERE clause_id = ?
```

## Data Relationships

### Entity Relationship Diagram
```
local_annual_plan_course_level (1) ←→ (N) local_annual_plan_course
                                           ↓
                                    [Course Statistics]
                                           
local_financeservices_clause (1) ←→ (N) local_financeservices
                                           ↓
                                    [Financial Statistics]
```

### Course Data Flow
```
Course Level Definition → Course Records → Statistics Aggregation → Display
     ↓                        ↓                    ↓                  ↓
is_internal flag    →    coursedate filter  →  COUNT/SUM ops   →  HTML tables
description_* fields →   courselevelid join →   beneficiary sum →  Formatted output
```

### Financial Data Flow
```
Approved Budget → Service Requests → Spent Calculation → Remaining Budget
      ↓                 ↓                  ↓                    ↓
clause amounts  →  price_requested  →   SUM operations   →  Display format
```

## SQL Query Patterns

### Internal Courses Query
```sql
SELECT 
    cl.id,
    CASE 
        WHEN ? = 'ar' THEN COALESCE(cl.description_ar, cl.description_en, cl.name)
        ELSE COALESCE(cl.description_en, cl.description_ar, cl.name)
    END AS i_level_name, 
    COUNT(ac.id) AS total_internal_courses_for_this_level
FROM {local_annual_plan_course_level} cl
JOIN {local_annual_plan_course} ac 
    ON ac.courselevelid = cl.id 
    AND ac.coursedate BETWEEN ? AND ? 
    AND ac.courselevelid IN (...)
WHERE ac.courselevelid IN (...)
    AND ac.approve = 1
GROUP BY cl.id
```

### Beneficiaries Aggregation Query
```sql
SELECT 
    courselevelid AS i_level, 
    SUM(numberofbeneficiaries) AS total_internal_trainees_for_this_level
FROM {local_annual_plan_course} 
WHERE coursedate BETWEEN ? AND ?
    AND courselevelid IN (...) 
    AND approve = 1
GROUP BY courselevelid
```

### Financial Summary Query
```sql
-- Get approved amount
SELECT amount FROM {local_financeservices_clause} WHERE id = ?

-- Get spent amount
SELECT SUM(price_requested) AS total_spent
FROM {local_financeservices}
WHERE clause_id = ?
```

## Query Performance Considerations

### Recommended Indexes
```sql
-- For course queries
CREATE INDEX idx_annual_course_date_level ON local_annual_plan_course(coursedate, courselevelid);
CREATE INDEX idx_annual_course_approve ON local_annual_plan_course(approve);

-- For level queries  
CREATE INDEX idx_course_level_internal ON local_annual_plan_course_level(is_internal);

-- For financial queries
CREATE INDEX idx_finance_clause ON local_financeservices(clause_id);
```

### Query Optimization Notes
- Date range filtering is applied early in WHERE clauses
- JOIN conditions use indexed foreign keys
- GROUP BY operations use indexed fields
- Parameter binding prevents SQL injection

## Data Validation Requirements

### Course Level Data
```php
// Validation example
if (empty($internal_level_ids)) {
    $internal_level_ids = [0]; // Prevent SQL errors
}
```

### Date Range Validation
```php
$yearStart = strtotime(date('Y-01-01 00:00:00'));
$yearEnd = strtotime(date('Y-12-31 23:59:59'));
```

### Financial Data Validation
```php
$total_spent = $data802->total_spent ?? 0;
$remaining = $approved_amount - $total_spent;
```

## Common Data Issues and Solutions

### Problem: Zero Beneficiaries Displayed
**Cause**: No enrollment data in course tables or mismatched course categories
**Solution**: 
1. Verify course records exist with proper `courselevelid`
2. Check `numberofbeneficiaries` field is populated
3. Ensure `approve` field is set to 1 for approved courses

### Problem: Missing Course Levels
**Cause**: Course levels not properly categorized as internal/external
**Solution**:
1. Verify `is_internal` field values (1 = internal, 0 = external)
2. Check for NULL values in course level relationships
3. Ensure foreign key integrity

### Problem: Financial Calculations Incorrect
**Cause**: Missing or incorrect clause ID references
**Solution**:
1. Verify clause IDs 1 and 2 exist in `local_financeservices_clause`
2. Check `clause_id` references in `local_financeservices`
3. Validate decimal precision for financial amounts

## Database Migration Considerations

### Schema Changes Handling
The plugin was updated to handle the transition from single `description` field to multilingual fields:

**Old Schema**:
```sql
description VARCHAR(255) -- Single description field
```

**New Schema**:
```sql
description_en TEXT -- English description
description_ar TEXT -- Arabic description
```

**Migration Strategy**:
```sql
-- Example migration query
CASE 
    WHEN ? = 'ar' THEN COALESCE(cl.description_ar, cl.description_en, cl.name)
    ELSE COALESCE(cl.description_en, cl.description_ar, cl.name)
END AS display_name
```

### Backward Compatibility
- COALESCE functions provide graceful fallbacks
- NULL handling prevents display errors
- Default values prevent query failures

## Testing Database Setup

### Minimal Test Data
```sql
-- Course levels
INSERT INTO local_annual_plan_course_level VALUES
(1, 'Internal Basic', 'Internal basic training', 'تدريب داخلي أساسي', 1),
(2, 'External Basic', 'External basic training', 'تدريب خارجي أساسي', 0);

-- Sample courses
INSERT INTO local_annual_plan_course VALUES
(1, 1, '2024-01-15 10:00:00', 25, 1),
(2, 2, '2024-02-20 09:00:00', 15, 1);

-- Financial clauses
INSERT INTO local_financeservices_clause VALUES
(1, 50000.00),
(2, 75000.00);

-- Sample expenditures
INSERT INTO local_financeservices VALUES
(1, 1, 5000.00),
(2, 2, 7500.00);
```

### Verification Queries
```sql
-- Verify internal courses
SELECT COUNT(*) FROM local_annual_plan_course ac
JOIN local_annual_plan_course_level cl ON ac.courselevelid = cl.id
WHERE cl.is_internal = 1;

-- Verify financial setup
SELECT 
    c.id, 
    c.amount as approved,
    COALESCE(SUM(f.price_requested), 0) as spent
FROM local_financeservices_clause c
LEFT JOIN local_financeservices f ON f.clause_id = c.id
WHERE c.id IN (1, 2)
GROUP BY c.id;
```

## Performance Monitoring

### Key Metrics to Monitor
- Query execution time (target: < 500ms)
- Database connection count
- Memory usage during data aggregation
- Cache hit rates (if caching implemented)

### Monitoring Queries
```sql
-- Check query performance
EXPLAIN SELECT COUNT(*) FROM local_annual_plan_course 
WHERE coursedate BETWEEN ? AND ?;

-- Monitor table sizes
SELECT 
    table_name,
    table_rows,
    data_length,
    index_length
FROM information_schema.tables 
WHERE table_name LIKE 'local_annual%';
``` 