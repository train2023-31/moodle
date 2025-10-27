<?php
// local/roombooking/classes/simple_workflow_manager.php

namespace local_roombooking;

defined('MOODLE_INTERNAL') || die();

/**
 * Simple Workflow Manager for Room Booking (hardcoded logic)
 * 
 * This manager implements a fixed, sequential approval workflow using
 * capability-based access checks without using dynamic database transitions.
 */
class simple_workflow_manager {

    // =============================================
    // Constants and Configuration
    // =============================================

    // Status IDs for the room booking workflow
    const STATUS_INITIAL = 49;         // Booking submitted (initial)
    const STATUS_LEADER1_REVIEW = 50;  // First-level review
    const STATUS_LEADER2_REVIEW = 51;  // Second-level review
    const STATUS_LEADER3_REVIEW = 52;  // Third-level review
    const STATUS_BOSS_REVIEW = 53;     // Final review (boss)
    const STATUS_APPROVED = 54;        // Fully approved
    const STATUS_REJECTED = 55;        // Rejected (final)

    // =============================================
    // Workflow Configuration Methods
    // =============================================

    /**
     * Defines the forward transition for each step (approval path).
     * Maps: current_status => [next_status, required_capability]
     */
    private static function get_workflow_map(): array {
        return [
            self::STATUS_INITIAL => [
                'next' => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:classroom_workflow_step1'
            ],
            self::STATUS_LEADER1_REVIEW => [
                'next' => self::STATUS_LEADER2_REVIEW,
                'capability' => 'local/status:classroom_workflow_step2'
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next' => self::STATUS_LEADER3_REVIEW,
                'capability' => 'local/status:classroom_workflow_step3'
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next' => self::STATUS_BOSS_REVIEW,
                'capability' => 'local/status:classroom_workflow_step4'
            ],
            self::STATUS_BOSS_REVIEW => [
                'next' => self::STATUS_APPROVED,
                'capability' => 'local/status:classroom_workflow_step4' // Boss step has no required capability to move forward
            ],
            self::STATUS_APPROVED => [
                'next' => self::STATUS_APPROVED, 
                'capability' => null // Final status - no further transitions possible
            ]
        ];
    }

    /**
     * Defines the reverse transitions (for rejection scenarios).
     * Maps: current_status => [previous_status, required_capability]
     */
    private static function get_rejection_map(): array {
        return [
            self::STATUS_LEADER1_REVIEW => [
                'next' => self::STATUS_REJECTED, // Leader 1 rejection ends the workflow
                'capability' => 'local/status:classroom_workflow_step1'
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next' => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:classroom_workflow_step2'
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next' => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:classroom_workflow_step3'
            ],
            self::STATUS_BOSS_REVIEW => [
                'next' => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:classroom_workflow_step4'
            ],
        ];
    }

    // =============================================
    // Status Management Methods
    // =============================================

    /**
     * Returns the initial status ID for new booking requests
     *
     * @return int Initial status
     */
    public static function get_initial_status_id(): int {
        return self::STATUS_INITIAL;
    }

    /**
     * Checks if the given status is considered "approved"
     *
     * @param int $status_id Status to check
     * @return bool Whether it is approved
     */
    public static function is_approved_status(int $status_id): bool {
        return $status_id == self::STATUS_APPROVED;
    }

    /**
     * Checks if the given status is considered "rejected"
     *
     * @param int $status_id Status to check
     * @return bool Whether it is rejected
     */
    public static function is_rejected_status(int $status_id): bool {
        return $status_id == self::STATUS_REJECTED;
    }

    /**
     * Fetch the English display name of a status
     *
     * @param int $status_id Status ID
     * @return string Status name or 'Unknown'
     */
    public static function get_status_name(int $status_id): string {
        global $DB;
        
        $status = $DB->get_record('local_status', ['id' => $status_id], 'display_name_ar');
        return $status ? $status->display_name_ar : 'Unknown';
    }

    // =============================================
    // Permission Check Methods
    // =============================================

    /**
     * Check if the current user can approve at a given status step
     *
     * @param int $status_id The current booking status
     * @return bool Whether the user has permission to approve
     */
    public static function can_user_approve(int $status_id): bool {
        // No approval possible at the final step
        if ($status_id == self::STATUS_APPROVED || $status_id == self::STATUS_REJECTED) {
            return false; // Final or rejected step
        }
        $workflow = self::get_workflow_map();
        if (!isset($workflow[$status_id])) {
            return false; // Invalid step
        }
        $required_capability = $workflow[$status_id]['capability'];
        return !empty($required_capability) && has_capability($required_capability, \context_system::instance());
    }

