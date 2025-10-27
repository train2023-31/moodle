<?php

namespace local_participant\form;

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__DIR__) . '/simple_workflow_manager.php');

class requests_filter_form extends \moodleform
{
    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Add the participant filters.
        $courses = $DB->get_records_menu('course', null, '', 'id, fullname');
        $mform->addElement('select', 'course_id', get_string('course'), [0 => get_string('all')] + $courses);
        $mform->setType('course_id', PARAM_INT);

        // Get participant types with language-aware names
        $types_records = $DB->get_records('local_participant_request_types', null, '', 'id, name_en, name_ar');
        $types = [];
        $current_lang = current_language();
        foreach ($types_records as $type) {
            // Use Arabic name if current language is Arabic, otherwise use English
            $display_name = ($current_lang == 'ar') ? $type->name_ar : $type->name_en;
            $types[$type->id] = $display_name;
        }
        $mform->addElement('select', 'participant_type_id', get_string('type', 'local_participant'), [0 => get_string('all')] + $types);
        $mform->setType('participant_type_id', PARAM_INT);

        $statuses = [];
        $all_statuses = \local_participant\simple_workflow_manager::get_all_statuses();
        foreach ($all_statuses as $status) {
            $statuses[$status->id] = \local_participant\simple_workflow_manager::get_status_name($status->id);
        }
        $mform->addElement('select', 'request_status_id', get_string('status', 'local_participant'), [0 => get_string('all')] + $statuses);
        $mform->setType('request_status_id', PARAM_INT);

        $this->add_action_buttons(false, get_string('filter', 'local_participant'));
    }
}
