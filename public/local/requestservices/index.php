<?php
require_once('../../config.php');

// Get the course ID from the URL.
$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

// Require login and permission to view the tab.
require_login($course);
require_capability('local/requestservices:view', $context);

// Include the form class
require_once($CFG->dirroot . '/local/computerservice/classes/form/request_form.php');

// Set up the page.
$PAGE->set_url('/local/requestservices/index.php', array('id' => $courseid));
$PAGE->set_title(get_string('requestservices', 'local_requestservices'));
$PAGE->set_heading($course->fullname);


// Include necessary JavaScript and CSS files (if any)



$tab = optional_param('tab', 'computerservicesTab', PARAM_ALPHA);

// Start outputting the page.
 echo $OUTPUT->header();

// Define the tabs using Moodle's tab API.
$tabs = [];

// List of available tabs.
$tabnames = ['allrequests', 'computerservicesTab', 'financialservices','registeringroom','requestparticipant', 'residencebooking'];

foreach ($tabnames as $tabname) {
    $tabs[] = new tabobject(
        $tabname,
        new moodle_url($PAGE->url, ['tab' => $tabname]),
        get_string($tabname, 'local_requestservices')
    );
}

// Render the tabs on the page.
print_tabs([$tabs], $tab);

$tabfile = $CFG->dirroot . '/local/requestservices/tabs/' . $tab . '.php';

if (file_exists($tabfile)) {
    include($tabfile);
} else {
    // Display an error message if the tab file doesn't exist.
    echo $OUTPUT->notification(get_string('invalidtab', 'local_requestservices'), 'error');
}


// Output the page footer.
echo $OUTPUT->footer();

