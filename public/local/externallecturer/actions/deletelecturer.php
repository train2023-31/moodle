<?php
require_once('../../../config.php');
require_login();

// Require AJAX request for security
if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

$context = context_system::instance();
require_capability('local/externallecturer:manage', $context);

// Set JSON content type
header('Content-Type: application/json');

try {
    // Get the lecturer ID from POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['id'])) {
        // Try to get from regular POST data as fallback
        $id = optional_param('id', 0, PARAM_INT);
        if (!$id) {
            throw new Exception('Invalid lecturer ID');
        }
    } else {
        $id = intval($data['id']);
    }
    
    if ($id <= 0) {
        throw new Exception('Invalid lecturer ID');
    }
    
    // Check if lecturer exists
    $lecturer = $DB->get_record('externallecturer', ['id' => $id]);
    if (!$lecturer) {
        throw new Exception('Lecturer not found');
    }
    
    // Note: Course enrollment functionality has been removed
    // Lecturers can now be deleted without course enrollment restrictions
    
    // Delete the lecturer
    $result = $DB->delete_records('externallecturer', ['id' => $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => get_string('lecturerdeletedsuccess', 'local_externallecturer')
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => get_string('lecturerdeletederror', 'local_externallecturer')
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}