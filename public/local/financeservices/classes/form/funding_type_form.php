<?php

namespace local_financeservices\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for adding/editing a Funding Type.
 */
class funding_type_form extends \moodleform {

    public function __construct($actionurl = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        global $PAGE;

        if (empty($actionurl)) {
            $actionurl = $PAGE->url; // ✅ Send POST to current page
        }

        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     * Defines the form elements.
     */
    public function definition() {
        $mform = $this->_form;

        // English Funding Type Name
        $mform->addElement('text', 'funding_type_en', get_string('fundingtypeen', 'local_financeservices'));
        $mform->setType('funding_type_en', PARAM_TEXT);
        $mform->addRule('funding_type_en', get_string('required'), 'required', null, 'client');
        $mform->addRule('funding_type_en', get_string('required'), 'required', null, 'server');

        // Arabic Funding Type Name
        $mform->addElement('text', 'funding_type_ar', get_string('fundingtypear', 'local_financeservices'));
        $mform->setType('funding_type_ar', PARAM_TEXT);
        $mform->addRule('funding_type_ar', get_string('required'), 'required', null, 'client');
        $mform->addRule('funding_type_ar', get_string('required'), 'required', null, 'server');
        
        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'deleted');
        $mform->setType('deleted', PARAM_INT);
        $mform->setDefault('deleted', 0);

        // Submit buttons
        $this->add_action_buttons(true, get_string('savechanges', 'local_financeservices'));
    }

    /**
     * Extra server-side validation.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty(trim($data['funding_type_en']))) {
            $errors['funding_type_en'] = get_string('required');
        }

        if (empty(trim($data['funding_type_ar']))) {
            $errors['funding_type_ar'] = get_string('required');
        }

        return $errors;
    }
}
