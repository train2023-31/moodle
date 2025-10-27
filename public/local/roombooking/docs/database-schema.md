# Database Schema Documentation

This document describes the database structure of the Room Booking plugin, including all tables, fields, relationships, and indexes.

## Overview

The Room Booking plugin creates several database tables with the prefix `local_roombooking_`. The schema is designed to support:

- Room management with detailed properties
- Booking records with time slots and status tracking
- Recurring booking patterns
- Workflow integration for approval processes
- Audit trail and history tracking

## Database Tables

### Core Tables

#### Table: `local_roombooking_rooms`

Stores information about bookable rooms and their properties.

| Field | Type | Length | Null | Default | Description |
|-------|------|--------|------|---------|-------------|
| `id` | BIGINT | 10 | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | 255 | NO | | Room name/identifier |
| `description` | TEXT | | YES | NULL | Detailed room description |
| `capacity` | INT | 10 | NO | 0 | Maximum occupancy |
| `location` | VARCHAR | 255 | YES | NULL | Physical location/building |
| `equipment` | TEXT | | YES | NULL | Available equipment (JSON or text) |
| `availability_hours` | TEXT | | YES | NULL | Available time slots (JSON) |
| `require_approval` | TINYINT | 1 | NO | 0 | Whether bookings need approval |
| `is_active` | TINYINT | 1 | NO | 1 | Whether room is active/bookable |
| `created_by` | BIGINT | 10 | NO | 0 | User ID who created the room |
| `modified_by` | BIGINT | 10 | NO | 0 | User ID who last modified |
| `time_created` | BIGINT | 10 | NO | 0 | Unix timestamp of creation |
| `time_modified` | BIGINT | 10 | NO | 0 | Unix timestamp of last modification |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_name` (`name`)
- INDEX `idx_active` (`is_active`)
- INDEX `idx_created_by` (`created_by`)

**Foreign Key Relationships:**
- `created_by` → `mdl_user.id`
- `modified_by` → `mdl_user.id`

#### Table: `local_roombooking_bookings`

Stores individual booking records with time slots and status information.

| Field | Type | Length | Null | Default | Description |
|-------|------|--------|------|---------|-------------|
| `id` | BIGINT | 10 | NO | AUTO_INCREMENT | Primary key |
| `room_id` | BIGINT | 10 | NO | 0 | Foreign key to rooms table |
| `user_id` | BIGINT | 10 | NO | 0 | User who made the booking |
| `title` | VARCHAR | 255 | NO | | Booking title/purpose |
| `description` | TEXT | | YES | NULL | Detailed booking description |
| `start_time` | BIGINT | 10 | NO | 0 | Start time (Unix timestamp) |
| `end_time` | BIGINT | 10 | NO | 0 | End time (Unix timestamp) |
| `status` | VARCHAR | 50 | NO | 'pending' | Booking status |
| `workflow_state` | VARCHAR | 50 | YES | NULL | Workflow approval state |
| `workflow_id` | BIGINT | 10 | YES | NULL | External workflow system ID |
| `recurring_pattern_id` | BIGINT | 10 | YES | NULL | Link to recurring pattern |
| `parent_booking_id` | BIGINT | 10 | YES | NULL | For recurring bookings |
| `is_recurring` | TINYINT | 1 | NO | 0 | Whether this is a recurring booking |
| `approved_by` | BIGINT | 10 | YES | NULL | User who approved the booking |
| `approved_time` | BIGINT | 10 | YES | NULL | When the booking was approved |
| `rejection_reason` | TEXT | | YES | NULL | Reason for rejection |
| `notes` | TEXT | | YES | NULL | Additional notes |
| `time_created` | BIGINT | 10 | NO | 0 | Unix timestamp of creation |
| `time_modified` | BIGINT | 10 | NO | 0 | Unix timestamp of last modification |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_room_id` (`room_id`)
- INDEX `idx_user_id` (`user_id`)
- INDEX `idx_time_range` (`start_time`, `end_time`)
- INDEX `idx_status` (`status`)
- INDEX `idx_workflow_state` (`workflow_state`)
- INDEX `idx_recurring` (`recurring_pattern_id`)
- INDEX `idx_parent` (`parent_booking_id`)

**Foreign Key Relationships:**
- `room_id` → `local_roombooking_rooms.id`
- `user_id` → `mdl_user.id`
- `approved_by` → `mdl_user.id`
- `recurring_pattern_id` → `local_roombooking_recurring_patterns.id`
- `parent_booking_id` → `local_roombooking_bookings.id`

#### Table: `local_roombooking_recurring_patterns`

Stores patterns for recurring bookings (daily, weekly, monthly, etc.).

