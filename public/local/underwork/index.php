<?php
require_once(__DIR__ . '/../../config.php');

// Require the user to be logged in.
require_login();

// Set up the page.
$PAGE->set_url(new moodle_url('/local/underwork/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_underwork'));
$PAGE->set_heading(get_string('pluginname', 'local_underwork'));

// Check if the user has the required capability.
require_capability('local/underwork:view', context_system::instance());

// Output starts here.
echo $OUTPUT->header();

// Display the message.
echo $OUTPUT->heading(get_string('underworkmessage', 'local_underwork'));

// After displaying the message
echo html_writer::link(new moodle_url('/'), get_string('backtohome', 'local_underwork'));

// Output ends here.
echo $OUTPUT->footer();
