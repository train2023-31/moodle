<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a new category under Site Administration for Residence Booking
    $category = new admin_category('local_residencebooking', get_string('pluginname', 'local_residencebooking'));
    $ADMIN->add('root', $category);

    // Create a settings page to host various settings
    $settingspage = new admin_settingpage('local_residencebooking_settings', get_string('settings', 'local_residencebooking'));
    
    // Check if we are on the full tree to avoid performance issues
    if ($ADMIN->fulltree) {
        // Add a setting for enabling/disabling the Residence Booking
        $settingspage->add(new admin_setting_configcheckbox(
            'local_residencebooking/enable',
            get_string('enable', 'local_residencebooking'),
            get_string('enable_desc', 'local_residencebooking'),
            1 // Default to enabled
        ));

        // Add a dropdown setting for selecting the default role for users
        $roles = get_all_roles();
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions[$role->id] = $role->shortname;
        }

        $settingspage->add(new admin_setting_configselect(
            'local_residencebooking/role',
            get_string('role', 'local_residencebooking'),
            get_string('role_desc', 'local_residencebooking'),
            null, // Default to no selection
            $roleoptions
        ));
    }

    // Add the settings page to the category
    $ADMIN->add('local_residencebooking', $settingspage);

    // Add additional management links for ease of access
    $ADMIN->add('local_residencebooking', new admin_externalpage(
        'local_residencebooking_manage',
        get_string('applyrequests', 'local_residencebooking'),
        new moodle_url('/local/residencebooking/index.php'),
        'moodle/site:config' // Require admin permissions to access this page
    ));

    // Add a new settings page under "Residence Booking plugin".
    $ADMIN->add('local_residencebooking', new admin_externalpage(
        'local_residencebooking_manage_requests',
        get_string('managerequests', 'local_residencebooking'),
        new moodle_url('/local/residencebooking/index.php?tab=manage'),
        'moodle/site:config' // Ensure only users with this capability can see the link.
    ));

    $ADMIN->add('local_residencebooking', new admin_externalpage(
        'local_residencebooking_manage_types',
        get_string('managetypes', 'local_residencebooking'),
        new moodle_url('/local/residencebooking/manage_types.php'),
        'moodle/site:config' // Admin capability required
    ));
    
}
