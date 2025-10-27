<?php
// local/residencebooking/classes/simple_workflow_manager.php

namespace local_residencebooking;

defined('MOODLE_INTERNAL') || die();

/**
 * Simple Workflow Manager for Residence Booking (hard-coded logic).
 *
 * Mirrors the structure of the room-booking workflow manager so
 * other plugins can share the same mental model.
 */
class simple_workflow_manager {

    /* ─────────────── 1. Status IDs (match {local_status}) ─────────────── */

    public const STATUS_INITIAL        = 15;
    public const STATUS_LEADER1_REVIEW = 16;
    public const STATUS_LEADER2_REVIEW = 17;
    public const STATUS_LEADER3_REVIEW = 18;
    public const STATUS_BOSS_REVIEW    = 19;
    public const STATUS_APPROVED       = 20;
    public const STATUS_REJECTED       = 21;

    /* ─────────────── 2. Transition maps ─────────────── */

    /** Forward transitions – current ⇒ [next, required_capability] */
    private static function get_workflow_map(): array {
        return [
            self::STATUS_INITIAL => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:residence_workflow_step1',
            ],
            self::STATUS_LEADER1_REVIEW => [
                'next'       => self::STATUS_LEADER2_REVIEW,
                'capability' => 'local/status:residence_workflow_step2',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER3_REVIEW,
                'capability' => 'local/status:residence_workflow_step3',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_BOSS_REVIEW,
                'capability' => 'local/status:residence_workflow_step4',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_APPROVED,
                'capability' => 'local/status:residence_workflow_step4',
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
                'capability' => 'local/status:residence_workflow_step1',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:residence_workflow_step2',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:residence_workflow_step3',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:residence_workflow_step4',
            ],
        ];
    }

    /* ─────────────── 3. Status helpers ─────────────── */

    public static function get_initial_status_id(): int { return self::STATUS_INITIAL; }

    public static function is_approved_status(int $sid): bool { return $sid == self::STATUS_APPROVED; }
    public static function is_rejected_status(int $sid): bool { return $sid == self::STATUS_REJECTED; }

    /**
     * **Needed by Manage tab** – returns every status (object) in order.
     */
    public static function get_all_statuses(): array {
        global $DB;
        return $DB->get_records('local_status', ['type_id' => 3], 'seq ASC');
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

        $req = $DB->get_record('local_residencebooking_request', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->status_id;

        if (!self::can_user_approve($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'approve');
        }

        $next = self::get_workflow_map()[$cur]['next'];

        $DB->set_field('local_residencebooking_request', 'status_id', $next, ['id' => $id]);
        $DB->set_field('local_residencebooking_request', 'rejection_note', null, ['id' => $id]);

        if ($note !== '') {
            $DB->set_field('local_residencebooking_request', 'approval_note', $note, ['id' => $id]);
        }

        self::log_action($id, $cur, $next, 'approve', $note);
        return true;
    }

    public static function reject_request(int $id, string $note): bool {
        global $DB;

        $req = $DB->get_record('local_residencebooking_request', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->status_id;

        if (!self::can_user_reject($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'reject');
        }

        $next = self::get_rejection_map()[$cur]['next'];

        $DB->set_field('local_residencebooking_request', 'status_id',     $next, ['id' => $id]);
        $DB->set_field('local_residencebooking_request', 'rejection_note', $note, ['id' => $id]);

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
            "RB-WF #{$rid}: {$action} {$from}→{$to} by user {$USER->id}. Note=\"{$note}\"",
            DEBUG_DEVELOPER
        );
    }

    /* Prevent instantiation */
    private function __construct() {}
}
