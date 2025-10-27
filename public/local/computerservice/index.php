<?php
// ==============================================================================
//  Computer Service – main entry page
// ==============================================================================

require_once('../../config.php');
require_once($CFG->dirroot . '/local/computerservice/classes/form/request_form.php');
require_once($CFG->dirroot . '/local/computerservice/classes/form/filter_form.php');
require_once($CFG->dirroot . '/local/computerservice/classes/output/manage_requests.php');
require_once($CFG->dirroot . '/local/computerservice/classes/simple_workflow_manager.php');

// Ensure the user is logged in.
require_login();
$context = context_system::instance();

// Set up the page metadata.
$PAGE->set_url('/local/computerservice/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('computerservice', 'local_computerservice'));
$PAGE->set_heading(get_string('computerservice', 'local_computerservice'));

// Include necessary CSS and JavaScript files.
$PAGE->requires->css('/local/shared_styles.css');
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));

$tab = optional_param('tab', 'form', PARAM_ALPHA);

$tabs = [
    new tabobject('form',    new moodle_url('/local/computerservice/index.php', ['tab' => 'form']),    get_string('requestdevices', 'local_computerservice')),
    new tabobject('manage',  new moodle_url('/local/computerservice/index.php', ['tab' => 'manage']),  get_string('managerequests', 'local_computerservice')),
    new tabobject('devices', new moodle_url('/local/computerservice/index.php', ['tab' => 'devices']), get_string('managedevices',  'local_computerservice')),
];

// Output the page header and navigation tabs.
echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, $tab);

// ──────────────────────────────────────────────────────────────────────────────
// TAB: "Request devices"
// ──────────────────────────────────────────────────────────────────────────────
if ($tab === 'form') {

    $mform = new \local_computerservice\form\request_form(
        new moodle_url('/local/computerservice/index.php', ['tab' => 'form'])
    );

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/computerservice/index.php', ['tab' => 'form']));

    } elseif ($data = $mform->get_data()) {

        // Resolve the language-specific device name from its ID.
        $device = $DB->get_record('local_computerservice_devices',
                                  ['id' => $data->deviceid],
                                  'devicename_en, devicename_ar',
                                  MUST_EXIST);

        $selecteddevicename = (current_language() === 'ar')
            ? $device->devicename_ar
            : $device->devicename_en;

        // Get default status ID from workflow
        $defaultstatusid = \local_computerservice\simple_workflow_manager::get_initial_status_id();

        // ----------------------------------------------------------------------
        // Build and insert the request.
        // ----------------------------------------------------------------------
        $now     = time();
        $needed  = $data->request_needed_by;
        // OLD CODE: Requests needed within 48 hours (2 days) were marked urgent
        // $is_urgent = (($needed - $now) < 2 * DAYSECS) ? 1 : 0;
        // NEW CODE: Only requests needed today or tomorrow are marked urgent
        $is_urgent = (($needed - $now) < DAYSECS) ? 1 : 0;

        $record = (object)[
            'userid'            => $USER->id,
            'courseid'          => $data->courseid,
            'deviceid'          => $data->deviceid,
            'numdevices'        => $data->numdevices,
            'comments'          => $data->comments,
            'status_id'         => $defaultstatusid,
            'timecreated'       => $now,
            'timemodified'      => $now,
            'request_needed_by' => $needed,
            'is_urgent'      => $is_urgent,
        ];

        $DB->insert_record('local_computerservice_requests', $record);

        redirect(
            new moodle_url('/local/computerservice/index.php', ['tab' => 'form']),
            get_string('requestsubmitted', 'local_computerservice'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    $mform->display();

// ──────────────────────────────────────────────────────────────────────────────
// TAB: "Manage requests"
// ──────────────────────────────────────────────────────────────────────────────
} elseif ($tab === 'manage') {

    define('FROM_REQUEST_PHP', true);
    require_capability('local/computerservice:managerequests', $context);
    include($CFG->dirroot . '/local/computerservice/pages/manage.php');

// ──────────────────────────────────────────────────────────────────────────────
// TAB: "Manage devices"
// ──────────────────────────────────────────────────────────────────────────────
} elseif ($tab === 'devices') {

    require_capability('local/computerservice:manage_devices', $context);
    include($CFG->dirroot . '/local/computerservice/pages/device_management.php');
}

echo $OUTPUT->footer();
