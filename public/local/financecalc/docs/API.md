# API Documentation - Finance Calculator Plugin

## Overview

This document provides technical details for developers who want to integrate with or extend the Finance Calculator plugin. It covers the public API, database schema, and development guidelines.

> **Note**: This is the API reference documentation. For general plugin information, see the main `README.md` file.

## Public API Reference

### Data Manager Class

The main API is provided through the `\local_financecalc\data_manager` class.

#### `get_financial_data($yearfilter = 0)`

Retrieves financial data for reporting.

**Parameters:**
- `$yearfilter` (int): Year to filter by (0 = all years)

**Returns:**
- `array`: Array of financial data objects with properties:
  - `year` (int): The fiscal year
  - `spending_omr` (float): Total spending in OMR
  - `budget_omr` (float): Total budget in OMR

**Example:**
```php
// Get all years data
$alldata = \local_financecalc\data_manager::get_financial_data(0);

// Get specific year data
$year2025 = \local_financecalc\data_manager::get_financial_data(2025);

// Process the data
foreach ($alldata as $record) {
    $balance = $record->budget_omr - $record->spending_omr;
    echo "Year {$record->year}: Budget {$record->budget_omr}, Spending {$record->spending_omr}, Balance {$balance}\n";
}
```

#### `refresh_cached_data()`

Refreshes the cached financial data from source tables.

**Parameters:**
- None

**Returns:**
- `bool`: True on success, false on failure

**Example:**
```php
$success = \local_financecalc\data_manager::refresh_cached_data();
if ($success) {
    echo "Cache refreshed successfully";
} else {
    echo "Cache refresh failed";
}
```

#### `get_detailed_breakdown($year)`

Gets detailed spending breakdown by source for a specific year.

**Parameters:**
- `$year` (int): The year to get breakdown for

**Returns:**
- `object`: Object with properties:
  - `finance_spending` (float): Spending from finance services
  - `participant_spending` (float): Spending from participant requests
  - `total_spending` (float): Total spending (sum of both sources)

**Example:**
```php
$breakdown = \local_financecalc\data_manager::get_detailed_breakdown(2025);
echo "Finance Services: {$breakdown->finance_spending} OMR\n";
echo "Participant Requests: {$breakdown->participant_spending} OMR\n";
echo "Total: {$breakdown->total_spending} OMR\n";
```

#### `get_last_updated_time()`

Gets the timestamp of the last cache update.

**Parameters:**
- None

**Returns:**
- `int|null`: Unix timestamp or null if no cache exists

**Example:**
```php
$lastUpdated = \local_financecalc\data_manager::get_last_updated_time();
if ($lastUpdated) {
    echo "Last updated: " . date('Y-m-d H:i:s', $lastUpdated);
} else {
    echo "No cached data available";
}
```

## Database Schema

### Cache Table: `local_financecalc_yearly`

```sql
CREATE TABLE mdl_local_financecalc_yearly (
    id INT(10) NOT NULL AUTO_INCREMENT,
    year INT(4) NOT NULL,
    spending_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_omr DECIMAL(12,2) NOT NULL DEFAULT 0,
    timecreated INT(10) NOT NULL,
    timemodified INT(10) NOT NULL,
    created_by INT(10) NOT NULL,
    modified_by INT(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY year (year),
    INDEX yearidx (year),
    INDEX timecreatedidx (timecreated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Field Descriptions:**
- `id`: Primary key
- `year`: Fiscal year (unique)
- `spending_omr`: Total spending in Omani Rials
- `budget_omr`: Total budget in Omani Rials
- `timecreated`: Unix timestamp when record was created
- `timemodified`: Unix timestamp when record was last modified
- `created_by`: User ID who created the record
- `modified_by`: User ID who last modified the record

### Source Tables (Read-only)

The plugin reads from these existing tables:

#### `local_financeservices_clause`
- `id`: Primary key
- `clause_year`: Fiscal year
- `amount`: Budget amount
- `deleted`: Soft delete flag (0 = active)

#### `local_financeservices`
- `id`: Primary key
- `clause_id`: Foreign key to clause table
- `price_requested`: Requested amount
- `status_id`: Request status (13 = approved)

#### `local_participant_requests`
- `id`: Primary key
- `annual_plan_id`: Foreign key to annual plan
- `duration_amount`: Duration in units
- `compensation_amount`: Direct compensation amount
- `participant_type_id`: Foreign key to request types
- `is_approved`: Approval flag (1 = approved)

#### `local_annual_plan`
- `id`: Primary key
- `year`: Fiscal year

#### `local_participant_request_types`
- `id`: Primary key
- `cost`: Cost per unit

## Core SQL Queries

### All Years Query (CTE-based)

```sql
WITH budget AS (
    SELECT c.clause_year AS year, SUM(c.amount) AS budget_omr
    FROM {local_financeservices_clause} c
    WHERE c.deleted = 0
    GROUP BY c.clause_year
),
fin_spend AS (
    SELECT c.clause_year AS year, SUM(f.price_requested) AS spending_fin_omr
    FROM {local_financeservices} f
    JOIN {local_financeservices_clause} c ON c.id = f.clause_id
    WHERE f.status_id = 13
    GROUP BY c.clause_year
),
part_spend AS (
    SELECT ap.year AS year,
           SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost))) AS spending_part_omr
    FROM {local_participant_requests} r
    JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
    LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
    WHERE r.is_approved = 1
    GROUP BY ap.year
),
years AS (
    SELECT year FROM budget
    UNION
    SELECT year FROM fin_spend
    UNION
    SELECT year FROM part_spend
)
SELECT y.year,
       (COALESCE(f.spending_fin_omr, 0) + COALESCE(p.spending_part_omr, 0)) AS spending_omr,
       COALESCE(b.budget_omr, 0) AS budget_omr
