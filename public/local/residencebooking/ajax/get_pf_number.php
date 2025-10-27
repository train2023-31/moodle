<?php
/**
 * AJAX endpoint to retrieve PF number for a selected guest
 * 
 * This endpoint provides a fallback mechanism for extracting PF numbers
 * when direct extraction from display text is not possible.
 * 
 * @package    local_residencebooking
 * @copyright  2024 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');

// Ensure user is logged in
require_login();

// Get guest name from request parameter
$guest_name = optional_param('guest_name', '', PARAM_TEXT);

// Set JSON response header
header('Content-Type: application/json');

// Validate input
if (empty($guest_name)) {
    echo json_encode(['success' => false, 'error' => 'Guest name is required']);
    exit;
}

try {
    // Method 1: Direct PF extraction from guest name
    if (preg_match('/PF(\d+)/', $guest_name, $matches)) {
        $pf_number = 'PF' . $matches[1];
        echo json_encode([
            'success' => true,
            'pf_number' => $pf_number
        ]);
        exit;
    }
    
    // Method 2: Smart name processing and database search
    // Extract name part (remove PF number if present)
    $name_only = preg_replace('/\s*-\s*PF\d+$/', '', $guest_name);
    
    // Extract first name for database search
    $name_parts = explode(' ', trim($name_only));
    $first_name = $name_parts[0];
    
    // Search for employees using the first name
    $employees = oracle_manager::search_employees($first_name);
    
    // Find matching employee and extract PF number
    $pf_number = null;
    foreach ($employees as $employee) {
        // Try exact match first
        if ($employee['fullname'] === $guest_name) {
            $pf_number = $employee['pf_number'];
            break;
        }
        
        // Try matching the name part (without PF number)
        if ($employee['fullname'] === $name_only) {
            $pf_number = $employee['pf_number'];
            break;
        }
        
        // Try partial match (guest name is part of the full name)
        if (strpos($employee['fullname'], $name_only) !== false) {
            $pf_number = $employee['pf_number'];
            break;
        }
        
        // Try reverse partial match (full name is part of guest name)
        if (strpos($name_only, $employee['fullname']) !== false) {
            $pf_number = $employee['pf_number'];
            break;
        }
        
        // Try matching just the name part (without PF number)
        $employee_name_only = preg_replace('/\s*-\s*PF\d+$/', '', $employee['fullname']);
        if ($employee_name_only === $name_only) {
            $pf_number = $employee['pf_number'];
            break;
        }
    }
    
    // Return result
    if ($pf_number) {
        echo json_encode([
            'success' => true,
            'pf_number' => $pf_number
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'PF number not found for this guest'
        ]);
    }
    
} catch (Exception $e) {
    // Log error and return error response
    error_log('PF number lookup error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to lookup PF number'
    ]);
}

exit;
