<?php
// Prevent direct access to the file outside Moodle's context.
defined('MOODLE_INTERNAL') || die();

/**
 * Define capabilities for the Room Booking plugin in the system context.
 */
$capabilities = [
    // Capability to view room bookings.
    'local/roombooking:viewbookings' => [
        'captype' => 'read',  // Capability for viewing data (read-only).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the entire system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Allow managers to view bookings.
            'teacher' => CAP_ALLOW,  // Allow teachers to view bookings.
            'editingteacher' => CAP_ALLOW,  // Allow editing teachers to view bookings.
            'student' => CAP_ALLOW,  // Allow students to view bookings.
        ],
    ],

    // Capability to manage room bookings (create and edit).
    'local/roombooking:managebookings' => [
        'captype' => 'write',  // Capability for modifying data (write operations).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Allow managers to manage bookings.
            'teacher' => CAP_ALLOW,  // Allow teachers to manage bookings.
            'editingteacher' => CAP_ALLOW,  // Allow editing teachers to manage bookings.
        ],
    ],

    // Capability to delete room bookings.
    'local/roombooking:deletebookings' => [
        'captype' => 'write',  // Capability for deleting data (write operations).
        'contextlevel' => CONTEXT_SYSTEM,  // Applies across the system (global).
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Only allow managers (admins) to delete bookings.
        ],
    ],

    'local/roombooking:managerooms' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
