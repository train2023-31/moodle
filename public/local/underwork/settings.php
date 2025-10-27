<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Create a new category under Site Administration for Page Underwork
    $category = new admin_category('local_underwork', get_string('pluginname', 'local_underwork'));
    $ADMIN->add('root', $category);

    $ADMIN->add('local_underwork', new admin_externalpage(
        'local_underwork_manage',
        get_string('pluginname', 'local_underwork'),
        new moodle_url('/local/underwork/index.php'),
        'moodle/site:config' // Require appropriate permissions to access this page
    ));

}
