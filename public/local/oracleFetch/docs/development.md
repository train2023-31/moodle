# Development Guide

## Setup Instructions

### Prerequisites

1. **Moodle Environment**
   - Moodle LMS installation
   - Web server (Apache/Nginx)
   - PHP 7.4 or higher

2. **Oracle Database**
   - Oracle Database 12c or higher
   - Oracle Instant Client
   - PHP OCI8 extension enabled

3. **Network Configuration**
   - Network connectivity between Moodle server and Oracle database
   - Firewall rules allowing connections on Oracle port (default: 1521)

### Installation Steps

1. **Plugin Installation**
   ```bash
   # Navigate to Moodle local plugins directory
   cd /path/to/moodle/local/
   
   # Create plugin directory
   mkdir oracleFetch
   
   # Copy plugin files
   cp fetchData.php oracleFetch/
   ```

2. **PHP OCI8 Extension Setup**
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-oci8
   
   # CentOS/RHEL
   sudo yum install php-oci8
   
   # Restart web server
   sudo service apache2 restart
   ```

3. **Oracle Instant Client Installation**
   ```bash
   # Download Oracle Instant Client
   # Extract to /opt/oracle/instantclient
   
   # Set environment variables
   export ORACLE_HOME=/opt/oracle/instantclient
   export LD_LIBRARY_PATH=$ORACLE_HOME:$LD_LIBRARY_PATH
   export PATH=$ORACLE_HOME:$PATH
   ```

### Database Configuration

#### 1. Create Database Tables

```sql
-- Create employees table
CREATE TABLE employees (
    pf_number NUMBER PRIMARY KEY,
    civil_number VARCHAR2(20) NOT NULL,
    first_name VARCHAR2(100) NOT NULL,
    last_name VARCHAR2(100) NOT NULL,
    created_date DATE DEFAULT SYSDATE
);

-- Create person_details table
CREATE TABLE person_details (
    civil_number VARCHAR2(20) PRIMARY KEY,
    first_name VARCHAR2(100) NOT NULL,
    last_name VARCHAR2(100) NOT NULL,
    created_date DATE DEFAULT SYSDATE
);

-- Create foreign key relationship
ALTER TABLE employees 
ADD CONSTRAINT fk_employee_civil 
FOREIGN KEY (civil_number) REFERENCES person_details(civil_number);
```

#### 2. Insert Sample Data

```sql
-- Insert sample person details
INSERT INTO person_details (civil_number, first_name, last_name) 
VALUES ('123456789', 'أحمد', 'الخالدي');

INSERT INTO person_details (civil_number, first_name, last_name) 
VALUES ('987654321', 'فاطمة', 'المحمد');

-- Insert sample employees
INSERT INTO employees (pf_number, civil_number, first_name, last_name) 
VALUES (1001, '123456789', 'أحمد', 'الخالدي');

INSERT INTO employees (pf_number, civil_number, first_name, last_name) 
VALUES (1002, '987654321', 'فاطمة', 'المحمد');

COMMIT;
```

#### 3. Create Database User

```sql
-- Create dedicated user for Moodle
CREATE USER moodleuser IDENTIFIED BY moodle;

-- Grant necessary privileges
GRANT CONNECT, RESOURCE TO moodleuser;
GRANT SELECT, INSERT, UPDATE, DELETE ON employees TO moodleuser;
GRANT SELECT, INSERT, UPDATE, DELETE ON person_details TO moodleuser;
```

## Development Guidelines

### 1. Code Standards

#### PHP Coding Standards
- Follow Moodle coding guidelines
- Use proper indentation (4 spaces)
- Include proper PHPDoc comments
- Sanitize all user inputs and database outputs

```php
/**
 * Fetch employee data from Oracle database
 * 
 * @param resource $conn Oracle database connection
 * @return array Array of employee records
 */
function fetch_employees($conn) {
    $sql = "SELECT pf_number, first_name, last_name FROM employees";
    $stid = oci_parse($conn, $sql);
    
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception("Query failed: " . $e['message']);
    }
    
    $employees = [];
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $employees[] = [
            'pf_number' => htmlspecialchars($row['PF_NUMBER']),
            'first_name' => htmlspecialchars($row['FIRST_NAME']),
            'last_name' => htmlspecialchars($row['LAST_NAME'])
        ];
    }
    
    oci_free_statement($stid);
    return $employees;
}
```

#### Security Best Practices

1. **Input Validation**
   ```php
   // Validate and sanitize inputs
   $search_term = isset($_GET['search']) ? clean_param($_GET['search'], PARAM_TEXT) : '';
   ```

2. **SQL Injection Prevention**
   ```php
   // Use parameterized queries
   $sql = "SELECT * FROM employees WHERE pf_number = :pf_number";
   $stid = oci_parse($conn, $sql);
   oci_bind_by_name($stid, ":pf_number", $pf_number);
   oci_execute($stid);
   ```

3. **XSS Prevention**
   ```php
   // Always escape output
   echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
   ```

### 2. Error Handling

#### Database Error Handling
```php
function execute_oracle_query($conn, $sql, $params = []) {
    $stid = oci_parse($conn, $sql);
    
    if (!$stid) {
        $e = oci_error($conn);
        error_log("Oracle parse error: " . $e['message']);
        return false;
    }
    
    // Bind parameters if provided
    foreach ($params as $key => $value) {
        oci_bind_by_name($stid, $key, $value);
    }
    
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        error_log("Oracle execute error: " . $e['message']);
        oci_free_statement($stid);
        return false;
    }
    
    return $stid;
}
```

#### Connection Management
```php
class OracleConnection {
    private $conn;
    
