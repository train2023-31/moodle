<?php
// ============================================================================
//  Local Status – Main Dashboard Page
//  Handles the workflow management dashboard display and navigation
// ============================================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');
require_once($CFG->dirroot . '/local/status/pages/renderers/workflows_renderer.php');
require_once($CFG->dirroot . '/local/status/pages/renderers/steps_renderer.php');

use local_status\workflow_dashboard_manager;

// Authentication and permissions
require_login();
admin_externalpage_setup('local_status_dashboard');

// Check permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Tab management
$tab = optional_param('tab', 'workflows', PARAM_ALPHA);
$workflow_id = optional_param('workflow_id', 0, PARAM_INT);

$validtabs = ['workflows', 'steps'];
if (!in_array($tab, $validtabs)) {
    $tab = 'workflows';
}

// Page setup
$PAGE->set_url(new moodle_url('/local/status/pages/dashboard.php', ['tab' => $tab]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('workflowdashboard', 'local_status'));
$PAGE->set_heading(get_string('workflowdashboard', 'local_status'));

// Include CSS
$PAGE->requires->css('/local/shared_styles.css');

// Tab tree
$tabs = [
    new tabobject('workflows', 
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'workflows']),
        get_string('workflows', 'local_status')),
    new tabobject('steps', 
        new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps']),
        get_string('workflowsteps', 'local_status')),
];

// Start output
echo $OUTPUT->header();

// Display tabs
echo $OUTPUT->tabtree($tabs, $tab);

// Main dashboard container
echo html_writer::start_div('workflow-dashboard-container');

// Handle different tabs
switch ($tab) {
    case 'workflows':
        $renderer = new workflows_renderer();
        echo $renderer->render();
        break;
    
    case 'steps':
        $renderer = new steps_renderer();
        echo $renderer->render($workflow_id);
        break;
}

echo html_writer::end_div(); // End dashboard container

// Footer
echo $OUTPUT->footer(); 