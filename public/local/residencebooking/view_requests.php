<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/local/residencebooking/classes/output/manage_requests_table.php');

// Set up the page
$PAGE->set_url(new moodle_url('/local/residencebooking/view_requests.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('view_requests', 'local_residencebooking'));
$PAGE->set_heading(get_string('view_requests', 'local_residencebooking'));

// Check if the user is logged in and has capability
require_login();
$context = context_system::instance();
require_capability('local/residencebooking:viewbookings', $context);

// Get the current page and define pagination settings
$page = optional_param('page', 0, PARAM_INT);
$perpage = 10;

// Fetch the data for the table
$table = new manage_requests_table();
$data = $table->get_requests_data($page, $perpage);

// Render the template
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_residencebooking/view_requests', $data);
echo $OUTPUT->footer();
