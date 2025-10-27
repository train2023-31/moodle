<?php
/**
 * Request Participant Tab for Request Services Plugin
 * 
 * This tab integrates with the local_participant plugin to provide
 * participant request functionality within the unified request services interface.
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

// Check if the participant plugin is available
$participant_path = $CFG->dirroot . '/local/participant';
if (!is_dir($participant_path)) {
    throw new moodle_exception('local_participant plugin not found', 'local_requestservices');
}

// Include the participant form class
require_once($participant_path . '/classes/form/request_form.php');

// Set up the context and action URL
$context = context_system::instance();
$actionurl = new moodle_url('/local/requestservices/index.php', ['id' => $courseid, 'tab' => 'requestparticipant']);

// Create the form instance
$mform = new \local_participant\form\request_form($actionurl, ['courseid' => $courseid]);

// Load JavaScript for employee autocomplete functionality and conditional field behavior
$PAGE->requires->js(new moodle_url('/local/participant/js/main.js'));

// Process the form if it's submitted
if ($mform->is_cancelled()) {
    // Redirect to the main page if the form is cancelled
    redirect(new moodle_url('/local/requestservices/index.php', ['id' => $courseid, 'tab' => 'requestparticipant']));
} else if ($data = $mform->get_data()) {
    // Process the form data
    try {
        // Validate required fields
        if (empty($data->courseid) || empty($data->participant_type_id) || empty($data->duration_amount)) {
            throw new moodle_exception('missingrequiredfields', 'local_requestservices');
        }

        // Fetch necessary course data
        $moodle_course = $DB->get_record('course', ['id' => $data->courseid], 'idnumber');
        if (!$moodle_course) {
            throw new moodle_exception('invalidcourseid', 'local_requestservices');
        }

        // Get annual plan data if available - prioritize current year and oldest active plans
        $annual_plan_id = null;
        if (!empty($moodle_course->idnumber)) {
            debugging('Looking for annual plan with course ID: ' . $moodle_course->idnumber, DEBUG_DEVELOPER);
            
            // Get current year for proper plan selection
            $current_year = date('Y');
            debugging('Current year: ' . $current_year, DEBUG_DEVELOPER);
            
            // First try to find a plan for the current year (2025)
            $sql = "SELECT apc.annualplanid, ap.year, ap.disabled, ap.title 
                    FROM {local_annual_plan_course} apc
                    JOIN {local_annual_plan} ap ON apc.annualplanid = ap.id
                    WHERE apc.courseid = ? 
                    AND ap.disabled = 0
                    ORDER BY ap.year = ? DESC, ap.year ASC, ap.id ASC";
            
            $plan_course = $DB->get_record_sql($sql, [$moodle_course->idnumber, $current_year]);
            
            if ($plan_course) {
                $annual_plan_id = $plan_course->annualplanid;
                debugging('Found annual plan ID: ' . $annual_plan_id . ' for year: ' . $plan_course->year . ' - ' . $plan_course->title, DEBUG_DEVELOPER);
                
                // Log warning if plan year doesn't match current year
                if ($plan_course->year != $current_year) {
                    debugging('WARNING: Selected plan year (' . $plan_course->year . ') does not match current year (' . $current_year . ')', DEBUG_DEVELOPER);
                }
            } else {
                debugging('No active annual plan found for course ID: ' . $moodle_course->idnumber, DEBUG_DEVELOPER);
            }
        } else {
            debugging('Course has no ID number, cannot assign annual plan', DEBUG_DEVELOPER);
        }

        // Get participant type data for automatic compensation calculation
        $participant_type = $DB->get_record('local_participant_request_types', 
            ['id' => $data->participant_type_id], 
            'cost, calculation_type', 
            MUST_EXIST
        );
        
        // Calculate compensation amount based on type and duration
        $compensation_amount = 0;
        if ($participant_type->calculation_type == 'dynamic') {
            // For dynamic types (like external lecturers), use the amount from form
            $compensation_amount = isset($data->compensation_amount) ? $data->compensation_amount : 0;
        } else {
            // For fixed rates (days/hours), calculate automatically
            $compensation_amount = $data->duration_amount * $participant_type->cost;
        }

        // Determine if this is an external lecturer (type 7)
        $is_external_lecturer = ($data->participant_type_id == 7);

        // Create the request record
        $new_request = new stdClass();
        $new_request->course_id = $data->courseid;
        $new_request->annual_plan_id = $annual_plan_id ?: 0;
        $new_request->participant_type_id = $data->participant_type_id;
        $new_request->duration_amount = $data->duration_amount;
        $new_request->compensation_amount = $compensation_amount;
        $new_request->is_internal_participant = $is_external_lecturer ? 0 : 1;
        
        // Handle participant assignment based on type
        if ($is_external_lecturer) {
            // External lecturer - store externallecturer table ID
            $new_request->external_lecturer_id = !empty($data->external_lecturer_id) ? (int)$data->external_lecturer_id : null;
            $new_request->pf_number = null;
            $new_request->participant_full_name = null;
        } else {
            // Internal participant - store Oracle data directly
            $new_request->external_lecturer_id = null;
            $new_request->pf_number = !empty($data->pf_number) ? $data->pf_number : null;
            $new_request->participant_full_name = !empty($data->participant_full_name) ? $data->participant_full_name : null;
        }
        
        // Handle date conversion properly
        if (isset($data->requested_date)) {
            if (is_array($data->requested_date)) {
                $timestamp = make_timestamp(
                    $data->requested_date['year'],
                    $data->requested_date['month'], 
                    $data->requested_date['day'],
                    $data->requested_date['hour'] ?? 0,
                    $data->requested_date['minute'] ?? 0
                );
            } else {
                $timestamp = $data->requested_date;
            }
        } else {
            $timestamp = time();
        }
        
        $new_request->requested_date = date('Y-m-d H:i:s', $timestamp);
        $new_request->request_status_id = \local_participant\simple_workflow_manager::get_initial_status_id();
        $new_request->is_approved = 0;
        $new_request->rejection_reason = null;
        $new_request->created_by = (int)$USER->id;
        $new_request->time_created = time();
        $new_request->time_modified = time();
        $new_request->modified_by = (int)$USER->id;

        // Insert the new request into the database
        $requestid = $DB->insert_record('local_participant_requests', $new_request);
        
        if (!$requestid) {
            throw new dml_exception('Failed to insert participant request');
        }

        // Log the successful request creation
        debugging('Participant request created successfully: ID=' . $requestid . 
                 ', User=' . $USER->id . ', Course=' . $data->courseid . 
                 ', Type=' . $data->participant_type_id . ', External=' . ($is_external_lecturer ? 'Yes' : 'No'), DEBUG_DEVELOPER);

        // Show success notification
        \core\notification::add(
            get_string('requestadded', 'local_participant'),
            \core\notification::SUCCESS
        );

        // Redirect to refresh the form
        redirect($actionurl);

    } catch (moodle_exception $e) {
        // Handle Moodle-specific errors
        debugging('Moodle exception in participant request: ' . $e->getMessage(), DEBUG_DEVELOPER);
        \core\notification::add(
            get_string('error', 'core') . ': ' . $e->getMessage(),
            \core\notification::ERROR
        );
    } catch (dml_exception $e) {
        // Handle database errors
        debugging('Database error in participant request: ' . $e->getMessage(), DEBUG_DEVELOPER);
        \core\notification::add(
            get_string('inserterror', 'local_participant'),
            \core\notification::ERROR
        );
    } catch (Exception $e) {
        // Handle any other errors
        debugging('Unexpected error in participant request: ' . $e->getMessage(), DEBUG_DEVELOPER);
        \core\notification::add(
            get_string('error', 'core') . ': ' . get_string('unknownerror', 'core'),
            \core\notification::ERROR
        );
    }
}

// Display the form
$mform->display();
