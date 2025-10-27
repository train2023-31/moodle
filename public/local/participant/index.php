<?php
require_once('../../config.php');
require_once('classes/form/requests_filter_form.php'); // Include the filter form
require_once('classes/simple_workflow_manager.php');

require_once(__DIR__ . '/../oracleFetch/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/participant:view', $context);

$PAGE->set_url('/local/participant/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_participant'));
$PAGE->set_heading(get_string('pluginname', 'local_participant'));
$PAGE->requires->css('/local/shared_styles.css');
$PAGE->requires->js(new moodle_url('/local/participant/js/main.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));

$defaultperpage = 10;
$perpage = optional_param('perpage', $SESSION->perpage ?? $defaultperpage, PARAM_INT);

$SESSION->perpage = $perpage;
$requestspage = optional_param('requestspage', 0, PARAM_INT);

// Define the tabs
$tabs = [];
$tabs[] = new tabobject('addrequest', new moodle_url('/local/participant/add_request.php'), get_string('addrequest', 'local_participant'));
$tabs[] = new tabobject('viewrequests', new moodle_url('/local/participant/index.php'), get_string('viewrequests', 'local_participant'));


// Output header and tabs
echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, 'viewrequests');

// Read filters from GET params (to match Mustache filter form)
$course_id_filter = optional_param('course_id', '', PARAM_INT);
$participant_type_id_filter = optional_param('participant_type_id', '', PARAM_INT);
$request_status_id_filter = optional_param('request_status_id', '', PARAM_INT);

// Build dynamic WHERE conditions
$where = [];
$params = [];

if (!empty($course_id_filter)) {
    $where[] = 'pr.course_id = :course_id';
    $params['course_id'] = $course_id_filter;
}
if (!empty($participant_type_id_filter)) {
    $where[] = 'pr.participant_type_id = :participant_type_id';
    $params['participant_type_id'] = $participant_type_id_filter;
}
if (!empty($request_status_id_filter)) {
    $where[] = 'pr.request_status_id = :request_status_id';
    $params['request_status_id'] = $request_status_id_filter;
}

$wheresql = '';
if (!empty($where)) {
    $wheresql = 'WHERE ' . implode(' AND ', $where);
}

// Query requests with joins for better data
$sql = "
    SELECT pr.*,
           c.fullname AS course_name,
           apt.name_en AS participant_type_en,
           apt.name_ar AS participant_type_ar,
           ls.display_name_en AS status_name_en,
           ls.display_name_ar AS status_name_ar
      FROM {local_participant_requests} pr
      JOIN {course} c ON pr.course_id = c.id
      JOIN {local_participant_request_types} apt ON pr.participant_type_id = apt.id
      JOIN {local_status} ls ON pr.request_status_id = ls.id
    $wheresql
  ORDER BY pr.time_created DESC
";

$requests = $DB->get_records_sql($sql, $params, $requestspage * $perpage, $perpage);

// Count total requests for pagination
$count_sql = "
    SELECT COUNT(*)
      FROM {local_participant_requests} pr
      JOIN {course} c ON pr.course_id = c.id
      JOIN {local_participant_request_types} apt ON pr.participant_type_id = apt.id
      JOIN {local_status} ls ON pr.request_status_id = ls.id
    $wheresql
";
$totalrequests = $DB->count_records_sql($count_sql, $params);

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
    // Fetch additional related data (course, status, participant)
    $course = $DB->get_record('course', ['id' => $request->course_id], 'fullname');
    $plan = $DB->get_record('local_annual_plan', ['id' => $request->annual_plan_id], 'title');
    $status_name = \local_participant\simple_workflow_manager::get_status_name($request->request_status_id);
    $type = $DB->get_record('local_participant_request_types', ['id' => $request->participant_type_id], 'name_en, name_ar');
    $orgname = 'N/A';
    $participant_name = 'N/A';

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
            $participant_name = oracle_get_employee_name($request->pf_number);
        }
        $orgname = '-';
    }

    // Fetch creator information
    $creator_name = 'N/A';
    if (!empty($request->created_by)) {
        $creator = $DB->get_record('user', ['id' => $request->created_by], 'firstname, lastname, username');
        $creator_name = $creator ? fullname($creator) : 'N/A';
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
        'created_by' => $creator_name,
        'time_created' => !empty($request->time_created) ? date('d-m-y H:i', $request->time_created) : 'N/A',
    ];
}

// Build filter dropdown data for Mustache template
$lang_is_ar = current_language() === 'ar';

// Courses (exclude site course)
$courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE visible = 1 AND id != 1');
$courses_tpl = [];
foreach ($courses as $course) {
    $courses_tpl[] = [
        'id' => $course->id,
        'fullname' => format_string($course->fullname),
        'selected' => ($course_id_filter == $course->id),
    ];
}

// Participant types
$participant_types = $DB->get_records('local_participant_request_types', null, '', 'id, name_en, name_ar');
$participant_types_tpl = [];
foreach ($participant_types as $pt) {
    $name = $lang_is_ar ? $pt->name_ar : $pt->name_en;
    $participant_types_tpl[] = [
        'id' => $pt->id,
        'name' => $name,
        'selected' => ($participant_type_id_filter == $pt->id),
    ];
}

// Statuses (type_id = 9 for participant requests)
$status_name_field = $lang_is_ar ? 'display_name_ar' : 'display_name_en';
$statuses = $DB->get_records_sql("SELECT id, $status_name_field AS name FROM {local_status} WHERE type_id = :typeid ORDER BY seq ASC", ['typeid' => 9]);
$statuses_tpl = [];
foreach ($statuses as $st) {
    $label = trim($st->name ?? '');
    if (preg_match('/^-+$/', $label)) { continue; }
    $statuses_tpl[] = [
        'id' => $st->id,
        'name' => format_string($st->name),
        'selected' => ($request_status_id_filter == $st->id),
    ];
}

// Filter data
$filter_data = [
    'form_action' => new moodle_url('/local/participant/index.php'),
];

// Prepare data for Mustache template
$template_data = [
    'filter' => $filter_data,
    'courses' => $courses_tpl,
    'participanttypes' => $participant_types_tpl,
    'statuses' => $statuses_tpl,
    'requests' => $formatted_requests,
    'requestspagination' => $OUTPUT->render($requestspagination),
    'perpage_options' => $perpage_options,
    'perpage' => $perpage,
    'totalrequests' => $totalrequests,
];

// Render the participant request table using Mustache
echo $OUTPUT->render_from_template('local_participant/view_requests', $template_data);

// Output footer
echo $OUTPUT->footer();
