# File Structure Documentation

## Plugin Directory Structure

```
local/oracleFetch/
├── ajax/
│   ├── get_employees.php
│   ├── get_persons.php
│   ├── get_employees_and_persons.php
│   └── search_employees.php
├── classes/
│   └── oracle_manager.php
├── docs/
│   ├── README.md
│   ├── file-structure.md
│   ├── api-reference.md
│   ├── development.md
│   └── OFFLINE_OPTIMIZATION.md
├── lib/
│   ├── select2.min.css
│   ├── select2.full.min.js
│   ├── select2.min.js
│   └── select2.full.js
├── tools/
│   ├── health.php
│   └── ajax_health.php
├── fetchData.php
└── lib.php
```

## File Descriptions

### `/fetchData.php`
**Type**: Main application file  
**Size**: 4.0KB (120 lines)  
**Purpose**: Primary script that demonstrates Oracle database connectivity and data fetching

**Key Responsibilities**:
- Establishes Oracle database connection using OCI functions
- Executes multiple example queries to demonstrate different data fetching patterns
- Displays data in various formats (lists, dropdowns)
- Integrates with Moodle's authentication and theming system
- Provides interactive search functionality using Select2

**Dependencies**:
- Moodle core (`config.php`)
- jQuery (Moodle built-in)
- Select2 library (local files under `local/oracleFetch/lib/`)
- PHP OCI8 extension
- Oracle database connectivity

### `/docs/` Directory
**Type**: Documentation folder  
**Purpose**: Contains all plugin documentation files

**Contents**:
- **README.md**: Main plugin overview and quick start guide
- **file-structure.md**: This file - detailed explanation of all files and folders
- **api-reference.md**: Technical documentation of code functions and APIs
- **development.md**: Development guidelines, setup instructions, and best practices

## File Dependencies

### External Dependencies
- **jQuery 3.6.0**: Loaded from `https://code.jquery.com/jquery-3.6.0.min.js`
- **Select2 4.1.0-rc.0**: 
  - CSS: `https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css`
  - JS: `https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js`

### Moodle Core Dependencies
- `config.php`: Moodle configuration and initialization
- `$OUTPUT`: Moodle output rendering system
- `require_login()`: Moodle authentication system

### Database Dependencies
- Oracle Database with OCI connectivity
- Required database tables: `employees`, `person_details`

## Missing Files (Typical for Moodle Plugins)

The following files are commonly found in Moodle plugins but are not present in this plugin:

- `version.php`: Plugin version information and dependencies
- `index.php`: Default landing page
- `lang/en/local_oraclefetch.php`: Language strings
- `lib.php`: Plugin library functions
- `settings.php`: Admin settings page
- `access.php`: Capability definitions
- `install.xml`: Database installation schema