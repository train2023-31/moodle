<?php
// ============================================================================
//  Local Status – Step Actions Handler
//  Handles all step-related actions (delete, activate, deactivate)
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
$step_id = optional_param('step_id', 0, PARAM_INT);

// Set page setup
$PAGE->set_context($context);
$PAGE->requires->css(new moodle_url('/local/status/styles.css'));

// Validate sesskey for all actions
if (!confirm_sesskey()) {
    throw new moodle_exception('sesskey', 'local_status');
}

if (!$step_id) {
    throw new moodle_exception('invalidstepid', 'local_status');
}

// Get step details
$step = $DB->get_record('local_status', ['id' => $step_id], '*', MUST_EXIST);
$redirect_url = new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $step->type_id]);

try {
    // Check if step can be modified (for applicable actions)
    if (in_array($action, ['delete', 'deactivate']) && !workflow_dashboard_manager::can_modify_step($step_id)) {
        throw new moodle_exception('cannot_modify_critical_step', 'local_status');
    }
    
    switch ($action) {
        case 'delete':
            handle_delete_step($step_id, $redirect_url);
            break;
            
        case 'deactivate':
            handle_deactivate_step($step_id, $redirect_url);
            break;
            
        case 'activate':
            handle_activate_step($step_id, $redirect_url);
            break;
            
        default:
            throw new moodle_exception('unknownaction', 'local_status', '', $action);
    }
} catch (Exception $e) {
    redirect(
        $redirect_url, 
        $e->getMessage(), 
        null, 
        \core\output\notification::NOTIFY_ERROR
    );
}

/**
 * Handle step deletion
 */
function handle_delete_step(int $step_id, moodle_url $redirect_url): void {
                workflow_dashboard_manager::delete_workflow_step($step_id);
    
    redirect(
        $redirect_url, 
        get_string('stepdeleted', 'local_status'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Handle step deactivation (hide)
 */
function handle_deactivate_step(int $step_id, moodle_url $redirect_url): void {
    workflow_dashboard_manager::hide_workflow_step($step_id);
    
    redirect(
        $redirect_url, 
        get_string('stephidden', 'local_status'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/**
 * Handle step activation (show)
 */
function handle_activate_step(int $step_id, moodle_url $redirect_url): void {
    workflow_dashboard_manager::update_workflow_step($step_id, ['is_active' => 1]);
    
    redirect(
        $redirect_url, 
        get_string('stepshown', 'local_status'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
} 