<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Ensure user is logged in
require_login();

// Get the code ID from the request
$code_id = required_param('code_id', PARAM_INT);

// Security check - verify user has appropriate permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Get the code from the database
$code = $DB->get_record('local_annual_plan_course_codes', array('id' => $code_id));

if ($code && !is_null($code->type_id)) {
    // Set appropriate headers
    header('Content-Type: text/plain');
    // Output the type_id value
    echo $code->type_id;
} else {
    // Set HTTP status to 404 Not Found
    header("HTTP/1.0 404 Not Found");
    echo 'Code not found or type_id is null';
}

// End execution to prevent any other output
exit; 