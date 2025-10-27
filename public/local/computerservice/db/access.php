<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Defines the capabilities for the local_computerservice plugin.
 *
 * Each capability controls what actions different roles (e.g., teachers, managers) can perform.
 */

$capabilities = [

    /**
     * Capability: local/computerservice:submitrequest
     * 
     * Allows users to submit a device request.
     * 
     * - Context: COURSE (applies within a course context).
     * - Allowed Roles:
     *   - Teacher
     *   - Editing Teacher
     *   - Manager
     */
    'local/computerservice:submitrequest' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],

    /**
     * Capability: local/computerservice:managerequests
     * 
     * Allows managers to view and manage all submitted requests.
     * 
     * - Context: SYSTEM (applies across the entire site).
     * - Allowed Roles:
     *   - Manager (Only managers can manage requests)
     */
    'local/computerservice:managerequests' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    /**
     * Capability: local/computerservice:manage_devices
     * 
     * Allows managers to manage available devices in the system.
     * 
     * - Context: SYSTEM (applies across the entire site).
     * - Allowed Roles:
     *   - Manager (Only managers can modify device inventory)
     */
    'local/computerservice:manage_devices' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],

    /**
     * Capability: local/computerservice:handleurgent
     * 
     * Allows specific users to approve and deny urgent requests instantly.
     * 
     * - Context: SYSTEM (applies across the entire site).
     * - Allowed Roles:
     *   - Admin (Only admins can handle urgent requests)
     */
    'local/computerservice:can_handle_urgent' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'admin' => CAP_ALLOW,
        ],
    ],

    // ---------------------------------------------------------------------
    // NEW – meta capability to *attempt* a status transition.
    // ---------------------------------------------------------------------
    'local/computerservice:changestatus' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'manager' => CAP_ALLOW,   // seed value – adjust as you like
        ],
    ],
];
