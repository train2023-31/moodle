<?php

namespace local_financeservices\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Finance Services Filter Form
 */
class filter_form extends \moodleform {

    public function definition() {
        global $DB;
        $mform = $this->_form;

        // ------------------------------------
        // Course filter
        // ------------------------------------
        $courses = $DB->get_records_sql('SELECT id, fullname FROM {course} WHERE visible = 1 AND id != 1');
        $courseoptions = ['' => get_string('allcourses', 'local_financeservices')];

        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        $mform->addElement('select', 'course_id', get_string('course', 'local_financeservices'), $courseoptions);
        $mform->setType('course_id', PARAM_INT);

        // ------------------------------------
        // Funding Type filter (dynamic EN/AR)
        // ------------------------------------
        $langfield = current_language() === 'ar' ? 'funding_type_ar' : 'funding_type_en';
        $fundingtypes = $DB->get_records_sql_menu("SELECT id, $langfield AS name FROM {local_financeservices_funding_type} WHERE deleted = 0");
        $fundingoptions = ['' => get_string('allfundingtypes', 'local_financeservices')] + $fundingtypes;

        $mform->addElement('select', 'funding_type_id', get_string('fundingtype', 'local_financeservices'), $fundingoptions);
        $mform->setType('funding_type_id', PARAM_INT);

        // ------------------------------------
        // Status filter (dynamic EN/AR)
        // ------------------------------------
        $langfieldstatus = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';

        $statuses = $DB->get_records_sql_menu("
            SELECT id, $langfieldstatus AS name
              FROM {local_status}
             WHERE type_id = :typeid
               AND $langfieldstatus <> '-------------'
             ORDER BY seq ASC
        ", ['typeid' => 2]);

        $statusoptions = ['' => get_string('allstatuses', 'local_financeservices')] + $statuses;

        $mform->addElement(
            'select',
            'statusid',
            get_string('statusfilter', 'local_financeservices'),
            $statusoptions,
            ['class' => 'form-select']
        );
        $mform->setType('statusid', PARAM_INT);

        // ------------------------------------
        // Clause filter (dynamic EN/AR)
        // ------------------------------------
        $langfieldclause = current_language() === 'ar' ? 'clause_name_ar' : 'clause_name_en';
        $clauses = $DB->get_records_sql_menu("SELECT id, $langfieldclause AS name FROM {local_financeservices_clause} WHERE deleted = 0");
        $clauseoptions = ['' => get_string('allclauses', 'local_financeservices')] + $clauses;

        $mform->addElement('select', 'clause_id', get_string('clause', 'local_financeservices'), $clauseoptions);
        $mform->setType('clause_id', PARAM_INT);

        // ------------------------------------
        // Filter button
        // ------------------------------------
        $mform->addElement('submit', 'filter', get_string('filter', 'local_financeservices'));
    }
}
