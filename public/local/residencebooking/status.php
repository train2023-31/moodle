<?php
// ============================================================================
//  Handles POST actions: approve / reject / delete (via status_manager)
// ============================================================================
require_once('../../config.php');
require_once($CFG->dirroot . '/local/residencebooking/classes/simple_workflow_manager.php');

require_login();
$context = context_system::instance();
require_capability('local/residencebooking:managebookings', $context);

// Required params
$id     = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);
require_sesskey();

if (!in_array($action, ['approve', 'reject', 'delete'])) {
    throw new moodle_exception('invalidaction', 'local_residencebooking');
}

global $DB;

// Handle each action
if ($action === 'delete') {
    $DB->delete_records('local_residencebooking_request', ['id' => $id]);

} elseif ($action === 'approve') {
    \local_residencebooking\simple_workflow_manager::approve_request($id);

} elseif ($action === 'reject') {
    $note = required_param('rejection_note', PARAM_TEXT);
    \local_residencebooking\simple_workflow_manager::reject_request($id, $note);
}

// Redirect back to manage tab with message
redirect(
    new moodle_url('/local/residencebooking/index.php', ['tab' => 'manage']),
    get_string('statusupdated', 'local_residencebooking'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
