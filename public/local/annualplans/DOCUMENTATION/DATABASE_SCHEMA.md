# Database Schema Documentation

This document provides a comprehensive overview of the database schema used by the Annual Plans plugin, including table structures, relationships, and data flow.

## Schema Overview

The Annual Plans plugin uses a normalized database design with several interconnected tables to manage annual plans, courses, codes, levels, and user data. The schema follows Moodle's database conventions and includes proper foreign key relationships.

## Core Tables

### 1. local_annual_plan

**Purpose**: Container table for annual plans
**Description**: Stores metadata about annual training plans

| Field | Type | Length | Constraints | Description |
|-------|------|--------|-------------|-------------|
| id | int | 10 | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| year | int | 4 | NOT NULL | Academic/calendar year |
| title | char | 255 | NOT NULL | Plan title/name |
| date_created | int | 10 | NOT NULL | Creation timestamp |
| status | char | 255 | NOT NULL | Plan status |
| description | text | - | NULL | Plan description |
| disabled | int | 1 | NOT NULL, DEFAULT 0 | Soft delete flag |
| deletion_note | text | - | NULL | Reason for deletion |

**Indexes**:
- PRIMARY KEY on `id`

**Key Relationships**:
- Referenced by `local_annual_plan_course.annualplanid`

---

### 2. local_annual_plan_course

**Purpose**: Main course data table
**Description**: Stores detailed information about courses within annual plans

| Field | Type | Length | Constraints | Description |
|-------|------|--------|-------------|-------------|
| id | int | 10 | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| annualplanid | int | 10 | NOT NULL, FOREIGN KEY | Reference to annual plan |
| courseid | char | 100 | NOT NULL | Course identifier |
| coursename | char | 255 | NOT NULL | Course name |
| category | char | 255 | NOT NULL | Course category |
| coursedate | int | 10 | NOT NULL | Course date (timestamp) |
| numberofbeneficiaries | int | 10 | NOT NULL | Number of participants |
| status | char | 100 | NOT NULL | Course status |
| approve | int | 1 | NOT NULL, DEFAULT 0 | Approval flag |
| userid | int | 10 | NULL | Creator user ID |
| courselevelid | int | 10 | NULL, FOREIGN KEY | Course level reference |
| place | char | 100 | NOT NULL | Course location |
| disabled | int | 1 | NOT NULL, DEFAULT 0 | Soft delete flag |
| deletion_note | text | - | NULL | Deletion reason |
| unapprove_note | text | - | NULL | Unapproval reason |
| finance_source | text | - | NULL | Finance source code |
| finance_remarks | text | - | NULL | Finance notes |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `coursename` for faster queries

**Foreign Keys**:
- `annualplanid` → `local_annual_plan.id`
- `courselevelid` → `local_annual_plan_course_level.id`

---

### 3. local_annual_plan_course_codes

**Purpose**: Course code management
**Description**: Stores hierarchical course codes for categorization with bilingual support

| Field | Type | Length | Constraints | Description |
|-------|------|--------|-------------|-------------|
| id | int | 10 | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| type | char | 50 | NOT NULL | Code type (category, grade, course, targeted_group, group_number) |
| code_en | char | 50 | NOT NULL | Alphanumeric code in English (used for display and logic) |
| code_ar | char | 50 | NOT NULL | Alphanumeric code in Arabic (stored but not used in UI/logic) |
| description_en | text | - | NULL | Code description in English |
| description_ar | text | - | NULL | Code description in Arabic |
| type_id | int | 10 | NULL | Associated type identifier (category_id or level_id) |
| timecreated | int | 10 | NOT NULL | Creation timestamp |
| timemodified | int | 10 | NOT NULL | Last modification timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `code_en` for faster code lookups
- INDEX on `type, type_id` for efficient filtering

**Usage Notes**:
- Only `code_en` is used for display, selection, and logic
- `code_ar` is stored for future use but not currently utilized
- Descriptions are language-dependent using `description_en` or `description_ar`
- All queries should order by `code_en`, not the removed `code` field

---

### 4. local_annual_plan_course_level

**Purpose**: Course level definitions
**Description**: Defines hierarchical course levels and difficulty ratings with bilingual support

| Field | Type | Length | Constraints | Description |
|-------|------|--------|-------------|-------------|
| id | int | 10 | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| name | char | 255 | NOT NULL | Level name |
| description_en | text | - | NULL | Level description in English |
| description_ar | text | - | NULL | Level description in Arabic |
| is_internal | int | 1 | NOT NULL, DEFAULT 1 | Whether this level is for internal (1) or external (0) courses |

