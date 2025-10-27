<?php
// allreports.php — list **all** reports across **all** courses
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once(__DIR__ . '/lib.php');           // bring in $workflow and $reverse arrays

$tab = optional_param('tab', 'pending', PARAM_ALPHA); // Default to pending reports tab
$coursename = optional_param('coursename', '', PARAM_TEXT); // Filter by course name
$studentname = optional_param('studentname', '', PARAM_TEXT); // Filter by student name

$syscontext = context_system::instance();
require_login();
require_capability('local/status:reports_workflow_step3', $syscontext);

// JS & page setup (same as index.php)
$PAGE->requires->js('/theme/stream/js/modal.js');
$PAGE->requires->js('/theme/stream/js/openreportform.js');
$PAGE->requires->js('/theme/stream/js/approve.js');
$PAGE->requires->js('/theme/stream/js/previewreport.js');

// Initialize JavaScript variables - for allreports we set a flag to indicate we're in the all reports page
$PAGE->requires->js_init_code('window.LOCALREPORTS = {isAllReports: true};');

$PAGE->set_url(new moodle_url('/local/reports/allreports.php', ['tab' => $tab, 'coursename' => $coursename, 'studentname' => $studentname]));
$PAGE->set_context($syscontext);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_reports') . ' • ' . get_string('allreports', 'local_reports'));
$PAGE->set_heading(get_string('allreports', 'local_reports'));

echo $OUTPUT->header();

/* ------------------------------------------------------------------
 * Tabs setup
 * ------------------------------------------------------------------ */
$taburl = new moodle_url('/local/reports/allreports.php');
$tabs = [];
$tabs[] = new tabobject('pending', new moodle_url($taburl, ['tab' => 'pending', 'coursename' => $coursename, 'studentname' => $studentname]), 
                      get_string('pendingreports', 'local_reports'));
$tabs[] = new tabobject('approved', new moodle_url($taburl, ['tab' => 'approved', 'coursename' => $coursename, 'studentname' => $studentname]), 
                      get_string('approvedreports', 'local_reports'));
echo $OUTPUT->tabtree($tabs, $tab);

/* ------------------------------------------------------------------
 * Filters
 * ------------------------------------------------------------------ */
global $DB, $USER;

// Output filter form
echo '<div class="filter-container mb-3">';
echo '<form method="get" action="' . $PAGE->url . '" class="form-inline">';
echo '<input type="hidden" name="tab" value="' . $tab . '">';

echo '<div class="form-group mr-2">';
echo '<label for="coursename" class="mr-1">' . get_string('course', 'local_reports') . ': </label>';
echo '<input type="text" name="coursename" id="coursename" value="' . s($coursename) . '" placeholder="' . get_string('course', 'local_reports') . '" class="form-control" style="width: 200px;">';
echo '</div>';

echo '<div class="form-group mr-2">';
echo '<label for="studentname" class="mr-1">' . get_string('user', 'local_reports') . ': </label>';
echo '<input type="text" name="studentname" id="studentname" value="' . s($studentname) . '" placeholder="' . get_string('user', 'local_reports') . '" class="form-control" style="width: 200px;">';
echo '</div>';

echo '<button type="submit" class="btn btn-primary">' . get_string('filter') . '</button>';
echo '<a href="' . new moodle_url('/local/reports/allreports.php', ['tab' => $tab]) . '" class="btn btn-secondary ml-2">' . get_string('clear') . '</a>';
echo '</form>';
echo '</div>';

/* ------------------------------------------------------------------
 * OLD Capability‐based workflow map
 * ------------------------------------------------------------------ */
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

// Fetch reports with filters
$params = [];
$sql = "SELECT r.* FROM {local_reports} r
        JOIN {course} c ON c.id = r.courseid
        JOIN {user} u ON u.id = r.userid
        WHERE 1=1";

if (!empty($coursename)) {
    $sql .= " AND " . $DB->sql_like('c.fullname', ':coursename', false);
    $params['coursename'] = '%' . $DB->sql_like_escape($coursename) . '%';
}

if (!empty($studentname)) {
    $sql .= " AND (" . $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname'), ':studentname', false) . 
            " OR " . $DB->sql_like('u.firstname', ':studentname2', false) . 
            " OR " . $DB->sql_like('u.lastname', ':studentname3', false) . ")";
    $params['studentname'] = '%' . $DB->sql_like_escape($studentname) . '%';
    $params['studentname2'] = '%' . $DB->sql_like_escape($studentname) . '%';
    $params['studentname3'] = '%' . $DB->sql_like_escape($studentname) . '%';
}

