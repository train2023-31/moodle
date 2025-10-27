<?php

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');

require_login();
$term = optional_param('term', '', PARAM_TEXT);

header('Content-Type: application/json');

try {
    // Use centralized Oracle manager for employee search
    // Always get results, even if no search term is provided
    $employees = oracle_manager::search_employees($term);
    
    $results = [];
    foreach ($employees as $employee) {
        $results[] = [
            'id' => $employee['fullname'],                    // Use full name as the value
            'text' => $employee['display_text'],              // Display PF number and name for selection
            'pf_number' => $employee['pf_number']             // Include PF number for service number field
        ];
    }
    
    // Always return results in Select2 format, even if empty
    echo json_encode([
        'results' => $results,
        'pagination' => ['more' => false]
    ]);
    
} catch (Exception $e) {
    // Log error and return empty results
    error_log('Guest search error: ' . $e->getMessage());
    echo json_encode(['results' => [], 'error' => 'Search failed']);
}

exit;
