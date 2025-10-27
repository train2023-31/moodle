# File Structure Documentation - Annual Report Block

## Overview

This document provides detailed information about each file in the Annual Report Block plugin, explaining their purpose, functionality, and how they work together.

## Root Directory Files

### `block_annual_report.php`
**Purpose**: Main block class implementation  
**Size**: 336 lines (~14KB)  
**Description**: Contains the core functionality of the block plugin.

#### Key Methods:
- `init()`: Initializes the block with title from language strings
- `get_content()`: Main method that generates all block content

#### Functionality:
1. **Data Retrieval**: Queries multiple database tables to gather statistics
   - Internal/external course levels from `local_annual_plan_course_level`
   - Course data from `local_annual_plan_course`
   - Financial data from `local_financeservices` and `local_financeservices_clause`

2. **Internal Courses Section** (Lines 22-143):
   - Retrieves course counts by level type
   - Calculates total beneficiaries
   - Supports multilingual field selection (English/Arabic)
   - Handles dynamic level IDs from database

3. **External Courses Section** (Lines 145-218):
   - Similar functionality to internal courses
   - Uses external level categories
   - Displays separate statistics table

4. **Financial Section** (Lines 220-320):
   - Retrieves approved budget amounts for clauses 802 and 811
   - Calculates spent amounts from service requests
   - Computes remaining budget
   - Formats currency display in OMR

#### Key Features:
- **Language-Aware Queries**: Uses CASE statements to select appropriate language fields
- **Parameterized Queries**: All SQL uses proper parameter binding for security
- **Error Handling**: Graceful fallbacks for missing data
- **HTML Generation**: Inline HTML construction with CSS classes

### `version.php`
**Purpose**: Plugin version and metadata definition  
**Size**: 8 lines (~174B)  
**Description**: Standard Moodle plugin version file.

#### Properties:
- `$plugin->component`: Plugin identifier (`block_annual_report`)
- `$plugin->version`: Version timestamp (2025071302)
- `$plugin->requires`: Minimum Moodle version (2022041900 = Moodle 4.0)

### `styles.css`
**Purpose**: Block-specific styling  
**Size**: 67 lines (~1.3KB)  
**Description**: Provides modern, responsive styling for the block content.

#### CSS Classes:
- `.wrapper`: Main container with background, padding, and shadow
- `.title`: Section headers with centered, bold styling
- `.count-row`: Flex container for summary statistics
- `.box`: Highlighted summary boxes with background and borders
- `.stat-table`: Responsive table styling with alternating row colors
- `.line`: Horizontal divider between sections

