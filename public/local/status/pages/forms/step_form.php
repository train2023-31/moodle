<?php
// ============================================================================
//  Local Status – Step Form
//  Form for creating and editing workflow steps using Moodle's mform
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
$step_id = optional_param('id', 0, PARAM_INT);
$workflow_id = optional_param('workflow_id', 0, PARAM_INT);

// Get step data if editing
$step = null;
if ($step_id) {
    $step = $DB->get_record('local_status', ['id' => $step_id]);
    if (!$step) {
        print_error('invalidstep', 'local_status');
    }
    $workflow_id = $step->type_id;
}

// Get workflow
if (!$workflow_id) {
    print_error('missingworkflow', 'local_status');
}

$workflow = $DB->get_record('local_status_type', ['id' => $workflow_id]);
if (!$workflow) {
    print_error('invalidworkflow', 'local_status');
}

// Get existing steps for sequence management
$existing_steps = workflow_dashboard_manager::get_workflow_steps($workflow_id, true); // Only active steps for positioning

// Page setup
$PAGE->set_url(new moodle_url('/local/status/pages/forms/step_form.php', ['id' => $step_id, 'workflow_id' => $workflow_id]));
$PAGE->set_context($context);
$title = $step_id ? get_string('editstep', 'local_status') : get_string('addstep', 'local_status');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Include CSS
$PAGE->requires->css(new moodle_url('/local/status/styles.css'));

/**
 * Step form class
 */
class step_form extends moodleform {
    
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $step = $this->_customdata['step'] ?? null;
        $workflow = $this->_customdata['workflow'];
        $existing_steps = $this->_customdata['existing_steps'];
        
        // Workflow info header
        $current_lang = current_language();
        $workflow_display_name = ($current_lang === 'ar' && !empty($workflow->display_name_ar)) 
            ? format_string($workflow->display_name_ar)
            : format_string($workflow->display_name_en);
            
        $mform->addElement('static', 'workflow_info', get_string('workflow', 'local_status'), 
                          $workflow_display_name);
        
