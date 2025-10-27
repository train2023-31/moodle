<?php
// ============================================================================
//  Output renderer for the "Manage requests" page
//  Shows each request and, when permitted, "Approve" and "Reject" buttons
//  to move the request forward or backward one step.
// ============================================================================

namespace local_computerservice\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/computerservice/classes/simple_workflow_manager.php');

use renderable;
use renderer_base;
use templatable;
use stdClass;

class manage_requests implements renderable, templatable {

    /** @var stdClass[] Requests loaded from manage.php */
    private array $requests;

    public function __construct(array $requests) {
        $this->requests = $requests;
    }

    /**
     * Prepares data for the Mustache template.
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG, $DB;

        $data = ['requests' => []];

        // Language-aware field for status names
        $langfield = current_language() === 'ar' ? 'status_string_ar' : 'status_string_en';

        foreach ($this->requests as $req) {
            // Fetch device name from deviceid
            $devicename = '';
            if (!empty($req->deviceid)) {
                $device = $DB->get_record('local_computerservice_devices', ['id' => $req->deviceid]);
                if ($device) {
                    $lang = current_language();
                    if ($lang === 'ar' && !empty($device->devicename_ar)) {
                        $devicename = $device->devicename_ar;
                    } else {
                        $devicename = $device->devicename_en ?? '';
                    }
                }
            }

            $isApproved = \local_computerservice\simple_workflow_manager::is_approved_status($req->status_id);
            $isRejected = \local_computerservice\simple_workflow_manager::is_rejected_status($req->status_id);
            $isInitial = $req->status_id == \local_computerservice\simple_workflow_manager::get_initial_status_id();
            $hasRejectionNote = !empty($req->rejection_note);
            $wasRejectedAndMovedBack = \local_computerservice\simple_workflow_manager::has_rejection_note($req->status_id) && $hasRejectionNote;

            $row = [
                'id'               => $req->id,
                'coursefullname'   => format_string(get_course($req->courseid)->fullname),
                'userfullname'     => fullname(\core_user::get_user($req->userid)),
                'devices'          => format_text($devicename, FORMAT_PLAIN),
                'numdevices'       => format_text($req->numdevices, FORMAT_PLAIN),
                'comments'         => format_text($req->comments,   FORMAT_PLAIN),
                'status'           => $req->statusname,
                'timecreated'      => userdate($req->timecreated),
                'request_needed_by'=> $req->request_needed_by
                    ? userdate($req->request_needed_by, get_string('strftimedate', 'core_langconfig'))
                    : get_string('not_set', 'local_computerservice'),
                'is_urgent'     => !empty($req->is_urgent),
                'urgent_class'  => !empty($req->is_urgent) ? 'urgent-request' : '',
                'canchangestatus'  => has_capability('local/computerservice:managerequests', \context_system::instance()),
                // Status flags for template logic
                'status_is_approved'  => $isApproved,
                'status_is_rejected'  => $isRejected,
                'status_is_initial'   => $isInitial,
                // Notes for display
                'rejection_note'      => $req->rejection_note ?? '',
                'approval_note'       => $req->approval_note ?? '',
                // Rejection tracking
                'has_rejection_note'  => $hasRejectionNote,
                'was_rejected_and_moved_back' => $wasRejectedAndMovedBack,
                // Workflow permissions
                'can_approve'         => \local_computerservice\simple_workflow_manager::can_user_approve($req->status_id),
                'is_final'            => $isApproved || $isRejected,
            ];

            $data['requests'][] = $row;
        }

        // Add JavaScript variables for AJAX calls
        $data['js'] = [
            'action_url_base' => $CFG->wwwroot . '/local/computerservice/actions/update_request_status.php',
            'sesskey' => sesskey()
        ];

        return $data;
    }
}
