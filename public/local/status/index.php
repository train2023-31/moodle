<?php
// ============================================================================
//  Local Status – Main Entry Point
//  Redirects to the new dashboard structure for better organization
// ============================================================================

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Authentication and permissions
require_login();
admin_externalpage_setup('local_status_dashboard');

// Check permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Get parameters and redirect to new dashboard
$tab = optional_param('tab', 'workflows', PARAM_ALPHA);
$workflow_id = optional_param('workflow_id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// Handle legacy action URLs by redirecting to appropriate action handlers
if ($action) {
    $workflow_id = optional_param('workflow_id', 0, PARAM_INT);
    $step_id = optional_param('step_id', 0, PARAM_INT);
    
    // Workflow actions
    if (in_array($action, ['confirm_delete_workflow', 'delete_workflow', 'toggle_workflow', 'reorder_steps'])) {
        $redirect_url = new moodle_url('/local/status/pages/actions/workflow_actions.php', [
            'action' => $action,
            'workflow_id' => $workflow_id,
            'sesskey' => optional_param('sesskey', '', PARAM_ALPHANUM)
        ]);
        redirect($redirect_url);
    }
    
    // Step actions
    if (in_array($action, ['delete', 'deactivate', 'activate']) && $step_id) {
        $redirect_url = new moodle_url('/local/status/pages/actions/step_actions.php', [
            'action' => $action,
            'step_id' => $step_id,
            'sesskey' => optional_param('sesskey', '', PARAM_ALPHANUM)
        ]);
        redirect($redirect_url);
    }
}

// Build redirect URL parameters
$params = ['tab' => $tab];
if ($workflow_id) {
    $params['workflow_id'] = $workflow_id;
}

// Add any additional parameters that might be needed
$show_hidden = optional_param('show_hidden', 0, PARAM_BOOL);
if ($show_hidden) {
    $params['show_hidden'] = $show_hidden;
}

// Redirect to new dashboard
$redirect_url = new moodle_url('/local/status/pages/dashboard.php', $params);
redirect($redirect_url); 