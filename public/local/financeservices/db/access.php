<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/financeservices:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        ),
    ),
    'local/financeservices:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW,
        ),
    ),
);