FROM years y
LEFT JOIN budget b ON b.year = y.year
LEFT JOIN fin_spend f ON f.year = y.year
LEFT JOIN part_spend p ON p.year = y.year
ORDER BY y.year DESC;
```

### Single Year Query (Subquery-based)

```sql
SELECT 
    :year AS year,
    COALESCE((
        SELECT SUM(f.price_requested)
        FROM {local_financeservices} f
        JOIN {local_financeservices_clause} c ON c.id = f.clause_id
        WHERE f.status_id = 13 AND c.clause_year = :year
    ), 0) + COALESCE((
        SELECT SUM(COALESCE(r.compensation_amount, (r.duration_amount * t.cost)))
        FROM {local_participant_requests} r
        JOIN {local_annual_plan} ap ON ap.id = r.annual_plan_id
        LEFT JOIN {local_participant_request_types} t ON t.id = r.participant_type_id
        WHERE r.is_approved = 1 AND ap.year = :year
    ), 0) AS spending_omr,
    COALESCE((
        SELECT SUM(c.amount)
        FROM {local_financeservices_clause} c
        WHERE c.deleted = 0 AND c.clause_year = :year
    ), 0) AS budget_omr;
```

## Capabilities

### `local/financecalc:view`
- **Description**: View financial reports
- **Context**: System
- **Default Roles**: Manager, Course Creator
- **Usage**: Required to access the financial overview page

### `local/financecalc:manage`
- **Description**: Manage financial calculations
- **Context**: System
- **Default Roles**: Manager only
- **Usage**: Required to refresh data and manage cache

## Hooks and Callbacks

### Navigation Hooks

The plugin extends Moodle navigation through these functions in `lib.php`:

#### `local_financecalc_extend_navigation_global()`
Adds the Financial Overview link to Site administration > Reports, pointing to `/local/financecalc/pages/report.php`.

#### `local_financecalc_extend_settings_navigation()`
Adds settings links when appropriate (currently minimal implementation).

### Scheduled Task

#### `\local_financecalc\tasks\refresh_financial_data`
Automatically refreshes cached data.

**Configuration:**
- **Frequency**: Daily (configurable)
- **Enabled**: Yes (by default)
- **Last Run**: Tracked in Moodle scheduled tasks

**Dependencies:**
- Requires `local_financeservices` plugin to be installed
- Requires `local_participant` plugin to be installed
- Will skip execution if dependencies are missing

## Integration Examples

### Custom Report Integration

```php
// In your custom plugin
class custom_financial_report {
    
    public function get_enhanced_report($year) {
        // Get base financial data
        $financial_data = \local_financecalc\data_manager::get_financial_data($year);
        
        // Add your custom calculations
        $enhanced_data = [];
        foreach ($financial_data as $record) {
            $enhanced_record = clone $record;
            $enhanced_record->utilization_rate = ($record->spending_omr / $record->budget_omr) * 100;
            $enhanced_record->remaining_budget = $record->budget_omr - $record->spending_omr;
            $enhanced_data[] = $enhanced_record;
        }
        
        return $enhanced_data;
    }
}
```

### Custom Filter Integration

```php
// Add custom filters to the financial report
class custom_financial_filters {
    
    public static function add_department_filter($form) {
        global $DB;
        
        // Get departments from your custom table
        $departments = $DB->get_records_menu('your_departments_table', null, 'name', 'id,name');
        
        $form->addElement('select', 'department', 'Department', $departments);
        $form->setType('department', PARAM_INT);
    }
    
