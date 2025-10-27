<?php
define('MOODLE_INTERNAL', 1);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/filelib.php');

// Check if user is logged in
require_login();

// Get the annual plan ID from the request
$id = required_param('id', PARAM_INT);

// Get the annual plan year from the database
global $DB;
$annual_plan = $DB->get_record('local_annual_plan', array('id' => $id), 'year');

if ($annual_plan && !empty($annual_plan->year)) {
    // Extract only the last two digits of the year
    $year = (string)$annual_plan->year;
    if (strlen($year) >= 2) {
        echo substr($year, -2); // Get last two digits
    } else {
        echo $year; // If year is less than 2 digits, return it as is
    }
} else {
    echo '';
} 