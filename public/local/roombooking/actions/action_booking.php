<?php
// local/roombooking/actions/action_booking.php
// (only small security tweaks marked with ►CHANGE)

define('AJAX_SCRIPT', true);

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/local/roombooking/classes/service/booking_service.php');   // ►CHANGE

use local_roombooking\service\booking_service as svc;                                    // ►CHANGE
use moodle_exception;

require_login();
$context = \context_system::instance();
require_capability('local/roombooking:managebookings', $context);

header('Content-Type: application/json');
$response = [];

try {
    $input = file_get_contents('php://input');
    $data  = json_decode($input, true, 512, JSON_THROW_ON_ERROR);                        // ►CHANGE

    $action       = clean_param($data['action']  ?? '', PARAM_ALPHA);
    $id           = clean_param($data['id']      ?? 0, PARAM_INT);
    $rejection    = clean_param($data['rejection_note'] ?? '', PARAM_TEXT);
    $sesskeyInput = clean_param($data['sesskey'] ?? '', PARAM_RAW);

    if (!confirm_sesskey($sesskeyInput)) {
        throw new moodle_exception('invalidsesskey','local_roombooking');
    }

    if ($action === 'approve') {
        svc::approve($id, '');                                                           // ►CHANGE
        $response = ['status'=>'success','message'=>get_string('booking_approved','local_roombooking')];

    } elseif ($action === 'reject') {
        if ($rejection === '') {
            throw new moodle_exception('rejectionnotprovided','local_roombooking');
        }
        svc::reject($id, $rejection);                                                    // ►CHANGE
        $response = ['status'=>'success','message'=>get_string('booking_rejected','local_roombooking')];

    } else {
        throw new moodle_exception('invalidaction','local_roombooking');
    }

} catch (moodle_exception $e) {
    $response = ['status'=>'error','message'=>$e->getMessage()];
} catch (\Throwable $e) {
    $response = ['status'=>'error','message'=>get_string('unexpectederror','local_roombooking')];
}

echo json_encode($response);
exit;
