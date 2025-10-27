<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/grade/querylib.php');

$courseid = required_param('id', PARAM_INT);
$tab = optional_param('tab', 'pending', PARAM_ALPHA); // Default to pending reports tab
$course   = get_course($courseid);
$context  = context_course::instance($course->id);

require_login($course);
require_capability('local/reports:manage', $context);

/* ------------------------------------------------------------------
 * JS & page setup
 * ------------------------------------------------------------------ */
$PAGE->requires->js('/theme/stream/js/modal.js');
$PAGE->requires->js('/theme/stream/js/openreportform.js');
$PAGE->requires->js('/theme/stream/js/approve.js');
$PAGE->requires->js('/theme/stream/js/previewreport.js');



$PAGE->requires->js_init_code('window.LOCALREPORTS = {courseid:' . $courseid . '};');

$PAGE->set_url(new moodle_url('/local/reports/index.php', ['id' => $courseid, 'tab' => $tab]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_reports'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

// /* ------------------------------------------------------------------
//  * OLD Capability‐based workflow map
//  * ------------------------------------------------------------------ */
// $workflow = [
//     41 => ['next' => 42, 'capability' => 'local/status:type5_step1'],
//     42 => ['next' => 44, 'capability' => 'local/status:type5_step2'],
//     44 => ['next' => 50, 'capability' => 'local/status:type5_step3'],
// ];
// $reverse = [
//     42 => ['next' => 41, 'capability' => 'local/status:type5_step2'],
//     44 => ['next' => 42, 'capability' => 'local/status:type5_step3'],
// ];

/* ------------------------------------------------------------------
 * CURRENT Capability-based workflow map
 * ------------------------------------------------------------------ */
$workflow = [
    30 => ['next' => 31, 'capability' => 'local/status:reports_workflow_step1'],
    31 => ['next' => 32, 'capability' => 'local/status:reports_workflow_step2'],
    32 => ['next' => 33, 'capability' => 'local/status:reports_workflow_step3'],
];
$reverse = [
    31 => ['next' => 30, 'capability' => 'local/status:reports_workflow_step2'],
    32 => ['next' => 31, 'capability' => 'local/status:reports_workflow_step3'],
];

/* ------------------------------------------------------------------
 * Tabs setup
 * ------------------------------------------------------------------ */
$taburl = new moodle_url('/local/reports/index.php', ['id' => $courseid]);
$tabs = [];
$tabs[] = new tabobject('add', new moodle_url($taburl, ['tab' => 'add']), 
                      get_string('addreport', 'local_reports'));
$tabs[] = new tabobject('pending', new moodle_url($taburl, ['tab' => 'pending']), 
                      get_string('pendingreports', 'local_reports'));
$tabs[] = new tabobject('approved', new moodle_url($taburl, ['tab' => 'approved']), 
                      get_string('approvedreports', 'local_reports'));

echo $OUTPUT->tabtree($tabs, $tab);

/* ------------------------------------------------------------------
 * Build rows
 * ------------------------------------------------------------------ */
global $DB, $USER;
$rows = [];
$pending_rows = [];
$approved_rows = [];
$add_rows = [];
$enrolled = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname');
$users_with_reports = [];

foreach ($enrolled as $u) {
    // Only include users with role "trainee"
    if (!user_has_role_assignment($u->id, $DB->get_field('role', 'id', ['shortname' => 'trainee']), $context->id)) {
        continue;
    }

    $report = $DB->get_record('local_reports', [
        'courseid' => $courseid,
        'userid'   => $u->id
    ], '*', IGNORE_MISSING);
    
    // Keep track of users with reports
    if ($report) {
        $users_with_reports[$u->id] = true;
    }
    
    // For Add tab, we only care about users without reports
    if (!$report && $tab === 'add') {
        // Add form URL for creating a new report
        $addurl = new moodle_url('/local/reports/form_ajax.php', [
            'courseid' => $courseid,
            'userid'   => $u->id,
            'reportid' => 0,
            'sesskey'  => sesskey()
        ]);
        
        // course total grade
        $gradeout = '-';
        if ($g = grade_get_course_grades($courseid, $u->id)) {
            if (!empty($g->grades[$u->id]->grade)) {
                $gradeout = $g->grades[$u->id]->grade;
            }
        }
        
        $add_rows[] = [
            'course'         => $course->fullname,
            'userid'         => $u->id,
            'username'       => fullname($u),
            'grade'          => $gradeout,
            'researchtitle'  => '',
            'timemodified'   => '',
            'status'         => '',
            'actiontitle'    => get_string('add', 'local_reports'),
            'url'            => $addurl->out(false),
            'canapprove'     => false,
            'approveurl'     => '',
            'candisapprove'  => false,
            'disapproveurl'  => '',
            'canedit'        => true,
            'previewurl'     => '',
        ];
        
        continue;
    }
    
    // Skip if no report exists for the regular tabs
    if (!$report && $tab !== 'add') {
        continue;
    }

    // course total grade
    $gradeout = '-';
    if ($g = grade_get_course_grades($courseid, $u->id)) {
        if (!empty($g->grades[$u->id]->grade)) {
            $gradeout = $g->grades[$u->id]->grade;
        }
    }

    // status string
    $statusstring = '';
    if ($report && $report->status_id) {
        $statusstring = $DB->get_field(
            'local_status', 'display_name_ar',
            ['id' => $report->status_id],
            IGNORE_MISSING
        ) ?? '';
    }

    // can approve?
    $canapprove = false;
    $approveurl = '';
    if ($report && isset($workflow[$report->status_id])) {
        $cap = $workflow[$report->status_id]['capability'];
        if (has_capability($cap, $context, $USER)) {
            $canapprove = true;
            $approveurl = (new moodle_url(
                '/local/reports/approve_ajax.php',
                ['reportid' => $report->id, 'sesskey' => sesskey()]
            ))->out(false);
        }
    }

    // can disapprove?
    $candisapprove = false;
    $disapproveurl = '';
    if ($report && isset($reverse[$report->status_id])) {
        $cap = $reverse[$report->status_id]['capability'];
        if (has_capability($cap, $context, $USER)) {
            $candisapprove = true;
            $disapproveurl = (new moodle_url(
                '/local/reports/disapprove_ajax.php',
                ['reportid' => $report->id, 'sesskey' => sesskey()]
            ))->out(false);
        }
    }

    // edit/add form URL
    $editurl = new moodle_url('/local/reports/form_ajax.php', [
        'courseid' => $courseid,
        'userid'   => $u->id,
        'reportid' => $report->id ?? 0,
        'sesskey'  => sesskey()
    ]);
    $previewurl = new moodle_url('/local/reports/form_ajax.php', [
        'courseid' => $courseid,
        'userid'   => $u->id,
        'reportid' => $report->id ?? 0,
        'preview'  => 1,          //  ← tells script to freeze
        'sesskey'  => sesskey()
    ]);
    
    $canedit = empty($report) || ($report->status_id == 30);

    $row = [
        'course'         => $course->fullname,
        'userid'         => $u->id,
        'username'       => fullname($u),
        'grade'          => $gradeout,
        'researchtitle'  => $report->researchtitle ?? '',
        'timemodified'   => $report ? userdate($report->timemodified) : '',
        'status'         => $statusstring,
        'actiontitle'    => $report ? get_string('edit', 'local_reports') : get_string('add', 'local_reports'),
        'url'            => $editurl->out(false),
        'canapprove'     => $canapprove,
        'approveurl'     => $approveurl,
        'candisapprove'  => $candisapprove,
        'disapproveurl'  => $disapproveurl,
        'canedit'        => $canedit,
        'previewurl'     => $previewurl->out(false),
    ];
    
    // Separate into pending and approved
    if ($report->status_id == 33) {
        $approved_rows[] = $row;
    } else {
        $pending_rows[] = $row;
    }
}

// Check if there are any users without reports for the "Add" tab
if ($tab === 'add' && empty($add_rows)) {
    echo html_writer::div(
        get_string('allstudentshavereports', 'local_reports'), 
        'alert alert-info'
    );
} else {
    // Display appropriate rows based on the active tab
    if ($tab === 'pending') {
        $renderable = new \local_reports\output\reports_list($pending_rows);
        echo $OUTPUT->render($renderable);
    } else if ($tab === 'approved') {
        $renderable = new \local_reports\output\reports_list($approved_rows);
        echo $OUTPUT->render($renderable);
    } else if ($tab === 'add') {
        $renderable = new \local_reports\output\reports_list($add_rows);
        echo $OUTPUT->render($renderable);
    }
}

echo $OUTPUT->footer();
