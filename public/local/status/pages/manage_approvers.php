<?php
// ============================================================================
//  Local Status – Manage Step Approvers
//  Interface for adding/removing specific users as approvers for workflow steps
// ============================================================================

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');

use local_status\workflow_dashboard_manager;

// Authentication and permissions
require_login();
admin_externalpage_setup('local_status_dashboard');

// Check permissions
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Parameters
$step_id = required_param('step_id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// Get step details
$step = $DB->get_record_sql("
    SELECT s.*, t.display_name_en as workflow_name 
    FROM {local_status} s 
    JOIN {local_status_type} t ON s.type_id = t.id 
    WHERE s.id = ?", [$step_id], MUST_EXIST);

// Page setup
$PAGE->set_url(new moodle_url('/local/status/pages/manage_approvers.php', ['step_id' => $step_id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('manageapprovers', 'local_status'));
$PAGE->set_heading(get_string('manageapprovers', 'local_status'));

// Include CSS only (no JavaScript)
$PAGE->requires->css(new moodle_url('/local/status/styles.css'));

// Handle actions
if ($action && confirm_sesskey()) {
    switch ($action) {
        case 'add':
            $user_id = required_param('user_id', PARAM_INT);
            $is_required = optional_param('is_required', 1, PARAM_INT);
            
            try {
                workflow_dashboard_manager::add_step_approver($step_id, $user_id, null, (bool)$is_required);
                \core\notification::success(get_string('approveradded', 'local_status'));
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'does not exist') !== false) {
                    \core\notification::error('Database tables are missing. Please <a href="' . 
                        new moodle_url('/local/status/setup_database.php') . '">check database setup</a>.');
                } else {
                    \core\notification::error(get_string('error_adding_approver', 'local_status') . ': ' . $e->getMessage());
                }
            }
            break;
            
        case 'remove':
            $user_id = required_param('user_id', PARAM_INT);
            
            try {
                workflow_dashboard_manager::remove_step_approver($step_id, $user_id);
                \core\notification::success(get_string('approverremoved', 'local_status'));
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'does not exist') !== false) {
                    \core\notification::error('Database tables are missing. Please <a href="' . 
                        new moodle_url('/local/status/setup_database.php') . '">check database setup</a>.');
                } else {
                    \core\notification::error(get_string('error_removing_approver', 'local_status') . ': ' . $e->getMessage());
                }
            }
            break;
            
        case 'reorder':
            $user_ids = required_param('user_ids', PARAM_RAW);
            $user_ids = json_decode($user_ids);
            
            if ($user_ids && is_array($user_ids)) {
                try {
                    workflow_dashboard_manager::reorder_step_approvers($step_id, $user_ids);
                    \core\notification::success(get_string('approversreordered', 'local_status'));
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'does not exist') !== false) {
                        \core\notification::error('Database tables are missing. Please <a href="' . 
                            new moodle_url('/local/status/setup_database.php') . '">check database setup</a>.');
                    } else {
                        \core\notification::error(get_string('error_reordering_approvers', 'local_status') . ': ' . $e->getMessage());
                    }
                }
            }
            break;
    }
    
    // Redirect to avoid resubmission
    redirect($PAGE->url);
}

// Start output
echo $OUTPUT->header();

// Breadcrumb navigation
echo html_writer::start_div('mb-3');
echo html_writer::link(
    new moodle_url('/local/status/index.php'),
    get_string('workflowdashboard', 'local_status')
) . ' / ';
echo html_writer::link(
    new moodle_url('/local/status/index.php', ['tab' => 'steps']),
    get_string('workflowsteps', 'local_status')
) . ' / ';
echo html_writer::span(get_string('manageapprovers', 'local_status'));
echo html_writer::end_div();

// Page header
echo html_writer::start_div('d-flex justify-content-between align-items-center mb-4');
echo html_writer::tag('h2', get_string('manageapprovers', 'local_status'));
echo html_writer::end_div();

// Step information
echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('stepinformation', 'local_status'), ['class' => 'card-title']);
echo html_writer::start_tag('dl', ['class' => 'row']);

echo html_writer::tag('dt', get_string('workflow', 'local_status'), ['class' => 'col-sm-3']);
echo html_writer::tag('dd', format_string($step->workflow_name), ['class' => 'col-sm-9']);

echo html_writer::tag('dt', get_string('stepname', 'local_status'), ['class' => 'col-sm-3']);
$current_lang = current_language();
$step_display_name = ($current_lang === 'ar' && !empty($step->display_name_ar)) 
    ? format_string($step->display_name_ar)
    : format_string($step->display_name_en);
echo html_writer::tag('dd', $step_display_name, ['class' => 'col-sm-9']);

echo html_writer::tag('dt', get_string('approvaltype', 'local_status'), ['class' => 'col-sm-3']);
$approval_type_badge = $step->approval_type === 'user' ? 
    html_writer::span(get_string('userbased', 'local_status'), 'badge badge-primary') :
    html_writer::span(get_string('capabilitybased', 'local_status'), 'badge badge-secondary');
echo html_writer::tag('dd', $approval_type_badge, ['class' => 'col-sm-9']);

echo html_writer::end_tag('dl');
echo html_writer::end_div();
echo html_writer::end_div();

