<?php
// =============================================================
//  Finance Services - Main Page Controller
// =============================================================

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/local/financeservices/classes/form/add_form.php');
//require_once($CFG->dirroot . '/local/financeservices/classes/form/filter_form.php');
require_once($CFG->dirroot . '/local/financeservices/classes/simple_workflow_manager.php');

use local_financeservices\simple_workflow_manager;

global $DB, $OUTPUT, $PAGE, $USER;

// ─────────────────────────────────────────────────────────────
// Setup page context
// ─────────────────────────────────────────────────────────────
$context = call_user_func(['context_system', 'instance']);
$PAGE->set_url(new moodle_url('/local/financeservices/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('financeservices', 'local_financeservices'));
$PAGE->set_heading(get_string('financeservices', 'local_financeservices'));
$PAGE->requires->css('/local/shared_styles.css');

// Load JS
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));

// ─────────────────────────────────────────────────────────────
// Global tab: add | list | manage
// ─────────────────────────────────────────────────────────────
$maintabs = [
    new tabobject('add', new moodle_url('/local/financeservices/index.php', ['tab' => 'add']),
        get_string('add', 'local_financeservices')),
    new tabobject('list', new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),
        get_string('list', 'local_financeservices')),
    new tabobject('manage', new moodle_url('/local/financeservices/index.php', ['tab' => 'manage']),
        get_string('manageservices', 'local_financeservices')),
];

$currenttab = optional_param('tab', 'add', PARAM_ALPHA);

// ─────────────────────────────────────────────────────────────
// Header + tab bar
// ─────────────────────────────────────────────────────────────
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $currenttab);

