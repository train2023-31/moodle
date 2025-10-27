<?php
// AJAX-style health diagnostics for Oracle manager endpoints (offline safe)

define('NO_OUTPUT_BUFFERING', true);

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

@header('Content-Type: application/json; charset=utf-8');

$php_errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$php_errors) {
    $php_errors[] = [
        'errno' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
    ];
    return true; // swallow into report
});

require_once(__DIR__ . '/../classes/oracle_manager.php');

$report = [
    'time' => date('c'),
    'assets' => [
        'select2_css_exists' => file_exists(__DIR__ . '/../lib/select2.min.css'),
        'select2_js_exists' => file_exists(__DIR__ . '/../lib/select2.full.min.js'),
    ],
    'endpoints' => [
        'get_all_employees' => null,
        'get_all_persons' => null,
        'get_all_employees_and_persons' => null,
        'search_employees_empty' => null,
        'search_employees_sample' => null,
    ],
    'php_errors' => [],
];

// 1) get_all_employees
try {
    $data = oracle_manager::get_all_employees();
    $report['endpoints']['get_all_employees'] = [
        'ok' => is_array($data),
        'count' => is_array($data) ? count($data) : null,
        'sample' => is_array($data) && count($data) ? array_slice($data, 0, 1)[0] : null,
    ];
} catch (Throwable $ex) {
    $report['endpoints']['get_all_employees'] = [ 'ok' => false, 'error' => $ex->getMessage() ];
}

// 2) get_all_persons
try {
    $data = oracle_manager::get_all_persons();
    $report['endpoints']['get_all_persons'] = [
        'ok' => is_array($data),
        'count' => is_array($data) ? count($data) : null,
        'sample' => is_array($data) && count($data) ? array_slice($data, 0, 1)[0] : null,
    ];
} catch (Throwable $ex) {
    $report['endpoints']['get_all_persons'] = [ 'ok' => false, 'error' => $ex->getMessage() ];
}

// 3) get_all_employees_and_persons
try {
    $data = oracle_manager::get_all_employees_and_persons();
    $report['endpoints']['get_all_employees_and_persons'] = [
        'ok' => is_array($data),
        'count' => is_array($data) ? count($data) : null,
        'sample_key' => is_array($data) && count($data) ? array_key_first($data) : null,
        'sample_value' => is_array($data) && count($data) ? $data[array_key_first($data)] : null,
    ];
} catch (Throwable $ex) {
    $report['endpoints']['get_all_employees_and_persons'] = [ 'ok' => false, 'error' => $ex->getMessage() ];
}

// 4) search_employees with empty term
try {
    $data = oracle_manager::search_employees('');
    $report['endpoints']['search_employees_empty'] = [
        'ok' => is_array($data),
        'count' => is_array($data) ? count($data) : null,
        'sample' => is_array($data) && count($data) ? array_slice($data, 0, 1)[0] : null,
    ];
} catch (Throwable $ex) {
    $report['endpoints']['search_employees_empty'] = [ 'ok' => false, 'error' => $ex->getMessage() ];
}

// 5) search_employees with sample tribe/term
$sampleterm = optional_param('term', 'PF', PARAM_TEXT);
try {
    $data = oracle_manager::search_employees($sampleterm);
    $report['endpoints']['search_employees_sample'] = [
        'term' => $sampleterm,
        'ok' => is_array($data),
        'count' => is_array($data) ? count($data) : null,
        'sample' => is_array($data) && count($data) ? array_slice($data, 0, 1)[0] : null,
    ];
} catch (Throwable $ex) {
    $report['endpoints']['search_employees_sample'] = [ 'term' => $sampleterm, 'ok' => false, 'error' => $ex->getMessage() ];
}

$report['php_errors'] = $php_errors;

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

restore_error_handler();

