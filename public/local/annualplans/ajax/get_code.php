<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Ensure user is logged in
require_login();

// Get the code ID from the request
$code_id = required_param('id', PARAM_INT);

// Security check - verify user has appropriate permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Get the code from the database
$code = $DB->get_record('local_annual_plan_course_codes', array('id' => $code_id));

if ($code) {
    // Set appropriate headers
    header('Content-Type: text/plain');
    // Just output the code value
    echo $code->code_en;
} else {
    // Set HTTP status to 404 Not Found
    header("HTTP/1.0 404 Not Found");
    echo 'Code not found';
}

// End execution to prevent any other output
exit; 