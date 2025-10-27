<?php
// Prevent direct access to the file outside Moodle's context.
defined('MOODLE_INTERNAL') || die();

/**
 * Define capabilities for the Room Booking plugin in the system context.
 */
$capabilities = [
    // Capability to view room bookings.
    'local/residencebooking:viewbookings' => [
        'captype' => 'read',  // Capability for viewing data (read-only).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the entire system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Allow managers to view bookings.
        ],
    ],

    // Capability to manage room bookings (create and edit).
    'local/residencebooking:managebookings' => [
        'captype' => 'write',  // Capability for modifying data (write operations).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Allow managers to manage bookings.
        ],
    ],

    // Capability to delete room bookings.
    'local/residencebooking:deletebookings' => [
        'captype' => 'write',  // Capability for deleting data (write operations).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Only allow managers (admins) to delete bookings.
        ],
    ],

    'local/residencebooking:managerooms' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
