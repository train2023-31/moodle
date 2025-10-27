<?php

namespace local_financeservices\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class add_form extends \moodleform
{
    public function __construct($actionurl = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition()
    {
        global $USER, $DB, $PAGE, $OUTPUT;

        // Use the $courseid from the URL or default to 0.
        $courseid = optional_param('id', 0, PARAM_INT);
        $mform = $this->_form;
        $categories = \core_course_category::get_all();
        
        // Conditionally add the hidden 'id' field if 'id' is provided in custom data.
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }

        if ($courseid > 0) {
            if (enrol_get_users_courses($USER->id, true, null, 'fullname ASC')) {
                $courses = [get_course($courseid)];
            }
            $course = get_course($courseid);

            // Step 2: Get the category ID from the course
            $categoryid = $course->category;

            // Step 3: Get the context for that category
            $context = \context_coursecat::instance($categoryid);

            // Check if user has capability in this category
            if (has_capability('moodle/category:viewcourselist', $context, $USER)) {
                $courses = [get_course($courseid)];
            }
        } else {
            // Get approved annual plan courses for current year with course codes
            $courses = $this->get_approved_annual_plan_courses();
        }

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
        
        $mform = $this->_form;

        // Course ID (dropdown of available courses)
        $mform->addElement('select', 'courseid', get_string('course', 'local_financeservices'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);
        $mform->addRule('courseid', null, 'required', null, 'client');

        /// Funding Types (dynamic language)
        $mform->addElement('select', 'funding_type_id', get_string('fundingtype', 'local_financeservices'), $this->get_funding_types());
        $mform->setType('funding_type_id', PARAM_INT);
        $mform->addRule('funding_type_id', null, 'required', null, 'client');

        // Clause (OPTIONAL) — NEW
        $mform->addElement('select', 'clause_id', get_string('clause', 'local_financeservices'), $this->get_clauses());
        $mform->setType('clause_id', PARAM_INT);
        // Clause is optional, no rule added

        // Price requested
        $mform->addElement('text', 'price_requested', get_string('pricerequested', 'local_financeservices'));
        $mform->setType('price_requested', PARAM_FLOAT);
        $mform->addRule('price_requested', null, 'required', null, 'client');

        // Notes (optional)
        $mform->addElement('textarea', 'notes', get_string('notes', 'local_financeservices'), 'wrap="virtual" rows="10" cols="50"');
        $mform->setType('notes', PARAM_TEXT);

        // Date required
        $mform->addElement('date_selector', 'date_type_required', get_string('daterequired', 'local_financeservices'));

        // Submit button
        $this->add_action_buttons(true, get_string('submit', 'local_financeservices'));
    }

    private function get_courses()
    {
        global $DB;
        $courses = $DB->get_records_menu('course', null, 'fullname ASC', 'id, fullname');
        return $courses;
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

    private function get_funding_types()
    {
        global $DB;
        $langfield = current_language() === 'ar' ? 'funding_type_ar' : 'funding_type_en';
        // Only get non-deleted funding types
        return $DB->get_records_menu('local_financeservices_funding_type', ['deleted' => 0], '', "id, $langfield");
    }

    private function get_clauses()
    {
        global $DB;
        $langfield = current_language() === 'ar' ? 'clause_name_ar' : 'clause_name_en';
        $currentyear = (int)date('Y');

        // Only get non-deleted clauses for the current year
        $sql = "SELECT id, $langfield AS name FROM {local_financeservices_clause} WHERE deleted = 0 AND clause_year = :yr";
        $clauses = $DB->get_records_sql_menu($sql, ['yr' => $currentyear]);

        return ['' => get_string('none', 'local_financeservices')] + $clauses;
    }
}
