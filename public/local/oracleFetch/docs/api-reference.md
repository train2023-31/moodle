# API Reference and Code Documentation

## Overview

This document provides technical documentation for the centralized Oracle manager and the example `fetchData.php`, covering configuration, connectivity, and examples.

## Core Class: classes/oracle_manager.php

### Configuration (precedence)
1. Moodle `config.php`: `$CFG->local_oraclefetch_dbuser`, `$CFG->local_oraclefetch_dbpass`, `$CFG->local_oraclefetch_dsn`
2. Environment variables: `ORACLE_DBUSER`, `ORACLE_DBPASS`, `ORACLE_DSN`
3. Hardcoded defaults in `oracle_manager.php`

Example `config.php`:
```php
$CFG->local_oraclefetch_dbuser = 'YOUR_USER';
$CFG->local_oraclefetch_dbpass = 'YOUR_PASS';
$CFG->local_oraclefetch_dsn    = '//DB_HOST:1521/SERVICE_NAME';
```

### Methods
- get_connection(): returns OCI connection (UTF-8 `AL32UTF8`, sets `NLS_LANG`) or false
- get_all_employees(): list of employees
- get_all_persons(): list of persons
- search_employees($term)
- get_employee_by_pf($pf)
- get_person_by_civil($civil)
- get_all_employees_and_persons(): map id => display string

## File: fetchData.php

### Purpose
Demonstrates Oracle database connectivity and data fetching patterns within a Moodle environment.

### Code Structure

#### 1. Initialization and Setup

```php
<?php
require_once(__DIR__ . '/../../config.php');
require_login();
echo $OUTPUT->header();
```

**Description**: 
- Loads Moodle configuration
- Enforces user authentication
- Outputs Moodle page header

#### 2. Local Library Loading (offline)

```php
$PAGE->requires->jquery();
$PAGE->requires->css(new moodle_url('/local/oracleFetch/lib/select2.min.css'));
$PAGE->requires->js(new moodle_url('/local/oracleFetch/lib/select2.full.min.js'));
```

Libraries:
- jQuery (Moodle built-in)
- Select2 from `/local/oracleFetch/lib/`

#### 3. Oracle Configuration

```php
// Fix Arabic character encoding
putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");

// Prefer centralized manager
require_once(__DIR__ . '/../classes/oracle_manager.php');
$conn = oracle_manager::get_connection();
// Or resolve raw params via config/env
global $CFG;
$dbuser = $CFG->local_oraclefetch_dbuser ?? getenv('ORACLE_DBUSER') ?? 'moodleuser';
$dbpass = $CFG->local_oraclefetch_dbpass ?? getenv('ORACLE_DBPASS') ?? 'moodle';
$dbname = $CFG->local_oraclefetch_dsn    ?? getenv('ORACLE_DSN')    ?? '//localhost:1521/XEPDB1';
```

**Configuration Details**:
- **Character Encoding**: `AL32UTF8` for proper Arabic character support
- **Database**: Oracle PDB (Pluggable Database) on localhost
- **Port**: 1521 (standard Oracle port)
- **Service**: ORCLPDB

#### 4. Database Connection

```php
$conn = oci_connect($dbuser, $dbpass, $dbname);
if (!$conn) {
    $e = oci_error();
    die("Could not connect to Oracle: " . htmlentities($e['message']));
}
```

**Error Handling**: Terminates execution with error message if connection fails

## Database Operations

### Example 1: Simple Employee Query

```sql
SELECT pf_number, first_name, last_name from employees
```

**Implementation**:
```php
$sql = "SELECT pf_number, first_name, last_name from employees";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo "<li>Employee PF_Number: " . htmlspecialchars($row['PF_NUMBER']) . 
    "  -- Name: " . htmlspecialchars($row['FIRST_NAME']) . " " . htmlspecialchars($row['LAST_NAME']) . "</li>";
}
```

**Features**:
- Uses `htmlspecialchars()` for XSS protection
- `OCI_ASSOC` returns associative array
- `OCI_RETURN_NULLS` includes NULL values

### Example 2: Multi-table Join Query

```sql
SELECT e.pf_number, p.civil_number, e.first_name, e.last_name 
FROM person_details p, employees e
WHERE e.civil_number = p.civil_number
```

**Purpose**: Demonstrates joining data from `person_details` and `employees` tables

