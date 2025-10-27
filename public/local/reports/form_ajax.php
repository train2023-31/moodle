<?php
/***********************************************************************
 * form_ajax.php  —  returns an mform + grade-table fragment (GET) or
 *                   processes the form (POST)   ·   Moodle 4.x
 *
 * ADDITION:  if called with &preview=1 the form is rendered read-only
 *            (no POST will ever be triggered).
 ***********************************************************************/

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir  . '/pdflib.php');  
/* ── core grade libs so we can show the user grade table ─────────── */
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/user/lib.php');
$PAGE->requires->js('/theme/stream/js/modal.js');
$PAGE->requires->js('/theme/stream/js/openreportform.js');
$PAGE->requires->js('/theme/stream/js/approve.js');
$PAGE->requires->js('/theme/stream/js/previewreport.js');



$PAGE->requires->js_init_code('window.LOCALREPORTS = {courseid:' . $courseid . '};');


$pdf = new pdf();
/* ── required params ─────────────────────────────────────────────── */
$courseid = required_param('courseid', PARAM_INT);
$userid   = required_param('userid',   PARAM_INT);
$reportid = optional_param('reportid', 0,          PARAM_INT);
$preview  = optional_param('preview',  0,          PARAM_BOOL); // NEW

/* ── security ─────────────────────────────────────────────────────── */
$course   = get_course($courseid);           // throws if not found
require_login($course);
$context  = context_course::instance($courseid);
require_capability('local/reports:manage', $context);

/* ── build the moodleform ─────────────────────────────────────────── */
require_once($CFG->dirroot . '/local/reports/classes/form/report_form.php');
/* --- NEW: we may be downloading even in preview mode ------------- */
$download = optional_param('download', 0, PARAM_BOOL);

/*  $editable = false → Moodle renders every element disabled.        */
$editable = $preview && !$download ? false : true;


$customdata = ['courseid' => $courseid,
               'userid'   => $userid,
               'reportid' => $reportid];

/*  $editable = false → Moodle renders every element disabled.        */
$editable = $preview ? false : true;

$mform = new \local_reports\form\report_form(
            null,           // action (null = self)
            $customdata,
            'post',         // method
            '',             // target
            null,           // attributes
            $editable       // ← key line for read-only preview
         );

/* strip Save / Cancel when read-only */
if (!$editable) {

}

/* if the record exists, seed defaults (works for edit & preview) */
if ($reportid) {
    $record = $DB->get_record('local_reports',
                ['id' => $reportid], '*', MUST_EXIST);
    $mform->set_data($record);
}

/* ───────────────────────────────────────────────────────────────────
 *  GET  → return HTML fragment:  mform + grade table
 * ───────────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ob_start();

    /* form HTML */
    $mform->display();

    /* ensure course final grades are up-to-date */
    grade_regrade_final_grades_if_required($course);

    /* build the core "User report" grade table */
    $gpr = new grade_plugin_return([
        'type'     => 'report',
        'plugin'   => 'user',
        'courseid' => $courseid,
        'userid'   => $userid
    ]);

    $userreport = new \gradereport_user\report\user(
        $courseid, $gpr, $context, $userid, false
    );

    if ($userreport->fill_table()) {
        echo html_writer::tag('h3', get_string('Grade', 'local_reports'));
        echo $userreport->print_table(true);
    
// Fetch all grade items and grades for the current user
// Fetch all grade items for the course
$items = grade_item::fetch_all(['courseid' => $courseid]);

$labels = [];
$studentgrades = [];
$maxgrades = [];
$mediangrades = [];
$possiblegrades = [];

foreach ($items as $item) {
    $name = $item->get_name();

    // Skip totals and empty names
    if (!$name || stripos($name, 'total') !== false) {
        continue;
    }

    // Skip ungradable items (type none)
    if ($item->gradetype == GRADE_TYPE_NONE) {
        continue;
    }

    // Get current student's grade
    $usergrade = grade_grade::fetch(['itemid' => $item->id, 'userid' => $userid]);
    $gradeval = $usergrade && is_numeric($usergrade->finalgrade) ? (float)$usergrade->finalgrade : null;

    // Get all grades for this item
    $allgrades = grade_grade::fetch_all(['itemid' => $item->id]);
    $validgrades = [];
    foreach ($allgrades as $g) {
        if (is_numeric($g->finalgrade)) {
            $validgrades[] = (float)$g->finalgrade;
        }
    }

    if ($gradeval !== null && count($validgrades)) {
        $labels[] = format_string($name);
        $studentgrades[] = round($gradeval, 2);
        $maxgrades[] = round(max($validgrades), 2);
        $possiblegrades[] = round($item->grademax, 2); // ✅ NEW LINE

        sort($validgrades);
        $count = count($validgrades);
        $median = ($count % 2 === 0)
            ? ($validgrades[$count / 2 - 1] + $validgrades[$count / 2]) / 2
            : $validgrades[floor($count / 2)];
        $mediangrades[] = round($median, 2);
    }
}

// Output if data exists

    $chartjson = json_encode([
        'labels'  => $labels,
        'student' => $studentgrades,
        'max'     => $maxgrades,
        'median'  => $mediangrades,
        'weight'  => $possiblegrades,
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    
    echo '<div style="max-width:100%;height:400px;">
            <canvas id="myChart"
                    width="800" height="400"
                    data-chart=\'' . $chartjson . '\'
                    style="display:block;margin:0 auto;">
            </canvas>
          </div>';
    if (empty($labels)) {
    echo '<p style="text-align:center;color:gray;">لا توجد بيانات لعرضها في الرسم البياني.</p>';
    }

        }
        
        echo ob_get_clean();
        die;                     // fragment delivered to the modal
    }

/* ───────────────────────────────────────────────────────────────────
 *  POST  → save form (only when editable)
 * ───────────────────────────────────────────────────────────────── */
if (!$editable && !$download) {
    // Someone tried to POST a preview form → just exit safely.
    print_error('nopermissions', 'error', '', 'local/reports:manage');
}

if ($data = $mform->get_data()) {
    /* save via the form's own handler */
    $saved = $mform->process_data($data);     // bool

    /* refresh minimal row JSON (if you need AJAX response) ---------
       In the current UI you redirect back, so this is kept intact   */
    $user   = $DB->get_record('user', ['id'=>$data->userid],
                              'id,firstname,lastname');
    $report = $DB->get_record('local_reports',
                              ['courseid'=>$data->courseid,
                               'userid'  =>$data->userid], '*', MUST_EXIST);

    $statusstring = '';
    if ($report && $report->status_id) {
        $status = $DB->get_record('local_status',
                    ['id'=>$report->status_id],
                    'display_name_ar', IGNORE_MISSING);
        $statusstring = $status->display_name_ar ?? '';
    }

    /* preview URL for the row (read-only) */
    $previewurl = (new moodle_url('/local/reports/form_ajax.php', [
                        'courseid'=>$courseid,
                        'userid'  =>$user->id,
                        'reportid'=>$report->id,
                        'preview' =>1,
                        'sesskey' =>sesskey()
                   ]))->out(false);

    /* row array (if you later want JSON) */
    $row = [
        'userid'        => $user->id,
        'username'      => fullname($user),
        'researchtitle' => $report->researchtitle ?: '',
        'timemodified'  => userdate($report->timemodified),
        'status'        => $statusstring,
        'actiontitle'   => get_string('edit'),
        'previewurl'    => $previewurl,
        'url'           => (new moodle_url('/local/reports/form_ajax.php', [
                                'courseid'=>$courseid,
                                'userid'  =>$user->id,
                                'reportid'=>$report->id,
                                'sesskey' =>sesskey()
                         ]))->out(false),
    ];

    // Add grade information to the row
    $gradeout = '-';
    if ($g = grade_get_course_grades($courseid, $user->id)) {
        if (!empty($g->grades[$user->id]->grade)) {
            $gradeout = $g->grades[$user->id]->grade;
        }
    }
    $row['grade'] = $gradeout;

    // If this is an AJAX request, return JSON
    if (empty($_GET['download']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' || 
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'row' => $row]);
        exit;
    }

    /* Current UI just sends the browser back to the list */
    redirect(new moodle_url('/local/reports/index.php', ['id'=>$courseid]));
    die;
}

