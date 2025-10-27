<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a new category under Site Administration
    $category = new admin_category('local_financeservices', get_string('pluginname', 'local_financeservices'));
    $ADMIN->add('root', $category);

    // Create a settings page to host various settings
    $settingspage = new admin_settingpage('local_financeservices_settings', get_string('settings', 'local_financeservices'));
    
    // Check if we are on the full tree to avoid performance issues with unnecessary setting load
    if ($ADMIN->fulltree) {
        // Add a setting for enabling/disabling the feature
        $name = 'local_financeservices/enable';
        $title = get_string('enable', 'local_financeservices');
        $description = get_string('enabledesc', 'local_financeservices');
        $default = 1;
        $settingspage->add(new admin_setting_configcheckbox($name, $title, $description, $default));

        // Add a setting for a path input (if your plugin requires a path configuration)
        $name = 'local_financeservices/path';
        $title = get_string('path', 'local_financeservices');
        $description = get_string('pathdesc', 'local_financeservices');
        $default = '/path/to/resource';
        $settingspage->add(new admin_setting_configtext($name, $title, $description, $default));
    }

    // Add the settings page to the category
    $ADMIN->add('local_financeservices', $settingspage);

    // Additionally, add a direct link to manage or use the plugin if necessary
    $managelink = new admin_externalpage('local_financeservices_manage', get_string('financeRecords', 'local_financeservices'), 
                new moodle_url('/local/financeservices/index.php'), 'moodle/site:config');
    $ADMIN->add('local_financeservices', $managelink);
    $ADMIN->add('local_financeservices', new admin_externalpage(
        'local_financeservices_add_request',
        get_string('newrequest', 'local_financeservices'),
        new moodle_url('/local/financeservices/add_request.php'),
        'moodle/site:config'
    ));
    
}