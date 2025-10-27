<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/annualplans:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
          
        ),
        'clonepermissionsfrom' => 'moodle/site:config'
    ),

    'local/annualplans:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
          
        )
    ),
);
