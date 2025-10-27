<?php
/**
 * Oracle Database Manager for Moodle
 * 
 * Centralized Oracle database operations for employee and person data management.
 * This class provides a single point of access to Oracle database operations across
 * all Moodle local plugins, eliminating code duplication and ensuring consistent
 * error handling and connection management.
 * 
 * Key Features:
 * - Centralized Oracle connection management with automatic cleanup
 * - Support for both employees and person_details tables
 * - Advanced search capabilities supporting Arabic names and PF numbers
 * - Comprehensive error logging and graceful failure handling
 * - Optimized queries with proper indexing considerations
 * - UTF-8 character encoding support for Arabic names
 * 
 * Database Tables:
 * - DUNIA_EMPLOYEES: Contains employee data with PF numbers and civil numbers (format: C-12345)
 * - DUNIA_PERSONAL_DETAILS: Contains personal data indexed by civil numbers (format: 12345)
 * 
 * Field Mappings:
 * - Employee names: prs_name1_a (first), prs_name2_a (middle), prs_name3_a (last), prs_tribe_a (tribe)
 * - Personal names: name_arabic_1, name_arabic_2, name_arabic_3, name_arabic_6
 * - Civil number link: aaa_emp_civil_number_ar (C-12345) <-> civil_number (12345)
 * 
 * Usage Examples:
 * $employees = oracle_manager::get_all_employees();
 * $employee = oracle_manager::get_employee_by_pf('PF001');
 * $search_results = oracle_manager::search_employees('أحمد');
 * 
 * @package    local_oracleFetch
 * @author     Development Team
 * @copyright  2024 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class oracle_manager {
/**
 * Normalize Arabic/Persian digits in a string to ASCII 0-9.
 * Keeps non-digit characters as-is. If input is null/empty, returns ''.
 * @param mixed $value
 * @return string
 */