/* ───────────────────────────────────────────────────────────────────
 * Validation failed (unlikely on GET) – redisplay form then exit
 * ───────────────────────────────────────────────────────────────── */


$download   = optional_param('download', 0, PARAM_BOOL);
$chartpng   = optional_param('chartpng',  '', PARAM_RAW);   // may be empty

/* ---------- flatten mform: replace <input … value="x"> with "x" ---------- */
$mformhtml = $mform->render();       // still contains <form> + controls
$flatmform = preg_replace_callback(
    '/<input[^>]*value="([^"]*)"[^>]*>/i',
    function ($m) { return htmlspecialchars($m[1]); },
    $mformhtml
);
/* strip residual form tags & buttons */
$flatmform = preg_replace('/<\/?(form|input|select|option|textarea|button)[^>]*>/i', '', $flatmform);

if ($download) {
    /* ── build template context ───────────────────────────────────── */
    // Get report data and related user/type

    $reportid = required_param('reportid', PARAM_INT);
    $report = $DB->get_record('local_reports', ['id' => $reportid], '*', MUST_EXIST);
    $user   = $DB->get_record('user', ['id' => $report->userid], '*', MUST_EXIST);
    $type   = $DB->get_record('local_report_type', ['id' => $report->type_id], '*', IGNORE_MISSING);
    $context = [
        'course'          => $course->fullname,
        'fullname'        => fullname($user),
        'futureexpec'     => $report->futureexpec,
        'dep_op'          => $report->dep_op,
        'seg_path'        => $report->seg_path,
        'researchtitle'   => $report->researchtitle,
        'reporttype'      => $type ? $type->report_type_string_ar : '',   // plain text, already decoded
        'gradetable'      => $gradetable,    // HTML table
        'chartimg'        => $chartpng,      // "data:image/png;base64,..." or ''
        'profile_img_url' => $CFG->dirroot . '/local/reports/assets/image1.png',
    ];

    $html = $OUTPUT->render_from_template(
        'local_reports/report_modal_pdf',
        $context
    );

    /* ── TCPDF (Unicode + RTL) ────────────────────────────────────── */
    
    require_once($CFG->libdir.'/pdflib.php');
    $font = TCPDF_FONTS::addTTFfont($CFG->dirroot.'/lib/tcpdf/fonts/DejaVuSans.ttf', 'TrueTypeUnicode', '', 32);
    $pdf = new pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->setRTL(true);
    $pdf->SetFont($font, '', 12);
    $pdf->SetAutoPageBreak(true, 0);
    $pdf->AddPage('V');
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('student_report.pdf', 'I');   // "I" = open in browser
    exit;
}

/* ---------- build template for TCPDF ---------- */
$context = [
    'mform'      => $flatmform,
    'gradetable' => $gradetable,
    'chartimg'   => $chartpng,       // data:image/png;base64,… OR ''
];

/* if user had no grades chartpng=='' ⇒ we simply omit <img> */
$html = $OUTPUT->render_from_template('local_reports/report_modal_pdf', $context);

/* ---------- TCPDF ---------- */
$pdf = new pdf();
$pdf->SetTitle('Student Report');
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('student_report.pdf', 'D');   // force download
exit;



$mform->display();
die;
