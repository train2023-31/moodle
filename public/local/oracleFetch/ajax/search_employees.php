<?php
/**
 * AJAX script to search employees from Oracle database
 * 
 * @package    local_oracleFetch
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Get search term
$term = optional_param('term', '', PARAM_TEXT);

// Search employees and return as JSON
$employees = oracle_manager::search_employees($term);

// Format for Select2
$results = [];
foreach ($employees as $employee) {
    $results[] = [
        'id' => $employee['fullname'],
        'text' => $employee['display_text']
    ];
}

header('Content-Type: application/json');
echo json_encode(['results' => $results]);