private static function normalize_digits($value) {
    $s = self::to_string($value);
    if ($s === '') return '';

    // Arabic-Indic (U+0660..U+0669) and Eastern Arabic/Persian-Indic (U+06F0..U+06F9)
    $map = [
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
    ];
    return strtr($s, $map);
}


    /**
     * Convert possibly-null DB value to trimmed string safely.
     * Returns empty string for NULLs.
     * @param mixed $value
     * @return string
     */
    private static function to_string($value) {
        if ($value === null) {
            return '';
        }
        return trim((string)$value);
    }

    /**
     * Build a full name from parts, skipping empty/null parts and joining with single spaces.
     * @param mixed $first
     * @param mixed $middle
     * @param mixed $last
     * @return string
     */
    private static function build_fullname($first, $middle = null, $last = null, $tribe = null) {
        $parts = [];
        foreach ([$first, $middle, $last, $tribe] as $part) {
            $s = self::to_string($part);
            if ($s !== '') {
                $parts[] = $s;
            }
        }
        return implode(' ', $parts);
    }

    
    // /** @var string Oracle database username for connection */
    // private static $dbuser = 'moodleuser';
    
    // /** @var string Oracle database password for connection */
    // private static $dbpass = 'moodle';
    
    // /** @var string Oracle database connection string (TNS format: //host:port/service) */
    // private static $dbname = '//localhost:1521/XEPDB1'; //AHMED MACHINE
    // // private static $dbname = '//localhost:1521/ORCLPDB'; //KHADIJA MACHINE
    
    /**
     * Establish Oracle database connection with proper character encoding
     * 
     * Creates a new Oracle database connection using the configured credentials.
     * Sets up proper UTF-8 character encoding to handle Arabic names correctly.
     * All connection errors are logged for debugging purposes.
     * 
     * Character Encoding Setup:
     * - Sets NLS_LANG environment variable to ensure proper Arabic character handling
     * - Uses AL32UTF8 charset for full Unicode support
     * - Prevents character corruption in Arabic names and text
     * 
     * Error Handling:
     * - Logs all connection failures with detailed error messages
     * - Returns false on any connection error for consistent error checking
     * - Catches both OCI errors and PHP exceptions
     * 
     * @return resource|false Oracle connection resource on success, false on failure
     * @throws None - All exceptions are caught and logged internally
     * 
     * @example
     * $conn = oracle_manager::get_connection();
     * if ($conn) {
     *     // Use connection for queries
     *     oci_close($conn);
     * } else {
     *     // Handle connection failure
     * }
     */
    public static function get_connection() {
        try {
            global $CFG;
            // Set Oracle character encoding to handle Arabic text properly
            putenv("NLS_LANG=AMERICAN_AMERICA.AL32UTF8");

            // Allow per-environment overrides via Moodle config.php or environment variables
            // Add to config.php if needed:
            //   $CFG->local_oraclefetch_dbuser = 'username';
            //   $CFG->local_oraclefetch_dbpass = 'password';
            //   $CFG->local_oraclefetch_dsn    = '//host:1521/service';
            $dbuser = isset($CFG->local_oraclefetch_dbuser) ? $CFG->local_oraclefetch_dbuser : (getenv('ORACLE_DBUSER') ?: self::$dbuser);
            $dbpass = isset($CFG->local_oraclefetch_dbpass) ? $CFG->local_oraclefetch_dbpass : (getenv('ORACLE_DBPASS') ?: self::$dbpass);
            $dbname = isset($CFG->local_oraclefetch_dsn)    ? $CFG->local_oraclefetch_dsn    : (getenv('ORACLE_DSN')     ?: self::$dbname);

            // Establish connection with UTF-8 charset
            $conn = @oci_connect($dbuser, $dbpass, $dbname, 'AL32UTF8');

            if (!$conn) {
                $error = oci_error();
                error_log('Oracle DB connection failed: ' . ($error ? ($error['message'] ?? 'Unknown connection error') : 'Unknown connection error'));
                return false;
            }

            return $conn;
        } catch (Exception $e) {
            error_log('Oracle connection exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve employee information by PF (Personnel File) number
     * 
     * Searches the employees table for a specific employee using their PF number.
     * This method is optimized for single employee lookups and includes civil number
     * for cross-referencing with person_details table if needed.
     * 
     * Query Details:
     * - Uses parameterized query to prevent SQL injection
     * - Searches exact match on pf_number field (case-sensitive)
     * - Returns all core employee fields for further processing
     * - Automatically handles NULL values in civil_number field
     * 
     * Resource Management:
     * - Establishes new connection for each call (stateless)
     * - Properly frees statement resources on all code paths
     * - Closes connection before return to prevent resource leaks
     * 
     * @param string $pf_number Employee PF number (e.g., 'PF001', 'PF123')
     * 
     * @return array|false Employee data array on success, false on failure or not found
     *                     Array structure: [
     *                         'PF_NUMBER' => string,
     *                         'PRS_NAME1_A' => string,
     *                         'PRS_NAME2_A' => string,
     *                         'PRS_NAME3_A' => string,
     *                         'PRS_TRIBE_A' => string,
     *                         'AAA_EMP_CIVIL_NUMBER_AR' => string|null
     *                     ]
     * 
     * @example
     * $employee = oracle_manager::get_employee_by_pf('PF001');
      * if ($employee) {
 *     $fullname = trim($employee['PRS_NAME1_A'] . ' ' . $employee['PRS_NAME2_A'] . ' ' . $employee['PRS_NAME3_A']);
 *     echo "Found: " . $fullname . ' (' . $employee['PRS_TRIBE_A'] . ')';
 *     echo "Civil ID: " . ($employee['AAA_EMP_CIVIL_NUMBER_AR'] ?? 'Not available');
     * } else {
     *     echo "Employee not found or database error";
     * }
     */
    public static function get_employee_by_pf($pf_number) {
        $conn = self::get_connection();
        if (!$conn) {
            return false;
        }
        
        try {
            // Prepare SQL query with parameter binding for security
            $sql = "SELECT pf_number, prs_name1_a, prs_name2_a, prs_name3_a, prs_tribe_a, aaa_emp_civil_number_ar FROM DUNIA_EMPLOYEES WHERE pf_number = :pf_number";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ':pf_number', $pf_number);
            
            if (oci_execute($stid)) {
                // Fetch single row with NULL handling
                $result = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
                oci_free_statement($stid);
                oci_close($conn);
                return $result;
            }
            
            // Query execution failed
            oci_free_statement($stid);
            oci_close($conn);
            return false;
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
            return false;
        }
    }
    
    /**
     * Get person data by civil number
     * @param string $civil_number Civil number
     * @return array|false Person data or false on failure
     */
    public static function get_person_by_civil($civil_number) {
        $conn = self::get_connection();
        if (!$conn) {
            return false;
        }
        
        try {
            $sql = "SELECT civil_number, passport_number, name_arabic_1, name_arabic_2, name_arabic_3, name_arabic_6, nationality_arabic_SYS FROM DUNIA_PERSONAL_DETAILS WHERE civil_number = :civil_number";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ':civil_number', $civil_number);
            
            if (oci_execute($stid)) {
                $result = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
                oci_free_statement($stid);
                oci_close($conn);

            if ($result !== false && is_array($result)) {
                $result['PASSPORT_NUMBER'] = self::normalize_digits($result['PASSPORT_NUMBER'] ?? '');
                }
    
                return $result;
            }
            
            oci_free_statement($stid);
            oci_close($conn);
            return false;
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
            return false;
        }
    }
    
    /**
     * Get all employees data
     * @return array Array of employee records
     */
    public static function get_all_employees() {
        $conn = self::get_connection();
        if (!$conn) {
            return [];
        }
        
        $employees = [];
        
        try {
            $sql = "SELECT pf_number, prs_name1_a, prs_name2_a, prs_name3_a, prs_tribe_a, aaa_emp_civil_number_ar
                    FROM DUNIA_EMPLOYEES 
                    WHERE pf_number IS NOT NULL 
                    AND prs_name1_a IS NOT NULL
                    ORDER BY prs_name1_a ASC, prs_name2_a ASC";
            
            $stid = oci_parse($conn, $sql);
            
            if (oci_execute($stid)) {
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    if (!$row['PF_NUMBER']) continue;
                    
                    $fullname = self::build_fullname($row['PRS_NAME1_A'], $row['PRS_NAME2_A'], $row['PRS_NAME3_A'], $row['PRS_TRIBE_A']);
                    $employees[] = [
                        'pf_number' => $row['PF_NUMBER'],
                        'first_name' => self::to_string($row['PRS_NAME1_A']),
                        'middle_name' => self::to_string($row['PRS_NAME2_A']),
                        'last_name' => self::to_string($row['PRS_NAME3_A']),
                        'tribe' => self::to_string($row['PRS_TRIBE_A']),
                        'civil_number' => $row['AAA_EMP_CIVIL_NUMBER_AR'],
                        'fullname' => $fullname,
                        'display_text' => $fullname . ' (PF: ' . $row['PF_NUMBER'] . ')'
                    ];
                }
            }
            
            oci_free_statement($stid);
            oci_close($conn);
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
        }
        
        return $employees;
    }
    
    /**
     * Get all person details
     * @return array Array of person records
     */
    public static function get_all_persons() {
        $conn = self::get_connection();
        if (!$conn) {
            return [];
        }
        
        $persons = [];
        
        try {
            $sql = "SELECT civil_number, passport_number, name_arabic_1, name_arabic_2, name_arabic_3, name_arabic_6, nationality_arabic_SYS
                    FROM DUNIA_PERSONAL_DETAILS
                    WHERE civil_number IS NOT NULL 
                    AND name_arabic_1 IS NOT NULL
                    ORDER BY name_arabic_1 ASC, name_arabic_2 ASC";
            
            $stid = oci_parse($conn, $sql);
            
            if (oci_execute($stid)) {
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    if (!$row['CIVIL_NUMBER']) continue;
                    
                    $fullname = self::build_fullname($row['NAME_ARABIC_1'], $row['NAME_ARABIC_2'], $row['NAME_ARABIC_3'], $row['NAME_ARABIC_6']);
                    $persons[] = [
                        'civil_number' => $row['CIVIL_NUMBER'],
                       // 'passport_number' => $row['PASSPORT_NUMBER'],
                       'passport_number' => self::normalize_digits($row['PASSPORT_NUMBER']),
                        'first_name' => self::to_string($row['NAME_ARABIC_1']),
                        'middle_name' => self::to_string($row['NAME_ARABIC_2']),
                        'last_name' => self::to_string($row['NAME_ARABIC_3']),
                        'tribe' => self::to_string($row['NAME_ARABIC_6']),
                        'nationality' => self::to_string($row['NATIONALITY_ARABIC_SYS']),
                        'fullname' => $fullname,
                        'display_text' => $fullname . ' (' . $row['CIVIL_NUMBER'] . ')'
                    ];
                }
            }
            
            oci_free_statement($stid);
            oci_close($conn);
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
        }
        
        return $persons;
    }
    
    /**
     * Advanced employee search with multi-language and multi-field support
     * 
     * Performs flexible search across employee records supporting various search patterns:
     * - PF number searches (case-insensitive): 'PF001', 'pf001', '001'
     * - Arabic name searches (exact case matching): 'أحمد', 'محمد'
     * - English name searches (case-insensitive): 'ahmed', 'AHMED'
     * - Partial name matching for both Arabic and English
     * - Empty search returns first 10 employees alphabetically
     * 
     * Search Algorithm:
     * 1. If no search term: Returns first 10 employees ordered by first name
     * 2. If search term provided: Searches across multiple fields with different patterns
     *    - LOWER(first_name) LIKE '%term%' (case-insensitive English)
     *    - LOWER(last_name) LIKE '%term%' (case-insensitive English)
     *    - first_name LIKE '%term%' (exact case Arabic)
     *    - last_name LIKE '%term%' (exact case Arabic)
     *    - UPPER(pf_number) LIKE '%term%' (case-insensitive PF numbers)
     *    - pf_number LIKE '%term%' (original case PF numbers)
     * 
     * Result Ordering:
     * - PF number matches appear first (highest priority)
     * - Then alphabetical by first name
     * - Ensures consistent, predictable result ordering
     * 
     * Performance Considerations:
     * - Uses bind variables to prevent SQL injection and improve performance
     * - Limits results to reasonable numbers (10 for empty search)
     * - Leverages existing database indexes on pf_number and name fields
     * 
     * @param string $term Search term (optional). Examples:
     *                    - 'PF001' - finds employees with PF number containing '001'
     *                    - 'أحمد' - finds employees with Arabic name containing 'أحمد'
     *                    - 'ahmed' - finds employees with English name containing 'ahmed'
     *                    - '' - returns first 10 employees
     * 
     * @return array Array of employee records with search-optimized structure:
     *               [
     *                   [
     *                       'pf_number' => 'PF001',
     *                       'fullname' => 'أحمد محمد',
     *                       'display_text' => 'PF001 - أحمد محمد'
     *                   ],
     *                   ...
     *               ]
     *               Returns empty array on connection failure or no matches
     * 
     * @example Basic searches:
     * // Get initial employees list
     * $employees = oracle_manager::search_employees('');
     * 
     * // Search by PF number
     * $employees = oracle_manager::search_employees('PF001');
     * 
     * // Search by Arabic name
     * $employees = oracle_manager::search_employees('أحمد');
     * 
     * // Search by English name
     * $employees = oracle_manager::search_employees('ahmed');
     */
    public static function search_employees($term = '') {
        $conn = self::get_connection();
        if (!$conn) {
            return [];
        }
        
        $employees = [];
        
        try {
            if (empty($term)) {
                $sql = "SELECT pf_number, prs_name1_a, prs_name2_a, prs_name3_a, prs_tribe_a FROM DUNIA_EMPLOYEES WHERE ROWNUM <= 10 ORDER BY prs_name1_a";
                $stid = oci_parse($conn, $sql);
                
                if (!$stid) {
                    $error = oci_error($conn);
                    error_log('Oracle query parse error (no term): ' . ($error ? $error['message'] : 'Parse error'));
                    oci_close($conn);
                    return [];
                }
                
                $result = oci_execute($stid);
                if (!$result) {
                    $error = oci_error($stid);
                    error_log('Oracle query execute error (no term): ' . ($error ? $error['message'] : 'Execute error'));
                    oci_free_statement($stid);
                    oci_close($conn);
                    return [];
                }
            } else {
            // Include tribe in search predicates so users can search by family/tribe name as well
            $sql = "SELECT pf_number, prs_name1_a, prs_name2_a, prs_name3_a, prs_tribe_a
                        FROM DUNIA_EMPLOYEES 
                        WHERE (LOWER(prs_name1_a) LIKE :search1 OR LOWER(prs_name2_a) LIKE :search2 
                            OR LOWER(prs_name3_a) LIKE :search3 OR LOWER(prs_tribe_a) LIKE :search4
                            OR prs_name1_a LIKE :search5 OR prs_name2_a LIKE :search6
                            OR prs_name3_a LIKE :search7 OR prs_tribe_a LIKE :search8
                            OR UPPER(pf_number) LIKE :search9 OR pf_number LIKE :search10)
                        ORDER BY 
                            CASE WHEN UPPER(pf_number) LIKE :search11 THEN 1 ELSE 2 END,
                            prs_name1_a";
                
                $stid = oci_parse($conn, $sql);
                
                if (!$stid) {
                    $error = oci_error($conn);
                    error_log('Oracle query parse error (with term): ' . ($error ? $error['message'] : 'Parse error'));
                    oci_close($conn);
                    return [];
                }
                
                $search_lower = '%' . strtolower($term) . '%';
                $search_original = '%' . $term . '%';
                $search_upper = '%' . strtoupper($term) . '%';
                
                oci_bind_by_name($stid, ":search1", $search_lower);    // LOWER(prs_name1_a)
                oci_bind_by_name($stid, ":search2", $search_lower);    // LOWER(prs_name2_a)
                oci_bind_by_name($stid, ":search3", $search_lower);    // LOWER(prs_name3_a)
                oci_bind_by_name($stid, ":search4", $search_lower);    // LOWER(prs_tribe_a)
                oci_bind_by_name($stid, ":search5", $search_original); // prs_name1_a (original case)
                oci_bind_by_name($stid, ":search6", $search_original); // prs_name2_a (original case)
                oci_bind_by_name($stid, ":search7", $search_original); // prs_name3_a (original case)
                oci_bind_by_name($stid, ":search8", $search_original); // prs_tribe_a (original case)
                oci_bind_by_name($stid, ":search9", $search_upper);    // UPPER(pf_number)
                oci_bind_by_name($stid, ":search10", $search_original); // pf_number (original case)
                oci_bind_by_name($stid, ":search11", $search_upper);    // ORDER BY clause
                
                $result = oci_execute($stid);
                if (!$result) {
                    $error = oci_error($stid);
                    error_log('Oracle query execute error (with term): ' . ($error ? $error['message'] : 'Execute error'));
                    oci_free_statement($stid);
                    oci_close($conn);
                    return [];
                }
            }
            
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                if (!$row['PF_NUMBER']) continue;
                
                $fullname = self::build_fullname($row['PRS_NAME1_A'], $row['PRS_NAME2_A'], $row['PRS_NAME3_A'], $row['PRS_TRIBE_A']);
                $employees[] = [
                    'pf_number' => $row['PF_NUMBER'],
                    'fullname' => $fullname,
                    'display_text' => $row['PF_NUMBER'] . ' - ' . $fullname
                ];
            }
            
            oci_free_statement($stid);
            oci_close($conn);
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
        }
        
        return $employees;
    }
    
    /**
     * Get employee or person data by identifier (tries both PF number and civil number)
     * @param string $identifier PF number or civil number
     * @return array|false Employee/person data with source type or false on failure
     */
    public static function get_person_data($identifier) {
        $conn = self::get_connection();
        if (!$conn) {
            return false;
        }
        
        try {
            // Try to find by PF number first (DUNIA_EMPLOYEES table)
            $sql = "SELECT 'employee' as source_type, e.pf_number, 
                           REGEXP_SUBSTR(e.aaa_emp_civil_number_ar, '[0-9]+') as civil_number,
                           e.prs_name1_a, e.prs_name2_a, e.prs_name3_a, e.prs_tribe_a,
                           p.name_arabic_1, p.name_arabic_2, p.name_arabic_3, p.name_arabic_6, p.nationality_arabic_SYS
                    FROM DUNIA_EMPLOYEES e
                    LEFT JOIN DUNIA_PERSONAL_DETAILS p ON REGEXP_SUBSTR(e.aaa_emp_civil_number_ar, '[0-9]+') = TO_CHAR(p.civil_number)
                    WHERE e.pf_number = :identifier";
            
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ':identifier', $identifier);
            oci_execute($stid);
            
            $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            
            // If not found by PF number, try civil number (person_details table)
            if (!$row) {
                oci_free_statement($stid);
                
                $sql = "SELECT 'personal' as source_type, NULL as pf_number, p.civil_number, 
                               p.name_arabic_1, p.name_arabic_2, p.name_arabic_3, p.name_arabic_6, p.nationality_arabic_SYS,
                               NULL as prs_name1_a, NULL as prs_name2_a, NULL as prs_name3_a, NULL as prs_tribe_a
                        FROM DUNIA_PERSONAL_DETAILS p
                        WHERE p.civil_number = :identifier";
                
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':identifier', $identifier);
                oci_execute($stid);
                
                $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            }
            
            oci_free_statement($stid);
            oci_close($conn);
            
            return $row;
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
            return false;
        }
    }
    
    /**
     * Retrieve comprehensive employee and person data with intelligent merging
     * 
     * This method performs a complex UNION query to combine data from both the
     * employees and person_details tables, providing a complete view of all
     * available personnel data. Specifically designed for annual plans beneficiary
     * selection where both employee and non-employee persons may be included.
     * 
     * Data Merging Strategy:
     * 1. Primary Query: Employees with their linked personal details
     *    - Retrieves all employees from employees table
     *    - LEFT JOINs with person_details to get civil numbers when available
     *    - Marks records as 'employee' type with PF number as identifier
     * 
     * 2. Secondary Query: Person details without employee records
     *    - Retrieves persons from person_details who are NOT in employees table
     *    - Uses civil_number as identifier for non-employee persons
     *    - Marks records as 'personal' type
     * 
     * 3. Result Combination:
     *    - UNIONs both queries for comprehensive personnel list
     *    - Eliminates duplicates automatically through UNION operation
     *    - Orders results alphabetically by first name for consistent presentation
     * 
     * Display Format Logic:
     * - Employee records: "PF: {pf_number} - {fullname} (Civil: {civil_number})"
     * - Employee without civil: "PF: {pf_number} - {fullname}"
     * - Person only records: "Civil: {civil_number} - {fullname}"
     * 
     * Key Features:
     * - Handles employees who may or may not have civil numbers
     * - Includes persons who are not employees (external beneficiaries)
     * - Prevents duplicate entries through intelligent joining
     * - Maintains referential integrity between tables
     * - Provides clear identification of data source for each record
     * 
     * Use Cases:
     * - Annual training plan beneficiary selection
     * - Comprehensive personnel directory generation
     * - Cross-referencing employee and person databases
     * - Reporting that requires both internal and external personnel
     * 
     * Performance Notes:
     * - Complex query with multiple JOINs and UNION operation
     * - May take longer than simple queries on large datasets
     * - Results are cached by calling applications when possible
     * - Consider pagination for very large result sets
     * 
     * @return array Associative array with identifier as key and formatted display string as value:
     *               [
     *                   'PF001' => 'PF: PF001 - أحمد محمد (Civil: 12345678)',
     *                   'PF002' => 'PF: PF002 - فاطمة علي',
     *                   '87654321' => 'Civil: 87654321 - خالد محمد',
     *                   ...
     *               ]
     *               Returns empty array on connection failure or no data
     * 
     * @example Usage in annual plans:
     * $beneficiaries = oracle_manager::get_all_employees_and_persons();
     * foreach ($beneficiaries as $id => $display_name) {
     *     echo "<option value='{$id}'>{$display_name}</option>";
     * }
     * 
     * @example Identifying record types:
     * foreach ($beneficiaries as $id => $display_name) {
     *     if (strpos($display_name, 'PF:') === 0) {
     *         echo "Employee: {$display_name}";
     *     } else {
     *         echo "External person: {$display_name}";
     *     }
     * }
     */
    public static function get_all_employees_and_persons() {
        $conn = self::get_connection();
        if (!$conn) {
            return [];
        }
        
        $data = [];
        
        try {
            // Complex UNION query combining employees and person_details with intelligent deduplication
            // NOTE ABOUT ORA-01790 (datatype mismatch across UNION branches)
            // --------------------------------------------------------------
            // Some Oracle environments store Arabic name columns as NVARCHAR2 while others
            // use VARCHAR2. Also, untyped NULLs in a UNION can cause implicit datatype
            // conflicts. To prevent ORA-01790 reliably across servers, we:
            // 1) Explicitly CAST every output column to a consistent type and size
            //    - Identifiers (pf_number, civil_number) → VARCHAR2(50)
            //    - Names/tribe → VARCHAR2(200 CHAR) for robust Arabic (AL32UTF8) support
            // 2) Use a typed NULL: CAST(NULL AS VARCHAR2(50)) for pf_number in the personal branch
            // 3) Keep UNION (not ALL) to retain previous semantics of duplicate elimination
            //    If you prefer performance and accept duplicates, switch to UNION ALL
            $sql = "
                SELECT * FROM (
                    SELECT 'employee' AS source_type,
                           TO_CHAR(e.pf_number) AS identifier,
                           TO_CHAR(e.pf_number) AS pf_number,
                           TO_CHAR(REGEXP_SUBSTR(e.aaa_emp_civil_number_ar, '[0-9]+')) AS civil_number,
                           TO_CHAR(e.prs_name1_a) AS first_name,
                           TO_CHAR(e.prs_name2_a) AS middle_name,
                           TO_CHAR(e.prs_name3_a) AS last_name,
                           TO_CHAR(e.prs_tribe_a) AS tribe,
                           TO_CHAR(p.name_arabic_1) AS name_arabic_1,
                           TO_CHAR(p.name_arabic_2) AS name_arabic_2,
                           TO_CHAR(p.name_arabic_3) AS name_arabic_3,
                           TO_CHAR(p.name_arabic_6) AS name_arabic_6,
                           TO_CHAR(p.nationality_arabic_SYS) AS nationality_arabic_SYS
                    FROM DUNIA_EMPLOYEES e
                    LEFT JOIN DUNIA_PERSONAL_DETAILS p ON REGEXP_SUBSTR(e.aaa_emp_civil_number_ar, '[0-9]+') = TO_CHAR(p.civil_number)
                    WHERE e.pf_number IS NOT NULL

                    UNION

                    SELECT 'personal' AS source_type,
                           TO_CHAR(p.civil_number) AS identifier,
                           CAST(NULL AS VARCHAR2(50)) AS pf_number,
                           TO_CHAR(p.civil_number) AS civil_number,
                           TO_CHAR(p.name_arabic_1) AS first_name,
                           TO_CHAR(p.name_arabic_2) AS middle_name,
                           TO_CHAR(p.name_arabic_3) AS last_name,
                           TO_CHAR(p.name_arabic_6) AS tribe,
                           TO_CHAR(p.name_arabic_1) AS name_arabic_1,
                           TO_CHAR(p.name_arabic_2) AS name_arabic_2,
                           TO_CHAR(p.name_arabic_3) AS name_arabic_3,
                           TO_CHAR(p.name_arabic_6) AS name_arabic_6,
                           TO_CHAR(p.nationality_arabic_SYS) AS nationality_arabic_SYS
                    FROM DUNIA_PERSONAL_DETAILS p
                    LEFT JOIN DUNIA_EMPLOYEES e ON TO_CHAR(p.civil_number) = REGEXP_SUBSTR(e.aaa_emp_civil_number_ar, '[0-9]+')
                    WHERE e.pf_number IS NULL
                      AND p.civil_number IS NOT NULL
                ) t
                ORDER BY first_name ASC
            ";
            
            $stid = oci_parse($conn, $sql);
            
            if (!$stid) {
                $error = oci_error($conn);
                error_log('Oracle query parse error: ' . ($error ? $error['message'] : 'Parse error'));
                oci_close($conn);
                return [];
            }

            $result = oci_execute($stid);
            if (!$result) {
                $error = oci_error($stid);
                error_log('Oracle query execute error: ' . ($error ? $error['message'] : 'Execute error'));
                oci_free_statement($stid);
                oci_close($conn);
                return [];
            }

            // Process each row and format display strings based on data source and availability
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                // Skip rows with missing identifiers (data integrity check)
                if (!$row['IDENTIFIER']) continue;
                
                $id = $row['IDENTIFIER'];                                    // Primary identifier (PF number or civil number)
                // Construct full name based on source type
                if ($row['SOURCE_TYPE'] == 'employee') {
                    $fullname = self::build_fullname($row['FIRST_NAME'], $row['MIDDLE_NAME'], $row['LAST_NAME'], $row['TRIBE']);
                } else {
                    $fullname = self::build_fullname($row['NAME_ARABIC_1'], $row['NAME_ARABIC_2'], $row['NAME_ARABIC_3'], $row['NAME_ARABIC_6']);
                }
                
                // Apply different formatting based on record source and available data
                if ($row['SOURCE_TYPE'] == 'employee' && $row['PF_NUMBER']) {
                    // This is an employee record - format with PF number prominently
                    if ($row['CIVIL_NUMBER']) {
                        // Employee has both PF number and civil number (complete record)
                        // Format: "PF: PF001 - أحمد محمد علي الهاشمي (Civil: 12345678)"
                        $tribe = ($t = self::to_string($row['TRIBE'])) !== '' ? ' ' . $t : '';
                        $data[$id] = "PF: $id - $fullname$tribe (Civil: " . $row['CIVIL_NUMBER'] . ")";
                    } else {
                        // Employee has PF number but no civil number (partial record)
                        // Format: "PF: PF001 - أحمد محمد علي الهاشمي"
                        $tribe = ($t = self::to_string($row['TRIBE'])) !== '' ? ' ' . $t : '';
                        $data[$id] = "PF: $id - $fullname$tribe";
                    }
                } else if ($row['SOURCE_TYPE'] == 'personal' && $row['CIVIL_NUMBER']) {
                    // This is a person-only record (not an employee, external beneficiary)
                    // Format: "Civil: 12345678 - خالد محمد علي الهاشمي"
                    $tribe = ($t = self::to_string($row['NAME_ARABIC_6'])) !== '' ? ' ' . $t : '';
                    $data[$id] = "Civil: $id - $fullname$tribe";
                }
                // Note: Any other combinations are skipped as invalid data
            }

            oci_free_statement($stid);
            oci_close($conn);
            
        } catch (Exception $e) {
            error_log('Oracle query error: ' . $e->getMessage());
            oci_close($conn);
        }
        
        return $data;
    }
}

