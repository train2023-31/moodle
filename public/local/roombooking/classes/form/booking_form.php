<?php
namespace local_roombooking\form;

defined('MOODLE_INTERNAL') || die();  // Prevent direct access to the script.

require_once($CFG->libdir . '/formslib.php');  // Load the Moodle form library.
use local_roombooking\repository\booking_repository;
use local_roombooking\repository\room_repository;

class booking_form extends \moodleform {
    
    // Define the form fields and structure.
    protected function definition() {
        global $DB, $USER;
        $mform = $this->_form;  // Reference to the form object.

        // --- Course selection dropdown ---
        // Check if a specific course ID is passed in custom data
        $customdata = $this->_customdata;
        $current_courseid = isset($customdata['id']) ? $customdata['id'] : null;
        
        if ($current_courseid && $current_courseid > 1) {
            // If we have a specific course ID, only show that course as the only option
            $course = $DB->get_record('course', array('id' => $current_courseid, 'visible' => 1));
            if ($course) {
                // Only include the current course, no empty option
                $course_options = array($course->id => $course->fullname);
                $mform->addElement('select', 'courseid', get_string('course', 'local_roombooking'), $course_options);
                $mform->setDefault('courseid', $current_courseid);
            } else {
                // Fallback to all courses if specific course not found
                $course_options = array('' => get_string('selectacourse', 'local_roombooking'));
                $courses_sql = "SELECT id, fullname FROM {course} WHERE visible = 1 AND id > 1 ORDER BY fullname";
                $courses = $DB->get_records_sql($courses_sql);
                foreach ($courses as $course) {
                    $course_options[$course->id] = $course->fullname;
                }
                $mform->addElement('select', 'courseid', get_string('course', 'local_roombooking'), $course_options);
            }
        } else {
            // Get approved annual plan courses for current year with course codes
            $course_options = array('' => get_string('selectacourse', 'local_roombooking'));
            $approved_courses = $this->get_approved_annual_plan_courses();
            $course_options = $course_options + $approved_courses;
            $mform->addElement('select', 'courseid', get_string('course', 'local_roombooking'), $course_options);
        }
        
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');  // Make the course selection mandatory.
        
        // Add a help button to provide guidance for course selection.
        $mform->addHelpButton('courseid', 'course', 'local_roombooking');

        // --- Room selection dropdown with types and capacity from language file ---
        $rooms = $DB->get_records('local_roombooking_rooms', ['deleted' => 0], 'name', 'id, name, roomtype, capacity');

        $room_options = array('' => get_string('selectaroom', 'local_roombooking'));
        foreach ($rooms as $room) {
            if (isset($room->roomtype) && in_array($room->roomtype, ['fixed', 'dynamic'])) {
                $roomtype = get_string($room->roomtype, 'local_roombooking');
            } else {
                $roomtype = get_string('invalidroomtype', 'local_roombooking');
            }
            $capacity_label = get_string('capacity', 'local_roombooking');
            $room_options[$room->id] = format_string($room->name) . ' (' . $roomtype . ', ' . $capacity_label . ': ' . $room->capacity . ')';
        }

        $mform->addElement('select', 'roomid', get_string('room', 'local_roombooking'), $room_options);
        $mform->addRule('roomid', get_string('required'), 'required', null, 'client');
        // Add help button for the room field
        $mform->addHelpButton('roomid', 'room', 'local_roombooking');

        // ** Capacity field (new) **
        $mform->addElement('text', 'capacity', get_string('capacity', 'local_roombooking')); // Adding capacity field
        $mform->setType('capacity', PARAM_INT); // Set it to expect an integer
        $mform->addRule('capacity', get_string('required'), 'required', null, 'client');
        $mform->addRule('capacity', get_string('validnumber', 'local_roombooking'), 'numeric', null, 'client');
        $mform->setDefault('capacity', 1); // Set default capacity to 1

        // --- Hidden User ID field ---
        // Add a hidden field to store the ID of the logged-in user making the booking.
        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);  // Define the type of the hidden field as an integer.

