<?php
/**
 * Participant View Subtab for Request Services Plugin
 * 
 * This subtab displays participant requests for the current course
 * using data from the local_participant plugin.
 * 
 * @package    local_requestservices
 * @subpackage tabs/subtabs
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE, $OUTPUT, $DB, $USER, $CFG;

// Ensure necessary variables are available
$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

// Check if the participant plugin is available
$participant_path = $CFG->dirroot . '/local/participant';
if (!is_dir($participant_path)) {
    throw new moodle_exception('local_participant plugin not found', 'local_requestservices');
}

// Check capability
require_capability('local/participant:view', $context);

// Set up pagination
$defaultperpage = 10;
$perpage = optional_param('perpage', $SESSION->perpage ?? $defaultperpage, PARAM_INT);
$SESSION->perpage = $perpage;
$requestspage = optional_param('requestspage', 0, PARAM_INT);

// Fetch participant requests for the current course
$filters = ['course_id' => $courseid];

try {
    $requests = $DB->get_records('local_participant_requests', $filters, '', '*', $requestspage * $perpage, $perpage);
    $totalrequests = $DB->count_records('local_participant_requests', $filters);
} catch (dml_exception $e) {
    debugging('Database error fetching participant requests: ' . $e->getMessage(), DEBUG_DEVELOPER);
    $requests = [];
    $totalrequests = 0;
}

// Prepare pagination
$requestspagination = new paging_bar($totalrequests, $requestspage, $perpage, $PAGE->url, 'requestspage');

$perpage_options = [
    ['value' => 5, 'selected' => $perpage == 5],
    ['value' => 10, 'selected' => $perpage == 10],
    ['value' => 20, 'selected' => $perpage == 20],
    ['value' => 50, 'selected' => $perpage == 50]
];

// Prepare formatted data for the template
$formatted_requests = [];
foreach ($requests as $request) {
    try {
        // Fetch additional related data (course, status, participant)
        $course = $DB->get_record('course', ['id' => $request->course_id], 'fullname');
        $plan = $DB->get_record('local_annual_plan', ['id' => $request->annual_plan_id], 'title');
        $status_name = \local_participant\simple_workflow_manager::get_status_name($request->request_status_id);
        $type = $DB->get_record('local_participant_request_types', ['id' => $request->participant_type_id], 'name_en, name_ar');
        
        $orgname = 'N/A';
        $participant_name = 'N/A';

        // Handle external lecturer vs internal participant
        if ($request->participant_type_id == 7) {
            // External lecturer - lookup from externallecturer table
            $externallecturer = $DB->get_record('externallecturer', ['id' => $request->external_lecturer_id], 'name, organization');
            $participant_name = $externallecturer ? $externallecturer->name : 'N/A';
            $orgname = $externallecturer ? $externallecturer->organization : 'N/A';
        } else {
            // Internal participant - use stored Oracle data
            if (!empty($request->participant_full_name)) {
                // Use the stored full name from Oracle data
                $participant_name = $request->participant_full_name;
            } else if (!empty($request->pf_number)) {
                // Fallback: try to get name from Oracle using PF number
                if (file_exists(__DIR__ . '/../../../oracleFetch/lib.php')) {
                    require_once(__DIR__ . '/../../../oracleFetch/lib.php');
                    $participant_name = oracle_get_employee_name($request->pf_number);
                } else {
                    $participant_name = 'PF: ' . $request->pf_number;
                }
            } else {
                $participant_name = 'N/A';
            }
            $orgname = 'OmanTel';
        }

        // Format the request data for the Mustache template
        $formatted_requests[] = [
            'id' => $request->id,
            'participant_name' => $participant_name,
            'pf_number' => $request->pf_number ?? 'N/A',
            'course_name' => $course->fullname ?? 'N/A',
            'plan_name' => $plan->title ?? 'N/A',
            'type_name' => (current_language() == 'ar') ? ($type->name_ar ?? $type->name_en ?? 'N/A') : ($type->name_en ?? $type->name_ar ?? 'N/A'),
            'org_name' => $orgname,
            'is_inside' => $request->is_internal_participant == 1 ? get_string('internal', 'local_participant') : get_string('external', 'local_participant'),
            'is_approved' => \local_participant\simple_workflow_manager::is_approved_status($request->request_status_id),
            'is_rejected' => \local_participant\simple_workflow_manager::is_rejected_status($request->request_status_id),
            'can_approve' => \local_participant\simple_workflow_manager::can_user_approve($request->request_status_id),
            'can_reject' => \local_participant\simple_workflow_manager::can_user_reject($request->request_status_id),
            'duration_amount' => $request->duration_amount,
            'compensation_amount' => $request->compensation_amount,
            'status' => $request->request_status_id,
            'status_name' => $status_name,
            'rejection_reason' => $request->rejection_reason ?? '',
            'has_rejection_reason' => !empty($request->rejection_reason),
            'date' => date('d-m-y', strtotime($request->requested_date)),
        ];
    } catch (Exception $e) {
        // Log error but continue processing other requests
        debugging('Error processing participant request ' . $request->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        continue;
    }
}

// Include the renderable class
require_once($CFG->dirroot . '/local/requestservices/classes/output/participantview.php');

// Instantiate the renderable
$renderable = new \local_requestservices\output\participantview($formatted_requests, $totalrequests, $perpage_options, $perpage);

// Render the participant request table using Mustache
echo $OUTPUT->render($renderable);

// Render the pagination
echo $OUTPUT->render($requestspagination);

