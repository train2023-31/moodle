<?php
require_once('../../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/externallecturer:manage', $context);

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

try {
    // Debug: Log the incoming data
    error_log('External Lecturer Debug - POST data: ' . print_r($_POST, true));
    
    // Get POST data
    $name = required_param('name', PARAM_TEXT);
    $age = required_param('age', PARAM_INT);
    $specialization = required_param('specialization', PARAM_TEXT);
    $organization = required_param('organization', PARAM_TEXT);
    $degree = required_param('degree', PARAM_TEXT);
    $passport = required_param('passport', PARAM_TEXT);
    $civil_number = optional_param('civil_number', '', PARAM_TEXT);
    $lecturer_type = optional_param('lecturer_type', 'external_visitor', PARAM_TEXT);
    $nationality = optional_param('nationality', '', PARAM_TEXT);

    // Create lecturer object
    $lecturer = new stdClass();
    $lecturer->name = $name;
    $lecturer->age = $age;
    $lecturer->specialization = $specialization;
    $lecturer->organization = $organization;
    $lecturer->degree = $degree;
    $lecturer->passport = $passport;
    $lecturer->civil_number = $civil_number;
    $lecturer->lecturer_type = $lecturer_type ?: 'external_visitor';
    $lecturer->nationality = $nationality;
    $lecturer->courses_count = 0;
    $now = time();
    // Add audit fields (standardised)
    $lecturer->timecreated = $now;
    $lecturer->timemodified = $now;
    $lecturer->created_by = $USER->id;
    $lecturer->modified_by = $USER->id;

    // Debug: Log the lecturer object
    error_log('External Lecturer Debug - Lecturer object: ' . print_r($lecturer, true));
    
    // Check if table exists
    $table_exists = $DB->get_manager()->table_exists('externallecturer');
    error_log('External Lecturer Debug - Table exists: ' . ($table_exists ? 'yes' : 'no'));
    
    if (!$table_exists) {
        throw new Exception('Database table externallecturer does not exist');
    }
    
    // Insert into database
    $id = $DB->insert_record('externallecturer', $lecturer);
    
    // Debug: Log the result
    error_log('External Lecturer Debug - Insert result: ' . ($id ? $id : 'false'));
    
    if ($id) {
        // For AJAX requests, return JSON response
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => get_string('lectureraddedsuccess', 'local_externallecturer'),
                'id' => $id
            ]);
            exit;
        } else {
            \core\notification::success(get_string('lectureraddedsuccess', 'local_externallecturer'));
        }
    } else {
        throw new Exception('Failed to insert lecturer record');
    }
} catch (Exception $e) {
    error_log('Error adding lecturer: ' . $e->getMessage());
    
    // For AJAX requests, return JSON error response
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => get_string('lectureraddederror', 'local_externallecturer'),
            'error' => $e->getMessage()
        ]);
        exit;
    } else {
        \core\notification::error(get_string('lectureraddederror', 'local_externallecturer'));
    }
}

// Only redirect if not an AJAX request
if (!$is_ajax) {
    redirect(new moodle_url('/local/externallecturer/index.php'));
}
