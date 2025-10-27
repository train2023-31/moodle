<?php
// local/computerservice/classes/form/device_form.php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for adding a new device type.
 *   • Asks for both English and Arabic names.
 */
class device_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // English label.
        $mform->addElement('text', 'devicename_en',
            get_string('devicename_en', 'local_computerservice'));
        $mform->setType('devicename_en', PARAM_TEXT);
        $mform->addRule('devicename_en', get_string('required'), 'required', null, 'client');

        // Arabic label.
        $mform->addElement('text', 'devicename_ar',
            get_string('devicename_ar', 'local_computerservice'));
        $mform->setType('devicename_ar', PARAM_TEXT);
        $mform->addRule('devicename_ar', get_string('required'), 'required', null, 'client');

        // Submit / cancel.
        $this->add_action_buttons(true, get_string('submit'));
    }
}
