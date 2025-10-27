# Development Guide - Annual Report Block

## Development Environment Setup

### Prerequisites
- Moodle 4.0+ development environment
- PHP 7.4+ with required Moodle extensions
- MySQL/PostgreSQL database with custom tables
- Git for version control

### Development Database Requirements

Ensure these custom tables exist in your development database:

```sql
-- Course planning tables
local_annual_plan_course
local_annual_plan_course_level

-- Financial services tables  
local_financeservices
local_financeservices_clause
```

## Coding Standards

### PHP Standards
- Follow [Moodle Coding Style](https://docs.moodle.org/dev/Coding_style)
- Use PHPDoc comments for all functions and classes
- Validate all database inputs and sanitize outputs
- Use Moodle's database API exclusively (no direct SQL)

### Security Guidelines
- Always use `defined('MOODLE_INTERNAL') || die();` at the top of PHP files
- Sanitize all user inputs using appropriate Moodle functions
- Use parameterized queries to prevent SQL injection
- Implement proper capability checks

### Language Strings
- Store all user-facing strings in language files
- Use descriptive string identifiers
- Provide translations for both English and Arabic
- Use `get_string()` function for string retrieval

## Architecture Overview

### Main Components

1. **Block Class** (`block_annual_report.php`)
   - Extends `block_base`
   - Implements `init()` and `get_content()` methods
   - Handles all data retrieval and HTML generation

2. **Database Layer**
   - Uses Moodle's database API (`$DB` object)
   - Implements proper error handling
   - Uses parameterized queries for security

3. **Presentation Layer**
   - Inline HTML generation within PHP
   - CSS styling in separate file
   - Responsive design principles

### Data Flow

```
Database Tables → SQL Queries → PHP Processing → HTML Generation → CSS Styling → User Display
```

## Working with Database Queries

### Query Structure Pattern
```php
// 1. Define placeholders for parameters
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// 2. Build SQL query with placeholders
$sql = "SELECT field FROM table WHERE id IN ($placeholders) AND condition = ?";

// 3. Merge parameters in correct order
$params = array_merge($ids, [$additional_param]);

// 4. Execute query with error handling
$result = $DB->get_records_sql($sql, $params);
```

### Key Database Methods Used
- `$DB->get_records_sql()` - Multiple records
- `$DB->get_record_sql()` - Single record
- `$DB->get_field()` - Single field value
- `$DB->get_records()` - Simple record retrieval

## Adding New Features

### 1. Adding New Statistics Section

**Step 1: Update Database Queries**
```php
// Add new SQL query following existing pattern
$new_stats_sql = "SELECT ... FROM {table} WHERE condition = ?";
$new_params = [$param1, $param2];
$new_result = $DB->get_records_sql($new_stats_sql, $new_params);
```

**Step 2: Add Language Strings**
```php
// In lang/en/block_annual_report.php
$string['newsection'] = 'New Section Title';

// In lang/ar/block_annual_report.php  
$string['newsection'] = 'عنوان القسم الجديد';
```

**Step 3: Generate HTML Output**
```php
$this->content->text .= "<div>
    <h3 class='title'>" . get_string('newsection', 'block_annual_report') . "</h3>
    <!-- Additional HTML structure -->
</div>";
```

### 2. Modifying Existing Queries

When modifying queries, always:
1. Test with various data scenarios
2. Update parameter arrays accordingly
3. Handle edge cases (empty results, null values)
4. Maintain backward compatibility where possible

### 3. Adding New Languages

**Step 1: Create Language Directory**
```bash
mkdir blocks/annual_report/lang/[language_code]/
```

**Step 2: Create Language File**
```php
// blocks/annual_report/lang/[language_code]/block_annual_report.php
<?php
$string['pluginname'] = 'Translated Plugin Name';
// ... other strings
```

**Step 3: Update Queries (if needed)**
Add language-specific field handling in SQL queries using CASE statements.

### 4. Financial Data Filtering

**Important**: When working with finance services data, always include status filtering:

```php
// ✅ Correct: Only approved requests
$sql = "SELECT SUM(price_requested) FROM {local_financeservices} 
        WHERE clause_id = ? AND status_id = 13";

// ❌ Incorrect: All requests (including rejected/pending)
$sql = "SELECT SUM(price_requested) FROM {local_financeservices} 
        WHERE clause_id = ?";
```

**Status IDs**:
- `13`: Approved requests (include in calculations)
- `14`: Rejected requests (exclude from calculations)
- `8-12`: Pending requests (exclude from calculations)

## Testing Guidelines

### Manual Testing Checklist
- [ ] Block displays correctly in different languages
- [ ] All statistics show accurate data
- [ ] Financial calculations are correct
- [ ] No database errors in logs
- [ ] Responsive design works on mobile
- [ ] No JavaScript errors in console

### Test Data Scenarios
- Empty database tables
- Large datasets (performance testing)
- Mixed language content
- Edge cases (zero values, null fields)

### Database Testing
```sql
-- Test internal courses
SELECT COUNT(*) FROM local_annual_plan_course 
WHERE courselevelid IN (SELECT id FROM local_annual_plan_course_level WHERE is_internal = 1);

-- Test financial data
SELECT * FROM local_financeservices_clause WHERE id IN (1,2);
```

## Common Development Tasks

### Debugging Database Issues
1. Enable Moodle debugging: Admin → Development → Debugging
2. Check database logs for SQL errors
3. Use `var_dump()` or `debugging()` for variable inspection
4. Verify table structure matches query expectations

### Performance Optimization
- Use database indexes on frequently queried fields
- Minimize the number of database queries
- Consider caching for expensive calculations
- Use `LIMIT` clauses where appropriate

### Error Handling
```php
try {
    $result = $DB->get_records_sql($sql, $params);
    if (!$result) {
        $result = []; // Provide fallback
    }
} catch (dml_exception $e) {
    debugging('Database error: ' . $e->getMessage());
    $result = []; // Graceful fallback
}
```

## Version Control Workflow

### Branching Strategy
- `main` - Production-ready code
- `develop` - Integration branch for features
- `feature/*` - Individual feature development
- `hotfix/*` - Critical bug fixes

### Commit Message Format
```
type(scope): description

Examples:
feat(queries): add new financial statistics section
fix(database): resolve multilingual field selection issue
docs(readme): update installation instructions
style(css): improve mobile responsiveness
```

## Code Review Guidelines

### Review Checklist
- [ ] Code follows Moodle coding standards
- [ ] Security best practices implemented
- [ ] Database queries are optimized and secure
- [ ] Language strings properly externalized
- [ ] Error handling implemented
- [ ] Documentation updated

### Performance Considerations
- Avoid N+1 query problems
- Use appropriate database methods
- Consider query execution time
- Monitor memory usage with large datasets

## Deployment Process

### Pre-deployment Checklist
- [ ] All tests pass
- [ ] Code reviewed and approved
- [ ] Database schema changes documented
- [ ] Language files updated
- [ ] Version number incremented

### Deployment Steps
1. Backup current plugin files
2. Deploy new plugin code
3. Run Moodle upgrade process
4. Clear all caches
5. Verify functionality in production

## Troubleshooting Common Issues

### Database Connection Issues
- Verify custom tables exist
- Check table permissions
- Validate field names match queries

### Language Display Issues
- Clear language caches
- Verify language file syntax
- Check `current_language()` function usage

### Performance Issues
- Review query complexity
- Check for missing database indexes
- Monitor slow query logs

## Future Development Considerations

### Potential Enhancements
- Add date range filtering options
- Implement data export functionality
- Create administrative configuration interface
- Add graphical charts and visualizations
- Implement caching for improved performance

### Scalability Considerations
- Database query optimization
- Caching strategy implementation
- Memory usage optimization
- Multi-site compatibility 