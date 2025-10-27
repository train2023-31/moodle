<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/requestservices:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        ),
    ),
);
