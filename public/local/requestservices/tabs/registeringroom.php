<?php
// Tab file for the 'registeringroom' tab.

// Include necessary libraries.
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

// Include the form class from roombooking plugin
require_once($CFG->dirroot . '/local/roombooking/classes/form/booking_form.php');

// Require login and permission to view the tab.
require_capability('local/requestservices:view', $context);

$actionurl = new moodle_url('/local/requestservices/index.php', array('id' => $courseid, 'tab' => 'registeringroom'));

// Instantiate the form
$mform = new \local_roombooking\form\booking_form($actionurl, array('id' => $courseid));

// Process form data if submitted
if ($mform->is_cancelled()) {
    // Handle form cancellation (e.g., redirect to another page)
    redirect(new moodle_url('/local/requestservices/index.php', array('id' => $courseid, 'tab' => 'registeringroom')));
} else if ($data = $mform->get_data()) {
    // Prepare the data for insertion into the database.
    $record = new stdClass();
    $record->userid = $data->userid;  // The ID of the logged-in user.
    $record->courseid = $data->courseid;  // The selected course ID.
    $record->roomid = $data->roomid;  // The selected room ID.
    $record->capacity = $data->capacity;  // Number of capacity needed.
    $record->starttime = $data->start_date_time;  // Start date and time.
    $record->endtime = $data->end_date_time;  // End date and time.
    $record->recurrence = $data->recurrence;  // Recurrence type.
    $record->recurrence_end_date = isset($data->recurrence_end_date) ? $data->recurrence_end_date : null;  // Recurrence end date.
    $record->groupid = uniqid();  // Generate a unique group ID for recurring bookings.
    $record->status_id = \local_roombooking\simple_workflow_manager::get_initial_status_id();  // Use the correct initial status from workflow manager
    $record->timecreated = time();  // Timestamp when the request was created.
    $record->timemodified = time();  // Timestamp when the request was last modified.

    // Insert the record into the local_roombooking_course_bookings table.
    $DB->insert_record('local_roombooking_course_bookings', $record);
    
    // Add a redirect after successful insertion to avoid double submission
    redirect(
        new moodle_url('/local/requestservices/index.php', ['id' => $courseid, 'tab' => 'registeringroom']),
        get_string('requestsubmitted', 'local_roombooking'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
    require_sesskey();  // Add this after form submission check if applicable 
}

$mform->display();