| Field | Type | Length | Null | Default | Description |
|-------|------|--------|------|---------|-------------|
| `id` | BIGINT | 10 | NO | AUTO_INCREMENT | Primary key |
| `pattern_type` | VARCHAR | 50 | NO | | Type: daily, weekly, monthly, yearly |
| `pattern_data` | TEXT | | NO | | JSON data with pattern specifics |
| `start_date` | BIGINT | 10 | NO | 0 | Pattern start date |
| `end_date` | BIGINT | 10 | YES | NULL | Pattern end date (NULL = indefinite) |
| `max_occurrences` | INT | 10 | YES | NULL | Maximum number of occurrences |
| `days_of_week` | VARCHAR | 20 | YES | NULL | For weekly patterns (1,2,3,4,5) |
| `day_of_month` | INT | 3 | YES | NULL | For monthly patterns |
| `interval_count` | INT | 3 | NO | 1 | Interval (every N days/weeks/months) |
| `exceptions` | TEXT | | YES | NULL | JSON array of exception dates |
| `created_by` | BIGINT | 10 | NO | 0 | User who created the pattern |
| `time_created` | BIGINT | 10 | NO | 0 | Unix timestamp of creation |
| `time_modified` | BIGINT | 10 | NO | 0 | Unix timestamp of last modification |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_pattern_type` (`pattern_type`)
- INDEX `idx_date_range` (`start_date`, `end_date`)
- INDEX `idx_created_by` (`created_by`)

**Foreign Key Relationships:**
- `created_by` → `mdl_user.id`

### Audit and History Tables

#### Table: `local_roombooking_booking_history`

Tracks changes to bookings for audit purposes.

| Field | Type | Length | Null | Default | Description |
|-------|------|--------|------|---------|-------------|
| `id` | BIGINT | 10 | NO | AUTO_INCREMENT | Primary key |
| `booking_id` | BIGINT | 10 | NO | 0 | Reference to booking |
| `action` | VARCHAR | 50 | NO | | Action performed |
| `old_data` | TEXT | | YES | NULL | Previous data (JSON) |
| `new_data` | TEXT | | YES | NULL | New data (JSON) |
| `user_id` | BIGINT | 10 | NO | 0 | User who performed action |
| `ip_address` | VARCHAR | 45 | YES | NULL | User's IP address |
| `user_agent` | TEXT | | YES | NULL | User's browser info |
| `time_created` | BIGINT | 10 | NO | 0 | Unix timestamp of action |

**Indexes:**
- PRIMARY KEY (`id`)
- INDEX `idx_booking_id` (`booking_id`)
- INDEX `idx_user_id` (`user_id`)
- INDEX `idx_action` (`action`)
- INDEX `idx_time` (`time_created`)

**Foreign Key Relationships:**
- `booking_id` → `local_roombooking_bookings.id`
- `user_id` → `mdl_user.id`

### Configuration Tables

#### Table: `local_roombooking_settings`

Stores plugin configuration settings.

| Field | Type | Length | Null | Default | Description |
|-------|------|--------|------|---------|-------------|
| `id` | BIGINT | 10 | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR | 255 | NO | | Setting name |
| `value` | TEXT | | YES | NULL | Setting value |
| `type` | VARCHAR | 50 | NO | 'text' | Setting type (text, json, boolean) |
| `description` | TEXT | | YES | NULL | Setting description |
| `is_system` | TINYINT | 1 | NO | 0 | Whether it's a system setting |
| `time_created` | BIGINT | 10 | NO | 0 | Unix timestamp of creation |
| `time_modified` | BIGINT | 10 | NO | 0 | Unix timestamp of last modification |

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `uk_name` (`name`)
- INDEX `idx_type` (`type`)

## Data Relationships

### Entity Relationship Diagram

```
┌─────────────────────┐     ┌──────────────────────┐     ┌─────────────────────┐
│      mdl_user       │     │  roombooking_rooms   │     │ roombooking_bookings│
│                     │     │                      │     │                     │
├─────────────────────┤     ├──────────────────────┤     ├─────────────────────┤
│ id (PK)             │◄────┤ created_by (FK)      │     │ id (PK)             │
│ username            │  ┌──┤ modified_by (FK)     │◄────┤ room_id (FK)        │
│ email               │  │  │ name                 │     │ user_id (FK)        ├──┐
│ ...                 │  │  │ capacity             │     │ start_time          │  │
└─────────────────────┘  │  │ location             │     │ end_time            │  │
                         │  │ ...                  │     │ status              │  │
                         │  └──────────────────────┘     │ workflow_state      │  │
                         │                               │ recurring_pattern_id├──┼──┐
                         │                               │ parent_booking_id   ├──┘  │
                         │                               │ approved_by (FK)    ├─────┘
                         │                               │ ...                 │
                         │                               └─────────────────────┘
                         │
                         │  ┌──────────────────────────────────────────┐
                         │  │     roombooking_recurring_patterns       │
                         │  │                                          │
                         │  ├──────────────────────────────────────────┤
                         │  │ id (PK)                                  │◄─────┐
                         │  │ pattern_type                             │      │
                         │  │ pattern_data                             │      │
                         │  │ start_date                               │      │
                         │  │ end_date                                 │      │
                         └─►│ created_by (FK)                          │      │
                            │ ...                                      │      │
                            └──────────────────────────────────────────┘      │
                                                                              │
