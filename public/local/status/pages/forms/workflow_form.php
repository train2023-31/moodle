<?php
// ============================================================================
//  Local Status – Workflow Form
//  Form for creating and editing workflows using Moodle's mform
// ============================================================================

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');

use local_status\workflow_dashboard_manager;

// Authentication and permissions
require_login();
admin_externalpage_setup('local_status_dashboard');

// Check permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Get parameters
$workflow_id = optional_param('id', 0, PARAM_INT);

// Get workflow data if editing
$workflow = null;
if ($workflow_id) {
    $workflow = $DB->get_record('local_status_type', ['id' => $workflow_id]);
    if (!$workflow) {
        print_error('invalidworkflow', 'local_status');
    }
}

// Page setup
$PAGE->set_url(new moodle_url('/local/status/pages/forms/workflow_form.php', ['id' => $workflow_id]));
$PAGE->set_context($context);
$title = $workflow_id ? get_string('editworkflow', 'local_status') : get_string('addworkflow', 'local_status');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Include CSS
$PAGE->requires->css(new moodle_url('/local/status/styles.css'));

/**
 * Workflow form class
 */
class workflow_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $workflow = $this->_customdata['workflow'] ?? null;
        
        // Name field
        $mform->addElement('text', 'name', get_string('name', 'local_status'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addHelpButton('name', 'namehelp', 'local_status');
        
        if ($workflow) {
            $mform->freeze('name'); // Cannot change name after creation
        }
        
        // Display name (English)
        $mform->addElement('text', 'display_name_en', get_string('displayname_en', 'local_status'), ['size' => 50]);
        $mform->setType('display_name_en', PARAM_TEXT);
        $mform->addRule('display_name_en', null, 'required', null, 'client');
        
        // Display name (Arabic) - required
        $mform->addElement('text', 'display_name_ar', get_string('displayname_ar', 'local_status'), ['size' => 50]);
        $mform->setType('display_name_ar', PARAM_TEXT);
        $mform->addRule('display_name_ar', null, 'required', null, 'client');
        $mform->addHelpButton('display_name_ar', 'displayname_ar_help', 'local_status');
        
        // Plugin name - optional
        $mform->addElement('text', 'plugin_name', get_string('plugin', 'local_status'), ['size' => 30]);
        $mform->setType('plugin_name', PARAM_TEXT);
        $mform->addHelpButton('plugin_name', 'pluginhelp', 'local_status');
        
        // Sort order
        $mform->addElement('text', 'sort_order', get_string('sortorder', 'local_status'), ['size' => 10]);
        $mform->setType('sort_order', PARAM_INT);
        $mform->setDefault('sort_order', 0);
        
        // Active status
        $mform->addElement('checkbox', 'is_active', get_string('active', 'local_status'));
        $mform->setDefault('is_active', 1);
        
        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        // Action buttons
        $this->add_action_buttons(true, get_string('save'));
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Validate Display Name (Arabic) - required and cannot be empty or just whitespace
        if (empty($data['display_name_ar']) || trim($data['display_name_ar']) === '') {
            $errors['display_name_ar'] = get_string('required');
        }
        
        // Check for duplicate workflow name
        global $DB;
        $conditions = ['name' => $data['name']];
        if (!empty($data['id'])) {
            $conditions['id'] = ['!=', $data['id']];
            $sql = "name = :name AND id != :id";
            $params = ['name' => $data['name'], 'id' => $data['id']];
        } else {
            $sql = "name = :name";
            $params = ['name' => $data['name']];
        }
        
        if ($DB->record_exists_select('local_status_type', $sql, $params)) {
            $errors['name'] = get_string('duplicateworkflowname', 'local_status');
        }
        
        return $errors;
    }
}

// Create form
$customdata = ['workflow' => $workflow];
$mform = new workflow_form(null, $customdata);

// Handle form submission
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']));
} else if ($data = $mform->get_data()) {
    
    $workflow_data = [
        'name' => $data->name,
        'display_name_en' => $data->display_name_en,
        'display_name_ar' => $data->display_name_ar,
        'plugin_name' => $data->plugin_name ?: '',
        'is_active' => $data->is_active ? 1 : 0,
        'sort_order' => $data->sort_order
    ];
    
    try {
        if ($workflow_id) {
            // Update existing workflow
            workflow_dashboard_manager::update_workflow_type($workflow_id, $workflow_data);
            $message = get_string('workflowupdated', 'local_status');
        } else {
            // Create new workflow
            $workflow_id = workflow_dashboard_manager::create_workflow_type($workflow_data);
            $message = get_string('workflowcreated', 'local_status');
        }
        
        redirect(new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']), 
                $message, null, \core\output\notification::NOTIFY_SUCCESS);
                
    } catch (Exception $e) {
        redirect(new moodle_url('/local/status/pages/forms/workflow_form.php', ['id' => $workflow_id]), 
                $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Set form data
if ($workflow) {
    $mform->set_data($workflow);
}

// Start output
echo $OUTPUT->header();

// Breadcrumb navigation
echo html_writer::start_div('mb-3');
echo html_writer::link(
    new moodle_url('/local/status/pages/dashboard.php'),
    get_string('workflowdashboard', 'local_status')
) . ' / ';
echo html_writer::link(
    new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']),
    get_string('workflows', 'local_status')
) . ' / ';
echo html_writer::span($title);
echo html_writer::end_div();

// Display form
$mform->display();

// Footer
echo $OUTPUT->footer(); 