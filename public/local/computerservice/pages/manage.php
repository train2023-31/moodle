<?php
// ============================================================================
//  Management view – filter, view, approve, and reject device requests
// ============================================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/computerservice/classes/form/filter_form.php');
require_once($CFG->dirroot . '/local/computerservice/classes/simple_workflow_manager.php');

$fromrequestphp = defined('FROM_REQUEST_PHP') && FROM_REQUEST_PHP;

if (!$fromrequestphp) {
    require_login();
    $context = context_system::instance();
    require_capability('local/computerservice:managerequests', $context);

    $PAGE->set_url('/local/computerservice/manage.php');
    $PAGE->set_context($context);
    $PAGE->set_title(get_string('managerequests', 'local_computerservice'));
    $PAGE->set_heading(get_string('managerequests', 'local_computerservice'));
} else {
    $context = context_system::instance();
}

/* ───────────────────────────── LIST + FILTER ───────────────────────────── */

// Read filters from GET params (to match Mustache filter form)
$status_id_filter = optional_param('statusid', '', PARAM_INT);
$course_id_filter = optional_param('courseid', '', PARAM_INT);
$user_id_filter = optional_param('userid', '', PARAM_INT);
$urgency_filter = optional_param('urgency', '', PARAM_TEXT);

// Build dynamic WHERE conditions
$conditions = [];
$params = [];

if (!empty($status_id_filter)) {
    $conditions[] = 'r.status_id = :status_id';
    $params['status_id'] = $status_id_filter;
}
if (!empty($course_id_filter)) {
    $conditions[] = 'r.courseid = :courseid';
    $params['courseid'] = $course_id_filter;
}
if (!empty($user_id_filter)) {
    $conditions[] = 'r.userid = :userid';
    $params['userid'] = $user_id_filter;
}
if ($urgency_filter !== '') {
    $conditions[] = 'r.is_urgent = :is_urgent';
    $params['is_urgent'] = (int)$urgency_filter;
}

$sqlwhere = '';
if (!empty($conditions)) {
    $sqlwhere = 'WHERE ' . implode(' AND ', $conditions);
}

$langfield = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';

$sql = "SELECT r.*,
               c.fullname AS course_name,
               s.$langfield AS statusname
          FROM {local_computerservice_requests} r
          JOIN {course}                 c ON c.id = r.courseid
          JOIN {local_status}           s ON s.id = r.status_id
        $sqlwhere
      ORDER BY r.timecreated DESC";

$requests = $DB->get_records_sql($sql, $params);

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

// Statuses (type_id = 4 for computer service requests)
$status_name_field = $lang_is_ar ? 'display_name_ar' : 'display_name_en';
$statuses = $DB->get_records_sql("SELECT id, $status_name_field AS name FROM {local_status} WHERE type_id = :typeid ORDER BY seq ASC", ['typeid' => 4]);
$statuses_tpl = [];
foreach ($statuses as $st) {
    $label = trim($st->name ?? '');
    if (preg_match('/^-+$/', $label)) { continue; }
    $statuses_tpl[] = [
        'id' => $st->id,
        'name' => format_string($st->name),
        'selected' => ($status_id_filter == $st->id),
    ];
}

// Users
$users = $DB->get_records_sql("SELECT id, CONCAT(firstname, ' ', lastname) AS fullname FROM {user} ORDER BY lastname ASC, firstname ASC");
$users_tpl = [];
foreach ($users as $user) {
    $users_tpl[] = [
        'id' => $user->id,
        'fullname' => $user->fullname,
        'selected' => ($user_id_filter == $user->id),
    ];
}

// Urgency options
$urgency_options = [
    ['value' => '', 'label' => get_string('allrequests', 'local_computerservice'), 'selected' => ($urgency_filter === '')],
    ['value' => '1', 'label' => get_string('urgent', 'local_computerservice'), 'selected' => ($urgency_filter === '1')],
    ['value' => '0', 'label' => get_string('not_urgent', 'local_computerservice'), 'selected' => ($urgency_filter === '0')],
];

// Filter data
$filter_data = [
    'form_action' => new moodle_url('/local/computerservice/index.php', ['tab' => 'manage']),
];

$renderable = new \local_computerservice\output\manage_requests($requests);

/* ───────────────────────────── OUTPUT ───────────────────────────── */

// Always render the content, regardless of FROM_REQUEST_PHP
if (!$fromrequestphp) {
    echo $OUTPUT->header();
}

// Prepare template data with filter information
$template_data = array_merge(
    $renderable->export_for_template($OUTPUT),
    [
        'filter' => $filter_data,
        'courses' => $courses_tpl,
        'statuses' => $statuses_tpl,
        'users' => $users_tpl,
        'urgency_options' => $urgency_options,
    ]
);

$rendered = $OUTPUT->render_from_template('local_computerservice/manage_requests', $template_data);
echo $rendered;

if (!$fromrequestphp) {
    echo $OUTPUT->footer();
}
