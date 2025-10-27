<?php
defined('MOODLE_INTERNAL') || die();


$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);



global $DB, $OUTPUT, $PAGE, $USER;

// Fetch finance services requests for the current course
$sql = "SELECT rbr.*, c.fullname AS course_name, rbt.type_name_ar AS residence_type, rbp.description_ar AS purpose_type, rbr.status_id,
               u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, 
               u.middlename, u.alternatename
    FROM {local_residencebooking_request} rbr
    JOIN {course} c ON rbr.courseid = c.id
    JOIN {local_residencebooking_types} rbt ON rbr.residence_type = rbt.id
    JOIN {local_residencebooking_purpose} rbp ON rbr.purpose = rbp.id
    JOIN {user} u ON rbr.userid = u.id
    WHERE rbr.courseid = :course_id
    ORDER BY rbr.id DESC";

$params = ['course_id' => $courseid];
$residencebooking = $DB->get_records_sql($sql, $params);

require_once($CFG->dirroot . '/local/residencebooking/classes/simple_workflow_manager.php');
foreach ($residencebooking as $id => $service) {
    $service->readable_status = \local_residencebooking\simple_workflow_manager::get_status_name($service->status_id);
    $residencebooking[$id] = $service;
} 

// Include the renderable class
require_once($CFG->dirroot . '/local/requestservices/classes/output/residencebookingview.php');

// Instantiate the renderable
$renderable = new \local_requestservices\output\residencebookingview($residencebooking);

// Output the page

echo $OUTPUT->render($renderable);