$allrecords = $DB->get_records_sql($sql, $params);

$pending_rows = [];
$approved_rows = [];

foreach ($allrecords as $rec) {
    $user   = $DB->get_record('user', ['id' => $rec->userid], '*', MUST_EXIST);
    $course = get_course($rec->courseid);
    $ccontext = context_course::instance($course->id);

    // course grade for this user
    $gradeout = '-';
    $grades = grade_get_course_grades($course->id, $user->id);
    if (!empty($grades->grades[$user->id]->grade)) {
        $gradeout = $grades->grades[$user->id]->grade;
    }

    // status string (Arabic)
    $statusstring = '';
    if ($rec->status_id) {
        $statusstring = $DB->get_field(
            'local_status', 'display_name_ar',
            ['id' => $rec->status_id],
            IGNORE_MISSING
        ) ?: '';
    }

    // Can Approve?
    $canapprove = false;
    $approveurl = '';
    if (!empty($workflow[$rec->status_id])) {
        $cap = $workflow[$rec->status_id]['capability'];
        if (has_capability($cap, $ccontext, $USER)) {
            $canapprove   = true;
            $approveurl   = (new moodle_url(
                '/local/reports/approve_ajax.php',
                ['reportid' => $rec->id, 'sesskey' => sesskey()]
            ))->out(false);
        }
    }

    // Can Disapprove?
    $candisapprove = false;
    $disapproveurl = '';
    if (!empty($reverse[$rec->status_id])) {
        $cap = $reverse[$rec->status_id]['capability'];
        if (has_capability($cap, $ccontext, $USER)) {
            $candisapprove = true;
            $disapproveurl = (new moodle_url(
                '/local/reports/disapprove_ajax.php',
                ['reportid' => $rec->id, 'sesskey' => sesskey()]
            ))->out(false);
        }
    }

    // Can Edit (status 41 = draft)
    $canedit = ($rec->status_id == 41);
    $formurl = (new moodle_url('/local/reports/form_ajax.php', [
        'courseid' => $course->id,
        'userid'   => $user->id,
        'reportid' => $rec->id,
        'sesskey'  => sesskey()
    ]))->out(false);
    $previewurl = (new moodle_url('/local/reports/form_ajax.php', [
        'courseid' => $course->id,
        'userid'   => $user->id,
        'reportid' => $rec->id,
        'preview'  => 1,
        'sesskey'  => sesskey()
    ]))->out(false);

    $row = [
        'course'        => $course->fullname,
        'userid'        => $user->id,
        'username'      => fullname($user),
        'grade'         => $gradeout,
        'researchtitle' => $rec->researchtitle,
        'timemodified'  => userdate($rec->timemodified),
        'status'        => $statusstring,
        'actiontitle'   => $canedit ? get_string('edit', 'local_reports') : get_string('edit', 'local_reports'),
        'url'           => $formurl,
        'previewurl'    => $previewurl,
        'canedit'       => $canedit,
        'canapprove'    => $canapprove,
        'approveurl'    => $approveurl,
        'candisapprove' => $candisapprove,
        'disapproveurl' => $disapproveurl,
    ];
    
    // Separate into pending and approved
    if ($rec->status_id == 33) {
        $approved_rows[] = $row;
    } else {
        $pending_rows[] = $row;
    }
}

// Add filter information to the approved/disapproved buttons
echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var urlParams = "coursename=' . urlencode($coursename) . '&studentname=' . urlencode($studentname) . '";
        var approveAllBtn = document.getElementById("approve-all-btn");
        var disapproveAllBtn = document.getElementById("disapprove-all-btn");
        
        if (approveAllBtn) {
            approveAllBtn.setAttribute("data-filter-params", urlParams);
        }
        
        if (disapproveAllBtn) {
            disapproveAllBtn.setAttribute("data-filter-params", urlParams);
        }
    });
</script>';

// Display appropriate rows based on the active tab
if ($tab === 'pending') {
    $renderable = new \local_reports\output\reports_list($pending_rows);
    echo $OUTPUT->render($renderable);
} else {
    $renderable = new \local_reports\output\reports_list($approved_rows);
    echo $OUTPUT->render($renderable);
}

echo $OUTPUT->footer();
