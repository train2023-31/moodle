<?php
// ============================================================================
//  CRUD for local_residencebooking_types  (Residence Types)
//  Includes:
//    ① Global tab nav: Apply / Manage / Lookups
//    ② Local tab nav:  Manage Types / Manage Purposes
// ============================================================================

require_once(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context); // Admin-only

$action  = optional_param('action', '', PARAM_ALPHA); // list | add | edit | hide | restore
$id      = optional_param('id', 0, PARAM_INT);
$sesskey = sesskey();

// -----------------------------------------------------------------------------
// Moodle page setup
// -----------------------------------------------------------------------------
$PAGE->set_url(new moodle_url('/local/residencebooking/pages/manage_types.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('managetypes', 'local_residencebooking'));
$PAGE->set_heading(get_string('managetypes', 'local_residencebooking'));

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
$currentsub = 'manage_types';

// -----------------------------------------------------------------------------
// Output header and navigation
// -----------------------------------------------------------------------------
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $maintab);    // global tabs
echo $OUTPUT->tabtree($subtabs, $currentsub);  // local tabs
echo $OUTPUT->heading(get_string('managetypes', 'local_residencebooking'));

// -----------------------------------------------------------------------------
// Internal mform for Add / Edit operations
// -----------------------------------------------------------------------------
require_once($CFG->libdir . '/formslib.php');

class local_residencebooking_type_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add hidden elements for form action handling
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);
        
        // English name field
        $mform->addElement('text', 'type_name_en', get_string('type_name_en', 'local_residencebooking'), ['size' => 50]);
        $mform->setType('type_name_en', PARAM_TEXT);
        $mform->addRule('type_name_en', get_string('required'), 'required', null, 'client');
        
        // Arabic name field
        $mform->addElement('text', 'type_name_ar', get_string('type_name_ar', 'local_residencebooking'), ['size' => 50, 'dir' => 'rtl']);
        $mform->setType('type_name_ar', PARAM_TEXT);
        $mform->addRule('type_name_ar', get_string('required'), 'required', null, 'client');
        
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
       ADD / EDIT: show form and handle submission
    ========================================================= */
    case 'add':
    case 'edit':
        // Setup form with the correct action URL to preserve action parameter
        $form_url = new moodle_url('/local/residencebooking/pages/manage_types.php', [
            'action' => $action,
            'id' => $id
        ]);
        $mform = new local_residencebooking_type_form($form_url);

        // If editing, load existing record
        if ($action === 'edit') {
            $record = $DB->get_record('local_residencebooking_types', ['id' => $id], '*', MUST_EXIST);
            $mform->set_data($record);
        } else {
            // Set default values for add mode
            $data = new stdClass();
            $data->action = $action;
            $data->deleted = 0;
            $mform->set_data($data);
        }

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/local/residencebooking/pages/manage_types.php'));

        } elseif ($data = $mform->get_data()) {
            // No need to set legacy field anymore - it's been removed
            
            if ($action === 'edit') {
                $data->id = $id;
                $DB->update_record('local_residencebooking_types', $data);
            } else {
                $DB->insert_record('local_residencebooking_types', $data);
            }

            redirect(
                new moodle_url('/local/residencebooking/pages/manage_types.php'),
                get_string('changessaved'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }

        // Display the form
        $mform->display();
        break;

    /* =========================================================
       HIDE / RESTORE: soft-delete or restore records
    ========================================================= */
    case 'hide':
        require_sesskey();
        $DB->set_field('local_residencebooking_types', 'deleted', 1, ['id' => $id]);
        redirect(new moodle_url('/local/residencebooking/pages/manage_types.php'));
        break;

    case 'restore':
        require_sesskey();
        $DB->set_field('local_residencebooking_types', 'deleted', 0, ['id' => $id]);
        redirect(new moodle_url('/local/residencebooking/pages/manage_types.php', ['showhidden' => 1]));
        break;

    /* =========================================================
       LIST view: show all current residence types
    ========================================================= */
    default:
        $showhidden = optional_param('showhidden', 0, PARAM_BOOL);

        $select = $showhidden ? null : ['deleted' => 0];
        $types = $DB->get_records('local_residencebooking_types', $select, 'id ASC');

        $table = new html_table();
        $table->head = [
            get_string('id', 'local_residencebooking'),
            get_string('type_name', 'local_residencebooking'),
            get_string('actions', 'local_residencebooking')
        ];

        foreach ($types as $t) {
            $actions = [];

            // Edit action
            $actions[] = html_writer::link(
                new moodle_url('/local/residencebooking/pages/manage_types.php', ['action' => 'edit', 'id' => $t->id]),
                get_string('edit')
            );

            // Hide or Restore action
            if (!$t->deleted) {
                $actions[] = html_writer::link(
                    new moodle_url('/local/residencebooking/pages/manage_types.php', ['action' => 'hide', 'id' => $t->id, 'sesskey' => $sesskey]),
                    get_string('hide', 'local_residencebooking')
                );
            } else {
                $actions[] = html_writer::link(
                    new moodle_url('/local/residencebooking/pages/manage_types.php', ['action' => 'restore', 'id' => $t->id, 'sesskey' => $sesskey]),
                    get_string('restore', 'local_residencebooking')
                );
            }

            // Display name in current language
            $display_name = current_language() === 'ar' ? $t->type_name_ar : $t->type_name_en;
            
            // Fallback to other language if current language field is empty
            if (empty($display_name)) {
                $display_name = current_language() === 'ar' ? $t->type_name_en : $t->type_name_ar;
            }

            $table->data[] = [$t->id, s($display_name), implode(' | ', $actions)];
        }

        // Render table
        echo html_writer::table($table);

        // Add New button
        echo $OUTPUT->single_button(
            new moodle_url('/local/residencebooking/pages/manage_types.php', ['action' => 'add']),
            get_string('addnewtype', 'local_residencebooking'),
            'get',
            ['class' => 'btn btn-primary']
        );

        // Toggle hidden records link
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/local/residencebooking/pages/manage_types.php', ['showhidden' => $showhidden ? 0 : 1]),
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
