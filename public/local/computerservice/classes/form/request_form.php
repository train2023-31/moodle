<?php

namespace local_computerservice\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class request_form extends \moodleform {

    public function __construct($actionurl = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition() {
        global $USER, $DB;

        // Get the form object.
        $mform = $this->_form;

        // Retrieve course ID from URL if set.
        $courseid = optional_param('id', 0, PARAM_INT);

        // Add hidden ID if editing.
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }

        // Fetch courses available to the user.
        if ($courseid > 0) {
            if (enrol_get_users_courses($USER->id, true, null, 'fullname ASC')) {
                $courses = [get_course($courseid)];
            }

            $course = get_course($courseid);
            $categoryid = $course->category;

            // Step 3: Get the context for that category
            $context = \context_coursecat::instance($categoryid);
           
            
             // Check if user has capability in this category
            if (has_capability('moodle/category:viewcourselist', $context, $USER)) {
                // Get all visible courses in this category
                // $category_courses = get_courses($category->id, 'fullname ASC', '*');
                $courses = [get_course($courseid)];
            }
        } else {
            // Get approved annual plan courses for current year with course codes
            $courses = $this->get_approved_annual_plan_courses();
        }

        // Course selection.
        $courseoptions = [];
        if ($courseid > 0) {
            // Handle object format for specific course
            foreach ($courses as $course) {
                $courseoptions[$course->id] = format_string($course->fullname);
            }
        } else {
            // Handle array format from get_approved_annual_plan_courses (id => display_name)
            $courseoptions = $courses;
        }

        // Add course selection dropdown.
        $mform->addElement('select', 'courseid', get_string('choosecourse', 'local_computerservice'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);
        $mform->addRule('courseid', null, 'required', null, 'client');

        // Number of devices.
        $mform->addElement('text', 'numdevices', get_string('numdevices', 'local_computerservice'));
        $mform->setType('numdevices', PARAM_INT);
        $mform->addRule('numdevices', null, 'numeric', null, 'client');
        $mform->addRule('numdevices', null, 'required', null, 'client');

        // ──────────────────────────────────────────────────────────────
        // Device selection — use devicename_en / devicename_ar
        // ──────────────────────────────────────────────────────────────
        $records = $DB->get_records('local_computerservice_devices', ['status' => 'active'], 'id ASC', 'id, devicename_en, devicename_ar');

        $devices = [];
        $lang = current_language();
        foreach ($records as $device) {
            $label = ($lang === 'ar') ? $device->devicename_ar : $device->devicename_en;
            $devices[$device->id] = $label;
        }

        if (!empty($devices)) {
            // Add a dropdown menu for device selection.
            $mform->addElement('select', 'deviceid', get_string('devices', 'local_computerservice'), $devices);
            $mform->setType('deviceid', PARAM_INT);
            $mform->addRule('deviceid', get_string('required'), 'required', null, 'client');
        } else {
            // Show a message if no active devices are available.
            $mform->addElement('static', 'nodevices', get_string('devices', 'local_computerservice'), get_string('nodevicesavailable', 'local_computerservice'));
        }

        // Comments (optional).
        $mform->addElement('textarea', 'comments', get_string('comments', 'local_computerservice'), 'wrap="virtual" rows="3" cols="50"');
        $mform->setType('comments', PARAM_TEXT);

        // Request date.
        $mform->addElement('date_selector', 'request_needed_by', get_string('request_needed_by', 'local_computerservice'));
        $mform->addHelpButton('request_needed_by', 'request_needed_by', 'local_computerservice');
        $mform->addRule('request_needed_by', null, 'required', null, 'client');
        
        // Note: is_urgent will be calculated automatically based on the needed-by date
        // We don't need to add it as a form element

        // Final action buttons.
        $this->add_action_buttons(true, get_string('submitrequest', 'local_computerservice'));
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

    public function set_data($default_values) {
        if (!isset($default_values->id) && !empty($this->_customdata['id'])) {
            $default_values->id = $this->_customdata['id'];
        }
        parent::set_data($default_values);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Allow requests for today or future dates (not past dates).
        if (!empty($data['request_needed_by'])) {
            // Get the start of today (midnight) to allow same-day requests
            $today_start = strtotime('today');
            if ($data['request_needed_by'] < $today_start) {
                $errors['request_needed_by'] = get_string('error_past_date', 'local_computerservice');
            }
        }

        return $errors;
    }
}
