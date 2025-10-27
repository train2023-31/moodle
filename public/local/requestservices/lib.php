<?php
defined('MOODLE_INTERNAL') || die();

function local_requestservices_extend_navigation_course($navigation, $course, $context) {
    // Check if the user has the capability to view the Request Services tab.
    if (has_capability('local/requestservices:view', $context)) {
        // Add the new tab to the navigation menu.
        $navigation->add(
            get_string('requestservices', 'local_requestservices'), // Tab name from language file.
            new moodle_url('/local/requestservices/index.php', array('id' => $course->id)), // URL to the tab.
            navigation_node::TYPE_CUSTOM, // Type of node.
            null, // No shortname.
            'requestservices' // Unique key for the tab.
        );
    }
}
