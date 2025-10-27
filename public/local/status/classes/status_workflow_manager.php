<?php
// ============================================================================
//  Local Status – Status Workflow Manager
//  Handles workflow status transitions, rejections and history logging
// ============================================================================

namespace local_status;

use stdClass;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Status Workflow Manager
 * Handles workflow status transitions, rejections and history logging
 */
class status_workflow_manager {

    // ============================================================================
    // STATUS CHANGE AND WORKFLOW PROCESSING
    // ============================================================================

    /**
     * Reject a workflow request
     * Logic:
     * - If rejecting from first step after initial: close permanently
     * - If rejecting from any other step: go back to previous step
     * - Initial steps cannot be rejected
     */
    public static function reject_workflow(int $type_id, int $record_id, int $user_id, string $note = ''): bool {
        global $DB;
        
        // Get current workflow instance
        $instance = $DB->get_record('local_status_instance', [
            'type_id' => $type_id,
            'record_id' => $record_id
        ], '*', MUST_EXIST);
        
        $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id], '*', MUST_EXIST);
        
        // Check permissions
        if (!local_status_can_approve($current_step->id)) {
            throw new moodle_exception('nopermissions', 'error', '', 'reject');
        }
        
        // Cannot reject from initial step
        if ($current_step->is_initial) {
            throw new moodle_exception('cannotreject', 'local_status');
        }
        
        // Get all active steps in sequence order to find workflow structure
        $all_steps = $DB->get_records('local_status', [
            'type_id' => $type_id,
            'is_active' => 1
        ], 'seq ASC');
        
        // Find initial step and first step after initial
        $initial_step = null;
        $first_after_initial = null;
        $found_initial = false;
        
        foreach ($all_steps as $step) {
            if ($step->is_initial) {
                $initial_step = $step;
                $found_initial = true;
                continue;
            }
            if ($found_initial && !$first_after_initial) {
                $first_after_initial = $step;
                break;
            }
        }
        
        if (!$initial_step || !$first_after_initial) {
            throw new moodle_exception('invalidworkflowstructure', 'local_status');
        }
        
        // Check if current step is the first step after initial
        if ($current_step->id == $first_after_initial->id) {
            // Rejection from first step after initial = close permanently
            return self::close_workflow_permanently($type_id, $record_id, $user_id, $note);
        } else {
            // Normal rejection = go back to previous step
            return self::reject_to_previous_step($type_id, $record_id, $user_id, $note);
        }
    }

    /**
     * Find previous step in sequence (more robust)
     */
    private static function reject_to_previous_step(int $type_id, int $record_id, int $user_id, string $note): bool {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            $instance = $DB->get_record('local_status_instance', [
                'type_id' => $type_id,
                'record_id' => $record_id
            ], '*', MUST_EXIST);
            
            $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id], '*', MUST_EXIST);
            
            // Get all active steps in order
            $all_steps = $DB->get_records('local_status', [
                'type_id' => $type_id,
                'is_active' => 1
            ], 'seq ASC');
            
            // Find previous step
            $previous_step = null;
            $prev = null;
            foreach ($all_steps as $step) {
                if ($step->id == $current_step->id) {
                    $previous_step = $prev;
                    break;
                }
                $prev = $step;
            }
            
            if (!$previous_step) {
                throw new moodle_exception('nopreviousstep', 'local_status');
            }
            
            // Update to previous step
            $old_status_id = $instance->current_status_id;
            $instance->current_status_id = $previous_step->id;
            $instance->timemodified = time();
            
            $DB->update_record('local_status_instance', $instance);
            
            // Log the rejection
            self::log_workflow_history($type_id, $record_id, 
                $old_status_id, $previous_step->id, $user_id, 'reject', $note);
            
            $transaction->allow_commit();
            return true;
            
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Close workflow permanently (for first step rejections)
     */
    private static function close_workflow_permanently(int $type_id, int $record_id, int $user_id, string $note): bool {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            // Get the workflow instance
            $instance = $DB->get_record('local_status_instance', [
                'type_id' => $type_id,
                'record_id' => $record_id
            ], '*', MUST_EXIST);
            
            // Create a special "rejected/closed" status or mark as closed
            $instance->is_closed = 1;
            $instance->closed_by = $user_id;
            $instance->closed_time = time();
            $instance->rejection_note = $note;
            $instance->timemodified = time();
            
            $DB->update_record('local_status_instance', $instance);
            
            // Log the permanent closure
            self::log_workflow_history($type_id, $record_id, 
                $instance->current_status_id, null, $user_id, 'reject_permanent', $note);
            
            $transaction->allow_commit();
            return true;
            
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Approve current step and move to next step
     * 
     * @param int $type_id Workflow type ID
     * @param int $record_id Record ID
     * @param int $user_id User ID performing the approval
     * @param string $note Optional approval note
     * @return bool
     */
    public static function approve_workflow(int $type_id, int $record_id, int $user_id, string $note = ''): bool {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            // Get current workflow instance
            $instance = $DB->get_record('local_status_instance', [
                'type_id' => $type_id,
                'record_id' => $record_id
            ], '*', MUST_EXIST);
            
            $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id], '*', MUST_EXIST);
            
            // Check permissions
            if (!local_status_can_approve($current_step->id)) {
                throw new moodle_exception('nopermissions', 'error', '', 'approve');
            }
            
            // If this is the final step, mark as completed
            if ($current_step->is_final) {
                $instance->is_completed = 1;
                $instance->completed_by = $user_id;
                $instance->completed_time = time();
                $instance->timemodified = time();
                
                $DB->update_record('local_status_instance', $instance);
                
                // Log completion
                self::log_workflow_history($type_id, $record_id, 
                    $current_step->id, null, $user_id, 'complete', $note);
                
                $transaction->allow_commit();
                return true;
            }
            
            // Find next step
            $next_step = self::get_next_step($type_id, $current_step->seq);
            
            if (!$next_step) {
                throw new moodle_exception('nonextstep', 'local_status');
            }
            
            // Update to next step
            $old_status_id = $instance->current_status_id;
            $instance->current_status_id = $next_step->id;
            $instance->timemodified = time();
            
            $DB->update_record('local_status_instance', $instance);
            
            // Log the approval and progression
            self::log_workflow_history($type_id, $record_id, 
                $old_status_id, $next_step->id, $user_id, 'approve', $note);
            
            $transaction->allow_commit();
            return true;
            
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Start a new workflow instance
     * 
     * @param int $type_id Workflow type ID
     * @param int $record_id Record ID
     * @param int $user_id User starting the workflow
     * @param string $note Optional note
     * @return bool
     */
    public static function start_workflow(int $type_id, int $record_id, int $user_id, string $note = ''): bool {
        global $DB;
        
        $transaction = $DB->start_delegated_transaction();
        
        try {
            // Check if workflow instance already exists
            $existing = $DB->get_record('local_status_instance', [
                'type_id' => $type_id,
                'record_id' => $record_id
            ]);
            
            if ($existing) {
                throw new moodle_exception('workflowalreadystarted', 'local_status');
            }
            
            // Find initial step
            $initial_step = $DB->get_record('local_status', [
                'type_id' => $type_id,
                'is_initial' => 1,
                'is_active' => 1
            ], '*', MUST_EXIST);
            
            // Create workflow instance
            $instance = new stdClass();
            $instance->type_id = $type_id;
            $instance->record_id = $record_id;
            $instance->current_status_id = $initial_step->id;
            $instance->created_by = $user_id;
            $instance->timecreated = time();
            $instance->timemodified = time();
            
            $instance_id = $DB->insert_record('local_status_instance', $instance);
            
            // Log the start
            self::log_workflow_history($type_id, $record_id, 
                null, $initial_step->id, $user_id, 'start', $note);
            
            $transaction->allow_commit();
            return true;
            
        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Get the next step in workflow sequence
     * 
     * @param int $type_id Workflow type ID
     * @param int $current_seq Current step sequence
     * @return stdClass|null
     */
    private static function get_next_step(int $type_id, int $current_seq): ?stdClass {
        global $DB;
        
        $sql = "SELECT * FROM {local_status}
                WHERE type_id = ? AND seq > ? AND is_active = 1
                ORDER BY seq ASC LIMIT 1";
                
        return $DB->get_record_sql($sql, [$type_id, $current_seq]) ?: null;
    }

    /**
     * Get the previous step in workflow sequence
     * 
     * @param int $type_id Workflow type ID
     * @param int $current_seq Current step sequence
     * @return stdClass|null
     */
    private static function get_previous_step(int $type_id, int $current_seq): ?stdClass {
        global $DB;
        
        $sql = "SELECT * FROM {local_status}
                WHERE type_id = ? AND seq < ? AND is_active = 1
                ORDER BY seq DESC LIMIT 1";
                
        return $DB->get_record_sql($sql, [$type_id, $current_seq]) ?: null;
    }

    /**
     * Get current workflow status for a record
     * 
     * @param int $type_id Workflow type ID
     * @param int $record_id Record ID
     * @return stdClass|null
     */
    public static function get_current_status(int $type_id, int $record_id): ?stdClass {
        global $DB;
        
        $sql = "SELECT i.*, s.name as status_name, s.display_name_en, s.display_name_ar,
                       s.is_initial, s.is_final, s.color, s.icon
                FROM {local_status_instance} i
                JOIN {local_status} s ON i.current_status_id = s.id
                WHERE i.type_id = ? AND i.record_id = ?";
                
        return $DB->get_record_sql($sql, [$type_id, $record_id]) ?: null;
    }

    /**
     * Get workflow history for a record
     * 
     * @param int $type_id Workflow type ID
     * @param int $record_id Record ID
     * @return array
     */
    public static function get_workflow_history(int $type_id, int $record_id): array {
        global $DB;
        
        $sql = "SELECT h.*, 
                       fs.name as from_status_name, fs.display_name_en as from_display_name_en,
                       ts.name as to_status_name, ts.display_name_en as to_display_name_en,
                       u.firstname, u.lastname
                FROM {local_status_history} h
                LEFT JOIN {local_status} fs ON h.from_status_id = fs.id
                LEFT JOIN {local_status} ts ON h.to_status_id = ts.id
                JOIN {user} u ON h.user_id = u.id
                WHERE h.type_id = ? AND h.record_id = ?
                ORDER BY h.timecreated ASC";
                
        return $DB->get_records_sql($sql, [$type_id, $record_id]);
    }

    /**
     * Check if a user can approve the current step
     * 
     * @param int $type_id Workflow type ID
     * @param int $record_id Record ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function can_user_approve(int $type_id, int $record_id, int $user_id): bool {
        global $DB;
        
        $instance = $DB->get_record('local_status_instance', [
            'type_id' => $type_id,
            'record_id' => $record_id
        ]);
        
        if (!$instance || $instance->is_completed || $instance->is_closed) {
            return false;
        }
        
        $current_step = $DB->get_record('local_status', ['id' => $instance->current_status_id]);
        
        if (!$current_step || !$current_step->is_active) {
            return false;
        }
        
        // Check capability-based approval
        if ($current_step->capability) {
            return has_capability($current_step->capability, context_system::instance(), $user_id);
        }
        
        // Check approver-based approval
        if ($current_step->approval_type === 'approver') {
            return $DB->record_exists('local_status_approvers', [
                'step_id' => $current_step->id,
                'user_id' => $user_id,
                'is_active' => 1
            ]);
        }
        
        // Check for "any user can approve" type
        if ($current_step->approval_type === 'any') {
            return true; // Any logged-in user can approve
        }
        
        return false;
    }

    /**
     * Log workflow history
     */
    private static function log_workflow_history(int $type_id, int $record_id, 
        ?int $from_status_id, ?int $to_status_id, int $user_id, string $action, string $note): void {
        global $DB;
        
        $history = new stdClass();
        $history->type_id = $type_id;
        $history->record_id = $record_id;
        $history->from_status_id = $from_status_id;
        $history->to_status_id = $to_status_id;
        $history->user_id = $user_id;
        $history->note = $note;
        $history->timecreated = time();
        
        $DB->insert_record('local_status_history', $history);
    }
} 