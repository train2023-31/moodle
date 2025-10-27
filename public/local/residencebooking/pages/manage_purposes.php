<?php
// ============================================================================
//  CRUD for local_residencebooking_purpose  (Residence Purposes)
//  Includes:
//    ① Global tab nav: Apply / Manage / Lookups
//    ② Local tab nav:  Manage Types / Manage Purposes
// ============================================================================

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context); // Admin only

$action  = optional_param('action', '', PARAM_ALPHA); // list | add | edit | hide | restore
$id      = optional_param('id', 0, PARAM_INT);
$sesskey = sesskey();

// -----------------------------------------------------------------------------
// Page setup
// -----------------------------------------------------------------------------
$PAGE->set_url(new moodle_url('/local/residencebooking/pages/manage_purposes.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('managepurposes', 'local_residencebooking'));
$PAGE->set_heading(get_string('managepurposes', 'local_residencebooking'));

// -----------------------------------------------------------------------------
// ① Global 3-tab navigation (Apply / Manage / Lookups)
// -----------------------------------------------------------------------------
$maintabs = [
    new tabobject('form',
        new moodle_url('/local/residencebooking/index.php', ['tab' => 'form']),
        get_string('applyrequests', 'local_residencebooking')),
    new tabobject('manage',
        new moodle_url('/local/residencebooking/index.php', ['tab' => 'manage']),
        get_string('managerequests', 'local_residencebooking')),
    new tabobject('lookups',
        new moodle_url('/local/residencebooking/index.php', ['tab' => 'lookups']),
        get_string('management', 'local_residencebooking')),
];
$maintab = 'lookups';

// -----------------------------------------------------------------------------
// ② Local 2-tab navigation (Manage Types / Manage Purposes)
// -----------------------------------------------------------------------------
$subtabs = [
    new tabobject('manage_types',
        new moodle_url('/local/residencebooking/pages/manage_types.php'),
        get_string('managetypes', 'local_residencebooking')),
    new tabobject('manage_purposes',
        new moodle_url('/local/residencebooking/pages/manage_purposes.php'),
        get_string('managepurposes', 'local_residencebooking')),
];
$currentsub = 'manage_purposes';

// -----------------------------------------------------------------------------
// Output header and tab navigation
// -----------------------------------------------------------------------------
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $maintab);
echo $OUTPUT->tabtree($subtabs, $currentsub);
echo $OUTPUT->heading(get_string('managepurposes', 'local_residencebooking'));

// -----------------------------------------------------------------------------
// Form for Add / Edit
// -----------------------------------------------------------------------------
require_once($CFG->libdir.'/formslib.php');

class local_residencebooking_purpose_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add hidden elements for form action handling
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);
        
        // English description field
        $mform->addElement('text', 'description_en', get_string('description_en', 'local_residencebooking'), ['size' => 50]);
        $mform->setType('description_en', PARAM_TEXT);
        $mform->addRule('description_en', get_string('required'), 'required', null, 'client');
        
        // Arabic description field
        $mform->addElement('text', 'description_ar', get_string('description_ar', 'local_residencebooking'), ['size' => 50, 'dir' => 'rtl']);
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', get_string('required'), 'required', null, 'client');
        
        // Hidden deleted field (for editing)
        $mform->addElement('hidden', 'deleted');
        $mform->setType('deleted', PARAM_INT);
        $mform->setDefault('deleted', 0);

        $this->add_action_buttons(true);
    }
}

// -----------------------------------------------------------------------------
// Action router
// -----------------------------------------------------------------------------
switch ($action) {

    /* =========================================================
       ADD or EDIT logic
    ========================================================= */
    case 'add':
    case 'edit':
        // Setup form with the correct action URL to preserve action parameter
        $form_url = new moodle_url('/local/residencebooking/pages/manage_purposes.php', [
            'action' => $action,
            'id' => $id
        ]);
        $mform = new local_residencebooking_purpose_form($form_url);

        // Load existing record if editing
        if ($action === 'edit') {
            $record = $DB->get_record('local_residencebooking_purpose', ['id' => $id], '*', MUST_EXIST);
            $mform->set_data($record);
        } else {
            // Set default values for add mode
            $data = new stdClass();
            $data->action = $action;
            $data->deleted = 0;
            $mform->set_data($data);
        }

        // Cancel → return to list
        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/local/residencebooking/pages/manage_purposes.php'));

        // Save form
        } elseif ($data = $mform->get_data()) {
            // No need to set legacy field anymore - it's been removed
            
            if ($action === 'edit') {
                $data->id = $id;
                $DB->update_record('local_residencebooking_purpose', $data);
            } else {
                $DB->insert_record('local_residencebooking_purpose', $data);
            }

            redirect(
                new moodle_url('/local/residencebooking/pages/manage_purposes.php'),
                get_string('changessaved'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }

        // Display the form
        $mform->display();
        break;

    /* =========================================================
       HIDE / RESTORE actions
    ========================================================= */
    case 'hide':
        require_sesskey();
        $DB->set_field('local_residencebooking_purpose', 'deleted', 1, ['id' => $id]);
        redirect(new moodle_url('/local/residencebooking/pages/manage_purposes.php'));
        break;

    case 'restore':
        require_sesskey();
        $DB->set_field('local_residencebooking_purpose', 'deleted', 0, ['id' => $id]);
        redirect(new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['showhidden' => 1]));
        break;

    /* =========================================================
       Default LIST view
    ========================================================= */
    default:
        $showhidden = optional_param('showhidden', 0, PARAM_BOOL);

        $select   = $showhidden ? null : ['deleted' => 0];
        $purposes = $DB->get_records('local_residencebooking_purpose', $select, 'id ASC');

        // Build HTML table
        $table = new html_table();
        $table->head = [
            get_string('id', 'local_residencebooking'),
            get_string('description', 'local_residencebooking'),
            get_string('actions', 'local_residencebooking')
        ];

        foreach ($purposes as $p) {
            $actions = [];

            // Edit link
            $actions[] = html_writer::link(
                new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['action' => 'edit', 'id' => $p->id]),
                get_string('edit')
            );

            // Hide or Restore link
            if (!$p->deleted) {
                $actions[] = html_writer::link(
                    new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['action' => 'hide', 'id' => $p->id, 'sesskey' => $sesskey]),
                    get_string('hide', 'local_residencebooking')
                );
            } else {
                $actions[] = html_writer::link(
                    new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['action' => 'restore', 'id' => $p->id, 'sesskey' => $sesskey]),
                    get_string('restore', 'local_residencebooking')
                );
            }

            // Display description in current language
            $display_desc = current_language() === 'ar' ? $p->description_ar : $p->description_en;
            
            // Fallback to other language if current language field is empty
            if (empty($display_desc)) {
                $display_desc = current_language() === 'ar' ? $p->description_en : $p->description_ar;
            }

            $table->data[] = [$p->id, s($display_desc), implode(' | ', $actions)];
        }

        // Display table
        echo html_writer::table($table);

        // Add new purpose button
        echo $OUTPUT->single_button(
            new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['action' => 'add']),
            get_string('addnewpurpose', 'local_residencebooking'),
            'get',
            ['class' => 'btn btn-primary']
        );

        // Show/hide toggle link
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/local/residencebooking/pages/manage_purposes.php', ['showhidden' => $showhidden ? 0 : 1]),
                $showhidden ? get_string('showonlyvisible', 'local_residencebooking')
                            : get_string('showhidden', 'local_residencebooking')
            ),
            'mt-3'
        );
        break;
}

// -----------------------------------------------------------------------------
// Footer
// -----------------------------------------------------------------------------
echo $OUTPUT->footer();
