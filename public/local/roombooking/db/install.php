<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Post installation procedure; set up initial data.
 */
function xmldb_local_roombooking_install() {
    global $DB;

    $rooms = [
        ['name' => 'Room 101', 'capacity' => 30, 'location' => 'Building A',
         'description' => 'Projector-equipped IT room.',  'roomtype' => 'fixed',   'deleted' => 0],
        ['name' => 'Room 102', 'capacity' => 20, 'location' => 'Building A',
         'description' => 'Small discussion room.',       'roomtype' => 'fixed',   'deleted' => 0],
        ['name' => 'Room 201', 'capacity' => 50, 'location' => 'Building B',
         'description' => 'Large lecture hall.',          'roomtype' => 'dynamic', 'deleted' => 0],
        ['name' => 'Room 202', 'capacity' => 40, 'location' => 'Building B',
         'description' => 'Flexible seating.',            'roomtype' => 'dynamic', 'deleted' => 0]
    ];

    foreach ($rooms as $room) {
        $DB->insert_record('local_roombooking_rooms', (object)$room);
    }

    // // Define the roles and the corresponding capabilities to assign.
    // $roles_to_capabilities = [
    //     'manager' => [
    //         'local/roombooking:viewbookings',
    //         'local/roombooking:managebookings',
    //         'local/roombooking:deletebookings',
    //     ],
    //     'teacher' => [
    //         'local/roombooking:viewbookings',
    //         'local/roombooking:managebookings',
    //     ],
    //     'student' => [
    //         'local/roombooking:viewbookings',
    //     ]
    // ];

    // // Loop through each role and assign the specified capabilities.
    // foreach ($roles_to_capabilities as $role_shortname => $capabilities) {
    //     // Fetch the role by its shortname (e.g., manager, teacher, student).
    //     $role = $DB->get_record('role', ['shortname' => $role_shortname]);

    //     // If the role exists in the system, assign the capabilities.
    //     if ($role) {
    //         foreach ($capabilities as $capability) {
    //             // Assign the capability at the system level (global context).
    //             assign_capability($capability, CAP_ALLOW, $role->id, context_system::instance()->id);
    //         }
    //     }
    // }
}
