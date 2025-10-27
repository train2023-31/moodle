<?php
/**
 * AJAX script to search person by civil number from Oracle database
 * 
 * @package    local_oracleFetch
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Get search term (civil number)
$civil_number = optional_param('civil_number', '', PARAM_TEXT);

$result = [];

if (!empty($civil_number)) {
    // Search by civil number using Oracle manager
    $person = oracle_manager::get_person_by_civil($civil_number);
    
    if ($person) {
        // Format the response with all necessary fields
        $result = [
            'success' => true,
            'data' => [
                'civil_number' => $person['CIVIL_NUMBER'],
                'passport_number' => $person['PASSPORT_NUMBER'] ?? '',
                'first_name' => $person['NAME_ARABIC_1'] ?? '',
                'middle_name' => $person['NAME_ARABIC_2'] ?? '',
                'last_name' => $person['NAME_ARABIC_3'] ?? '',
                'tribe' => $person['NAME_ARABIC_6'] ?? '',
                'nationality' => $person['NATIONALITY_ARABIC_SYS'] ?? '',
                'fullname' => trim(($person['NAME_ARABIC_1'] ?? '') . ' ' . 
                            ($person['NAME_ARABIC_2'] ?? '') . ' ' . 
                            ($person['NAME_ARABIC_3'] ?? ''). ' ' .
                            ($person['NAME_ARABIC_6'] ?? '')),
                'display_text' => trim(($person['NAME_ARABIC_1'] ?? '') . ' ' . 
                                ($person['NAME_ARABIC_2'] ?? '') . ' ' . 
                                ($person['NAME_ARABIC_3'] ?? '')) . 
                                (!empty($person['NAME_ARABIC_6']) ? ' ' . $person['NAME_ARABIC_6'] : '') .
                                ' (' . $person['CIVIL_NUMBER'] . ')'
            ]
        ];
    } else {
        $result = [
            'success' => false,
            'message' => 'لا توجد بيانات للرقم المدني المحدد', // No data found for the specified civil number
            'data' => null
        ];
    }
} else {
    $result = [
        'success' => false,
        'message' => 'الرجاء إدخال الرقم المدني', // Please enter civil number
        'data' => null
    ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
