<?php
/**
 * Computer Services Tab for Request Services Plugin
 * 
 * This tab integrates with the local_computerservice plugin to provide
 * device request functionality within the unified request services interface.
 * 
 * @package    local_requestservices
 * @subpackage tabs
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Ensure we have the required context and variables
if (!isset($context) || !isset($courseid)) {
    throw new coding_exception('Required context or courseid not available');
}

// Require capability to view this tab
require_capability('local/requestservices:view', $context);

// Check if the computerservice plugin is available
$computerservice_path = $CFG->dirroot . '/local/computerservice';
if (!file_exists($computerservice_path)) {
    echo $OUTPUT->notification(
        get_string('plugin_not_available', 'local_requestservices', 'Computer Service'),
        'error'
    );
    return;
}

// Include required classes from computerservice plugin
try {
    require_once($computerservice_path . '/classes/form/request_form.php');
    require_once($computerservice_path . '/classes/simple_workflow_manager.php');
} catch (Exception $e) {
    debugging('Failed to load computerservice classes: ' . $e->getMessage(), DEBUG_DEVELOPER);
    echo $OUTPUT->notification(
        get_string('plugin_classes_not_found', 'local_requestservices', 'Computer Service'),
        'error'
    );
    return;
}

// Set up the form action URL
$actionurl = new moodle_url('/local/requestservices/index.php', [
    'id' => $courseid, 
    'tab' => 'computerservicesTab'
]);

// Instantiate the form with proper course context
try {
    $mform = new \local_computerservice\form\request_form($actionurl, ['id' => $courseid]);
} catch (Exception $e) {
    debugging('Failed to instantiate computerservice form: ' . $e->getMessage(), DEBUG_DEVELOPER);
    echo $OUTPUT->notification(
        get_string('form_instantiation_failed', 'local_requestservices'),
        'error'
    );
    return;
}

// Process form data if submitted
if ($mform->is_cancelled()) {
    // Handle form cancellation - redirect back to the same tab
    redirect($actionurl);
} else if ($data = $mform->get_data()) {
    // Validate session key for security
    require_sesskey();
    
    try {
        // Validate required data
        if (empty($data->deviceid) || empty($data->courseid) || empty($data->numdevices)) {
            throw new coding_exception('Required form data is missing');
        }
        
        // Validate device ID exists and is active
        $device = $DB->get_record('local_computerservice_devices', [
            'id' => $data->deviceid,
            'status' => 'active'
        ], 'id, devicename_en, devicename_ar', MUST_EXIST);
        
        if (!$device) {
            throw new coding_exception('Selected device is not available or inactive');
        }
        
        // Get device name in current language
        $selecteddevicename = (current_language() === 'ar') 
            ? $device->devicename_ar 
            : $device->devicename_en;
        
        // Validate course exists and user has access
        $course = $DB->get_record('course', ['id' => $data->courseid], 'id, fullname', MUST_EXIST);
        if (!$course) {
            throw new coding_exception('Selected course does not exist');
        }
        
        // Get initial status from workflow manager
        $defaultstatusid = \local_computerservice\simple_workflow_manager::get_initial_status_id();
        
        // Calculate urgent status (requests needed today or tomorrow)
        $now = time();
        $needed = $data->request_needed_by;
        $is_urgent = ($needed - $now) < DAYSECS ? 1 : 0;
        
        // Prepare the record for insertion
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $data->courseid;
        $record->deviceid = $data->deviceid;
        $record->numdevices = $data->numdevices;
        $record->comments = $data->comments ?? '';
        $record->status_id = $defaultstatusid;
        $record->timecreated = $now;
        $record->timemodified = $now;
        $record->request_needed_by = $needed;
        $record->is_urgent = $is_urgent;
        
        // Insert the record into the database
        $requestid = $DB->insert_record('local_computerservice_requests', $record);
        
        if (!$requestid) {
            throw new dml_exception('Failed to insert computer service request');
        }
        
        // Log the successful request creation for debugging/auditing
        debugging('Computer service request created successfully: ID=' . $requestid . 
                 ', User=' . $USER->id . ', Course=' . $data->courseid . 
                 ', Device=' . $data->deviceid . ', Urgent=' . $is_urgent, DEBUG_DEVELOPER);
        
        // Redirect with success message
        redirect(
            $actionurl,
            get_string('requestsubmitted', 'local_computerservice'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        
    } catch (Exception $e) {
        // Log the error for debugging
        debugging('Computer service request submission failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        
        // Show user-friendly error message
        $errormessage = get_string('request_submission_failed', 'local_requestservices');
        if (debugging('', DEBUG_DEVELOPER)) {
            $errormessage .= ' (' . $e->getMessage() . ')';
        }
        
        echo $OUTPUT->notification($errormessage, 'error');
    }
}

// Display the form
echo html_writer::start_div('computer-service-tab');
echo html_writer::tag('h3', get_string('computer_service_request', 'local_requestservices'), ['class' => 'mb-3']);
echo html_writer::tag('p', get_string('computer_service_description', 'local_requestservices'), ['class' => 'text-muted mb-4']);

$mform->display();

echo html_writer::end_div();