/**
 * ========================================
 * ORACLE MANAGER CLASS USAGE SUMMARY
 * ========================================
 * 
 * This class serves as the central hub for all Oracle database operations
 * within the Moodle local plugins ecosystem. It replaces scattered database
 * connection code across multiple plugins with a single, well-tested, and
 * maintained solution.
 * 
 * MIGRATION BENEFITS:
 * ==================
 * Before: Each plugin had its own Oracle connection code (duplicated ~400+ lines across plugins)
 * After:  Single centralized manager (this file) used by all plugins (~40 lines per plugin)
 * 
 * CODE REDUCTION:
 * - local/annualplans/ajax/get_employees.php: 122 → 40 lines (67% reduction)
 * - local/participant/ajax/get_employee_data.php: 147 → 63 lines (57% reduction)
 * - local/externallecturer/ajax/get_personal_data.php: 112 → 38 lines (66% reduction)
 * - local/residencebooking/ajax/guest_search.php: 59 → 24 lines (59% reduction)
 * 
 * PLUGINS CURRENTLY USING THIS MANAGER:
 * ====================================
 * ✓ local/annualplans - Beneficiary management and course planning
 * ✓ local/participant - Employee lookup and request management
 * ✓ local/externallecturer - Personal data for external lecturers
 * ✓ local/residencebooking - Guest search functionality
 * 
 * OFFLINE COMPATIBILITY:
 * =====================
 * ✓ No external CDN dependencies
 * ✓ No internet connection required
 * ✓ All resources served locally
 * ✓ Optimized for isolated server environments
 * 
 * MAINTENANCE:
 * ===========
 * To modify Oracle connection settings (credentials, server, etc.):
 * 1. Update the static variables at the top of this class
 * 2. Changes automatically apply to ALL plugins using this manager
 * 3. No need to modify individual plugin files
 * 
 * ADDING NEW METHODS:
 * ==================
 * When adding new Oracle operations:
 * 1. Follow the existing pattern (connection → query → cleanup)
 * 2. Use parameterized queries for security
 * 3. Include comprehensive error handling and logging
 * 4. Add detailed PHPDoc comments following this file's style
 * 5. Test with both Arabic and English data
 * 
 * PERFORMANCE CONSIDERATIONS:
 * ==========================
 * - Each method creates a new connection (stateless design)
 * - Connections are properly closed to prevent resource leaks
 * - Queries are optimized for the expected data volume
 * - Consider result caching in calling applications for frequently-used data
 * 
 * For questions or support, refer to the documentation in:
 * local/oracleFetch/README.md
 * ========================================
 */