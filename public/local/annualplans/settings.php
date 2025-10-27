<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a new category under Site Administration
    $category2 = new admin_category('levels', get_string('levelstitle', 'local_annualplans'));
    $ADMIN->add('root', $category2);

    $category = new admin_category('local_annualplans', get_string('pluginname', 'local_annualplans'));
    $ADMIN->add('root', $category);


    //create levels under site administration
  

    // Create a settings page to host various settings
    $settingspage = new admin_settingpage('local_annualplans_settings', get_string('settings', 'local_annualplans'));
    
    // Check if we are on the full tree to avoid performance issues with unnecessary setting load
    if ($ADMIN->fulltree) {
        // Add a setting for enabling/disabling the feature
        $name = 'local_annualplans/enable';
        $title = get_string('enable', 'local_annualplans');
        $description = get_string('enabledesc', 'local_annualplans');
        $default = 1;
        $settingspage->add(new admin_setting_configcheckbox($name, $title, $description, $default));

        // Add a setting for a path input (if your plugin requires a path configuration)
        $name = 'local_annualplans/path';
        $title = get_string('path', 'local_annualplans');
        $description = get_string('pathdesc', 'local_annualplans');
        $default = '/path/to/resource';
        $settingspage->add(new admin_setting_configtext($name, $title, $description, $default));
    }

    // Add the settings page to the category
    $ADMIN->add('local_annualplans', $settingspage);


    $ADMIN->add('levels', new admin_externalpage(
        'local_annualplans_levels',
        get_string('manglevelstitle', 'local_annualplans'),
        new moodle_url('/local/annualplans/manage_levels.php'),
        'moodle/site:config' // Require appropriate permissions to access this page
    ));

    // Additionally, add a direct link to manage or use the plugin if necessary
    $managelink = new admin_externalpage('local_annualplans_manage', get_string('manage', 'local_annualplans'), 
                new moodle_url('/local/annualplans/index.php'), 'moodle/site:config');
    $ADMIN->add('local_annualplans', $managelink);
    
    // Add the course codes management page to Annual Plans section
    $codeslink = new admin_externalpage(
        'local_annualplans_manage_codes', 
        get_string('managecoursecodesadmin', 'local_annualplans'),
        new moodle_url('/local/annualplans/manage_codes.php'), 
        'moodle/site:config'
    );
    $ADMIN->add('local_annualplans', $codeslink);
}