<?php
/**
 * Computer Services View Subtab for Request Services Plugin
 * 
 * This subtab displays computer service requests for the current course
 * using data from the local_computerservice plugin.
 * 
 * @package    local_requestservices
 * @subpackage tabs/subtabs
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE, $OUTPUT, $DB, $USER, $CFG;

// Ensure necessary variables are available
$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

// Check if the computerservice plugin is available
$computerservice_path = $CFG->dirroot . '/local/computerservice';
if (!file_exists($computerservice_path)) {
    echo $OUTPUT->notification(
        get_string('plugin_not_available', 'local_requestservices', 'Computer Service'),
        'error'
    );
    return;
}

try {
    // Get current language for bilingual support
    $lang = current_language();
    $devicenamefield = ($lang === 'ar') ? 'd.devicename_ar' : 'd.devicename_en';
    
    // Use LEFT JOINs to ensure all requests are shown even if device/course is missing
    $sql = "
        SELECT csr.id, csr.courseid, c.fullname as course, csr.numdevices, 
               csr.deviceid, $devicenamefield as devicename, csr.timecreated, 
               csr.status_id, csr.is_urgent, csr.comments
        FROM {local_computerservice_requests} csr
        LEFT JOIN {course} c ON csr.courseid = c.id
        LEFT JOIN {local_computerservice_devices} d ON csr.deviceid = d.id
        WHERE csr.courseid = :course_id
        ORDER BY csr.timecreated DESC
    ";
    
    $params = ['course_id' => $courseid];
    $computerservices = $DB->get_records_sql($sql, $params);
    
    // Include the simple_workflow_manager class
    require_once($computerservice_path . '/classes/simple_workflow_manager.php');
    
    // Prepare data for renderable
    $prepared = [];
    foreach ($computerservices as $row) {
        $row->devices = $row->devicename ?? get_string('device_not_found', 'local_requestservices');
        $row->readable_status = \local_computerservice\simple_workflow_manager::get_status_name($row->status_id);
        $prepared[] = $row;
    }
    
    // Include the renderable class
    require_once($CFG->dirroot . '/local/requestservices/classes/output/computerservices_requests.php');
    
    // Instantiate the renderable
    $renderable = new \local_requestservices\output\computerservices_requests($prepared);
    
    // Output the page
    echo $OUTPUT->render($renderable);
    
} catch (Exception $e) {
    // Log the error for debugging
    debugging('Computer services view failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
    
    // Show user-friendly error message
    echo $OUTPUT->notification(
        get_string('view_loading_failed', 'local_requestservices'),
        'error'
    );
}