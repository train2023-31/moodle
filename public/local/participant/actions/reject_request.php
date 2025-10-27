<?php
require_once(dirname(__DIR__, 3) . '/config.php');
require_once(dirname(__DIR__) . '/classes/simple_workflow_manager.php');

require_login();

$PAGE->set_pagelayout('embedded');

global $DB;

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (isset($data['id']) && is_numeric($data['id']) && isset($data['rejection_note'])) {
    $request_id = $data['id'];
    $rejection_note = $data['rejection_note'];

    if ($DB->record_exists('local_participant_requests', ['id' => $request_id])) {
        try {
            $result = \local_participant\simple_workflow_manager::reject_request($request_id, $rejection_note);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to reject request']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Request not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request ID or missing rejection note']);
}
