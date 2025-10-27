<?php
// local/participant/classes/simple_workflow_manager.php

namespace local_participant;

defined('MOODLE_INTERNAL') || die();

/**
 * Simple Workflow Manager for Participant Requests (hard-coded logic).
 *
 * Mirrors the structure of the room-booking workflow manager so
 * other plugins can share the same mental model.
 */
class simple_workflow_manager {

    /* ─────────────── 1. Status IDs (match {local_status}) ─────────────── */

    public const STATUS_INITIAL        = 56;  // request_submitted (seq: 1)
    public const STATUS_LEADER1_REVIEW = 57;  // leader1_review (seq: 2)
    public const STATUS_LEADER2_REVIEW = 58;  // leader2_review (seq: 3)
    public const STATUS_LEADER3_REVIEW = 59;  // leader3_review (seq: 4)
    public const STATUS_BOSS_REVIEW    = 60;  // boss_review (seq: 5)
    public const STATUS_APPROVED       = 61;  // approved (seq: 6)
    public const STATUS_REJECTED       = 62;  // rejected (seq: 7)

    /* ─────────────── 2. Transition maps ─────────────── */

    /** Forward transitions – current ⇒ [next, required_capability] */
    private static function get_workflow_map(): array {
        return [
            self::STATUS_INITIAL => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:participants_workflow_step1',
            ],
            self::STATUS_LEADER1_REVIEW => [
                'next'       => self::STATUS_LEADER2_REVIEW,
                'capability' => 'local/status:participants_workflow_step2',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER3_REVIEW,
                'capability' => 'local/status:participants_workflow_step3',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_BOSS_REVIEW,
                'capability' => 'local/status:participants_workflow_step4',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_APPROVED,
                'capability' => 'local/status:participants_workflow_step4',
            ],
            self::STATUS_APPROVED => [
                'next'       => self::STATUS_APPROVED,
                'capability' => null,
            ],
        ];
    }

    /** Rejection transitions – current ⇒ [previous/fallback, required_capability] */
    private static function get_rejection_map(): array {
        return [
            self::STATUS_LEADER1_REVIEW => [
                'next'       => self::STATUS_REJECTED,
                'capability' => 'local/status:participants_workflow_step1',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:participants_workflow_step2',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:participants_workflow_step3',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:participants_workflow_step4',
            ],
        ];
    }

    /* ─────────────── 3. Status helpers ─────────────── */

    public static function get_initial_status_id(): int { return self::get_verified_initial_status_id(); }

    public static function is_approved_status(int $sid): bool { return $sid == self::STATUS_APPROVED; }
    public static function is_rejected_status(int $sid): bool { return $sid == self::STATUS_REJECTED; }

    /**
     * **Needed by Manage tab** – returns every status (object) in order.
     */
    public static function get_all_statuses(): array {
        global $DB;
        return $DB->get_records('local_status', ['type_id' => 9], 'seq ASC');
    }

    /**
     * Translated label for one status.
     */
    public static function get_status_name(int $sid): string {
        global $DB;
        $field  = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';
        $row    = $DB->get_record('local_status', ['id' => $sid], $field);
        return $row ? $row->$field : 'Unknown';
    }

    /* ─────────────── 4. Capability checks ─────────────── */

    public static function can_user_approve(int $sid): bool {
        if (in_array($sid, [self::STATUS_APPROVED, self::STATUS_REJECTED], true)) {
            return false;
        }
        $cap = self::get_workflow_map()[$sid]['capability'] ?? null;
        return $cap && has_capability($cap, \context_system::instance());
    }

    public static function can_user_reject(int $sid): bool {
        $cap = self::get_rejection_map()[$sid]['capability'] ?? null;
        return $cap && has_capability($cap, \context_system::instance());
    }

    /* ─────────────── 5. Actions ─────────────── */

    public static function approve_request(int $id, string $note = ''): bool {
        global $DB;

        $req = $DB->get_record('local_participant_requests', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->request_status_id;

        if (!self::can_user_approve($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'approve');
        }

        $next = self::get_workflow_map()[$cur]['next'];

        $DB->set_field('local_participant_requests', 'request_status_id', $next, ['id' => $id]);
        $DB->set_field('local_participant_requests', 'rejection_reason', null, ['id' => $id]);
        $DB->set_field('local_participant_requests', 'time_modified', time(), ['id' => $id]);
        $DB->set_field('local_participant_requests', 'modified_by', $USER->id, ['id' => $id]);

        // Update is_approved field for backwards compatibility
        if ($next == self::STATUS_APPROVED) {
            $DB->set_field('local_participant_requests', 'is_approved', 1, ['id' => $id]);
        }

        // Store approval note if provided
        if ($note !== '') {
            // We could add an approval_note field to the table if needed
            // For now, we'll just log it
        }

        self::log_action($id, $cur, $next, 'approve', $note);
        return true;
    }

    public static function reject_request(int $id, string $note): bool {
        global $DB;

        $req = $DB->get_record('local_participant_requests', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->request_status_id;

        if (!self::can_user_reject($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'reject');
        }

        $next = self::get_rejection_map()[$cur]['next'];

        $DB->set_field('local_participant_requests', 'request_status_id', $next, ['id' => $id]);
        $DB->set_field('local_participant_requests', 'rejection_reason', $note, ['id' => $id]);
        $DB->set_field('local_participant_requests', 'time_modified', time(), ['id' => $id]);
        $DB->set_field('local_participant_requests', 'modified_by', $USER->id, ['id' => $id]);

        // Update is_approved field for backwards compatibility
        $DB->set_field('local_participant_requests', 'is_approved', 0, ['id' => $id]);

        self::log_action($id, $cur, $next, 'reject', $note);
        return true;
    }

    /* ─────────────── 6. Internal logging ─────────────── */

    private static function log_action(
        int    $rid,
        int    $from,
        int    $to,
        string $action,
        string $note
    ): void {
        global $USER;
        debugging(
            "PARTICIPANT-WF #{$rid}: {$action} {$from}→{$to} by user {$USER->id}. Note=\"{$note}\"",
            DEBUG_DEVELOPER
        );
    }

    /* ─────────────── 7. Migration utilities ─────────────── */

    /**
     * Migrate old status IDs to new workflow status IDs.
     * This helps transition from the old local_participant_request_status to the new workflow system.
     */
    public static function migrate_old_statuses(): void {
        global $DB;

        // Old status mappings (from local_participant_request_status)
        $old_to_new = [
            1 => self::STATUS_INITIAL,        // 'انتظار الاعتماد' -> Request Submitted
            2 => self::STATUS_APPROVED,       // 'تم الاعتماد' -> Approved
            3 => self::STATUS_REJECTED,       // 'تم رفض الاعتماد' -> Rejected
        ];

        foreach ($old_to_new as $old_status => $new_status) {
            $DB->set_field('local_participant_requests', 'request_status_id', $new_status, ['request_status_id' => $old_status]);
        }
    }

    /**
     * Verify and get the correct initial status ID from the database.
     * If our hard-coded ID doesn't exist, find the real one.
     */
    public static function get_verified_initial_status_id(): int {
        global $DB;
        
        // First try our hard-coded ID
        $status = $DB->get_record('local_status', ['id' => self::STATUS_INITIAL, 'type_id' => 9]);
        if ($status) {
            return self::STATUS_INITIAL;
        }
        
        // If that doesn't work, find the initial status by sequence
        $status = $DB->get_record('local_status', ['type_id' => 9, 'is_initial' => 1]);
        if ($status) {
            return $status->id;
        }
        
        // If still not found, find the first sequence
        $status = $DB->get_record('local_status', ['type_id' => 9, 'seq' => 1]);
        if ($status) {
            return $status->id;
        }
        
        // Fallback to our hard-coded value
        return self::STATUS_INITIAL;
    }

    /* Prevent instantiation */
    private function __construct() {}
} 