    public static function filter_data_by_department($data, $department_id) {
        if (empty($department_id)) {
            return $data;
        }
        
        // Apply department-specific filtering logic
        // This would depend on your data structure
        return array_filter($data, function($record) use ($department_id) {
            return $record->department_id == $department_id;
        });
    }
}
```

## Performance Considerations

### Query Optimization

1. **Indexes**: Ensure these indexes exist on source tables:
   ```sql
   -- Finance services
   CREATE INDEX idx_financeservices_status ON mdl_local_financeservices(status_id);
   CREATE INDEX idx_financeservices_clause ON mdl_local_financeservices(clause_id);
   CREATE INDEX idx_financeservices_clause_year ON mdl_local_financeservices_clause(clause_year);
   
   -- Participant requests
   CREATE INDEX idx_participant_approved ON mdl_local_participant_requests(is_approved);
   CREATE INDEX idx_participant_annual_plan ON mdl_local_participant_requests(annual_plan_id);
   CREATE INDEX idx_annual_plan_year ON mdl_local_annual_plan(year);
   ```

2. **Caching Strategy**: Use the built-in caching for large datasets
3. **Query Limits**: Consider pagination for very large result sets

### Memory Management

1. **Large Datasets**: Use generators for processing large amounts of data
2. **Connection Pooling**: Reuse database connections when possible
3. **Cleanup**: Properly close database connections and free memory

## Error Handling

### Common Exceptions

#### `dml_exception`
Database-related errors (table not found, connection issues)

```php
try {
    $data = \local_financecalc\data_manager::get_financial_data(2025);
} catch (dml_exception $e) {
    debugging("Database error: " . $e->getMessage());
    // Handle gracefully
}
```

#### `coding_exception`
Invalid parameters or configuration issues

```php
try {
    $breakdown = \local_financecalc\data_manager::get_detailed_breakdown('invalid');
} catch (coding_exception $e) {
    debugging("Invalid parameter: " . $e->getMessage());
    // Handle gracefully
}
```

### Error Recovery

```php
class financial_data_service {
    
    public function get_data_with_fallback($year) {
        try {
            // Try cached data first
            return \local_financecalc\data_manager::get_financial_data($year);
        } catch (Exception $e) {
            debugging("Cache failed, trying live calculation: " . $e->getMessage());
            
            try {
                // Fallback to live calculation
                return \local_financecalc\data_manager::calculate_financial_data($year);
            } catch (Exception $e2) {
                debugging("Live calculation also failed: " . $e2->getMessage());
                return []; // Return empty array as last resort
            }
        }
    }
}
```

## Testing

### Unit Tests

```php
// tests/data_manager_test.php
class local_financecalc_data_manager_testcase extends advanced_testcase {
    
    public function test_get_financial_data() {
        $this->resetAfterTest();
        
        // Set up test data
        $this->create_test_financial_data();
        
        // Test the function
        $data = \local_financecalc\data_manager::get_financial_data(2025);
        
        // Assertions
        $this->assertNotEmpty($data);
        $this->assertEquals(2025, $data[0]->year);
        $this->assertGreaterThan(0, $data[0]->budget_omr);
    }
    
    private function create_test_financial_data() {
        global $DB;
        
        // Create test data in the database
        // This would depend on your test data structure
    }
}
```

### Integration Tests

```php
// tests/integration_test.php
class local_financecalc_integration_testcase extends advanced_testcase {
    
    public function test_full_workflow() {
        $this->resetAfterTest();
        
        // Test complete workflow
        $this->create_test_data();
        
        // Test data retrieval
        $data = \local_financecalc\data_manager::get_financial_data(2025);
        $this->assertNotEmpty($data);
        
        // Test cache refresh
        $success = \local_financecalc\data_manager::refresh_cached_data();
        $this->assertTrue($success);
        
        // Test cached data
        $cached_data = \local_financecalc\data_manager::get_financial_data(2025);
        $this->assertEquals($data, $cached_data);
    }
}
```

## Security Considerations

### Input Validation

1. **Year Parameters**: Always validate year inputs
2. **SQL Injection**: Use Moodle's `$DB` methods with proper parameterization
3. **Access Control**: Check capabilities before performing operations

### Data Protection

1. **Sensitive Data**: Financial data should be protected
2. **Audit Trail**: Log important operations
3. **Access Logging**: Track who accesses financial reports

## Migration and Upgrades

### Version Compatibility

The plugin maintains backward compatibility within major versions. When upgrading:

1. **Backup Data**: Always backup before upgrading
2. **Test Migration**: Test on staging environment first
3. **Check Dependencies**: Verify source plugins are compatible

### Data Migration

```php
// Example migration script
class local_financecalc_migration {
    
    public static function migrate_from_old_version() {
        global $DB;
        
        // Check if migration is needed
        if ($DB->get_manager()->table_exists('old_finance_table')) {
            // Migrate data from old table
            $old_data = $DB->get_records('old_finance_table');
            
            foreach ($old_data as $record) {
                $new_record = new stdClass();
                $new_record->year = $record->fiscal_year;
                $new_record->spending_omr = $record->total_spending;
                $new_record->budget_omr = $record->total_budget;
                // ... map other fields
                
                $DB->insert_record('local_financecalc_yearly', $new_record);
            }
        }
    }
}
```

---

**Last Updated**: January 2025  
**API Version**: 1.0  
**Compatible with**: Moodle 4.1+