        // --- Date-Time selection for booking ---
        // Calculate the nearest full hour for the default start time of the booking.
        $currenttimestamp = time();  // Get the current time in Unix timestamp format.
        $nextFullHour = ceil($currenttimestamp / 3600) * 3600;  // Round the time to the nearest full hour.

        // Add date-time selector elements for both start and end time of the booking.
        $mform->addElement('date_time_selector', 'start_date_time', get_string('startdatetime', 'local_roombooking'));
        $mform->setDefault('start_date_time', $nextFullHour);
        $mform->addRule('start_date_time', get_string('required'), 'required', null, 'client');

        $mform->addElement('date_time_selector', 'end_date_time', get_string('enddatetime', 'local_roombooking'));
        $mform->setDefault('end_date_time', $nextFullHour + (2 * 3600)); // Default to 2 hours later
        $mform->addRule('end_date_time', get_string('required'), 'required', null, 'client');

        // --- Recurrence selection ---
        // Provide options for recurrence type: once, daily, weekly. Monthly is intentionally removed as requested.
        $recurrence_options = [
            0 => get_string('once', 'local_roombooking'),  // No recurrence (one-time booking).
            1 => get_string('daily', 'local_roombooking'),  // Recurs daily.
            2 => get_string('weekly', 'local_roombooking')  // Recurs weekly.
        ];
        // Add a select dropdown for recurrence type.
        $mform->addElement('select', 'recurrence', get_string('recurrence', 'local_roombooking'), $recurrence_options);
        $mform->setDefault('recurrence', 0); // Default to 'once'

        // Add a field to specify when the recurrence should end. This is only relevant if recurrence is selected.
        $mform->addElement('date_selector', 'recurrence_end_date', get_string('recurrenceenddate', 'local_roombooking'));
        
        // Disable the "recurrence end date" field if the user selects "once" (i.e., no recurrence).
        $mform->disabledIf('recurrence_end_date', 'recurrence', 'eq', 0);  // Only enabled for daily/weekly recurrence.

