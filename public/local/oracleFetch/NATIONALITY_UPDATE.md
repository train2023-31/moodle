# Nationality Field Update Summary

## Overview
Added support for the `nationality_arabic_SYS` field to the Oracle Fetch plugin to retrieve and display nationality information for persons in the DUNIA_PERSONAL_DETAILS table.

## Database Schema Changes

### Table: DUNIA_PERSONAL_DETAILS
Added new field:
- `nationality_arabic_SYS` VARCHAR2(100) -- Nationality in Arabic

## Files Modified

### 1. Database Schema Documentation
**File:** `database_dummy_data.md`
- Updated table schema to include `nationality_arabic_SYS` field
- Added sample nationality data (سعودي, كويتي, إماراتي, بحريني, قطري, عماني, أردني, لبناني, مصري)

### 2. Oracle Manager Class
**File:** `classes/oracle_manager.php`
- Updated `get_person_by_civil()` method to include nationality field
- Updated `get_all_persons()` method to retrieve and return nationality data
- Updated complex `get_all_employees_and_persons()` UNION query to include nationality
- Updated `get_person_data()` method (both employee and personal branches)

### 3. Helper Functions
**File:** `lib.php`
- Enhanced `oracle_get_person_name()` with optional nationality parameter
- Added new `oracle_get_person_nationality()` function
- Added fallback text in Arabic: 'غير محدد' (Not specified)

### 4. Demo and Examples
**File:** `fetchData.php`
- Updated person listing to display nationality information
- Added usage examples for new nationality functions

### 5. Health Diagnostics
**File:** `tools/health.php`
- Added `sample_persons_with_nationality` test section
- New diagnostic query for testing nationality field retrieval

### 6. Documentation Updates
**File:** `README.md`
- Added examples for nationality-related helper functions

**File:** `docs/api-reference.md`
- Updated table schema documentation

## New Functions Available

### 1. Enhanced Person Name Function
```php
// Get person name only
$person_name = oracle_get_person_name($civil_number);

// Get person name with nationality
$person_name_with_nationality = oracle_get_person_name($civil_number, true);
```

### 2. Nationality-Specific Function
```php
// Get person nationality only
$person_nationality = oracle_get_person_nationality($civil_number);
```

## Usage Examples

### Basic Usage
```php
require_once(__DIR__ . '/../oracleFetch/lib.php');

// Get nationality for a specific person
$nationality = oracle_get_person_nationality('12345678');
echo $nationality; // Output: سعودي

// Get full name with nationality
$full_name = oracle_get_person_name('12345678', true);
echo $full_name; // Output: أحمد محمد علي الهاشمي (سعودي)
```

### Using Oracle Manager Directly
```php
require_once(__DIR__ . '/../oracleFetch/classes/oracle_manager.php');

// Get all persons (now includes nationality)
$persons = oracle_manager::get_all_persons();
foreach ($persons as $person) {
    echo $person['fullname'] . ' - ' . $person['nationality'];
}

// Get specific person by civil number
$person = oracle_manager::get_person_by_civil('12345678');
if ($person) {
    echo 'Nationality: ' . $person['NATIONALITY_ARABIC_SYS'];
}
```

## Database Query Updates

All queries against `DUNIA_PERSONAL_DETAILS` now include the `nationality_arabic_SYS` field:

```sql
-- Example updated query
SELECT civil_number, passport_number, name_arabic_1, name_arabic_2, 
       name_arabic_3, name_arabic_6, nationality_arabic_SYS 
FROM DUNIA_PERSONAL_DETAILS 
WHERE civil_number = :civil_number;
```

## Backward Compatibility

✅ **Fully backward compatible** - all existing functions continue to work without modification.

The nationality parameter in `oracle_get_person_name()` is optional and defaults to `false`, maintaining existing behavior.

## Testing

Use the health diagnostics to verify nationality support:
```
/local/oracleFetch/tools/health.php?html=1
```

The health check now includes a `sample_persons_with_nationality` section that will show sample data with nationality fields.

## Implementation Notes

1. **Error Handling**: All functions gracefully handle missing nationality data
2. **Fallback Values**: Functions return 'غير محدد' (Not specified) when nationality is not available
3. **UTF-8 Support**: Maintains proper Arabic character encoding throughout
4. **NULL Safety**: All queries use `OCI_RETURN_NULLS` for proper NULL handling

## Migration for Existing Plugins

Existing plugins using Oracle Fetch will automatically benefit from nationality support without code changes. To utilize nationality features, update calls to helper functions:

**Before:**
```php
$person_name = oracle_get_person_name($civil_number);
```

**After (optional enhancement):**
```php
$person_name_with_nationality = oracle_get_person_name($civil_number, true);
$nationality_only = oracle_get_person_nationality($civil_number);
```
