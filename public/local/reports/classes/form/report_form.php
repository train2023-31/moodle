<?php
namespace local_reports\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class report_form extends \moodleform {

    /** Build form. */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        // Custom data sent from JS.
        $courseid  = $this->_customdata['courseid'];
        $userid    = $this->_customdata['userid'];
        $reportid  = $this->_customdata['reportid'];

        /* ------------------------------------------------------------------
         * Load default record (if editing)
         * ------------------------------------------------------------------ */
        $default = new \stdClass();
        if ($reportid) {
            $default = $DB->get_record('local_reports',
                ['id' => $reportid], '*', MUST_EXIST);
        }

        /* ------------------------------------------------------------------
         * Hidden IDs
         * ------------------------------------------------------------------ */
        $mform->addElement('hidden', 'id', $reportid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);


        /* ------------------------------------------------------------------
        * Student info (readonly)
        * ------------------------------------------------------------------ */
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $username = fullname($user);

        // Display static name
        $mform->addElement('static', 'userstatic', get_string('user', 'local_reports'), $username);

        // Still send userid as hidden field
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);
        /* ------------------------------------------------------------------
         * Student selector (enrolled users)
         * ------------------------------------------------------------------ */
        $context  = \context_course::instance($courseid);
        $enrolled = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname');

        $usermenu = [];
        foreach ($enrolled as $u) {
            $usermenu[$u->id] = fullname($u);
        }
        // $mform->addElement('static', 'الاسم: ', get_string('label', 'plugin'), 'Some static text');
        // $mform->addElement('select', 'userid', get_string('user'), $usermenu);
        // $mform->setType('userid', PARAM_INT);
        // $mform->addRule('userid', null, 'required', null, 'client');
        // $mform->setDefault('userid', $userid);   // pre‑select

        /* ------------------------------------------------------------------
         * Report fields
         * ------------------------------------------------------------------ */
        $mform->addElement('textarea', 'futureexpec',
            get_string('futureexpec', 'local_reports'),
            'wrap="virtual" rows="4" cols="60"');
        $mform->addElement('textarea', 'dep_op',
            get_string('dep_op', 'local_reports'),
            'wrap="virtual" rows="4" cols="60"');
        $mform->addElement('textarea', 'seg_path',
            get_string('seg_path', 'local_reports'),
            'wrap="virtual" rows="3" cols="60"');
        $mform->addElement('text', 'researchtitle',
            get_string('researchtitle', 'local_reports'),
            'size="60"');

        /* ------------------------------------------------------------------
         * Report type
         * ------------------------------------------------------------------ */
        $types = $DB->get_records_menu('local_report_type', null,
                   '', 'id, report_type_string_ar');
        $mform->addElement('select', 'type_id',
            get_string('type_id', 'local_reports'), $types);
        $mform->setType('type_id', PARAM_INT);


        /* ------------------------------------------------------------------
         * Buttons
         * ------------------------------------------------------------------ */
        $this->add_action_buttons(true, get_string('submit', 'local_reports'));

        // Defaults (when editing).
        $this->set_data($default);
    }

    /**
     * Save / update record when the modal is submitted.
     *
     * @param \stdClass $data cleaned form data
     * @return bool
     */
    public function process_data($data) {
        global $DB, $USER;
        $now = time();

        $record = (object)[
            'userid'        => $data->userid,
            'courseid'      => $data->courseid,
            'futureexpec'   => $data->futureexpec,
            'dep_op'        => $data->dep_op,
            'seg_path'      => $data->seg_path,
            'researchtitle' => $data->researchtitle,
            'type_id'       => $data->type_id,
            'status_id'     => 30,
        ];

        if (empty($data->id)) {            // insert
            $record->timecreated  = $now;
            $record->timemodified = $now;
            $record->createdby    = $USER->id;
            $record->modifiedby   = $USER->id;
            $DB->insert_record('local_reports', $record);
        } else {                           // update
            $record->id            = $data->id;
            $record->timemodified  = $now;
            $record->modifiedby    = $USER->id;
            $DB->update_record('local_reports', $record);
        }
        return true;   // tells the modal the submission succeeded
    }
}
