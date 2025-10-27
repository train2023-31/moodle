<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/annualplans/classes/control_form.php');

// Set up the page
$PAGE->set_url(new moodle_url('/local/annualplans/manage_levels.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('managelevels', 'local_annualplans'));
$PAGE->set_heading(get_string('managelevels', 'local_annualplans'));

// Require login and capability checks if necessary
require_login();
require_capability('moodle/site:config', context_system::instance());

// Instantiate the form
$mform = new manage_levels_form();

// Process the form data if submitted
if ($mform->is_cancelled()) {
    // Handle form cancellation if necessary
    redirect(new moodle_url('/local/annualplans/index.php'));
} else if ($data = $mform->get_data()) {
    $mform->process_data($data);
}

// Output the page header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managelevels', 'local_annualplans'));

// Display the form
$mform->display();

// Output the page footer
echo $OUTPUT->footer();