// Only show approver management for user-based approval steps
if ($step->approval_type === 'user') {
    
    // Add approver form
    echo html_writer::start_div('card mb-4');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('addapprover', 'local_status'), ['class' => 'card-title']);
    
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => $PAGE->url,
        'class' => 'form-inline'
    ]);
    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey()
    ]);
    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'action',
        'value' => 'add'
    ]);
    
    echo html_writer::start_div('form-group mr-3');
    echo html_writer::tag('label', get_string('selectuser', 'local_status'), [
        'for' => 'user_id',
        'class' => 'sr-only'
    ]);
    
    // User selector
    echo html_writer::start_tag('select', [
        'name' => 'user_id',
        'id' => 'user_id',
        'class' => 'form-control',
        'required' => 'required'
    ]);
    echo html_writer::tag('option', get_string('selectuser', 'local_status'), ['value' => '']);
    
    // Get users (excluding current approvers)
    $current_approvers = workflow_dashboard_manager::get_step_approvers($step_id);
    $current_approver_ids = array_column($current_approvers, 'user_id');
    
    $exclude_sql = '';
    $exclude_params = [];
    if (!empty($current_approver_ids)) {
        list($exclude_sql, $exclude_params) = $DB->get_in_or_equal($current_approver_ids, SQL_PARAMS_NAMED, 'exclude', false);
        $exclude_sql = " AND u.id $exclude_sql";
    }
    
    $users = $DB->get_records_sql("
        SELECT u.id, u.firstname, u.lastname, u.email, u.username
        FROM {user} u 
        WHERE u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 
              AND u.id > 1 $exclude_sql
        ORDER BY u.lastname, u.firstname", $exclude_params);
    
    foreach ($users as $user) {
        $display_name = fullname($user) . ' (' . $user->email . ')';
        echo html_writer::tag('option', $display_name, ['value' => $user->id]);
    }
    
    echo html_writer::end_tag('select');
    echo html_writer::end_div();
    
    echo html_writer::start_div('form-group mr-3');
    echo html_writer::start_div('form-check');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox',
        'name' => 'is_required',
        'id' => 'is_required',
        'value' => '1',
        'checked' => 'checked',
        'class' => 'form-check-input'
    ]);
    echo html_writer::tag('label', get_string('required', 'local_status'), [
        'for' => 'is_required',
        'class' => 'form-check-label'
    ]);
    echo html_writer::end_div();
    echo html_writer::end_div();
    
    echo html_writer::tag('button', get_string('add'), [
        'type' => 'submit',
        'class' => 'btn btn-primary'
    ]);
    
    echo html_writer::end_tag('form');
    echo html_writer::end_div();
    echo html_writer::end_div();
    
    // Current approvers list
    echo html_writer::start_div('card');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('currentapprovers', 'local_status'), ['class' => 'card-title']);
    
    $approvers = workflow_dashboard_manager::get_step_approvers($step_id);
    
    if (empty($approvers)) {
        echo $OUTPUT->notification(get_string('noapprovers', 'local_status'), 'info');
    } else {
        echo html_writer::start_div('approvers-list', ['data-step-id' => $step_id]);
        
        foreach ($approvers as $approver) {
            echo html_writer::start_div('approver-item card mb-2', [
                'data-user-id' => $approver->user_id,
                'data-sequence' => $approver->sequence_order
            ]);
            echo html_writer::start_div('card-body py-2');
            echo html_writer::start_div('d-flex justify-content-between align-items-center');
            
            // Approver info
            echo html_writer::start_div('approver-info d-flex align-items-center');
            echo html_writer::span($approver->sequence_order, 'badge badge-secondary mr-2 sequence-badge');
            echo html_writer::start_div();
            echo html_writer::tag('strong', fullname($approver));
            echo html_writer::empty_tag('br');
            echo html_writer::tag('small', $approver->email, ['class' => 'text-muted']);
            echo html_writer::end_div();
            echo html_writer::end_div();
            
            // Status and actions
            echo html_writer::start_div('approver-actions d-flex align-items-center');
            
            $required_badge = $approver->is_required ? 
                html_writer::span(get_string('required', 'local_status'), 'badge badge-warning mr-2') :
                html_writer::span(get_string('optional', 'local_status'), 'badge badge-secondary mr-2');
            echo $required_badge;
            
            // Move buttons
            echo html_writer::start_div('btn-group mr-2');
            echo html_writer::tag('button', '↑', [
                'type' => 'button',
                'class' => 'btn btn-sm btn-outline-secondary move-up',
                'title' => get_string('moveup', 'local_status')
            ]);
            echo html_writer::tag('button', '↓', [
                'type' => 'button',
                'class' => 'btn btn-sm btn-outline-secondary move-down',
                'title' => get_string('movedown', 'local_status')
            ]);
            echo html_writer::end_div();
            
            // Remove button
            echo html_writer::link(
                new moodle_url($PAGE->url, [
                    'action' => 'remove',
                    'user_id' => $approver->user_id,
                    'sesskey' => sesskey()
                ]),
                '×',
                [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'title' => get_string('remove'),
                    'onclick' => 'return confirm("' . get_string('confirmremoveapprover', 'local_status') . '");'
                ]
            );
            
            echo html_writer::end_div();
            echo html_writer::end_div();
            echo html_writer::end_div();
            echo html_writer::end_div();
        }
        
        echo html_writer::end_div(); // End approvers-list
        
        // Instructions
        echo html_writer::start_div('mt-3');
        echo html_writer::tag('small', get_string('approverorderinstructions', 'local_status'), [
            'class' => 'text-muted'
        ]);
        echo html_writer::end_div();
    }
    
    echo html_writer::end_div();
    echo html_writer::end_div();
    
} else {
    // Show message for non-user-based approval types
    echo $OUTPUT->notification(
        get_string('onlyuserbased_approvers', 'local_status'), 
        'info'
    );
}

// Back button
echo html_writer::start_div('mt-4');
echo html_writer::link(
    new moodle_url('/local/status/index.php', ['tab' => 'steps']),
    get_string('back'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

// Footer
echo $OUTPUT->footer(); 