// ─────────────────────────────────────────────────────────────
// Handle each tab's content
// ─────────────────────────────────────────────────────────────
if ($currenttab === 'add') {

    $mform = new \local_financeservices\form\add_form();

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/financeservices/index.php'));

    } else if ($data = $mform->get_data()) {
        $record = new stdClass();
        $record->course_id           = $data->courseid;
        $record->funding_type_id     = $data->funding_type_id;
        $record->price_requested     = $data->price_requested;
        $record->notes               = $data->notes;
        $record->user_id             = $USER->id;
        $record->date_time_requested = time();
        $record->date_type_required  = $data->date_type_required ?? null;
        $record->status_id           = simple_workflow_manager::get_initial_status_id();
        $record->clause_id           = $data->clause_id ?? null;

        // Insert the record and get its ID
        $requestid = $DB->insert_record('local_financeservices', $record);

        // Trigger request created event
        $event = \local_financeservices\event\request_created::create([
            'context' => $context,
            'objectid' => $requestid,
            'other' => [
                'course_id' => $record->course_id,
                'funding_type_id' => $record->funding_type_id,
                'price_requested' => $record->price_requested,
                'notes' => $record->notes,
                'clause_id' => $record->clause_id
            ]
        ]);
        $event->trigger();

        redirect(new moodle_url('/local/financeservices/index.php', ['tab' => 'list']));
    } else {
        $mform->display();
    }

} else if ($currenttab === 'list') {

    // Read filters from GET params (to match Mustache filter form)
    $course_id_filter       = optional_param('course_id', '', PARAM_INT);
    $funding_type_id_filter = optional_param('funding_type_id', '', PARAM_INT);
    $status_id_filter       = optional_param('statusid', '', PARAM_INT);
    $clause_id_filter       = optional_param('clause_id', '', PARAM_INT);

    // Build dynamic WHERE conditions
    $where  = [];
    $params = [];

    if (!empty($course_id_filter)) {
        $where[] = 'fs.course_id = :course_id';
        $params['course_id'] = $course_id_filter;
    }
    if (!empty($funding_type_id_filter)) {
        $where[] = 'fs.funding_type_id = :funding_type_id';
        $params['funding_type_id'] = $funding_type_id_filter;
    }
    if (!empty($status_id_filter)) {
        $where[] = 'fs.status_id = :statusid';
        $params['statusid'] = $status_id_filter;
    }
    if (!empty($clause_id_filter)) {
        $where[] = 'fs.clause_id = :clause_id';
        $params['clause_id'] = $clause_id_filter;
    }

    $wheresql = '';
    if (!empty($where)) {
        $wheresql = 'WHERE ' . implode(' AND ', $where);
    }

    // Query requests
    $sql = "
        SELECT fs.*,
               c.fullname AS course,
               " . (current_language() === 'ar' ? "f.funding_type_ar" : "f.funding_type_en") . " AS funding_type,
               ls.display_name_en,
               ls.display_name_ar,
               cl." . (current_language() === 'ar' ? "clause_name_ar" : "clause_name_en") . " AS clause_name
          FROM {local_financeservices} fs
          JOIN {course} c ON fs.course_id = c.id
          JOIN {local_financeservices_funding_type} f ON fs.funding_type_id = f.id
          JOIN {local_status} ls ON fs.status_id = ls.id
     LEFT JOIN {local_financeservices_clause} cl ON fs.clause_id = cl.id
        $wheresql
     ORDER BY fs.date_time_requested DESC
    ";

    $records = $DB->get_records_sql($sql, $params);

    // Build filter dropdown data for Mustache template
    $lang_is_ar = current_language() === 'ar';

    // Courses (exclude site course)
    $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE visible = 1 AND id != 1');
    $courses_tpl = [];
    foreach ($courses as $course) {
        $courses_tpl[] = [
            'id'       => $course->id,
            'fullname' => format_string($course->fullname),
            'selected' => ($course_id_filter == $course->id),
        ];
    }

    // Funding types
    $funding_name_field = $lang_is_ar ? 'funding_type_ar' : 'funding_type_en';
    $funding_types = $DB->get_records_sql("SELECT id, $funding_name_field AS name FROM {local_financeservices_funding_type} WHERE deleted = 0");
    $funding_tpl = [];
    foreach ($funding_types as $ft) {
        $funding_tpl[] = [
            'id'       => $ft->id,
            'name'     => $ft->name,
            'selected' => ($funding_type_id_filter == $ft->id),
        ];
    }

    // Statuses (type_id = 2 for finance services)
    $status_name_field = $lang_is_ar ? 'display_name_ar' : 'display_name_en';
    $statuses = $DB->get_records_sql("SELECT id, $status_name_field AS name FROM {local_status} WHERE type_id = :typeid ORDER BY seq ASC", ['typeid' => 2]);
    $statuses_tpl = [];
    foreach ($statuses as $st) {
        $label = trim($st->name ?? '');
        if (preg_match('/^-+$/', $label)) { continue; }
        $statuses_tpl[] = [
            'id'       => $st->id,
            'name'     => format_string($st->name),
            'selected' => ($status_id_filter == $st->id),
        ];
    }

    // Clauses
    $clause_name_field = $lang_is_ar ? 'clause_name_ar' : 'clause_name_en';
    $clauses = $DB->get_records_sql("SELECT id, $clause_name_field AS name FROM {local_financeservices_clause} WHERE deleted = 0");
    $clauses_tpl = [];
    foreach ($clauses as $cl) {
        $clauses_tpl[] = [
            'id'       => $cl->id,
            'name'     => $cl->name,
            'selected' => ($clause_id_filter == $cl->id),
        ];
    }

    // Filter data
    $filter_data = [
        'filter_url'  => new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),
        'form_action' => new moodle_url('/local/financeservices/index.php', ['tab' => 'list']),
    ];

    $renderable = new \local_financeservices\output\tab_list($records);
    $template_data = array_merge(
        $renderable->export_for_template($OUTPUT),
        [
            'filter'       => $filter_data,
            'courses'      => $courses_tpl,
            'fundingtypes' => $funding_tpl,
            'statuses'     => $statuses_tpl,
            'clauses'      => $clauses_tpl,
        ]
    );

    echo $OUTPUT->render_from_template('local_financeservices/list', $template_data);

} else if ($currenttab === 'manage') {
    // Load the dashboard, which includes its own subtabs
    require_once($CFG->dirroot . '/local/financeservices/pages/manage.php');
}

echo $OUTPUT->footer();
