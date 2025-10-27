<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

$disapproveall = optional_param('disapproveall', 0, PARAM_BOOL);
$courseid      = optional_param('courseid',      0, PARAM_INT);
$reportid      = optional_param('reportid',      0, PARAM_INT);
$reason        = optional_param('reason',        '', PARAM_TEXT);
$allcourses    = optional_param('allcourses',    0, PARAM_BOOL);
$studentid     = optional_param('studentid',     0, PARAM_INT);

require_login();
global $DB, $USER;

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

// gather targets
if ($disapproveall) {
    if ($allcourses) {
        // For allreports.php - operate on all courses
        $syscontext = context_system::instance();
        require_capability('local/reports:viewall', $syscontext);
        
        // Get all reports that can be disapproved (statuses that can move back) with filters
        $params = [];
        $sql = "SELECT r.* 
                FROM {local_reports} r
                WHERE r.status_id IN (31, 32)";
                
        // Apply filters if provided
        if ($courseid) {
            $sql .= " AND r.courseid = :courseid";
            $params['courseid'] = $courseid;
        }
        
        if ($studentid) {
            $sql .= " AND r.userid = :studentid";
            $params['studentid'] = $studentid;
        }
        
        $records = $DB->get_records_sql($sql, $params);
    } else if ($courseid) {
        // For index.php - operate on a single course
        $context = context_course::instance($courseid);
        require_capability('local/reports:manage', $context);
        
        $params = ['courseid' => $courseid];
        $sql = "SELECT * FROM {local_reports} WHERE courseid = :courseid AND status_id IN (31, 32)";
        
        // Add student filter if provided
        if ($studentid) {
            $sql .= " AND userid = :studentid";
            $params['studentid'] = $studentid;
        }
        
        $records = $DB->get_records_sql($sql, $params);
    } else {
        // Invalid parameters
        $out = ['success'=>false, 'error'=>'Missing parameters'];
        echo json_encode($out);
        die;
    }
} else {
    $rec     = $DB->get_record('local_reports', ['id'=>$reportid], '*', MUST_EXIST);
    $context = context_course::instance($rec->courseid);
    require_capability('local/reports:manage', $context);
    $records = [$rec];
}

$out = ['success'=>false, 'rows'=>[]];

foreach ($records as $rec) {
    $step = $reverse[$rec->status_id] ?? null;
    if (!$step) {
        // can't go back further
        continue;
    }

    // For allcourses, check capability for each course context
    $ccontext = context_course::instance($rec->courseid);

    // check if user has the required capability to disapprove
    if (!has_capability($step['capability'], $ccontext, $USER)) {
        continue;
    }

    // roll status back
    $rec->status_id    = $step['next'];
    $rec->timemodified = time();
    $rec->modifiedby   = $USER->id;
    
    // Store the rejection reason if provided
    if (!empty($reason)) {
        $rec->rejection_reason = $reason;
    }
    
    $DB->update_record('local_reports', $rec);

    // fresh status string
    $statusstr = $DB->get_field(
        'local_status','display_name_ar',
        ['id'=>$rec->status_id],
        IGNORE_MISSING
    ) ?? '';

    // recompute canapprove
    $canapprove = false;
    $approveurl = '';
    if (isset($workflow[$rec->status_id])) {
        $cap = $workflow[$rec->status_id]['capability'];
        if (has_capability($cap, $ccontext, $USER)) {
            $canapprove = true;
            $approveurl = (new moodle_url(
                '/local/reports/approve_ajax.php',
                ['reportid'=>$rec->id, 'sesskey'=>sesskey()]
            ))->out(false);
        }
    }

    // recompute candisapprove
    $candisapprove = false;
    $disapproveurl = '';
    if (isset($reverse[$rec->status_id])) {
        $cap = $reverse[$rec->status_id]['capability'];
        if (has_capability($cap, $ccontext, $USER)) {
            $candisapprove = true;
            $disapproveurl = (new moodle_url(
                '/local/reports/disapprove_ajax.php',
                ['reportid'=>$rec->id, 'sesskey'=>sesskey()]
            ))->out(false);
        }
    }

    $out['rows'][] = [
        'userid'        => $rec->userid,
        'status'        => $statusstr,
        'canapprove'    => $canapprove,
        'approveurl'    => $approveurl,
        'candisapprove' => $candisapprove,
        'disapproveurl' => $disapproveurl,
    ];
}

$out['success'] = true;
echo json_encode($out);
die;