        // Name field
        $mform->addElement('text', 'name', get_string('name', 'local_status'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'stepnamehelp', 'local_status');
        
        // Use $step_id from URL parameters as definitive source of truth for edit mode
        global $step_id;
        if ($step_id && $step_id > 0 && $step) {
            $mform->freeze('name'); // Cannot change name after creation
            // Remove required rule for frozen field to prevent validation errors
            $mform->removeElement('name');
            $mform->addElement('static', 'name_display', get_string('name', 'local_status'), format_string($step->name));
            $mform->addElement('hidden', 'name');
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->addRule('name', null, 'required', null, 'client');
        }
        
        // Display name (English)
        $mform->addElement('text', 'display_name_en', get_string('displayname_en', 'local_status'), ['size' => 50]);
        $mform->setType('display_name_en', PARAM_TEXT);
        $mform->addRule('display_name_en', null, 'required', null, 'client');
        
        // Display name (Arabic) - required
        $mform->addElement('text', 'display_name_ar', get_string('displayname_ar', 'local_status'), ['size' => 50]);
        $mform->setType('display_name_ar', PARAM_TEXT);
        $mform->addRule('display_name_ar', null, 'required', null, 'client');
        
        // Approval type
        $approval_types = [
            'capability' => get_string('capabilitybased', 'local_status'),
            'user' => get_string('userbased', 'local_status'),
            'any' => get_string('approval_any', 'local_status')
        ];
        $mform->addElement('select', 'approval_type', get_string('approvaltype', 'local_status'), $approval_types);
        $mform->addHelpButton('approval_type', 'approvaltypehelp', 'local_status');
        $mform->setDefault('approval_type', 'capability');
        
        // Capability (for capability-based approval)
        $mform->addElement('text', 'capability', get_string('capability', 'local_status'), ['size' => 50]);
        $mform->setType('capability', PARAM_TEXT);
        $mform->addHelpButton('capability', 'capabilityhelp', 'local_status');
        $mform->disabledIf('capability', 'approval_type', 'neq', 'capability');
        
        // Position in workflow - for inserting between steps
        // Use $step_id from URL parameters as definitive source of truth for add mode
        global $step_id;
        if (!$step_id || $step_id <= 0) { // Only show for new steps
            $position_options = ['' => get_string('selectposition', 'local_status')];
            
            if (empty($existing_steps)) {
                $position_options[1] = get_string('firststep', 'local_status');
            } else {
                // Sort steps by sequence to get proper order
                usort($existing_steps, function($a, $b) { return $a->seq - $b->seq; });
                
                // Find the initial and final step positions
                $initial_step_seq = null;
                $final_step_seq = null;
                foreach ($existing_steps as $step) {
                    if ($step->is_initial) {
                        $initial_step_seq = $step->seq;
                    }
                    if ($step->is_final) {
                        $final_step_seq = $step->seq;
                    }
                }
                
                $next_position = 0;
                foreach ($existing_steps as $existing_step) {
                    $next_position++;
                    
                    // PROTECTION: Never allow inserting after the final step
                    if ($existing_step->is_final) {
                        continue; // Skip final step - cannot add anything after it
                    }
                    
                    // Get localized display name for existing step
                    $current_lang = current_language();
                    $existing_step_display = ($current_lang === 'ar' && !empty($existing_step->display_name_ar)) 
                        ? format_string($existing_step->display_name_ar)
                        : format_string($existing_step->display_name_en);
                    $position_options[$next_position] = get_string('afterstep', 'local_status', $existing_step_display);
                }
            }
            
            $mform->addElement('select', 'insert_position', get_string('insertposition', 'local_status'), $position_options);
            $mform->addHelpButton('insert_position', 'insertpositionhelp', 'local_status');
            $mform->addRule('insert_position', null, 'required', null, 'client');
        }
        
        // Active status
        $mform->addElement('checkbox', 'is_active', get_string('active', 'local_status'));
        $mform->setDefault('is_active', 1);
        
        // Show step position info and actions ONLY when editing an existing step (not adding)
        // Use $step_id from URL parameters as definitive source of truth
        global $step_id;
        if ($step_id && $step_id > 0 && $step) {
            $step_info = '';
            if ($step->is_initial && $step->is_final) {
                $step_info = get_string('single_step_workflow', 'local_status');
            } elseif ($step->is_initial) {
                $step_info = get_string('initial_step_info', 'local_status');
            } elseif ($step->is_final) {
                $step_info = get_string('final_step_info', 'local_status');
            } else {
                $step_info = get_string('intermediate_step_info', 'local_status');
            }
            
            $mform->addElement('static', 'step_position_info', get_string('stepposition', 'local_status'), $step_info);
            
            // Add warning for protected steps
            if ($step->is_initial || $step->is_final) {
                $warning_text = html_writer::div(
                    html_writer::tag('i', '', ['class' => 'fa fa-exclamation-triangle']) . ' ' .
                    get_string('protected_step_warning', 'local_status'),
                    'alert alert-warning'
                );
                $mform->addElement('static', 'protection_warning', '', $warning_text);
            }
            
            // Add action buttons ONLY for existing steps that can be modified
            if (workflow_dashboard_manager::can_modify_step($step->id)) {
                $action_buttons = '';
                
                // Hide/Show button
                if ($step->is_active) {
                    $hide_url = new moodle_url('/local/status/pages/actions/step_actions.php', [
                        'action' => 'deactivate',
                        'step_id' => $step->id,
                        'sesskey' => sesskey()
                    ]);
                    $action_buttons .= html_writer::link($hide_url, get_string('hidestep', 'local_status'), 
                        ['class' => 'btn btn-warning btn-sm mr-2']);
                } else {
                    $show_url = new moodle_url('/local/status/pages/actions/step_actions.php', [
                        'action' => 'activate',
                        'step_id' => $step->id,
                        'sesskey' => sesskey()
                    ]);
                    $action_buttons .= html_writer::link($show_url, get_string('showstep', 'local_status'), 
                        ['class' => 'btn btn-success btn-sm mr-2']);
                }
                
                // Delete button (with confirmation)
                $delete_url = new moodle_url('/local/status/pages/actions/step_actions.php', [
                    'action' => 'delete',
                    'step_id' => $step->id,
                    'sesskey' => sesskey()
                ]);
                $action_buttons .= html_writer::link($delete_url, get_string('deletestep', 'local_status'), 
                    ['class' => 'btn btn-danger btn-sm', 'onclick' => 'return confirm("' . get_string('confirmdelete', 'local_status') . '")']);
                
                $mform->addElement('static', 'step_actions', get_string('actions', 'local_status'), $action_buttons);
            }
        }
        
        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'workflow_id');
        $mform->setType('workflow_id', PARAM_INT);
        
