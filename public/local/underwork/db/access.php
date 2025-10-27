<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/underwork:view' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW, // Now students can access
        ],
    ],
];
