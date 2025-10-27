<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Can the current user approve the given status?
 *
 * @param int $statusid
 * @param context $context  Optional – defaults to system.
 * @return bool
 */
function local_status_can_approve(int $statusid, ?context $context = null): bool {
    global $DB;

    $status = $DB->get_record('local_status', ['id' => $statusid], '*', MUST_EXIST);
    
    // Check for "any user can approve" type first
    if (isset($status->approval_type) && $status->approval_type === 'any') {
        return true; // Any logged-in user can approve
    }
    
    // If no capability is set, this is un-approvable or terminal status
    if (empty($status->capability)) {
        return false;
    }
    
    $context = $context ?? context_system::instance();
    return has_capability($status->capability, $context);
}
