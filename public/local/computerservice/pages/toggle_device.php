<?php
// Include the Moodle configuration file.
require_once(__DIR__ . '/../../../config.php');

// Access global variables.
global $DB, $PAGE;

// Ensure the user is logged in.
require_login();
$context = context_system::instance();

// Check if the user has the capability to manage devices.
require_capability('local/computerservice:manage_devices', $context);

// Retrieve the device ID from the request parameters.
$id = required_param('id', PARAM_INT);

// Fetch the device record from the database; throw an error if it doesn't exist.
$device = $DB->get_record('local_computerservice_devices', ['id' => $id], '*', MUST_EXIST);

// Toggle the device status between 'active' and 'inactive'.
$new_status = ($device->status === 'active') ? 'inactive' : 'active';
$device->status = $new_status;

// Update the record in the database.
$DB->update_record('local_computerservice_devices', $device);

// Redirect back to the device management page with a success message.
redirect(
    new moodle_url('/local/computerservice/pages/manage_devices.php'),
    get_string('status_updated', 'local_computerservice'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
