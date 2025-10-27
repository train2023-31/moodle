<?php
// ============================================================================
//  Local Status – Workflow Actions Handler
//  Handles all workflow-related actions (CRUD operations)
// ============================================================================

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');

use local_status\workflow_dashboard_manager;

// Authentication and permissions
require_login();
admin_externalpage_setup('local_status_dashboard');

// Check permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Get parameters
$action = required_param('action', PARAM_ALPHA);
$workflow_id = optional_param('workflow_id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

// Set page setup for confirmation pages
$PAGE->set_context($context);
$PAGE->requires->css(new moodle_url('/local/status/styles.css'));

try {
    switch ($action) {
        case 'confirm_delete_workflow':
            handle_confirm_delete_workflow($workflow_id);
            break;
            
        case 'delete_workflow':
            handle_delete_workflow($workflow_id);
            break;
            
        case 'toggle_workflow':
            handle_toggle_workflow($workflow_id);
            break;
            
        case 'reorder_steps':
            handle_reorder_steps($workflow_id);
            break;
            
        default:
            throw new moodle_exception('unknownaction', 'local_status', '', $action);
    }
} catch (Exception $e) {
    redirect(
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']), 
        $e->getMessage(), 
        null, 
        \core\output\notification::NOTIFY_ERROR
    );
}

/**
 * Handle workflow deletion confirmation
 */
function handle_confirm_delete_workflow(int $workflow_id): void {
    global $DB, $OUTPUT, $PAGE;
    
    if (!$workflow_id) {
        throw new moodle_exception('invalidworkflowid', 'local_status');
    }
    
    $workflow = $DB->get_record('local_status_type', ['id' => $workflow_id], '*', MUST_EXIST);
    
    // Show confirmation page
    $PAGE->set_url(new moodle_url('/local/status/pages/actions/workflow_actions.php'));
    $PAGE->set_title(get_string('deleteworkflow', 'local_status'));
    $PAGE->set_heading(get_string('deleteworkflow', 'local_status'));
    
    echo $OUTPUT->header();
    
    // Get localized workflow display name
    $current_lang = current_language();
    $workflow_display_name = ($current_lang === 'ar' && !empty($workflow->display_name_ar)) 
        ? format_string($workflow->display_name_ar)
        : format_string($workflow->display_name_en);
    
    $message = get_string('confirmdelete', 'local_status') . '<br><br>' . 
               '<strong>' . get_string('workflow', 'local_status') . ':</strong> ' . $workflow_display_name;
    
    $continue = new moodle_url('/local/status/pages/actions/workflow_actions.php', [
        'action' => 'delete_workflow',
        'workflow_id' => $workflow_id,
        'sesskey' => sesskey()
    ]);
    
    $cancel = new moodle_url('/local/status/pages/dashboard.php', [
        'tab' => 'workflows'
    ]);
    
    echo $OUTPUT->confirm($message, $continue, $cancel);
    echo $OUTPUT->footer();
}

/**
 * Handle workflow deletion
 */
function handle_delete_workflow(int $workflow_id): void {
    if (!confirm_sesskey()) {
        throw new moodle_exception('sesskey', 'local_status');
    }
    
    if (!$workflow_id) {
        throw new moodle_exception('invalidworkflowid', 'local_status');
    }
    
            workflow_dashboard_manager::delete_workflow_type($workflow_id);
    
    redirect(
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']), 
        get_string('workflowdeleted', 'local_status'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Handle workflow toggle (activate/deactivate)
 */
function handle_toggle_workflow(int $workflow_id): void {
    global $DB;
    
    if (!confirm_sesskey()) {
        throw new moodle_exception('sesskey', 'local_status');
    }
    
    if (!$workflow_id) {
        throw new moodle_exception('invalidworkflowid', 'local_status');
    }
    
    $workflow = $DB->get_record('local_status_type', ['id' => $workflow_id]);
    if (!$workflow) {
        throw new moodle_exception('invalidworkflow', 'local_status');
    }
    
    workflow_dashboard_manager::update_workflow_type($workflow_id, ['is_active' => !$workflow->is_active]);
    
    $status = $workflow->is_active ? get_string('deactivated', 'local_status') : get_string('activated', 'local_status');
    
    redirect(
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']), 
        get_string('workflowstatuschanged', 'local_status', $status), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Handle step reordering
 */
function handle_reorder_steps(int $workflow_id): void {
    if (!confirm_sesskey()) {
        throw new moodle_exception('sesskey', 'local_status');
    }
    
    if (!$workflow_id) {
        throw new moodle_exception('invalidworkflowid', 'local_status');
    }
    
    $step_order = optional_param_array('step_order', [], PARAM_INT);
    if (empty($step_order)) {
        throw new moodle_exception('nosteporder', 'local_status');
    }
    
    workflow_dashboard_manager::reorder_workflow_steps($workflow_id, $step_order);
    
    redirect(
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id]), 
        get_string('stepsreordered', 'local_status'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
} 