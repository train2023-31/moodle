<?php
defined('MOODLE_INTERNAL') || die();


$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);



global $DB, $OUTPUT, $PAGE, $USER;

// Fetch finance services requests for the current course
$sql = "
    SELECT fs.id, c.fullname as course, f.funding_type_ar, fs.price_requested, fs.status_id, fs.date_time_requested
    FROM {local_financeservices} fs
    JOIN {course} c ON fs.course_id = c.id
    JOIN {local_financeservices_funding_type} f ON fs.funding_type_id = f.id
    WHERE fs.course_id = :course_id
    ORDER BY fs.date_time_requested DESC
";
$params = ['course_id' => $courseid];
$financeservices = $DB->get_records_sql($sql, $params);

require_once($CFG->dirroot . '/local/financeservices/classes/simple_workflow_manager.php');
foreach ($financeservices as $id => $service) {
    $service->readable_status = \local_financeservices\simple_workflow_manager::get_status_name($service->status_id);
    $financeservices[$id] = $service;
} 
// Include the renderable class
require_once($CFG->dirroot . '/local/requestservices/classes/output/financialservicesview.php');

// Instantiate the renderable
$renderable = new \local_requestservices\output\financialservicesview($financeservices);

// Output the page

echo $OUTPUT->render($renderable);

