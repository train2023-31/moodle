<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'local/reports:manage' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype'     => 'write',
        'contextlevel'=> CONTEXT_COURSE,
        'archetypes'  => [
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW,
           
        ],
    ],

    'local/reports:viewall' => [
        'captype'     => 'read',
        'contextlevel'=> CONTEXT_COURSE,
        'archetypes'  => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,

        ],
    ],
];