        // --- Submission buttons ---
        $this->add_action_buttons(true, get_string('bookroom', 'local_roombooking'));
    }

    private function get_approved_annual_plan_courses()
    {
        global $DB;
        $currentyear = (int)date('Y');
        
        // Get approved annual plan courses for current year with course codes
        $sql = "SELECT DISTINCT c.id, c.fullname, cap.courseid as course_code
                FROM {course} c
                JOIN {local_annual_plan_course} cap ON c.fullname = cap.coursename
                JOIN {local_annual_plan} ap ON cap.annualplanid = ap.id
                WHERE cap.approve = 1 
                AND cap.disabled = 0
                AND ap.year = :currentyear
                AND c.visible = 1
                AND c.id != 1
                ORDER BY c.fullname ASC";
        
        $courses = $DB->get_records_sql($sql, ['currentyear' => $currentyear]);
        
        // Format the results to include course code in the display name
        $formatted_courses = [];
        foreach ($courses as $course) {
            $display_name = $course->fullname;
            if (!empty($course->course_code)) {
                $display_name = $course->fullname . "   -   ". $course->course_code;
            }
            $formatted_courses[$course->id] = $display_name;
        }
        
        return $formatted_courses;
    }

    // Custom validation function for validating the submitted form data.
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);  // Start with basic Moodle form validation.

        global $DB; // Ensure the global $DB object is available

        // Check if room is selected
        if (empty($data['roomid'])) {
            $errors['roomid'] = get_string('required');
        }

        // Check if course is selected
        if (empty($data['courseid'])) {
            $errors['courseid'] = get_string('required');
        }

        // --- Check if end time is before or equal to start time ---
        // Validate that the booking end time is after the start time.
        if (isset($data['end_date_time']) && isset($data['start_date_time']) && $data['end_date_time'] <= $data['start_date_time']) {
            $errors['end_date_time'] = get_string('endbeforestart', 'local_roombooking');
        }

        // --- Check if the room is available ---
        // Use the booking repository to check whether the selected room is available at the requested time.
        if (isset($data['roomid']) && isset($data['start_date_time']) && isset($data['end_date_time']) && !empty($data['roomid'])) {
            try {
                if (!booking_repository::is_room_available($data['roomid'], $data['start_date_time'], $data['end_date_time'])) {
                    // Get alternative available rooms for the same time period
                    $capacity = isset($data['capacity']) ? (int)$data['capacity'] : 1;
                    
                    // Get all rooms that meet capacity requirements
                    $all_suitable_rooms = $DB->get_records_select('local_roombooking_rooms', 'capacity >= ?', [$capacity], 'capacity ASC');
                    
                    // Filter to only truly available rooms using the same logic as is_room_available
                    $available_room_ids = [];
                    foreach ($all_suitable_rooms as $room) {
                        if (booking_repository::is_room_available($room->id, $data['start_date_time'], $data['end_date_time'])) {
                            $available_room_ids[] = $room->id;
                        }
                    }
                    
                    if (!empty($available_room_ids)) {
                        // Format the room names with their details for display
                        $formatted_rooms = [];
                        foreach ($available_room_ids as $room_id) {
                            $room_details = room_repository::get($room_id);
                            if ($room_details) {
                                if (isset($room_details->roomtype) && in_array($room_details->roomtype, ['fixed', 'dynamic'])) {
                                    $roomtype = get_string($room_details->roomtype, 'local_roombooking');
                                } else {
                                    $roomtype = get_string('invalidroomtype', 'local_roombooking');
                                }
                                $capacity_label = get_string('capacity', 'local_roombooking');
                                $formatted_rooms[] = format_string($room_details->name) . ' (' . $roomtype . ', ' . $capacity_label . ': ' . $room_details->capacity . ')';
                            }
                        }
                        
                        $alternative_rooms_text = implode(', ', $formatted_rooms);
                        $errors['roomid'] = get_string('roomnotavailable', 'local_roombooking') . '<br/><br/>' . 
                                          '<strong>' . get_string('alternativerooms', 'local_roombooking') . '</strong><br/>' . 
                                          $alternative_rooms_text;
                    } else {
                        $errors['roomid'] = get_string('roomnotavailable', 'local_roombooking') . '<br/><br/>' . 
                                          get_string('noalternativerooms', 'local_roombooking');
                    }
                }
            } catch (\Exception $e) {
                // If room availability check fails, log it but don't block submission
                error_log('Room availability check failed: ' . $e->getMessage());
            }
        }
        
        // --- Check booking duration does not exceed the maximum allowed time (6 hours) ---
        // Calculate the duration of the booking and ensure it does not exceed the 6-hour limit.
        if (isset($data['end_date_time']) && isset($data['start_date_time'])) {
            $duration = $data['end_date_time'] - $data['start_date_time'];
            $max_duration = 6 * 3600;  // 6 hours in seconds.
            if ($duration > $max_duration) {
                $errors['end_date_time'] = get_string('exceedmaximumduration', 'local_roombooking', 6);
            }
        }

        // --- Validate that the recurrence end date is later than the start date ---
        // If the booking is recurring (daily/weekly), ensure the recurrence end date is valid.
        if (isset($data['recurrence']) && $data['recurrence'] != 0 && isset($data['recurrence_end_date']) && isset($data['start_date_time'])) {
            if ($data['recurrence_end_date'] < $data['start_date_time']) {
                $errors['recurrence_end_date'] = get_string('recurrenceendbeforestart', 'local_roombooking');
            }
        }

        // Check if capacity is a positive number
        if (isset($data['capacity']) && $data['capacity'] <= 0) {
            $errors['capacity'] = get_string('invalidcapacity', 'local_roombooking');
        }

        // Fetch the selected room's data to validate capacity
        if (isset($data['roomid']) && isset($data['capacity']) && !empty($data['roomid'])) {
            $room = $DB->get_record('local_roombooking_rooms', ['id' => $data['roomid']]);
            if ($room) {
                // Check if the requested capacity is greater than the room's capacity
                if ($data['capacity'] > $room->capacity) {
                    $errors['capacity'] = get_string('roomexceedsmaxcapacity', 'local_roombooking', $room->capacity);
                }
            }
        }

        return $errors;  // Return any errors found during validation.
    }
}
