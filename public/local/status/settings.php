<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    
    // Create a new admin category for workflow management
    $ADMIN->add('localplugins', new admin_category('local_status', get_string('pluginname', 'local_status')));
    
    // Add the main dashboard page
    $ADMIN->add('local_status', new admin_externalpage(
        'local_status_dashboard',
        get_string('workflowdashboard', 'local_status'),
        new moodle_url('/local/status/index.php'),
        'moodle/site:config'
    ));
    
    // Add direct links to specific management pages
    $ADMIN->add('local_status', new admin_externalpage(
        'local_status_workflows',
        get_string('manageworkflows', 'local_status'),
        new moodle_url('/local/status/index.php', ['tab' => 'workflows']),
        'moodle/site:config'
    ));
    
    $ADMIN->add('local_status', new admin_externalpage(
        'local_status_steps',
        get_string('workflowsteps', 'local_status'),
        new moodle_url('/local/status/index.php', ['tab' => 'steps']),
        'moodle/site:config'
    ));
    
    $ADMIN->add('local_status', new admin_externalpage(
        'local_status_history',
        get_string('workflowhistory', 'local_status'),
        new moodle_url('/local/status/index.php', ['tab' => 'history']),
        'moodle/site:config'
    ));
    
    // Add a settings page for global configuration
    $settings = new admin_settingpage('local_status_settings', get_string('settings'));
    
    // Global workflow settings
    $settings->add(new admin_setting_configcheckbox(
        'local_status/enable_audit',
        get_string('enableaudit', 'local_status'),
        get_string('enableaudit_desc', 'local_status'),
        1
    ));
    
    $settings->add(new admin_setting_configtext(
        'local_status/audit_retention_days',
        get_string('auditretentiondays', 'local_status'),
        get_string('auditretentiondays_desc', 'local_status'),
        365,
        PARAM_INT
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_status/enable_notifications',
        get_string('enablenotifications', 'local_status'),
        get_string('enablenotifications_desc', 'local_status'),
        1
    ));
    
    $ADMIN->add('local_status', $settings);
} 