**Indexes**:
- PRIMARY KEY on `id`

**Key Relationships**:
- Referenced by `local_annual_plan_course.courselevelid`

---

### 5. local_annual_plan_beneficiaries

**Purpose**: Stores the selected beneficiaries per annual plan course (sourced from Oracle)

| Field | Type | Length | Constraints | Description |
|-------|------|--------|-------------|-------------|
| id | int | 10 | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| courseid | char | 100 | NOT NULL | Course identifier (idnumber) |
| coursedate | int | 10 | NOT NULL | Course date (timestamp) |
| annualplanid | int | 10 | NOT NULL | Plan reference |
| pf_number | char | 50 | NOT NULL | PF/Civil identifier sourced from Oracle |
| fullname | char | 255 | NOT NULL | Full name sourced from Oracle |
| timecreated | int | 10 | NOT NULL | Creation timestamp |
| timemodified | int | 10 | NULL | Last modification timestamp |
| created_by | int | 10 | NULL | User who created the record |
| modified_by | int | 10 | NULL | User who last modified the record |

**Indexes**:
- `courseid_date_plan_idx` on `(courseid, coursedate, annualplanid)`

## Entity Relationship Overview

```
local_annual_plan (1) ──→ (n) local_annual_plan_course
                                    │
                                    └── (1) local_annual_plan_course_level

local_annual_plan_course_codes (n) ──→ (1) course_categories (via type_id for category codes)
local_annual_plan_course_codes (n) ──→ (1) local_annual_plan_course_level (via type_id for level codes)
```

## Data Flow and Relationships

### 1. Plan to Course Relationship
- Each Annual Plan can contain multiple courses
- Courses belong to exactly one Annual Plan
- Relationship maintained via `annualplanid` foreign key

### 2. Course Code Structure
- Courses are categorized using codes with bilingual support
- Codes support multiple types: category, grade, course, targeted_group, group_number
- Only English codes (`code_en`) are used for display and logic
- Arabic codes (`code_ar`) are stored for future use
- Descriptions are language-dependent using `description_en` or `description_ar`

### 3. Approval Workflow
- Courses have approval status tracking
- Approval notes stored for audit trail
- Soft delete functionality with deletion notes

## Data Integrity Constraints

### Foreign Key Constraints
1. **Course to Plan**: `local_annual_plan_course.annualplanid` → `local_annual_plan.id`
2. **Course to Level**: `local_annual_plan_course.courselevelid` → `local_annual_plan_course_level.id`
3. **Category Codes to Categories**: `local_annual_plan_course_codes.type_id` → `course_categories.id` (when type = 'category')
4. **Level Codes to Levels**: `local_annual_plan_course_codes.type_id` → `local_annual_plan_course_level.id` (when type = 'grade')

### Business Rules
1. **Soft Deletes**: Records are marked as disabled rather than physically deleted
2. **Audit Trail**: All modifications include notes and timestamps
3. **Status Tracking**: Comprehensive status management for plans and courses
4. **Bilingual Support**: Codes and descriptions support both English and Arabic
5. **Code Usage**: Only English codes (`code_en`) are used for display and logic
6. **Language-Dependent Descriptions**: Descriptions use `description_en` or `description_ar` based on current language

## Indexing Strategy

### Performance Indexes
1. **coursename_idx**: Fast course name searches
2. **code_en_idx**: Quick code lookups (replaces old code_idx)
3. **type_typeid_idx**: Efficient type and type_id filtering
4. **courseid_date_plan_idx**: Fast beneficiary lookups by course, date, and plan
5. **userid_courseid_uix**: Unique constraint on user enrollments

## Data Types and Constraints

### Field Types
- **int(10)**: Standard integers with 10-digit capacity
- **char(n)**: Fixed-length strings for codes and short text
- **text**: Variable-length text for descriptions and notes
- **timestamp**: Date/time storage using Unix timestamps

### Constraint Patterns
- **NOT NULL**: Required fields
- **DEFAULT values**: Sensible defaults for flags and status
- **AUTO_INCREMENT**: Automatic primary key generation
- **FOREIGN KEY**: Referential integrity enforcement

## Security Considerations

### Data Protection
- User capability checks at application level
- Parameterized queries prevent SQL injection
- Input validation and sanitization
- Proper escaping of output data

### Access Control
- Capability-based access control
- Row-level security through user context
- Audit logging for sensitive operations

This database schema provides a robust foundation for managing complex annual training plans while maintaining data integrity, performance, and extensibility.