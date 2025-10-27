<?php
require_once('../../../config.php');
global $DB, $USER;

$id = required_param('id', PARAM_INT);  // Booking ID
$confirm = optional_param('confirm', 0, PARAM_INT);  // Confirmation flag
$deleteall = optional_param('deleteall', 0, PARAM_INT);  // Delete all with the same groupid
$booking = $DB->get_record('local_roombooking_course_bookings', ['id' => $id], '*', MUST_EXIST);

// Check permissions
if ($booking->userid != $USER->id && !is_siteadmin()) {
    print_error('nopermission', 'local_roombooking');
}

// Fetch additional details for the confirmation message.
$course = $DB->get_field('course', 'fullname', ['id' => $booking->courseid], MUST_EXIST);
$room = $DB->get_field('local_roombooking_rooms', 'name', ['id' => $booking->roomid], MUST_EXIST);
$starttime = userdate($booking->starttime, get_string('strftimedate', 'langconfig') . ' ' . get_string('strftimetime', 'langconfig'));
$endtime = userdate($booking->endtime, get_string('strftimetime', 'langconfig'));

// Get recurrence label.
$recurrence = $booking->recurrence;
$recurrencelabels = [
    0 => get_string('once', 'local_roombooking'),
    1 => get_string('daily', 'local_roombooking'),
    2 => get_string('weekly', 'local_roombooking'),
];
$recurrencelabel = $recurrencelabels[$recurrence];

// Fetch all bookings with the same groupid
$groupbookings = $DB->get_records('local_roombooking_course_bookings', ['groupid' => $booking->groupid]);

// Set up the page
$PAGE->set_url(new moodle_url('/local/roombooking/pages/delete.php', ['id' => $id]));
$PAGE->set_title(get_string('deletebooking', 'local_roombooking'));
$PAGE->set_heading(get_string('deletebooking', 'local_roombooking'));
$PAGE->set_context(context_system::instance());

if ($confirm) {
    if ($deleteall) {
        // Delete all bookings with the same groupid
        $DB->delete_records('local_roombooking_course_bookings', ['groupid' => $booking->groupid]);
        // Redirect with success message
        redirect(new moodle_url('/local/roombooking/index.php'), get_string('groupbookingdeleted', 'local_roombooking', $booking->groupid));  // Pass the group ID
    } else {
        // Delete only this booking
        $DB->delete_records('local_roombooking_course_bookings', ['id' => $id]);
        // Redirect with success message
        redirect(new moodle_url('/local/roombooking/index.php'), get_string('bookingdeleted', 'local_roombooking'));
    }
} else {
    // Display confirmation message
    echo $OUTPUT->header();

    // Prepare a list of all bookings in the group
    $groupbookingdetails = '';
    foreach ($groupbookings as $groupbooking) {
        $groupstarttime = userdate($groupbooking->starttime, get_string('strftimedate', 'langconfig') . ' ' . get_string('strftimetime', 'langconfig'));
        $groupendtime = userdate($groupbooking->endtime, get_string('strftimetime', 'langconfig'));
        $groupbookingdetails .= "<li>" . get_string('course') . ": " . $course . ", " . get_string('room', 'local_roombooking') . ": " . $room . ", " . get_string('starttime', 'local_roombooking') . ": " . $groupstarttime . ", " . get_string('endtime', 'local_roombooking') . ": " . $groupendtime . "</li>";
    }

    $message = get_string('deleteconfirmationdetails', 'local_roombooking', [
        'course' => format_string($course),
        'room' => format_string($room),
        'starttime' => $starttime,
        'endtime' => $endtime,
        'recurrence' => $recurrencelabel
    ]);
    $message .= "<br><strong>" . get_string('groupbookingdetails', 'local_roombooking') . "</strong><ul>{$groupbookingdetails}</ul>";

    // Add a checkbox or link to delete all bookings in the group
    $yesurl = new moodle_url('/local/roombooking/pages/delete.php', ['id' => $id, 'confirm' => 1, 'deleteall' => 1, 'sesskey' => sesskey()]);
    $nourl = new moodle_url('/local/roombooking/index.php');
    echo $OUTPUT->confirm($message, $yesurl, $nourl);
    echo $OUTPUT->footer();
} 