**Key Fields**:
- `civil_number`: Common field used for joining
- `pf_number`: Employee Personal File number
- `first_name`, `last_name`: Person identification

### Example 3: Interactive Dropdown with Search

```sql
SELECT civil_Number, first_name, last_name FROM person_details
```

**Implementation Features**:
- Generates HTML `<select>` element
- Each option includes searchable text in `data-text` attribute
- Arabic placeholder text: "الرجاء اختيار شخص"
- RTL (Right-to-Left) direction support

## JavaScript Integration

### Select2 Configuration

```javascript
$('#person_select').select2({
    placeholder: "-- الرجاء اختيار شخص --",
    allowClear: true,
    matcher: function(params, data) {
        // Custom search logic
        if ($.trim(params.term) === '') {
            return data;
        }
        
        var searchableText = $(data.element).data('search') || data.text;
        if (searchableText.toString().toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            return data;
        }
        return null;
    }
});
```

**Features**:
- **Placeholder**: Arabic text for user guidance
- **allowClear**: Enables clearing selection
- **Custom Matcher**: Implements full-text search across option data

## Database Schema Requirements

### Table: employees
```sql
-- Required columns
pf_number     VARCHAR2/NUMBER  -- Employee Personal File Number
first_name    VARCHAR2         -- Employee First Name
last_name     VARCHAR2         -- Employee Last Name
civil_number  VARCHAR2/NUMBER  -- Civil ID Number (for joining)
```

### Table: person_details
```sql
-- Required columns
civil_number         VARCHAR2/NUMBER  -- Civil ID Number (primary/foreign key)
first_name           VARCHAR2         -- Person First Name
last_name            VARCHAR2         -- Person Last Name
nationality_arabic_SYS VARCHAR2       -- Person Nationality in Arabic
```

## Security Considerations

### 1. HTML Output Sanitization
- All database output is processed through `htmlspecialchars()`
- Prevents XSS (Cross-Site Scripting) attacks

### 2. Authentication
- `require_login()` ensures only authenticated Moodle users can access

### 3. Database Connection
- Uses OCI prepared statements
- Connection parameters should be externalized in production

## Error Handling

### Database Connection Errors
```php
if (!$conn) {
    $e = oci_error();
    die("Could not connect to Oracle: " . htmlentities($e['message']));
}
```

### Query Execution
- The current implementation doesn't include explicit query error handling
- Recommended: Add `oci_error($stid)` checks after `oci_execute()`

## Resource Cleanup

```php
oci_free_statement($stid);
oci_close($conn);
```

**Best Practice**: Always free Oracle resources to prevent memory leaks

## Performance Considerations

1. **Connection Management**: Single connection used for multiple queries
2. **Result Fetching**: Uses efficient `oci_fetch_array()` method
3. **Client-side Processing**: Select2 provides client-side search filtering
4. **CDN Resources**: External libraries loaded from CDN for faster delivery

## Internationalization

- **Arabic Support**: Proper UTF-8 encoding with `AL32UTF8`
- **RTL Direction**: Form elements support right-to-left text direction
- **Multilingual Text**: Placeholder text in Arabic

## AJAX Endpoints

All AJAX endpoints return JSON data and require proper Moodle authentication.

### get_employees.php
Returns all employees from DUNIA_EMPLOYEES table with personal details.

### get_persons.php
Returns all persons from DUNIA_PERSONAL_DETAILS table.

### get_employees_and_persons.php
Returns combined results from both employees and persons tables.

### search_employees.php
Searches for employees by name or PF number.

### search_by_civil.php
**NEW**: Searches for a person by civil number only.
- **Method**: POST
- **Parameter**: `civil_number` (required)
- **Returns**: 
  ```json
  {
    "success": true,
    "data": {
      "civil_number": "10001",
      "passport_number": "P-A001",
      "first_name": "أحمد",
      "middle_name": "محمد",
      "last_name": "علي",
      "tribe": "الهاشمي",
      "nationality": "سعودي",
      "fullname": "أحمد محمد علي",
      "display_text": "أحمد محمد علي الهاشمي (10001)"
    }
  }
  ```
- **Error Response**:
  ```json
  {
    "success": false,
    "message": "لا توجد بيانات للرقم المدني المحدد",
    "data": null
  }
  ```
- **Usage**: Primarily used by external lecturer plugin for resident lecturer form autocomplete
- **Integration**: Used in `local/externallecturer` plugin for civil number-based person lookup 