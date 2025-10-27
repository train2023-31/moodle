<?php
namespace local_computerservice\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Filter form for managing Computer Service requests.
 * Filters include status, course, and user.
 */
class filter_form extends \moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // ─────────────────────────────────────────────────────────────────────
        // STATUS FILTER – Workflow steps (type_id = 4)
        // ─────────────────────────────────────────────────────────────────────
        $langfield = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';

        // Retrieve all status steps
        $records = $DB->get_records_sql("
            SELECT id, $langfield AS label
              FROM {local_status}
             WHERE type_id = :typeid
          ORDER BY seq ASC
        ", ['typeid' => 4]);

        $statusoptions = ['' => get_string('allstatuses', 'local_computerservice')];
        foreach ($records as $status) {
            $statusoptions[$status->id] = $status->label;
        }

        $mform->addElement(
            'select',
            'statusid',
            get_string('statusfilter', 'local_computerservice'),
            $statusoptions,
            ['class' => 'form-select']
        );
        $mform->setType('statusid', PARAM_INT);

        // ─────────────────────────────────────────────────────────────────────
        // COURSE FILTER – Only visible courses excluding front page (id != 1)
        // ─────────────────────────────────────────────────────────────────────
        $courses = $DB->get_records_sql(
            "SELECT id, fullname FROM {course} WHERE visible = 1 AND id != 1"
        );

        $courseoptions = ['' => get_string('allcourses', 'local_computerservice')];
        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        $mform->addElement(
            'select',
            'courseid',
            get_string('choosecourse', 'local_computerservice'),
            $courseoptions,
            ['class' => 'form-select']
        );
        $mform->setType('courseid', PARAM_INT);

        // ─────────────────────────────────────────────────────────────────────
        // USER FILTER – Sorted by full name
        // ─────────────────────────────────────────────────────────────────────
        // Depending on DB engine, CONCAT might not work inside get_records_menu
        $users = $DB->get_records_sql("
            SELECT id, CONCAT(firstname, ' ', lastname) AS fullname
              FROM {user}
          ORDER BY lastname ASC, firstname ASC
        ");

        $useroptions = ['' => get_string('allusers', 'local_computerservice')];
        foreach ($users as $user) {
            $useroptions[$user->id] = $user->fullname;
        }

        $mform->addElement(
            'select',
            'userid',
            get_string('chooseuser', 'local_computerservice'),
            $useroptions,
            ['class' => 'form-select']
        );
        $mform->setType('userid', PARAM_INT);

        // ─────────────────────────────────────────────────────────────────────
        // URGENCY FILTER – All requests, Urgent, Not Urgent
        // ─────────────────────────────────────────────────────────────────────
        $urgencyoptions = [
            '' => get_string('allrequests', 'local_computerservice'),
            '1' => get_string('urgent', 'local_computerservice'),
            '0' => get_string('not_urgent', 'local_computerservice')
        ];

        $mform->addElement(
            'select',
            'urgency',
            get_string('urgencyfilter', 'local_computerservice'),
            $urgencyoptions,
            ['class' => 'form-select']
        );
        $mform->setType('urgency', PARAM_TEXT);

        // ─────────────────────────────────────────────────────────────────────
        // SUBMIT BUTTON
        // ─────────────────────────────────────────────────────────────────────
        $mform->addElement('submit', 'filterbutton', get_string('filter', 'local_computerservice'));
        
        // Set default values to ensure "All requests" is selected by default
        $mform->setDefault('urgency', '');
    }
}
