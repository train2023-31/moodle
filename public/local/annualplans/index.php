<?php
// Include necessary Moodle config and libraries.
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/course/lib.php');  // Include the course library
require_once('classes/table.php');  // Include the table class
require_once('classes/control_form.php');

// Include our new classes
require_once('classes/AnnualPlansController.php');
require_once('classes/CourseManager.php');

require_login();
$context = context_system::instance();
$PAGE->requires->js(new moodle_url('/local/annualplans/js/main.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));
$PAGE->set_context($context);
require_capability('local/annualplans:manage', $context);

$PAGE->set_url('/local/annualplans/index.php');

$controller = new AnnualPlansController();
$controller->handle_request();
