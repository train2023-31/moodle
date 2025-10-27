<?php
defined('MOODLE_INTERNAL') || die();



$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($courseid);


global $DB, $OUTPUT;

// Fetch booking records for the current course
$sql = "SELECT bc.*, bc.status_id, c.fullname AS course_name, r.name AS room_name, 
               u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, 
               u.middlename, u.alternatename
        FROM {local_roombooking_course_bookings} bc
        JOIN {course} c ON bc.courseid = c.id
        JOIN {local_roombooking_rooms} r ON bc.roomid = r.id
        JOIN {user} u ON bc.userid = u.id
        WHERE bc.courseid = :courseid
        ORDER BY bc.starttime DESC";

$params = ['courseid' => $courseid];

$records = $DB->get_records_sql($sql, $params);

// Include the simple_workflow_manager class
require_once($CFG->dirroot . '/local/roombooking/classes/simple_workflow_manager.php');
// Prepare data for renderable
$prepared = [];
foreach ($records as $row) {
    $row->readable_status = \local_roombooking\simple_workflow_manager::get_status_name($row->status_id);
    $prepared[] = $row;
}



// Include the renderable class
require_once($CFG->dirroot . '/local/requestservices/classes/output/registeringroomview.php');

// Instantiate the renderable
$renderable = new \local_requestservices\output\registeringroomview($records);

// Output the page

echo $OUTPUT->render($renderable);