┌───────────────────────────────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────┐
│  │     roombooking_booking_history          │
│  │                                          │
│  ├──────────────────────────────────────────┤
│  │ id (PK)                                  │
└─►│ booking_id (FK)                          │
   │ action                                   │
   │ old_data                                 │
   │ new_data                                 │
   │ user_id (FK)                             ├──┐
   │ time_created                             │  │
   │ ...                                      │  │
   └──────────────────────────────────────────┘  │
                                                  │
┌─────────────────────────────────────────────────┘
│
│  ┌──────────────────────────────────────────┐
│  │      roombooking_settings                │
│  │                                          │
│  ├──────────────────────────────────────────┤
└─►│ name                                     │
   │ value                                    │
   │ type                                     │
   │ description                              │
   │ ...                                      │
   └──────────────────────────────────────────┘
```

## Data Types and Constraints

### Field Types Used

1. **BIGINT(10)**: Primary keys, foreign keys, timestamps
2. **VARCHAR(255)**: Names, titles, short text fields
3. **TEXT**: Long text, JSON data, descriptions
4. **INT(10)**: Numeric values, counts, capacities
5. **TINYINT(1)**: Boolean flags (0/1)

### Constraints

#### NOT NULL Constraints
- All primary keys
- Essential fields like names, user IDs, timestamps
- Foreign key fields that must have valid references

#### DEFAULT Values
- Boolean flags default to 0 (false)
- Status fields have meaningful defaults
- Timestamps default to 0 (will be set by application)

#### CHECK Constraints (Application Level)
- Start time must be before end time
- Capacity must be positive
- Status values must be from allowed list

## Indexes and Performance

### Primary Indexes
- All tables have auto-incrementing primary keys
- Primary keys are clustered indexes for optimal performance

### Secondary Indexes
- Foreign key fields are indexed for join performance
- Date/time fields used in range queries are indexed
- Status and state fields used in filtering are indexed
- Composite indexes for common query patterns

### Query Optimization Strategies

1. **Conflict Detection**: Index on `(room_id, start_time, end_time)` for fast overlap detection
2. **User Bookings**: Index on `(user_id, time_created)` for user history
3. **Room Availability**: Index on `(room_id, is_active)` for room listings
4. **Workflow Queries**: Index on `(workflow_state, time_created)` for approval queues

## Data Integrity Rules

### Referential Integrity
- All foreign keys must reference valid records
- Cascade rules defined for dependent data cleanup
- Orphaned records are prevented through constraints

### Business Rules (Application Enforced)
- Booking end time must be after start time
- Room capacity must be positive
- User can only modify their own bookings (unless admin)
- Approved bookings require higher privileges to modify
- **Workflow Rejection Rules**: Leader 1 rejections go to final rejection, while higher-level rejections return to Leader 1 Review for re-evaluation

### Data Validation
- Email formats validated
- Date ranges validated
- JSON data structure validated
- Required fields enforced

## Backup and Maintenance

### Backup Strategy
- Full backup includes all plugin tables
- Incremental backups focus on booking and history tables
- Archive old booking data periodically

### Maintenance Tasks
- Purge old history records (configurable retention)
- Clean up orphaned recurring pattern records
- Rebuild indexes periodically for large datasets
- Analyze query performance regularly

### Data Migration
- Version-specific upgrade scripts in `db/upgrade.php`
- Data transformation scripts for major version changes
- Rollback procedures for failed upgrades

## Security Considerations

### SQL Injection Prevention
- All database access through Moodle's DB API
- Parameterized queries only
- Input validation before database operations

### Data Privacy
- Personal data identified and protected
- GDPR compliance for user data handling
- Audit trail for data access and modifications

### Access Control
- Database access through application layer only
- No direct database access for users
- Capability-based access control enforced

This database schema provides a robust foundation for the Room Booking plugin while maintaining compatibility with Moodle's standards and best practices. 