        // Action buttons
        $this->add_action_buttons(true, get_string('save'));
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Validate Display Name (Arabic) - required and cannot be empty or just whitespace
        if (empty($data['display_name_ar']) || trim($data['display_name_ar']) === '') {
            $errors['display_name_ar'] = get_string('required');
        }
        
        // Check for duplicate step name within workflow
        global $DB;
        $conditions = ['name' => $data['name'], 'type_id' => $data['workflow_id']];
        if (!empty($data['id'])) {
            $sql = "name = :name AND type_id = :type_id AND id != :id";
            $params = ['name' => $data['name'], 'type_id' => $data['workflow_id'], 'id' => $data['id']];
        } else {
            $sql = "name = :name AND type_id = :type_id";
            $params = ['name' => $data['name'], 'type_id' => $data['workflow_id']];
        }
        
        if ($DB->record_exists_select('local_status', $sql, $params)) {
            $errors['name'] = get_string('duplicatestepname', 'local_status');
        }
        
        // Validate capability requirement for capability-based approval
        if ($data['approval_type'] === 'capability' && empty($data['capability'])) {
            $errors['capability'] = get_string('capabilityrequired', 'local_status');
        }
        
        return $errors;
    }
}

// Create form
$customdata = [
    'step' => $step,
    'workflow' => $workflow,
    'existing_steps' => $existing_steps
];
$mform = new step_form(null, $customdata);

// Handle form submission
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id]));
} else if ($data = $mform->get_data()) {
    
    $step_data = [
        'name' => $data->name,
        'display_name_en' => $data->display_name_en,
        'display_name_ar' => $data->display_name_ar,
        'approval_type' => $data->approval_type,
        'capability' => ($data->approval_type === 'capability') ? $data->capability : null,
        'is_active' => $data->is_active ? 1 : 0,
        'type_id' => $workflow_id
    ];
    
    try {
        if ($step_id) {
            // Update existing step
            workflow_dashboard_manager::update_workflow_step($step_id, $step_data);
            $message = get_string('stepupdated', 'local_status');
        } else {
            // Create new step
            $step_data['insert_position'] = $data->insert_position;
            $step_id = workflow_dashboard_manager::create_workflow_step($step_data);
            $message = get_string('stepcreated', 'local_status');
        }
        
        redirect(new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id]), 
                $message, null, \core\output\notification::NOTIFY_SUCCESS);
                
    } catch (Exception $e) {
        $redirect_params = ['workflow_id' => $workflow_id];
        // Only include step ID if we were editing an existing step (not creating a new one)
        if ($step_id && $step_id > 0) {
            $redirect_params['id'] = $step_id;
        }
        
        // Add debugging information for development
        $error_message = $e->getMessage();
        if (debugging('', DEBUG_DEVELOPER)) {
            $error_message .= "\n\nDebug Info:\n";
            $error_message .= "File: " . $e->getFile() . "\n";
            $error_message .= "Line: " . $e->getLine() . "\n";
            $error_message .= "Stack trace:\n" . $e->getTraceAsString();
        }
        
        redirect(new moodle_url('/local/status/pages/forms/step_form.php', $redirect_params), 
                $error_message, null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Set form data
if ($step_id && $step_id > 0 && $step) {
    $form_data = (object) [
        'id' => $step->id,
        'workflow_id' => $workflow_id,
        'name' => $step->name,
        'display_name_en' => $step->display_name_en,
        'display_name_ar' => $step->display_name_ar,
        'approval_type' => $step->approval_type,
        'capability' => $step->capability,
        'is_active' => $step->is_active
    ];
    $mform->set_data($form_data);
} else {
    $mform->set_data(['workflow_id' => $workflow_id]);
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
    new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id]),
    get_string('workflowsteps', 'local_status')
) . ' / ';
echo html_writer::span($title);
echo html_writer::end_div();

// Display form
$mform->display();

// Footer
echo $OUTPUT->footer(); 