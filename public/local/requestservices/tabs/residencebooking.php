<?php
/**
 * Residence Booking Tab for Request Services Plugin
 * 
 * This tab integrates with the local_residencebooking plugin to provide
 * accommodation booking functionality within the unified request services interface.
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

// Check if the residencebooking plugin is available
$residencebooking_path = $CFG->dirroot . '/local/residencebooking';
if (!file_exists($residencebooking_path)) {
    echo $OUTPUT->notification(
        get_string('plugin_not_available', 'local_requestservices', 'Residence Booking'),
        'error'
    );
    return;
}

// Include required classes from residencebooking plugin
try {
    require_once($residencebooking_path . '/classes/form/residencebooking_form.php');
    require_once($residencebooking_path . '/classes/simple_workflow_manager.php');
} catch (Exception $e) {
    debugging('Failed to load residencebooking classes: ' . $e->getMessage(), DEBUG_DEVELOPER);
    echo $OUTPUT->notification(
        get_string('plugin_classes_not_found', 'local_requestservices', 'Residence Booking'),
        'error'
    );
    return;
}

// Set up the form action URL
$actionurl = new moodle_url('/local/requestservices/index.php', [
    'id' => $courseid, 
    'tab' => 'residencebooking'
]);

// Instantiate the form with proper course context
try {
    $mform = new \local_residencebooking\form\residencebooking_form($actionurl, ['courseid' => $courseid]);
} catch (Exception $e) {
    debugging('Failed to instantiate residencebooking form: ' . $e->getMessage(), DEBUG_DEVELOPER);
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
        if (empty($data->courseid) || empty($data->residence_type) || 
            empty($data->start_date) || empty($data->end_date) || 
            empty($data->purpose) || empty($data->guest_name)) {
            throw new coding_exception('Required form data is missing');
        }
        
        // Validate course exists and user has access
        $course = $DB->get_record('course', ['id' => $data->courseid], 'id, fullname', MUST_EXIST);
        if (!$course) {
            throw new coding_exception('Selected course does not exist');
        }
        
        // Validate residence type exists and is active
        $residence_type = $DB->get_record('local_residencebooking_types', [
            'id' => $data->residence_type,
            'deleted' => 0
        ], 'id, type_name_en, type_name_ar', MUST_EXIST);
        
        if (!$residence_type) {
            throw new coding_exception('Selected residence type is not available');
        }
        
        // Validate purpose exists and is active
        $purpose = $DB->get_record('local_residencebooking_purpose', [
            'id' => $data->purpose,
            'deleted' => 0
        ], 'id, description_en, description_ar', MUST_EXIST);
        
        if (!$purpose) {
            throw new coding_exception('Selected purpose is not available');
        }
        
        // Validate date logic
        if ($data->end_date < $data->start_date) {
            throw new coding_exception('End date cannot be before start date');
        }
        
        // Get initial status from workflow manager
        $defaultstatusid = \local_residencebooking\simple_workflow_manager::get_initial_status_id();
        
        // Prepare the record for insertion
        $record = new stdClass();
        $record->courseid = $data->courseid;
        $record->residence_type = $data->residence_type;
        $record->start_date = $data->start_date;
        $record->end_date = $data->end_date;
        $record->purpose = $data->purpose;
        $record->notes = $data->notes ?? '';
        $record->guest_name = $data->guest_name;
        $record->service_number = $data->service_number ?? '';
        $record->userid = $USER->id;
        $record->status_id = $defaultstatusid;
        $record->timecreated = time();
        $record->timemodified = time();
        
        // Insert the record into the database
        $requestid = $DB->insert_record('local_residencebooking_request', $record);
        
        if (!$requestid) {
            throw new dml_exception('Failed to insert residence booking request');
        }
        
        // Log the successful request creation for debugging/auditing
        debugging('Residence booking request created successfully: ID=' . $requestid . 
                 ', User=' . $USER->id . ', Course=' . $data->courseid . 
                 ', Guest=' . $data->guest_name . ', Type=' . $data->residence_type, DEBUG_DEVELOPER);
        
        // Redirect with success message
        redirect(
            $actionurl,
            get_string('requestsubmitted', 'local_residencebooking'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
        
    } catch (Exception $e) {
        // Log the error for debugging
        debugging('Residence booking request submission failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        
        // Show user-friendly error message
        $errormessage = get_string('request_submission_failed', 'local_requestservices');
        if (debugging('', DEBUG_DEVELOPER)) {
            $errormessage .= ' (' . $e->getMessage() . ')';
        }
        
        echo $OUTPUT->notification($errormessage, 'error');
    }
}

// Display the form
echo html_writer::start_div('residence-booking-tab');
echo html_writer::tag('h3', get_string('residence_booking_request', 'local_requestservices'), ['class' => 'mb-3']);
echo html_writer::tag('p', get_string('residence_booking_description', 'local_requestservices'), ['class' => 'text-muted mb-4']);

$mform->display();

echo html_writer::end_div();

// Load the guest autocomplete JavaScript file
$PAGE->requires->js(new moodle_url('/local/requestservices/js/guest_autocomplete.js'));

// Initialize guest autocomplete with service number population
echo html_writer::script('
$(document).ready(function() {
    // Initialize the guest autocomplete functionality
    if (typeof initGuestAutocomplete === "function") {
        initGuestAutocomplete("#id_guest_name", "#id_service_number");
    } else {
        console.error("initGuestAutocomplete function not found. Make sure guest_autocomplete.js is loaded.");
    }
});
');

