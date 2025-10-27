<?php
// local/computerservice/actions/update_request_status.php

define('AJAX_SCRIPT', true);

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/local/computerservice/classes/simple_workflow_manager.php');

use local_computerservice\simple_workflow_manager as swf;
use moodle_exception;

require_login();
$context = \context_system::instance();
require_capability('local/computerservice:managerequests', $context);

header('Content-Type: application/json');
$response = [];

try {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

    $action       = clean_param($data['action']  ?? '', PARAM_ALPHA);
    $id           = clean_param($data['id']      ?? 0, PARAM_INT);
    $rejection    = clean_param($data['rejection_note'] ?? '', PARAM_TEXT);
    $sesskeyInput = clean_param($data['sesskey'] ?? '', PARAM_RAW);

    if (!confirm_sesskey($sesskeyInput)) {
        throw new moodle_exception('invalidsesskey','local_computerservice');
    }

    if ($action === 'approve') {
        swf::approve_request($id, '');
        $response = ['status'=>'success','message'=>get_string('request_approved','local_computerservice')];

    } elseif ($action === 'reject') {
        if ($rejection === '') {
            throw new moodle_exception('rejectionnotprovided','local_computerservice');
        }
        swf::reject_request($id, $rejection);
        $response = ['status'=>'success','message'=>get_string('request_rejected','local_computerservice')];

    } else {
        throw new moodle_exception('invalidaction','local_computerservice');
    }

} catch (moodle_exception $e) {
    $response = ['status'=>'error','message'=>$e->getMessage()];
} catch (\Throwable $e) {
    $response = ['status'=>'error','message'=>get_string('unexpectederror','local_computerservice')];
}

echo json_encode($response);
exit; 