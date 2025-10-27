<?php
require_once('../../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/externallecturer:manage', $context);

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

try {
    // Get data from the request
    $id = required_param('id', PARAM_INT);
    $name = required_param('name', PARAM_TEXT);
    $age = required_param('age', PARAM_INT);
    $specialization = required_param('specialization', PARAM_TEXT);
    $organization = required_param('organization', PARAM_TEXT);
    $degree = required_param('degree', PARAM_TEXT);
    $passport = required_param('passport', PARAM_TEXT);
    $civil_number = optional_param('civil_number', '', PARAM_TEXT);
    $lecturer_type = optional_param('lecturer_type', 'external_visitor', PARAM_TEXT);
    $nationality = optional_param('nationality', '', PARAM_TEXT);

    // Update lecturer record
    $lecturer = $DB->get_record('externallecturer', ['id' => $id]);

    if (!$lecturer) {
        throw new Exception('Lecturer not found');
    }

    $lecturer->name = $name;
    $lecturer->age = $age;
    $lecturer->specialization = $specialization;
    $lecturer->organization = $organization;
    $lecturer->degree = $degree;
    $lecturer->passport = $passport;
    $lecturer->civil_number = $civil_number;
    $lecturer->lecturer_type = $lecturer_type ?: 'external_visitor';
    $lecturer->nationality = $nationality;
    
    // Update audit fields
    $lecturer->timemodified = time();
    $lecturer->modified_by = $USER->id;

    $success = $DB->update_record('externallecturer', $lecturer);
    
    if ($success) {
        // For AJAX requests, return JSON response
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => get_string('lecturereditedsuccess', 'local_externallecturer'),
                'id' => $id
            ]);
            exit;
        } else {
            \core\notification::success(get_string('lecturereditedsuccess', 'local_externallecturer'));
        }
    } else {
        throw new Exception('Failed to update lecturer record');
    }
} catch (Exception $e) {
    error_log('Error editing lecturer: ' . $e->getMessage());
    
    // For AJAX requests, return JSON error response
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => get_string('lecturereditederror', 'local_externallecturer'),
            'error' => $e->getMessage()
        ]);
        exit;
    } else {
        \core\notification::error(get_string('lecturereditederror', 'local_externallecturer'));
    }
}

// Only redirect if not an AJAX request
if (!$is_ajax) {
    redirect(new moodle_url('/local/externallecturer/index.php'));
}
