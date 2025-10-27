<?php
defined('MOODLE_INTERNAL') || die();

// Get the current subtab from URL or set default
$subtab = optional_param('subtab', 'computerservicesview', PARAM_ALPHA);

// Define vertical tabs
$subtabnames = [
    'computerservicesview' => get_string('computerservicesTab', 'local_requestservices'),
    'financialservicesview' => get_string('financialservices', 'local_requestservices'),
    'registeringroomview' => get_string('registeringroom', 'local_requestservices'),
    'participantview' => get_string('requestparticipant', 'local_requestservices'),
    'residencebookingview' => get_string('residencebooking', 'local_requestservices'),
];

// Begin layout with Bootstrap's grid system
echo html_writer::start_div('container-fluid');
echo html_writer::start_div('row');

// Left column for vertical tabs with border class
echo html_writer::start_div('col-md-3 border-right'); // Add 'border-right' class here

// Create vertical nav-pills
echo html_writer::start_tag('ul', ['class' => 'nav nav-pills flex-column', 'role' => 'tablist']);

// Loop through subtabs and create list items
foreach ($subtabnames as $subtabname => $subtablabel) {
    $active = ($subtab == $subtabname) ? 'active' : '';
    echo html_writer::start_tag('li', ['class' => 'nav-item']);
    echo html_writer::link(
        new moodle_url($PAGE->url, ['id' => $courseid, 'tab' => 'allrequests', 'subtab' => $subtabname]),
        $subtablabel,
        ['class' => 'nav-link ' . $active, 'role' => 'tab']
    );
    echo html_writer::end_tag('li');
}

echo html_writer::end_tag('ul'); // End of nav-pills
echo html_writer::end_div(); // End of left column

// Right column for subtab content
echo html_writer::start_div('col-md-9');

// Determine the file to include based on the current subtab
$subtabfile = $CFG->dirroot . '/local/requestservices/tabs/subtabs/' . $subtab . '.php';

if (file_exists($subtabfile)) {
    include($subtabfile);
} else {
    echo $OUTPUT->notification(get_string('invalidsubtab', 'local_requestservices'), 'error');
}

echo html_writer::end_div(); // End of right column
echo html_writer::end_div(); // End of row
echo html_writer::end_div(); // End of container
?>
