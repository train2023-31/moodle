# Oracle Fetch - Centralized Oracle Database Access

This plugin provides centralized Oracle database access for all Moodle local plugins.

## Features

- Single Oracle connection configuration
- Consistent error handling
- Common employee and person data operations
- AJAX endpoints for front-end integration
- Helper functions for easy integration
- **Self-contained design** - includes all required resources locally

## Usage

### 1. Include Oracle Manager Class

```php
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');
```

### 2. Use Helper Functions

```php
require_once(__DIR__ . '/../oracleFetch/lib.php');

// Get employee name by PF number
$employee_name = oracle_get_employee_name($pf_number);

// Get person name by civil number  
$person_name = oracle_get_person_name($civil_number);

// Get person name with nationality
$person_name_with_nationality = oracle_get_person_name($civil_number, true);

// Get person nationality only
$person_nationality = oracle_get_person_nationality($civil_number);

// Search by civil number (AJAX endpoint)
// Use ajax/search_by_civil.php for AJAX requests

// Get employee or person data by identifier
$person_data = oracle_get_person_data($identifier);
```

### 3. Use Oracle Manager Directly

```php
// Get all employees
$employees = oracle_manager::get_all_employees();

// Get all persons
$persons = oracle_manager::get_all_persons();

// Get specific employee by PF number
$employee = oracle_manager::get_employee_by_pf($pf_number);

// Get specific person by civil number
$person = oracle_manager::get_person_by_civil($civil_number);

// Search employees (supports Arabic names and PF numbers)
$results = oracle_manager::search_employees($search_term);

// Get combined employees and persons (for annual plans beneficiaries)
$combined_data = oracle_manager::get_all_employees_and_persons();

// Get person data (tries both employee and person tables)
$data = oracle_manager::get_person_data($identifier);
```

## AJAX Endpoints

- `/local/oracleFetch/ajax/get_employees.php` - Get all employees
- `/local/oracleFetch/ajax/get_persons.php` - Get all persons  
- `/local/oracleFetch/ajax/get_employees_and_persons.php` - Get combined employees and persons (for beneficiaries)
- `/local/oracleFetch/ajax/search_employees.php?term=search` - Search employees

## Migration

Replace direct Oracle connections in your plugins:

### Before:
```php
$dbuser = 'moodleuser';
$dbpass = 'moodle';
$dbname = '//localhost:1521/XEPDB1';
$conn = oci_connect($dbuser, $dbpass, $dbname, 'AL32UTF8');
// ... manual query code ...
```

### After:
```php
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');
$employees = oracle_manager::get_all_employees();
```

## Configuration

All Oracle connection settings are centralized in:
- `local/oracleFetch/classes/oracle_manager.php`

Update the connection details in one place to affect all plugins.

## Search Functionality

The search function supports multiple search patterns:

```php
// Search by PF number
$results = oracle_manager::search_employees('PF001');

// Search by Arabic name
$results = oracle_manager::search_employees('أحمد');

// Search by partial name
$results = oracle_manager::search_employees('محمد');

// Get initial results (no search term)
$results = oracle_manager::search_employees('');
```

**Search Features:**
- ✅ PF number search (case-insensitive)
- ✅ Arabic name search (exact match)
- ✅ Partial name matching
- ✅ Returns first 10 employees when no search term
- ✅ Results ordered by PF number matches first

## Error Handling

All methods include comprehensive error handling:
- Database connection failures are logged
- Query errors are logged with details
- Methods return empty arrays/false on failure
- No exceptions thrown to calling code

## Troubleshooting

**Common Issues:**

1. **No search results:** Check Oracle connection and table names
2. **Search not working:** Verify search term format (try PF numbers)
3. **Connection failed:** Check credentials in `oracle_manager.php`

**Debug Tools:**
- Check PHP error logs for Oracle connection issues
- Test direct AJAX endpoints in browser
- Verify table names: `employees` and `person_details`

## Database Tables

This plugin works with these Oracle database tables:
- **`employees`** (plural) - Contains employee data with PF numbers
- **`person_details`** - Contains personal data with civil numbers

## Plugins Using Oracle Fetch

Current integrations:
- ✅ `local/annualplans` - Beneficiary management
- ✅ `local/participant` - Employee lookup
- ✅ `local/externallecturer` - Personal data
- ✅ `local/residencebooking` - Guest search

## Offline Compatibility

This plugin is **100% offline compatible**:
- ✅ No external CDN dependencies
- ✅ Local Select2 files included
- ✅ Works without internet connection
- ✅ Self-contained design