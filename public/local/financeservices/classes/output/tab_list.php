<?php
// ============================================================================
//  Finance Services Plugin – List Renderer
//  Prepares finance requests for display in Mustache templates
// ============================================================================

namespace local_financeservices\output;

use local_financeservices\simple_workflow_manager;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Prepares data for the list.mustache template.
 */
class tab_list implements \renderable, \templatable {

    /** @var array List of finance services */
    private $financeservices;

    /**
     * Constructor
     *
     * @param array|\stdClass[] $financeservices List of records from database
     */
    public function __construct(array $financeservices) {
        $this->financeservices = $financeservices;
    }

    /**
     * Export data for Mustache templates.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;
        
        $data = ['financeservices' => []];

        // Determine which language to use for status names
        $langfield = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';

        foreach ($this->financeservices as $service) {
            $statusname = $service->$langfield;
            $isApproved = simple_workflow_manager::is_approved_status($service->status_id);
            $isRejected = simple_workflow_manager::is_rejected_status($service->status_id);
            $isInitial = $service->status_id == simple_workflow_manager::get_initial_status_id();
            $hasRejectionNote = !empty($service->rejection_note);
            $wasRejectedAndMovedBack = simple_workflow_manager::has_rejection_note($service->status_id) && $hasRejectionNote;

            $data['financeservices'][] = [
                'id'                  => $service->id,
                'course'              => $service->course,
                'funding_type'        => $service->funding_type,
                'price_requested'     => $service->price_requested,
                'date_time_requested' => userdate($service->date_time_requested, '%d-%m-%Y'),
                'readable_status'     => $service->$langfield,
                'clause_name'         => $service->clause_name ?? '',
                'can_approve'         => simple_workflow_manager::can_user_approve($service->status_id),
                'is_final'            => $isApproved || $isRejected,
                // Status flags for template logic
                'status_is_approved'  => $isApproved,
                'status_is_rejected'  => $isRejected,
                'status_is_initial'   => $isInitial,
                // Notes for display
                'rejection_note'      => $service->rejection_note ?? '',
                'approval_note'       => $service->approval_note ?? '',
                // Rejection tracking
                'has_rejection_note'  => $hasRejectionNote,
                'was_rejected_and_moved_back' => $wasRejectedAndMovedBack,
            ];            
        }

        // Add JavaScript variables for AJAX calls
        $data['js'] = [
            'action_url_base' => $CFG->wwwroot . '/local/financeservices/actions/update_request_status.php',
            'sesskey' => sesskey()
        ];

        return $data;
    }
}
