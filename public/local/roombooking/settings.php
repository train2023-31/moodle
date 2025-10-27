<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {  // Make sure this is only done on site configuration pages
    // Create a new category under the 'Site administration' menu
    $ADMIN->add('root', new admin_category('local_roombooking', get_string('pluginname', 'local_roombooking')));

    // Add a settings page under the newly created category
    $settingspage = new admin_settingpage('managesettings', get_string('settings', 'local_roombooking'));

    // Add settings fields on the settings page
    $settingspage->add(new admin_setting_configcheckbox('local_roombooking/enable',
        get_string('enable', 'local_roombooking'),
        get_string('enable_desc', 'local_roombooking'), 0));

    // Define more settings as needed
    $settingspage->add(new admin_setting_configtext('local_roombooking/role',
        get_string('role', 'local_roombooking'),
        get_string('role_desc', 'local_roombooking'), 'defaultvalue'));

    // Ensure we add the settings page only if it's filled with settings
    if ($settingspage->settings) {
        $ADMIN->add('local_roombooking', $settingspage);
    }

    // Add direct link to the main booking interface with tabs
    $ADMIN->add('local_roombooking', new admin_externalpage('bookinginterface',
        get_string('bookinginterface', 'local_roombooking'),
        new moodle_url('/local/roombooking/index.php'), 'local/roombooking:viewbookings'));
    
    // Add direct link to the management dashboard
    $ADMIN->add('local_roombooking', new admin_externalpage('managementdashboard',
        get_string('managementdashboard', 'local_roombooking'),
        new moodle_url('/local/roombooking/pages/classroom_management.php'), 'local/roombooking:managebookings'));

    // Add direct link to the standalone bookings management page
    $ADMIN->add('local_roombooking', new admin_externalpage('viewbookings',
        get_string('viewbookings', 'local_roombooking'),
        new moodle_url('/local/roombooking/pages/bookings.php'), 'local/roombooking:viewbookings'));

    // Add direct link to the room management page
    $ADMIN->add('local_roombooking', new admin_externalpage('managerooms',
        get_string('managerooms', 'local_roombooking'),
        new moodle_url('/local/roombooking/pages/manage_rooms.php'), 'local/roombooking:managerooms'));
}
