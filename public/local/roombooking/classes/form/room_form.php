<?php

namespace local_roombooking\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class room_form extends \moodleform
{
    public function definition()
    {
        $mform = $this->_form;

        // Name field (required)
        $mform->addElement('text', 'name', get_string('roomname', 'local_roombooking'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        // Capacity field (required)
        $mform->addElement('text', 'capacity', get_string('capacity', 'local_roombooking'));
        $mform->setType('capacity', PARAM_INT);
        $mform->addRule('capacity', get_string('required'), 'required', null, 'client');

        // Room Type field (required)
        $options = [
            'fixed' => get_string('fixed', 'local_roombooking'),
            'dynamic' => get_string('dynamic', 'local_roombooking'),
        ];
        $mform->addElement('select', 'roomtype', get_string('roomtype', 'local_roombooking'), $options);
        $mform->addRule('roomtype', get_string('required'), 'required', null, 'client');
        $mform->setType('roomtype', PARAM_ALPHA);

        // Location field (optional)
        $mform->addElement('text', 'location', get_string('location', 'local_roombooking'));
        $mform->setType('location', PARAM_TEXT);

        // Description field (optional)
        $mform->addElement('textarea', 'description', get_string('description', 'local_roombooking'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('description', PARAM_TEXT);

        // Hidden id field (for editing)
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // Action buttons
        $this->add_action_buttons();
    }

    // Custom validation
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Ensure capacity is a positive number
        if ($data['capacity'] <= 0) {
            $errors['capacity'] = get_string('invalidcapacity', 'local_roombooking');
        }

        return $errors;
    }
}
