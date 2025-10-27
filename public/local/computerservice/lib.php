<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Extend course navigation to add the request link for teachers and managers.
 */

/**
 * Extend settings navigation to add the manage requests link for managers.
 */
function local_computerservice_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    if (has_capability('local/computerservice:managerequests', $context)) {
        $node = $settingsnav->add(
            get_string('computerservice', 'local_computerservice'),
            null,
            navigation_node::TYPE_CATEGORY,
            null,
            'computerservice',
            new pix_icon('i/settings', '')
        );

        $url = new moodle_url('/local/computerservice/manage.php');
        $node->add(
            get_string('managerequests', 'local_computerservice'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'computerservice-manage'
        );
    }
}
