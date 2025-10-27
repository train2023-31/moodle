<?php
// local/computerservice/classes/simple_workflow_manager.php

namespace local_computerservice;

defined('MOODLE_INTERNAL') || die();

/**
 * Simple Workflow Manager for Computer Service (hard-coded logic).
 *
 * Mirrors the structure of the financeservices workflow manager so
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
                'capability' => 'local/status:computer_workflow_step1',
            ],
            self::STATUS_LEADER1_REVIEW => [
                'next'       => self::STATUS_LEADER2_REVIEW,
                'capability' => 'local/status:computer_workflow_step2',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER3_REVIEW,
                'capability' => 'local/status:computer_workflow_step3',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_BOSS_REVIEW,
                'capability' => 'local/status:computer_workflow_step4',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_APPROVED,
                'capability' => 'local/status:computer_workflow_step4',
            ],
            self::STATUS_APPROVED => [
                'next'       => self::STATUS_APPROVED,
                'capability' => null,
            ],
        ];
    }

    /** Rejection transitions – current ⇒ [previous/fallback, required_capability] */
    public static function get_rejection_map(): array {
        return [
            self::STATUS_LEADER1_REVIEW => [
                'next'       => self::STATUS_REJECTED,
                'capability' => 'local/status:computer_workflow_step1',
            ],
            self::STATUS_LEADER2_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:computer_workflow_step2',
            ],
            self::STATUS_LEADER3_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:computer_workflow_step3',
            ],
            self::STATUS_BOSS_REVIEW => [
                'next'       => self::STATUS_LEADER1_REVIEW,
                'capability' => 'local/status:computer_workflow_step4',
            ],
        ];
    }

    /* ─────────────── 3. Status helpers ─────────────── */

    public static function get_initial_status_id(): int { return self::STATUS_INITIAL; }

    public static function is_approved_status(int $sid): bool { return $sid == self::STATUS_APPROVED; }
    public static function is_rejected_status(int $sid): bool { return $sid == self::STATUS_REJECTED; }

    public static function has_next_status(int $sid): bool {
        return isset(self::get_workflow_map()[$sid]) &&
               self::get_workflow_map()[$sid]['next'] !== $sid;
    }

    public static function get_next_status_id(int $sid): ?int {
        $map = self::get_workflow_map();
        if (!isset($map[$sid])) {
            return null;
        }
        $next = $map[$sid]['next'];
        return ($next === $sid) ? null : $next;
    }

    public static function get_next_rejection_status_id(int $sid): ?int {
        $map = self::get_rejection_map();
        if (!isset($map[$sid])) {
            return null;
        }
        return $map[$sid]['next'];
    }

    public static function get_all_statuses(): array {
        global $DB;
        return $DB->get_records('local_status', ['type_id' => 4], 'seq ASC');
    }

    public static function get_status_name(int $sid): string {
        global $DB;
        $field = current_language() === 'ar' ? 'display_name_ar' : 'display_name_en';
        $row   = $DB->get_record('local_status', ['id' => $sid], $field);
        return $row ? $row->$field : 'Unknown';
    }

    /**
     * Check if a request has been rejected and moved backwards in the workflow
     * This means it has a rejection note but is not in the final rejected status
     */
    public static function has_rejection_note(int $sid): bool {
        // If it's in the final rejected status, it's not "moved backwards"
        if ($sid == self::STATUS_REJECTED) {
            return false;
        }
        
        // Check if this status can be reached by rejection (moved backwards)
        $rejection_map = self::get_rejection_map();
        foreach ($rejection_map as $from_status => $transition) {
            if ($transition['next'] == $sid) {
                return true; // This status can be reached by rejection
            }
        }
        return false;
    }

    /* ─────────────── 4. Capability checks ─────────────── */

    /**
     * TRUE if the current user may move this request forward.
     *
     * Added: fallback to `local/computerservice:manage` so managers/admins
     * can always approve when the workflow-specific caps are not assigned.
     */
    public static function can_user_approve(int $sid): bool {
        if (in_array($sid, [self::STATUS_APPROVED, self::STATUS_REJECTED], true)) {
            return false; // Already final
        }

        $context = \context_system::instance();
        $caps    = [];

        // Workflow-specific capability (step-based)
        $wfmap = self::get_workflow_map();
        if (!empty($wfmap[$sid]['capability'])) {
            $caps[] = $wfmap[$sid]['capability'];
        }

        // Fallback: global manage capability
        $caps[] = 'local/computerservice:managerequests';

        foreach ($caps as $cap) {
            if (has_capability($cap, $context)) {
                return true;
            }
        }
        return false;
    }

    /**
     * TRUE if the current user may reject/back-step this request.
     * Same fallback logic as {@see can_user_approve()}.
     */
    public static function can_user_reject(int $sid): bool {
        $context = \context_system::instance();
        $caps    = [];

        $rejmap = self::get_rejection_map();
        if (!empty($rejmap[$sid]['capability'])) {
            $caps[] = $rejmap[$sid]['capability'];
        }

        $caps[] = 'local/computerservice:managerequests';

        foreach ($caps as $cap) {
            if (has_capability($cap, $context)) {
                return true;
            }
        }
        return false;
    }

    /* ─────────────── 5. Actions ─────────────── */

    public static function approve_request(int $id, string $note = ''): bool {
        global $DB;

        $req = $DB->get_record('local_computerservice_requests', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->status_id;

        if (!self::can_user_approve($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'approve');
        }

        $wfmap = self::get_workflow_map();
        if (!isset($wfmap[$cur])) {
            throw new \moodle_exception('Invalid status for approval: ' . $cur);
        }

        $next = $wfmap[$cur]['next'];

        // Update status and add timestamp for race condition detection
        $DB->set_field('local_computerservice_requests', 'status_id', $next, ['id' => $id]);
        $DB->set_field('local_computerservice_requests', 'rejection_note', null, ['id' => $id]);
        $DB->set_field('local_computerservice_requests', 'timemodified', time(), ['id' => $id]);

        if ($note !== '') {
            $DB->set_field('local_computerservice_requests', 'approval_note', $note, ['id' => $id]);
        }

        self::log_action($id, $cur, $next, 'approve', $note);
        return true;
    }

    public static function reject_request(int $id, string $note): bool {
        global $DB;

        $req = $DB->get_record('local_computerservice_requests', ['id' => $id], '*', MUST_EXIST);
        $cur = $req->status_id;

        if (!self::can_user_reject($cur)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'reject');
        }

        $next = self::get_rejection_map()[$cur]['next'];

        // Update status and add timestamp for race condition detection
        $DB->set_field('local_computerservice_requests', 'status_id',     $next, ['id' => $id]);
        $DB->set_field('local_computerservice_requests', 'rejection_note', $note, ['id' => $id]);
        $DB->set_field('local_computerservice_requests', 'timemodified', time(), ['id' => $id]);

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
            "CS-WF #{$rid}: {$action} {$from}→{$to} by user {$USER->id}. Note=\"{$note}\"",
            DEBUG_DEVELOPER
        );
    }

    /* Prevent instantiation */
    private function __construct() {}
} 