<?php

namespace local_residencebooking\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class residencebooking_form extends \moodleform {

    public function __construct($actionurl = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    // Define the form
    public function definition() {
        global $USER, $DB;
        $mform = $this->_form;

        // Optional course ID passed via URL
        $courseid = optional_param('id', 0, PARAM_INT);

        // If editing an existing request, add hidden field for ID
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }

        /**
         * Fetch courses:
         * - If $courseid is passed and exists, fetch that specific course.
         * - Otherwise, fetch all visible courses (excluding site home).
         */
        if ($courseid > 0 && $DB->record_exists('course', ['id' => $courseid])) {
            $course = get_course($courseid);
            $categoryid = $course->category;
            $context = \context_coursecat::instance($categoryid);

            if (has_capability('moodle/category:viewcourselist', $context, $USER)) {
                $courses = [$course];
            } else {
                $courses = [];
            }

        } else {
            // Fetch only approved annual plan courses for current year
            try {
                // Check if annual plan tables exist
                $table_exists = $DB->get_manager()->table_exists('local_annual_plan_course');
                
                if ($table_exists) {
                    // Get current year
                    $current_year = date('Y');
                    
                    // Get approved courses from annual plans for current year
                    $courses = $DB->get_records_sql(
                        'SELECT DISTINCT c.id, c.fullname, apc.coursename as annual_plan_course_name, apc.courseid as annual_plan_course_id, ap.year as annual_plan_year
                         FROM {course} c 
                         INNER JOIN {local_annual_plan_course} apc ON c.idnumber = apc.courseid 
                         INNER JOIN {local_annual_plan} ap ON apc.annualplanid = ap.id
                         WHERE c.visible = 1 
                         AND c.id != 1 
                         AND c.idnumber IS NOT NULL 
                         AND c.idnumber != ""
                         AND apc.approve = 1 
                         AND apc.disabled = 0
                         AND ap.disabled = 0
                         AND ap.year = ?
                         ORDER BY c.fullname ASC',
                        [$current_year]
                    );
                    
                    error_log('Approved annual plan courses found for year ' . $current_year . ': ' . count($courses));
                } else {
                    throw new Exception('local_annual_plan_course table does not exist');
                }
                
            } catch (Exception $e) {
                // If the query fails, show empty courses list
                error_log('Approved annual plan course query failed: ' . $e->getMessage());
                $courses = [];
            }
        }

        // Prepare course dropdown options
        $courseoptions = [];
        foreach ($courses as $course) {
            // Use annual plan course name if available, otherwise use Moodle course name
            $display_name = !empty($course->annual_plan_course_name) 
                ? $course->annual_plan_course_name 
                : format_string($course->fullname);
            
            // Add annual plan course ID to display name for better identification
            if (!empty($course->annual_plan_course_id)) {
                $display_name .= ' (' . $course->annual_plan_course_id . ')';
            }
            
            $courseoptions[$course->id] = $display_name;
        }
        
        // Add empty option if no courses found
        if (empty($courseoptions)) {
            $courseoptions[0] = get_string('noapprovedcoursesfound', 'local_residencebooking', 'No approved courses found for current year');
        }

        // Course selection dropdown
        $mform->addElement('select', 'courseid', get_string('choosecourse', 'local_residencebooking'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);
        $mform->addRule('courseid', null, 'required', null, 'client');

        // Residence Type dropdown
        $mform->addElement('select', 'residence_type', get_string('residence_type', 'local_residencebooking'), $this->get_residence_types());
        $mform->setType('residence_type', PARAM_INT);
        $mform->addRule('residence_type', null, 'required', null, 'client');

        // Start Date
        $mform->addElement('date_selector', 'start_date', get_string('start_date', 'local_residencebooking'));
        $mform->setType('start_date', PARAM_INT);
        $mform->addRule('start_date', null, 'required', null, 'client');

        // End Date
        $mform->addElement('date_selector', 'end_date', get_string('end_date', 'local_residencebooking'));
        $mform->setType('end_date', PARAM_INT);
        $mform->addRule('end_date', null, 'required', null, 'client');

        //Guest Name
        /* $mform->addElement('text', 'guest_name', get_string('guest_name', 'local_residencebooking'));
        $mform->setType('guest_name', PARAM_TEXT);
        $mform->addRule('guest_name', null, 'required', null, 'client'); */

        // Use autocomplete for guest selection
        $mform->addElement('autocomplete', 'guest_name', get_string('guest_name', 'local_residencebooking'), [], [
            'ajax' => 'local_residencebooking/guest_autocomplete',
            'multiple' => false,
            'placeholder' => '-- الرجاء اختيار شخص --',
            'casesensitive' => false,
            'showsuggestions' => true,
            'noselectionstring' => '-- الرجاء اختيار شخص --',
            'tags' => false,
        ]);
        $mform->setType('guest_name', PARAM_TEXT);
        $mform->addRule('guest_name', null, 'required', null, 'client');

        // Remove the old select dropdown code
        /* $guests = $this->get_all_guests();
        $mform->addElement('select', 'guest_name', get_string('guest_name', 'local_residencebooking'), ['' => '-- الرجاء اختيار شخص --'] + $guests);
        $mform->setType('guest_name', PARAM_TEXT);
        $mform->addRule('guest_name', null, 'required', null, 'client'); */

        // Remove the old commented autocomplete code
        /* $mform->addElement('autocomplete', 'guest_name', get_string('guest_name', 'local_residencebooking'), [], [
            'ajax' => 'local_residencebooking/guest_autocomplete',
            'multiple' => false,
            'placeholder' => '-- الرجاء اختيار شخص --',
        ]);
        $mform->setType('guest_name', PARAM_TEXT);
        $mform->addRule('guest_name', null, 'required', null, 'client'); */


        // Service Number
        $mform->addElement('text', 'service_number', get_string('service_number', 'local_residencebooking'));
        $mform->setType('service_number', PARAM_TEXT);
        $mform->addRule('service_number', null, 'required', null, 'client');

        // Purpose dropdown
        $mform->addElement('select', 'purpose', get_string('purpose', 'local_residencebooking'), $this->get_purposes());
        $mform->setType('purpose', PARAM_INT);
        $mform->addRule('purpose', null, 'required', null, 'client');

        // Notes
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_residencebooking'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('notes', PARAM_TEXT);

        // Hidden user ID
        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid', PARAM_INT);

        // Submit button
        $this->add_action_buttons(true, get_string('submit', 'local_residencebooking'));
    }

    /**
     * Fetch active residence types for dropdown (exclude soft-deleted ones)
     */
    private function get_residence_types() {
        global $DB;
        
        $lang_field = current_language() === 'ar' ? 'type_name_ar' : 'type_name_en';
        
        // Get data with the appropriate language field
        $types = $DB->get_records_sql(
            "SELECT id, $lang_field AS display_name 
             FROM {local_residencebooking_types}
             WHERE deleted = 0",
            []
        );
        
        // Convert to menu format
        $menu = [];
        foreach ($types as $type) {
            $menu[$type->id] = $type->display_name;
        }
        
        return $menu;
    }

    /**
     * Fetch active purposes for dropdown (exclude soft-deleted ones)
     */
    private function get_purposes() {
        global $DB;
        
        $lang_field = current_language() === 'ar' ? 'description_ar' : 'description_en';
        
        // Get data with the appropriate language field
        $purposes = $DB->get_records_sql(
            "SELECT id, $lang_field AS display_name 
             FROM {local_residencebooking_purpose}
             WHERE deleted = 0",
            []
        );
        
        // Convert to menu format
        $menu = [];
        foreach ($purposes as $purpose) {
            $menu[$purpose->id] = $purpose->display_name;
        }
        
        return $menu;
    }

    /**
     * Optional custom validation
     */
    /*public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Custom validation can go here
        return $errors;
    }*/

    public function validation($data, $files) {
    $errors = parent::validation($data, $files);

    if (!empty($data['start_date']) && !empty($data['end_date'])) {
        if ($data['end_date'] < $data['start_date']) {
            $errors['end_date'] = get_string('endDateNotBeforeStartDate', 'local_residencebooking');
        }
    }

    return $errors;
}


// get_all_guests() method removed since we're now using autocomplete with AJAX


}
