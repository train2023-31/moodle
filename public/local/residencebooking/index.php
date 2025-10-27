<?php
/**
 * Residence Booking – Main Page
 * --------------------------------------------------------------
 * Tabs:
 *   • Apply   – submit a new request
 *   • Manage  – list / filter / approve / reject
 *   • Lookups – admin‑only dashboard to maintain Types & Purposes
 *
 * Integrates with local_status (workflow engine, type_id = 3).
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/residencebooking/classes/form/residencebooking_form.php');
require_once($CFG->dirroot . '/local/residencebooking/classes/output/manage_requests_table.php');
require_once($CFG->dirroot . '/local/residencebooking/classes/simple_workflow_manager.php');

// -----------------------------------------------------------------------------
// Page setup
// -----------------------------------------------------------------------------
$context = context_system::instance();
$PAGE->set_url(new moodle_url('/local/residencebooking/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_residencebooking'));
$PAGE->set_heading(get_string('pluginname', 'local_residencebooking'));

$PAGE->requires->css('/local/shared_styles.css');
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));

// Removed old AMD module call - now using web service autocomplete

require_login();
require_capability('local/residencebooking:viewbookings', $context);

// -----------------------------------------------------------------------------
// Tabs definition
// -----------------------------------------------------------------------------
$tab  = optional_param('tab', 'form', PARAM_ALPHA);
$tabs = [
    new tabobject('form',   new moodle_url('/local/residencebooking/index.php', ['tab' => 'form']),    get_string('applyrequests',   'local_residencebooking')),
    new tabobject('manage', new moodle_url('/local/residencebooking/index.php', ['tab' => 'manage']),  get_string('managerequests', 'local_residencebooking')),
    new tabobject('lookups',new moodle_url('/local/residencebooking/index.php', ['tab' => 'lookups']), get_string('management',     'local_residencebooking')),
];

// -----------------------------------------------------------------------------
// Build the booking form up‑front (needed for Apply tab processing)
// -----------------------------------------------------------------------------
$mform = new \local_residencebooking\form\residencebooking_form(
    new moodle_url('/local/residencebooking/index.php', ['tab' => 'form'])
);

// -----------------------------------------------------------------------------
// Handle form submission (Apply tab)
// -----------------------------------------------------------------------------
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/residencebooking/index.php', ['tab' => 'form']));

} else if ($data = $mform->get_data()) {
    $now = time();
    $record = (object) [
        'courseid'       => $data->courseid,
        'residence_type' => $data->residence_type,
        'start_date'     => $data->start_date,
        'end_date'       => $data->end_date,
        'purpose'        => $data->purpose,
        'notes'          => $data->notes,
        'guest_name'     => $data->guest_name,  // Now contains full name directly from autocomplete
        'service_number' => $data->service_number,
        'userid'         => $data->userid,
        'status_id'      => \local_residencebooking\simple_workflow_manager::get_initial_status_id(), // workflow start
        'timecreated'    => $now,
        'timemodified'   => $now,
        'created_by'     => $USER->id,
        'modified_by'    => $USER->id,
    ];

    $DB->insert_record('local_residencebooking_request', $record);

    redirect(
        new moodle_url('/local/residencebooking/index.php', ['tab' => 'form']),
        get_string('requestsubmitted', 'local_residencebooking'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// -----------------------------------------------------------------------------
// Header & tab navigation
// -----------------------------------------------------------------------------

echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);

// -----------------------------------------------------------------------------
// TAB: Apply – display the form
// -----------------------------------------------------------------------------
if ($tab === 'form') {
    $mform->display();
    
    // Initialize guest autocomplete with service number population
    $PAGE->requires->js_call_amd('local_residencebooking/guest_autocomplete', 'initAutocomplete', ['#id_guest_name', '#id_service_number']);
}

// -----------------------------------------------------------------------------
// TAB: Manage – list & filter requests
// -----------------------------------------------------------------------------
elseif ($tab === 'manage') {

    $status_filter         = optional_param('status',         0,  PARAM_INT);
    $course_id_filter      = optional_param('course_id',      '', PARAM_TEXT);
    $guest_name_filter     = optional_param('guest_name',     '', PARAM_TEXT);
    $service_number_filter = optional_param('service_number', '', PARAM_TEXT);
    $start_date_filter     = optional_param('start_date',     '', PARAM_TEXT);
    $end_date_filter       = optional_param('end_date',       '', PARAM_TEXT);
    $residence_type_filter = optional_param('residence_type', 0,  PARAM_INT);
    $purpose_filter        = optional_param('purpose',        0,  PARAM_INT);

    $lang_is_ar = current_language() === 'ar';

    // Build status dropdown (skip placeholders)
    $statuses_tpl = [];
    foreach (\local_residencebooking\simple_workflow_manager::get_all_statuses() as $st) {
        $label = $lang_is_ar ? $st->display_name_ar : $st->display_name_en;
        if (preg_match('/^-+$/', trim($label ?? ''))) { continue; }
        $statuses_tpl[] = [
            'id'       => $st->id,
            'name'     => format_string($label),
            'selected' => ($status_filter == $st->id),
        ];
    }

    // Courses dropdown (exclude site course)
    $table      = new manage_requests_table();
    $allcourses = array_filter($table->get_all_courses_for_filter(), fn($c) => $c->id != 1);

    $courses_tpl = [];
    foreach ($allcourses as $cid => $course) {
        $courses_tpl[] = [
            'id'       => $cid,
            'fullname' => $course->fullname,
            'selected' => ($course_id_filter == $cid),
        ];
    }
    
    // Residence types dropdown
    $types_tpl = [];
    $alltypes = $table->get_all_residence_types_for_filter();
    foreach ($alltypes as $tid => $type) {
        $types_tpl[] = [
            'id'       => $tid,
            'name'     => $type->name,
            'selected' => ($residence_type_filter == $tid),
        ];
    }
    
    // Purposes dropdown
    $purposes_tpl = [];
    $allpurposes = $table->get_all_purposes_for_filter();
    foreach ($allpurposes as $pid => $purpose) {
        $purposes_tpl[] = [
            'id'       => $pid,
            'name'     => $purpose->name,
            'selected' => ($purpose_filter == $pid),
        ];
    }

    // Fetch requests with pagination
    $page    = optional_param('page', 0, PARAM_INT);
    $perpage = 10;

    $table_data = $table->get_requests_data(
        $page, $perpage,
        $status_filter,
        $guest_name_filter,
        $service_number_filter,
        $start_date_filter,
        $end_date_filter,
        $course_id_filter,
        $residence_type_filter,
        $purpose_filter
    );

    // Filter form values for template
    $filter_data = [
        'guest_name_filter'     => s($guest_name_filter),
        'service_number_filter' => s($service_number_filter),
        'start_date_filter'     => s($start_date_filter),
        'end_date_filter'       => s($end_date_filter),
        'filter_url'            => new moodle_url('/local/residencebooking/index.php', ['tab' => 'manage']),
        'form_action'           => new moodle_url('/local/residencebooking/index.php', ['tab' => 'manage']),
    ];

    // Render Mustache template
    $data = array_merge($table_data, [
        'filter'   => $filter_data,
        'courses'  => $courses_tpl,
        'statuses' => $statuses_tpl,
        'types'    => $types_tpl,
        'purposes' => $purposes_tpl,
    ]);

    echo $OUTPUT->render_from_template('local_residencebooking/view_requests', $data);
}

// -----------------------------------------------------------------------------
// TAB: Lookups – admin dashboard (Types & Purposes)
// -----------------------------------------------------------------------------
elseif ($tab === 'lookups') {
    // Only site administrators can access
    require_capability('moodle/site:config', $context);
    include($CFG->dirroot . '/local/residencebooking/pages/manage.php');
}




// get_fullname_from_oracle function removed - guest name now comes directly from autocomplete

// -----------------------------------------------------------------------------
// Footer
// -----------------------------------------------------------------------------

echo $OUTPUT->footer();
