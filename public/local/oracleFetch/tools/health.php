<?php
// Offline health diagnostics for Oracle + Select2 integration
// Admin-only

define('NO_OUTPUT_BUFFERING', true);
define('CLI_SCRIPT', false);

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Collect PHP notices/warnings to include in JSON safely
$php_errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$php_errors) {
    $php_errors[] = [
        'errno' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
    ];
    return true; // prevent default output that could corrupt JSON
});

$ashtml = optional_param('html', 0, PARAM_INT) ? true : false;
$term = optional_param('term', '', PARAM_TEXT);

$result = [
    'time' => date('c'),
    'moodle' => [
        'version' => $CFG->version ?? null,
        'release' => $CFG->release ?? null,
        'wwwroot' => $CFG->wwwroot ?? null,
    ],
    'environment' => [
        'php_version' => PHP_VERSION,
        'oci8_loaded' => extension_loaded('oci8'),
        'has_oci_connect' => function_exists('oci_connect'),
        'NLS_LANG' => getenv('NLS_LANG') ?: null,
        'ORACLE_HOME' => getenv('ORACLE_HOME') ?: null,
    ],
    'oracle' => [
        'connection' => [ 'ok' => false, 'error' => null ],
        'select_dual' => [ 'ok' => false, 'error' => null, 'row' => null ],
        'employees_count' => [ 'ok' => false, 'error' => null, 'count' => null ],
        'sample_employees' => [ 'ok' => false, 'error' => null, 'rows' => [] ],
        'sample_persons_with_nationality' => [ 'ok' => false, 'error' => null, 'rows' => [] ],
    ],
    'manager' => [
        'search_term' => $term,
        'search_employees' => [ 'ok' => false, 'error' => null, 'count' => null ],
        'get_all_persons' => [ 'ok' => false, 'error' => null, 'count' => null ],
    ],
    'php_errors' => [],
];

// Use centralized connection (ensures charset)
require_once(__DIR__ . '/../classes/oracle_manager.php');

$conn = oracle_manager::get_connection();
if (!$conn) {
    $e = oci_error();
    $result['oracle']['connection']['error'] = $e ? ($e['message'] ?? 'unknown') : 'unknown connection error';
} else {
    $result['oracle']['connection']['ok'] = true;

    // 1) SELECT 1 FROM dual
    $sql = "SELECT 1 AS OK FROM dual";
    $stid = @oci_parse($conn, $sql);
    if ($stid && @oci_execute($stid)) {
        $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
        $result['oracle']['select_dual']['ok'] = true;
        $result['oracle']['select_dual']['row'] = $row;
        oci_free_statement($stid);
    } else {
        $result['oracle']['select_dual']['error'] = ($stid ? (oci_error($stid)['message'] ?? 'execute error') : (oci_error($conn)['message'] ?? 'parse error'));
    }

    // 2) COUNT employees
    $sql = "SELECT COUNT(1) AS CNT FROM DUNIA_EMPLOYEES";
    $stid = @oci_parse($conn, $sql);
    if ($stid && @oci_execute($stid)) {
        $row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
        $result['oracle']['employees_count']['ok'] = true;
        $result['oracle']['employees_count']['count'] = $row ? ($row['CNT'] ?? null) : null;
        oci_free_statement($stid);
    } else {
        $result['oracle']['employees_count']['error'] = ($stid ? (oci_error($stid)['message'] ?? 'execute error') : (oci_error($conn)['message'] ?? 'parse error'));
    }

    // 3) Sample employees (show NULL handling)
    $sql = "SELECT pf_number, prs_name1_a, prs_name2_a, prs_name3_a, prs_tribe_a, aaa_emp_civil_number_ar FROM DUNIA_EMPLOYEES WHERE ROWNUM <= 3";
    $stid = @oci_parse($conn, $sql);
    if ($stid && @oci_execute($stid)) {
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $result['oracle']['sample_employees']['rows'][] = $row;
        }
        $result['oracle']['sample_employees']['ok'] = true;
        oci_free_statement($stid);
    } else {
        $result['oracle']['sample_employees']['error'] = ($stid ? (oci_error($stid)['message'] ?? 'execute error') : (oci_error($conn)['message'] ?? 'parse error'));
    }

    // 4) Sample persons with nationality
    $sql = "SELECT civil_number, name_arabic_1, name_arabic_2, name_arabic_3, name_arabic_6, nationality_arabic_SYS FROM DUNIA_PERSONAL_DETAILS WHERE ROWNUM <= 3";
    $stid = @oci_parse($conn, $sql);
    if ($stid && @oci_execute($stid)) {
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $result['oracle']['sample_persons_with_nationality']['rows'][] = $row;
        }
        $result['oracle']['sample_persons_with_nationality']['ok'] = true;
        oci_free_statement($stid);
    } else {
        $result['oracle']['sample_persons_with_nationality']['error'] = ($stid ? (oci_error($stid)['message'] ?? 'execute error') : (oci_error($conn)['message'] ?? 'parse error'));
    }

    oci_close($conn);
}

// Manager-level smoke tests
try {
    $employees = oracle_manager::search_employees($term);
    $result['manager']['search_employees']['ok'] = true;
    $result['manager']['search_employees']['count'] = is_array($employees) ? count($employees) : null;
} catch (Throwable $ex) {
    $result['manager']['search_employees']['error'] = $ex->getMessage();
}

try {
    $persons = oracle_manager::get_all_persons();
    $result['manager']['get_all_persons']['ok'] = true;
    $result['manager']['get_all_persons']['count'] = is_array($persons) ? count($persons) : null;
} catch (Throwable $ex) {
    $result['manager']['get_all_persons']['error'] = $ex->getMessage();
}

$result['php_errors'] = $php_errors;

if ($ashtml) {
    echo $OUTPUT->header();
    echo html_writer::tag('h3', 'Oracle Health (Offline)');
    echo html_writer::tag('pre', s(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    echo $OUTPUT->footer();
} else {
    @header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

restore_error_handler();