    /**
     * Check if the current user can reject at a given status step
     *
     * @param int $status_id The current booking status
     * @return bool Whether the user has permission to reject
     */
    public static function can_user_reject(int $status_id): bool {
        $rejection_map = self::get_rejection_map();

        if (!isset($rejection_map[$status_id]) || $status_id == self::STATUS_REJECTED) {
            return false;
        }

        $required_capability = $rejection_map[$status_id]['capability'];
        return has_capability($required_capability, \context_system::instance());
    }

    // =============================================
    // Workflow Action Methods
    // =============================================

    /**
     * Approves the booking and progresses to the next step (if any)
     *
     * @param int $booking_id Booking ID
     * @param string $note Optional note for approval
     * @return bool True on success
     * @throws \moodle_exception if user lacks permission or status is invalid
     */
    public static function approve_booking(int $booking_id, string $note = ''): bool {
        global $DB;
        $booking = $DB->get_record('local_roombooking_course_bookings', ['id' => $booking_id], '*', MUST_EXIST);
        $current_status = $booking->status_id;
        
        // Do not allow approval at the final step
        if ($current_status == self::STATUS_APPROVED) {
            throw new \moodle_exception('invalidstatus', 'local_roombooking', '', 'Cannot approve at final step.');
        }
        
        if (!self::can_user_approve($current_status)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'approve');
        }
        
        $workflow = self::get_workflow_map();
        if (!isset($workflow[$current_status])) {
            throw new \moodle_exception('invalidstatus', 'local_roombooking');
        }
        
        $next_status = $workflow[$current_status]['next'];
        
        // // Only update status if not already at boss review (final step before approved)
        // if ($current_status != self::STATUS_BOSS_REVIEW) {
        //     $DB->set_field('local_roombooking_course_bookings', 'status_id', $next_status, ['id' => $booking_id]);
        // }        
        // Update status to next step
        $DB->set_field('local_roombooking_course_bookings', 'status_id', $next_status, ['id' => $booking_id]);
        
        if (!empty($note)) {
            $DB->set_field('local_roombooking_course_bookings', 'approval_note', $note, ['id' => $booking_id]);
        }
        
        self::log_action($booking_id, $current_status, $next_status, 'approve', $note);
        return true;
    }

    /**
     * Rejects the given booking and logs the rejection note
     *
     * @param int $booking_id ID of the booking
     * @param string $note Rejection reason/note
     * @return bool True on success
     * @throws \moodle_exception if user lacks permission or status is invalid
     */
    public static function reject_booking(int $booking_id, string $note): bool {
        global $DB;

        $booking = $DB->get_record('local_roombooking_course_bookings', ['id' => $booking_id], '*', MUST_EXIST);
        $current_status = $booking->status_id;
        
        // Check if user can reject
        if (!self::can_user_reject($current_status)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'reject');
        }
        
        $rejection_map = self::get_rejection_map();
        
        // Get rejection status
        if (!isset($rejection_map[$current_status])) {
            throw new \moodle_exception('invalidstatus', 'local_roombooking');
        }
        
        $next_status = $rejection_map[$current_status]['next'];
        
        // Update booking status and rejection note
        $DB->set_field('local_roombooking_course_bookings', 'status_id', $next_status, ['id' => $booking_id]);
        $DB->set_field('local_roombooking_course_bookings', 'rejection_note', $note, ['id' => $booking_id]);
        
        // Log the rejection
        self::log_action($booking_id, $current_status, $next_status, 'reject', $note);

        return true;
    }

    // =============================================
    // Utility Methods
    // =============================================

    /**
     * Logs approval/rejection actions (can be extended to use history tables)
     *
     * @param int $booking_id Booking ID
     * @param int $from_status Previous status
     * @param int $to_status New status
     * @param string $action Action taken: approve|reject
     * @param string $note Any additional note
     */
    private static function log_action(int $booking_id, int $from_status, int $to_status, string $action, string $note): void {
        global $USER;
        debugging("Booking $booking_id: $action from status $from_status to $to_status by user {$USER->id}. Note: $note", DEBUG_DEVELOPER);
    }
}