#### Design Features:
- Modern color scheme (#2c3e50, #0073aa, #eef3f7)
- Responsive design principles
- Box shadows and rounded corners
- Hover effects and professional appearance

## Database Directory (`db/`)

### `access.php`
**Purpose**: Define plugin capabilities and permissions  
**Size**: 58 lines (~2.1KB)  
**Description**: Standard Moodle access control definition.

#### Capabilities Defined:
1. `block/annual_report:addinstance`
   - **Purpose**: Allow adding block instances to courses/pages
   - **Context**: CONTEXT_BLOCK
   - **Default Permissions**: Editing teachers, managers
   - **Type**: Write capability

2. `block/annual_report:myaddinstance`
   - **Purpose**: Allow adding block to personal My Moodle page
   - **Context**: CONTEXT_SYSTEM  
   - **Default Permissions**: All users
   - **Type**: Write capability

#### Security Features:
- Proper capability type assignment
- Context-level restrictions
- Archetype-based default permissions
- Permission inheritance from core Moodle capabilities

## Language Directory (`lang/`)

### English (`lang/en/block_annual_report.php`)
**Purpose**: English language strings  
**Size**: 18 lines (~777B)  
**Description**: Contains all English text used in the plugin interface.

#### String Categories:
1. **Core Plugin Strings**:
   - `pluginname`: Block title
   - Section headers for internal/external courses and financial data

2. **Interface Elements**:
   - Table headers (coursetype, count, beneficiaries)
   - Financial terms (approved amount, spent amount, remaining amount)
   - Clause identifiers (802, 811)

3. **Capability Strings**:
   - User-friendly descriptions for permission capabilities

### Arabic (`lang/ar/block_annual_report.php`)
**Purpose**: Arabic language strings  
**Size**: 18 lines (~1.0KB)  
**Description**: Arabic translations of all interface text.

#### Features:
- Complete Arabic translations for all English strings
- Culturally appropriate terminology
- Right-to-left text support
- Professional Arabic language usage

## Additional Files

### `Error_AHMED.txt`
**Purpose**: Development documentation and error resolution log  
**Size**: 90 lines (~3.5KB)  
**Description**: Detailed documentation of a resolved database schema issue.

#### Contents:
1. **Problem Description**: Database error due to schema changes
2. **Root Cause Analysis**: Transition from single `description` field to multilingual fields
3. **Solution Implementation**: Updated SQL queries with CASE statements
4. **Technical Details**: Before/after code examples
5. **Verification Steps**: Testing and validation procedures

#### Value for Development:
- Historical record of major bug fixes
- Example of proper problem documentation
- Reference for similar schema migration issues
- Troubleshooting guide for developers

## File Interdependencies

### Data Flow
```
version.php → Plugin Registration
    ↓
access.php → Permission Check
    ↓
block_annual_report.php → Content Generation
    ↓
lang/*/block_annual_report.php → String Translation
    ↓
styles.css → Visual Presentation
```

### Database Dependencies
The main block file depends on these external database tables:
- `local_annual_plan_course`: Course scheduling and planning data
- `local_annual_plan_course_level`: Course categorization and level definitions
- `local_financeservices`: Financial service requests and expenditures
- `local_financeservices_clause`: Budget allocations and approved amounts

### Language Integration
```php
// Language detection in SQL queries
CASE 
    WHEN ? = 'ar' THEN COALESCE(cl.description_ar, cl.description_en, cl.name)
    ELSE COALESCE(cl.description_en, cl.description_ar, cl.name)
END AS level_name
```

## Code Architecture Patterns

### SQL Query Pattern
1. **Dynamic Placeholder Generation**:
   ```php
   $placeholders = implode(',', array_fill(0, count($ids), '?'));
   ```

2. **Parameter Merging**:
   ```php
   $params = array_merge($level_ids, [$yearStart, $yearEnd]);
   ```

3. **Safe Query Execution**:
   ```php
   $result = $DB->get_records_sql($sql, $params);
   ```

### HTML Generation Pattern
```php
$this->content->text .= "<div class='section'>";
$this->content->text .= "<h3 class='title'>" . get_string('key', 'block_annual_report') . "</h3>";
// Table generation...
$this->content->text .= "</div>";
```

### Error Handling Pattern
```php
$result = $DB->get_record_sql($sql, $params);
$value = $result ? $result->field_name : 0; // Fallback for missing data
```

## Performance Considerations

### Database Optimization
- Uses parameterized queries to prevent SQL injection
- Employs efficient JOIN operations
- Includes proper WHERE clause filtering
- Aggregates data at database level using COUNT() and SUM()

### Memory Management
- Processes data in chunks rather than loading all at once
- Uses appropriate Moodle database methods
- Implements lazy loading of content

### Caching Implications
- No internal caching implemented (relies on Moodle's caching)
- Content regenerated on each page load
- Consider implementing cache for production use

## Security Features

### Input Validation
- All database parameters properly escaped
- No direct user input processing
- Uses Moodle's database API exclusively

### Output Sanitization
- Uses `format_string()` for user-generated content
- Proper HTML encoding for display
- No raw HTML output from database

### Access Control
- Implements proper capability checks
- Context-aware permissions
- Follows Moodle security guidelines

## Maintenance Guidelines

### Regular Maintenance Tasks
1. Monitor database query performance
2. Update language strings as needed
3. Review and update version numbers
4. Test compatibility with Moodle upgrades

### Code Quality Checks
- Follow Moodle coding standards
- Validate SQL queries for efficiency
- Ensure proper error handling
- Maintain documentation updates

### Monitoring Recommendations
- Track database query execution times
- Monitor for SQL errors in logs
- Validate data accuracy periodically
- Check language string completeness 