    public function __construct($dbuser, $dbpass, $dbname) {
        $this->conn = oci_connect($dbuser, $dbpass, $dbname);
        if (!$this->conn) {
            $e = oci_error();
            throw new Exception("Oracle connection failed: " . $e['message']);
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            oci_close($this->conn);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
```

### 3. Configuration Management

#### Centralized Manager + config.php (recommended)
Do not hardcode credentials. Configure per server via Moodle `config.php`:

```php
$CFG->local_oraclefetch_dbuser = 'YOUR_USER';
$CFG->local_oraclefetch_dbpass = 'YOUR_PASS';
$CFG->local_oraclefetch_dsn    = '//DB_HOST:1521/SERVICE_NAME';
```

Alternatively, set environment variables: `ORACLE_DBUSER`, `ORACLE_DBPASS`, `ORACLE_DSN`.

`classes/oracle_manager.php` resolves values in this order: config.php > env > defaults.

## Testing Guidelines

### 1. Unit Testing

```php
// tests/oracle_test.php
<?php
class OracleConnectionTest extends PHPUnit\Framework\TestCase {
    
    public function test_connection_success() {
        $conn = new OracleConnection('testuser', 'testpass', 'testdb');
        $this->assertNotNull($conn->getConnection());
    }
    
    public function test_employee_fetch() {
        $conn = new OracleConnection('testuser', 'testpass', 'testdb');
        $employees = fetch_employees($conn->getConnection());
        $this->assertIsArray($employees);
    }
}
```

### 2. Integration Testing

1. **Database Connectivity Test**
   - Verify Oracle connection establishment
   - Test query execution
   - Validate data retrieval

2. **Moodle Integration Test**
   - Test authentication requirements
   - Verify theme integration
   - Check permission handling

### 3. User Interface Testing

1. **Dropdown Functionality**
   - Test Select2 initialization
   - Verify search functionality
   - Check Arabic text display

2. **Cross-browser Testing**
   - Test on Chrome, Firefox, Safari
   - Verify RTL text direction
   - Check responsive design

## Deployment Guidelines

### 1. Production Deployment

1. **Environment Configuration**
   ```php
   // Use environment variables for sensitive data
   $dbuser = getenv('ORACLE_USER') ?: 'moodleuser';
   $dbpass = getenv('ORACLE_PASS') ?: 'default_password';
   $dbname = getenv('ORACLE_DSN') ?: '//localhost:1521/XEPDB1';
   ```

2. **Performance Optimization**
   - Implement connection pooling
   - Add query result caching
   - Optimize database queries

3. **Monitoring and Logging**
   ```php
   // Add logging for production monitoring
   error_log("Oracle query executed: " . $sql);
   error_log("Query execution time: " . (microtime(true) - $start_time) . "s");
   ```

### 2. Version Control

1. **Git Workflow**
   ```bash
   # Create feature branch
   git checkout -b feature/new-functionality
   
   # Make changes and commit
   git add .
   git commit -m "Add new Oracle query functionality"
   
   # Push and create pull request
   git push origin feature/new-functionality
   ```

2. **Version Tagging**
   ```bash
   # Tag stable releases
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```

## Extension Opportunities

### 1. Additional Features

1. **Admin Settings Page**
   - Create `settings.php` for database configuration
   - Add capability definitions in `access.php`

2. **Language Support**
   - Create language files in `lang/en/local_oraclefetch.php`
   - Add support for multiple languages

3. **API Endpoints**
   - Create REST API for external access
   - Add JSON response formatting

### 2. Advanced Functionality

1. **Data Export**
   - Add CSV/Excel export functionality
   - Implement data filtering options

2. **Real-time Updates**
   - Add AJAX-based data refresh
   - Implement WebSocket connections

3. **Caching Layer**
   - Add Redis/Memcached integration
   - Implement query result caching

## Troubleshooting

### Common Issues

1. **OCI8 Extension Not Found**
   ```bash
   # Check if extension is loaded
   php -m | grep oci8
   
   # If not found, install and configure
   sudo apt-get install php-oci8
   ```

2. **Oracle Connection Timeout**
   ```sql
   -- Check Oracle listener status
   lsnrctl status
   
   -- Verify network connectivity
   tnsping ORCLPDB
   ```

3. **Character Encoding Issues**
   ```php
   // Ensure proper NLS_LANG setting
   putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");
   ```

### Debug Mode

```php
// Enable debug mode for development
if (debugging()) {
    echo "Debug: Oracle connection parameters: " . print_r($oracle_config, true);
    echo "Debug: Query execution time: " . (microtime(true) - $start_time) . "s";
}
``` 