<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library functions for the Annual Plans plugin.
 * Includes handling for file processing and database interaction.
 */



/**
 * Hook to add plugin to user dashboard navigation.
 * @param global_navigation $nav The global navigation object.
 */
function local_annualplans_extend_navigation(global_navigation $nav) {
    global $USER;

    // Check if the user can view the settings.
    if (has_capability('moodle/site:config', context_system::instance())) {
        // Find or create a custom node in the user's dashboard navigation.
        $mainnode = $nav->find('mycourses', navigation_node::TYPE_ROOTNODE);
        if (!$mainnode) {
            $mainnode = $nav->add(get_string('mycourses'), null, navigation_node::TYPE_ROOTNODE, null, 'mycourses');
        }

        // Add the plugin link to the dashboard
        if ($mainnode) {
            $strpluginname = get_string('pluginname', 'local_annualplans');
            $url = new moodle_url('local/annualplans/index.php');
            $mainnode->add($strpluginname, $url, navigation_node::TYPE_SETTING, null, 'local_annualplans', new pix_icon('i/settings', ''));
        }
    }
}
