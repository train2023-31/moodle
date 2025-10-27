<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_residencebooking_fetch_guests' => [
        //  'classname'   => 'local_residencebooking\classes\external\fetch_guests',
        'methodname'  => 'execute',
        // 'classpath'   => 'local/residencebooking/externallib.php',
        'description' => 'Fetch guests list for autocomplete',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => '',
    ],
];
