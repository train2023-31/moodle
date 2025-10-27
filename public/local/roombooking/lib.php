<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library functions for the Room Booking plugin.
 * Includes handling for navigation and potentially other interactions.
 */

/**
 * Hook to add the Room Booking plugin to user dashboard navigation.
 * @param global_navigation $nav The global navigation object.
 */
function local_roombooking_extend_navigation(global_navigation $nav) {
    global $USER;

    // Check if the user has the capability to manage room bookings.
    if (has_capability('moodle/site:config', context_system::instance())) {
        // Find or create a custom node in the user's dashboard navigation.
        $mainnode = $nav->find('mycourses', navigation_node::TYPE_ROOTNODE);
        if (!$mainnode) {
            $mainnode = $nav->add(get_string('mycourses'), null, navigation_node::TYPE_ROOTNODE, null, 'mycourses');
        }

        // Add the Room Booking plugin link to the dashboard
        if ($mainnode) {
            $strpluginname = get_string('pluginname', 'local_roombooking');
            $url = new moodle_url('/local/roombooking/index.php');
            $mainnode->add($strpluginname, $url, navigation_node::TYPE_SETTING, null, 'local_roombooking', new pix_icon('i/scheduled', ''));
        }
    }
}
