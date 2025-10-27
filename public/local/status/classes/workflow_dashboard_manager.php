<?php
// ============================================================================
//  Local Status – Workflow Dashboard Manager
//  CRUD operations for workflows, steps and approvers
// ============================================================================

namespace local_status;

use stdClass;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Workflow Dashboard Manager
 * CRUD operations for workflows, steps and approvers
 */
class workflow_dashboard_manager {

    // ============================================================================
    // WORKFLOW TYPE MANAGEMENT
    // ============================================================================

    /**
     * Create a new workflow type
     *
     * @param array $data Workflow data
     * @return int The workflow type ID
     */
    public static function create_workflow_type(array $data): int {
        global $DB;

        // Check for duplicate name
        if ($DB->record_exists('local_status_type', ['name' => $data['name']])) {
            throw new moodle_exception('duplicateworkflowname', 'local_status');
        }

        $workflow = new stdClass();
        $workflow->name = $data['name'];
        $workflow->display_name_en = $data['display_name_en'];
        $workflow->display_name_ar = $data['display_name_ar'];
        $workflow->plugin_name = $data['plugin_name'] ?? '';
        $workflow->is_active = $data['is_active'] ?? 1;
        $workflow->sort_order = $data['sort_order'] ?? 0;
        $workflow->timecreated = time();

        return $DB->insert_record('local_status_type', $workflow);
    }

    /**
     * Update a workflow type
     *
     * @param int $id Workflow type ID
     * @param array $data Updated data
     * @return bool
     */
    public static function update_workflow_type(int $id, array $data): bool {
        global $DB;

        $workflow = $DB->get_record('local_status_type', ['id' => $id], '*', MUST_EXIST);
        
        $updatable_fields = ['display_name_en', 'display_name_ar', 'plugin_name', 'is_active', 'sort_order'];
        
        foreach ($updatable_fields as $field) {
            if (isset($data[$field])) {
                $workflow->$field = $data[$field];
            }
        }
        
        $workflow->timemodified = time();
        
        return $DB->update_record('local_status_type', $workflow);
    }

    /**
     * Delete a workflow type (soft delete by deactivating)
     *
     * @param int $id Workflow type ID
     * @return bool
     */
    public static function delete_workflow_type(int $id): bool {
        global $DB;

        // Check if workflow has steps or is in use
        $has_steps = $DB->record_exists('local_status', ['type_id' => $id]);
        if ($has_steps) {
            throw new moodle_exception('workflow_has_steps', 'local_status');
        }

        // Delete workflow type using execute method for compatibility
        $result = $DB->execute("DELETE FROM {local_status_type} WHERE id = ?", [$id]);
        return $result;
    }

    // ============================================================================
    // WORKFLOW STEP MANAGEMENT - ENHANCED
    // ============================================================================

    /**
     * Create a new workflow step with proper sequencing
     *
     * @param array $data Step data (including insert_position)
     * @return int The step ID
     */
    public static function create_workflow_step(array $data): int {
        global $DB;

        // Check for duplicate name within the workflow
        if ($DB->record_exists('local_status', ['name' => $data['name'], 'type_id' => $data['type_id']])) {
            throw new moodle_exception('duplicatestepname', 'local_status');
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            // Use "append and reorder" approach - always append to end first
            $max_seq = $DB->get_field('local_status', 'MAX(seq)', ['type_id' => $data['type_id']]);
            $sequence = ($max_seq ?? 0) + 1;

            $step = new stdClass();
            $step->type_id = $data['type_id'];
            $step->seq = $sequence;
            $step->name = $data['name'];
            $step->display_name_en = $data['display_name_en'];
            $step->display_name_ar = $data['display_name_ar'];
            $step->capability = $data['capability'] ?? null;
            $step->approval_type = $data['approval_type'] ?? 'capability';
            $step->color = $data['color'] ?? null;
            $step->icon = $data['icon'] ?? null;
            // PROTECTED: Never allow manual setting of initial/final flags - always managed automatically
            $step->is_initial = 0;
            $step->is_final = 0;
            $step->is_active = $data['is_active'] ?? 1;
            $step->timecreated = time();

            $stepid = $DB->insert_record('local_status', $step);

            // If insert_position is specified, move the step to that position
            if (isset($data['insert_position']) && $data['insert_position'] !== null && $data['insert_position'] > 0) {
                self::move_step_to_position_safe($stepid, $data['insert_position']);
            }

            // Auto-update initial/final flags based on workflow structure
            self::update_workflow_flags($data['type_id']);

            $transaction->allow_commit();
            return $stepid;

        } catch (Exception $e) {
            $transaction->rollback($e);
            // Add more specific error information
            throw new moodle_exception('Error creating workflow step: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }

    /**
     * Update a workflow step with protection for initial/final flags
     * IMPORTANT: initial/final flags are ALWAYS protected and automatically managed
     *
     * @param int $id Step ID
     * @param array $data Updated data
     * @return bool
     */
    public static function update_workflow_step(int $id, array $data): bool {
        global $DB;

        $step = $DB->get_record('local_status', ['id' => $id], '*', MUST_EXIST);
        
        // Only these fields can be updated by users - NEVER allow initial/final/seq changes
        $updatable_fields = [
            'display_name_en', 'display_name_ar', 'capability', 'approval_type', 
            'color', 'icon', 'is_active'
        ];

        // SECURITY: Remove any attempts to change protected fields
        unset($data['is_initial'], $data['is_final'], $data['seq'], $data['type_id'], $data['name']);
        
        foreach ($updatable_fields as $field) {
            if (isset($data[$field])) {
                $step->$field = $data[$field];
            }
        }
        
        $step->timemodified = time();
        
        $result = $DB->update_record('local_status', $step);

        // ALWAYS auto-update flags after any step modification to ensure integrity
        self::update_workflow_flags($step->type_id);

        return $result;
    }

    /**
     * Hide/deactivate a workflow step (soft delete)
     *
     * @param int $id Step ID
     * @return bool
     */
    public static function hide_workflow_step(int $id): bool {
        global $DB;

        $step = $DB->get_record('local_status', ['id' => $id], '*', MUST_EXIST);
        $step->is_active = 0;
        $step->timemodified = time();

        return $DB->update_record('local_status', $step);
    }

    /**
     * Delete a workflow step (hard delete with safety checks)
     *
     * @param int $id Step ID
     * @param bool $force Force deletion even if step is in use
     * @return bool
     */
    public static function delete_workflow_step(int $id, bool $force = false): bool {
        global $DB;

        $step = $DB->get_record('local_status', ['id' => $id], '*', MUST_EXIST);

        // Safety check: Don't delete if step is being used in workflows
        if (!$force) {
            $in_use = $DB->record_exists('local_status_instance', ['current_status_id' => $id]);
            if ($in_use) {
                throw new moodle_exception('cannotdeletestepinuse', 'local_status');
            }

            // Also check history records
            $has_history = $DB->record_exists_select('local_status_history', 
                'from_status_id = ? OR to_status_id = ?', [$id, $id]);
            if ($has_history) {
                throw new moodle_exception('cannotdeletestephashistory', 'local_status');
            }
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            // Delete the step using execute method for compatibility
            $DB->execute("DELETE FROM {local_status} WHERE id = ?", [$id]);

            // Re-sequence remaining steps in the workflow
            self::resequence_workflow_steps($step->type_id);

            // Update flags
            self::update_workflow_flags($step->type_id);

            $transaction->allow_commit();
            return true;

        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Reorder workflow steps
     * 
     * @param int $type_id Workflow type ID
     * @param array $step_order Array of step IDs in desired order
     * @return bool
     */
    public static function reorder_workflow_steps(int $type_id, array $step_order): bool {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            // Use temporary negative values first to avoid constraint conflicts
            // Step 1: Set all to negative temporary values
            $temp_sequence = -1;
            foreach ($step_order as $step_id) {
                // Verify that this step belongs to the workflow
                $step = $DB->get_record('local_status', [
                    'id' => $step_id,
                    'type_id' => $type_id
                ], '*', MUST_EXIST);

                $step->seq = $temp_sequence;
                $step->timemodified = time();
                $DB->update_record('local_status', $step);
                $temp_sequence--;
            }
            
            // Step 2: Set to final positive values
            $sequence = 1;
            foreach ($step_order as $step_id) {
                $step = $DB->get_record('local_status', ['id' => $step_id], '*', MUST_EXIST);
                $step->seq = $sequence;
                $step->timemodified = time();
                $DB->update_record('local_status', $step);
                $sequence++;
            }

            // Update initial/final flags based on new order
            self::update_workflow_flags($type_id);

            $transaction->allow_commit();
            return true;

        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Insert a step at a specific position, shifting other steps
     * 
     * @param int $step_id Step to move
     * @param int $new_position New position (1-based)
     * @return bool
     */
    public static function insert_step_at_position(int $step_id, int $new_position): bool {
        global $DB;

        $step = $DB->get_record('local_status', ['id' => $step_id], '*', MUST_EXIST);

        $transaction = $DB->start_delegated_transaction();

        try {
            self::move_step_to_position_safe($step_id, $new_position);
            self::update_workflow_flags($step->type_id);

            $transaction->allow_commit();
            return true;

        } catch (Exception $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Move step to position with safety checks (handles edge cases)
     * 
     * @param int $step_id Step to move
     * @param int $target_position Target position (1-based)
     * @return bool
     */
    private static function move_step_to_position_safe(int $step_id, int $target_position): bool {
        global $DB;

        $step = $DB->get_record('local_status', ['id' => $step_id], '*', MUST_EXIST);

        // Get all steps in this workflow ordered by sequence
        $all_steps = $DB->get_records('local_status', 
            ['type_id' => $step->type_id], 'seq ASC', 'id, seq');

        $total_steps = count($all_steps);

        // Validate target position
        if ($target_position < 1) {
            $target_position = 1;
        } elseif ($target_position > $total_steps) {
            $target_position = $total_steps;
        }

        // Get the step IDs in current order
        $step_ids = array_keys($all_steps);

        // Find current position of the step we're moving
        $current_index = array_search($step_id, $step_ids);
        if ($current_index === false) {
            throw new moodle_exception('stepnotfound', 'local_status');
        }

        // Convert to 0-based for array manipulation
        $target_index = $target_position - 1;

        // If it's already in the right position, do nothing
        if ($current_index === $target_index) {
            return true;
        }

        // Remove from current position and insert at target position
        $moving_step = array_splice($step_ids, $current_index, 1)[0];
        array_splice($step_ids, $target_index, 0, $moving_step);

        // Update all sequences using temporary negative values first to avoid constraint conflicts
        // Step 1: Set all to negative temporary values
        $temp_sequence = -1;
        foreach ($step_ids as $id) {
            $update_step = new stdClass();
            $update_step->id = $id;
            $update_step->seq = $temp_sequence;
            $update_step->timemodified = time();
            $DB->update_record('local_status', $update_step);
            $temp_sequence--;
        }
        
        // Step 2: Set to final positive values
        $sequence = 1;
        foreach ($step_ids as $id) {
            $update_step = new stdClass();
            $update_step->id = $id;
            $update_step->seq = $sequence;
            $update_step->timemodified = time();
            $DB->update_record('local_status', $update_step);
            $sequence++;
        }

        return true;
    }

    /**
     * Simple move step to position (basic implementation)
     * 
     * @param int $step_id Step to move
     * @param int $target_position Target position
     * @return bool
     */
    private static function move_step_to_position(int $step_id, int $target_position): bool {
        // For now, delegate to the safer version
        return self::move_step_to_position_safe($step_id, $target_position);
    }

    /**
     * Re-sequence workflow steps to ensure proper ordering
     * 
     * @param int $type_id Workflow type ID
     * @return bool
     */
    private static function resequence_workflow_steps(int $type_id): bool {
        global $DB;

        $steps = $DB->get_records('local_status', ['type_id' => $type_id], 'seq ASC');

        // Use temporary negative values first to avoid constraint conflicts
        // Step 1: Set all to negative temporary values
        $temp_sequence = -1;
        foreach ($steps as $step) {
            $step->seq = $temp_sequence;
            $step->timemodified = time();
            $DB->update_record('local_status', $step);
            $temp_sequence--;
        }
        
        // Step 2: Set to final positive values
        $sequence = 1;
        foreach ($steps as $step) {
            $step->seq = $sequence;
            $step->timemodified = time();
            $DB->update_record('local_status', $step);
            $sequence++;
        }

        return true;
    }

    /**
     * Update initial/final flags based on workflow structure
     * CRITICAL: This is the ONLY method that should ever modify initial/final flags
     * 
     * @param int $type_id Workflow type ID
     * @return void
     */
    private static function update_workflow_flags(int $type_id): void {
        global $DB;

        // PROTECTION: Get all active steps in sequence order
        $steps = $DB->get_records('local_status', [
            'type_id' => $type_id,
            'is_active' => 1
        ], 'seq ASC');

        if (empty($steps)) {
            // No active steps - ensure all flags are cleared
            $DB->execute("UPDATE {local_status} SET is_initial = 0, is_final = 0, timemodified = ? WHERE type_id = ?", 
                        [time(), $type_id]);
            return;
        }

        $step_ids = array_keys($steps);
        $first_step_id = reset($step_ids);
        $last_step_id = end($step_ids);

        // SECURITY: Reset ALL flags first - no step should have manual flags
        $DB->execute("UPDATE {local_status} SET is_initial = 0, is_final = 0, timemodified = ? WHERE type_id = ?", 
                    [time(), $type_id]);

        // AUTOMATIC: Set initial flag for first active step
        if ($first_step_id) {
            $first_step = new stdClass();
            $first_step->id = $first_step_id;
            $first_step->is_initial = 1;
            $first_step->timemodified = time();
            $DB->update_record('local_status', $first_step);
        }

        // AUTOMATIC: Set final flag for last active step (if different from first)
        if ($last_step_id && $last_step_id !== $first_step_id) {
            $last_step = new stdClass();
            $last_step->id = $last_step_id;
            $last_step->is_final = 1;
            $last_step->timemodified = time();
            $DB->update_record('local_status', $last_step);
        }

        // SPECIAL CASE: If only one active step, it's both initial AND final
        if ($first_step_id === $last_step_id) {
            $single_step = new stdClass();
            $single_step->id = $first_step_id;
            $single_step->is_initial = 1;
            $single_step->is_final = 1;
            $single_step->timemodified = time();
            $DB->update_record('local_status', $single_step);
        }
    }

    /**
     * Validate and fix workflow integrity (ensure proper initial/final flags)
     * This method can be called periodically to ensure data integrity
     * 
     * @param int $type_id Workflow type ID (optional - if null, validates all workflows)
     * @return array Report of fixes applied
     */
    public static function validate_workflow_integrity(int $type_id = null): array {
        global $DB;
        
        $report = [
            'workflows_checked' => 0,
            'workflows_fixed' => 0,
            'issues_found' => [],
            'fixes_applied' => []
        ];
        
        // Get workflows to check
        $workflows = $type_id ? [$DB->get_record('local_status_type', ['id' => $type_id])] 
                              : $DB->get_records('local_status_type');
        
        foreach ($workflows as $workflow) {
            if (!$workflow) continue;
            
            $report['workflows_checked']++;
            $workflow_issues = [];
            
            // Check for multiple initial steps
            $initial_count = $DB->count_records('local_status', [
                'type_id' => $workflow->id,
                'is_initial' => 1,
                'is_active' => 1
            ]);
            
            if ($initial_count > 1) {
                $workflow_issues[] = "Multiple initial steps found ({$initial_count})";
            }
            
            // Check for multiple final steps  
            $final_count = $DB->count_records('local_status', [
                'type_id' => $workflow->id,
                'is_final' => 1,
                'is_active' => 1
            ]);
            
            if ($final_count > 1) {
                $workflow_issues[] = "Multiple final steps found ({$final_count})";
            }
            
            // Check for missing initial/final when steps exist
            $active_steps = $DB->count_records('local_status', [
                'type_id' => $workflow->id,
                'is_active' => 1
            ]);
            
            if ($active_steps > 0) {
                if ($initial_count === 0) {
                    $workflow_issues[] = "No initial step found but {$active_steps} active steps exist";
                }
                if ($final_count === 0) {
                    $workflow_issues[] = "No final step found but {$active_steps} active steps exist";
                }
            }
            
            // If issues found, fix them
            if (!empty($workflow_issues)) {
                $report['workflows_fixed']++;
                $report['issues_found'][$workflow->id] = $workflow_issues;
                
                // Fix by running update_workflow_flags
                self::update_workflow_flags($workflow->id);
                $report['fixes_applied'][$workflow->id] = "Workflow flags regenerated automatically";
            }
        }
        
        return $report;
    }

    // ============================================================================
    // WORKFLOW QUERY METHODS
    // ============================================================================

    /**
     * Get all workflow types
     *
     * @param string $plugin_name Filter by plugin name (optional)
     * @param bool $active_only Whether to return only active workflows
     * @return array
     */
    public static function get_workflow_types(string $plugin_name = null, bool $active_only = true): array {
        global $DB;

        $params = [];
        $conditions = [];

        if ($plugin_name !== null) {
            $conditions[] = 'plugin_name = :plugin_name';
            $params['plugin_name'] = $plugin_name;
        }

        if ($active_only) {
            $conditions[] = 'is_active = 1';
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $sql = "SELECT * FROM {local_status_type} {$where} ORDER BY sort_order ASC, name ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get workflow steps for a type
     *
     * @param int $type_id Workflow type ID
     * @param bool $active_only Whether to return only active steps
     * @param bool $include_hidden Whether to include hidden steps
     * @return array
     */
    public static function get_workflow_steps(int $type_id, bool $active_only = true, bool $include_hidden = false): array {
        global $DB;

        $params = ['type_id' => $type_id];
        $conditions = ['type_id = :type_id'];

        if ($active_only && !$include_hidden) {
            $conditions[] = 'is_active = 1';
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM {local_status} WHERE {$where} ORDER BY seq ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Check if a step can be modified (not protected and not in use)
     *
     * @param int $step_id Step ID
     * @return bool
     */
    public static function can_modify_step(int $step_id): bool {
        global $DB;

        // Get step details
        $step = $DB->get_record('local_status', ['id' => $step_id]);
        if (!$step) {
            return false;
        }

        // PROTECTION: Initial and final steps are NEVER modifiable
        if ($step->is_initial || $step->is_final) {
            return false;
        }

        // Check if step is currently being used
        $in_use = $DB->record_exists('local_status_instance', ['current_status_id' => $step_id]);
        
        return !$in_use;
    }

    /**
     * Get steps that can be modified (excludes initial/final protected steps and steps in use)
     *
     * @param int $type_id Workflow type ID
     * @return array
     */
    public static function get_modifiable_steps(int $type_id): array {
        global $DB;

        // PROTECTION: Exclude initial and final steps - they are never modifiable
        $sql = "SELECT s.* FROM {local_status} s
                LEFT JOIN {local_status_instance} i ON s.id = i.current_status_id
                WHERE s.type_id = ? 
                  AND i.id IS NULL 
                  AND s.is_initial = 0 
                  AND s.is_final = 0
                ORDER BY s.seq ASC";

        return $DB->get_records_sql($sql, [$type_id]);
    }

    /**
     * Get actual workflow steps (excluding system flag steps)
     * 
     * @param int $type_id Workflow type ID
     * @param bool $active_only Whether to return only active steps
     * @return array
     */
    public static function get_actual_workflow_steps(int $type_id, bool $active_only = true): array {
        global $DB;

        $params = ['type_id' => $type_id];
        $conditions = [
            'type_id = :type_id',
            'is_initial = 0',
            'is_final = 0'
        ];

        if ($active_only) {
            $conditions[] = 'is_active = 1';
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM {local_status} WHERE {$where} ORDER BY seq ASC";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get workflow step count (excluding system flag steps)
     *
     * @param int $type_id Workflow type ID
     * @param bool $active_only Whether to count only active steps
     * @return int
     */
    public static function get_actual_step_count(int $type_id, bool $active_only = true): int {
        global $DB;

        $params = ['type_id' => $type_id];
        $conditions = [
            'type_id = :type_id',
            'is_initial = 0',
            'is_final = 0'
        ];

        if ($active_only) {
            $conditions[] = 'is_active = 1';
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM {local_status} WHERE {$where}";

        return $DB->count_records_sql($sql, $params);
    }

    // ============================================================================
    // APPROVER MANAGEMENT (Simplified for manage_approvers.php compatibility)
    // ============================================================================

    /**
     * Add an approver to a workflow step
     * @param int $step_id Step ID
     * @param int $user_id User ID
     * @param int $sequence_order Order in sequence
     * @param bool $is_required Whether this approver is required
     * @return int The approver record ID
     */
    public static function add_step_approver(int $step_id, int $user_id, int $sequence_order = null, bool $is_required = true): int {
        global $DB;

        // Check if the table exists first using raw SQL
        try {
            $table_check = $DB->get_records_sql("SHOW TABLES LIKE 'mdl_local_status_approvers'");
            if (empty($table_check)) {
                // Try alternative table check method
                $sql = "SELECT 1 FROM {local_status_approvers} LIMIT 1";
                $DB->get_records_sql($sql);
            }
        } catch (Exception $e) {
            throw new moodle_exception('Table local_status_approvers does not exist. Please run database upgrade.');
        }

        // Auto-assign sequence if not provided using raw SQL
        if ($sequence_order === null) {
            $max_seq_result = $DB->get_record_sql(
                "SELECT MAX(sequence_order) as max_seq FROM {local_status_approvers} WHERE step_id = ?",
                [$step_id]
            );
            $sequence_order = ($max_seq_result && $max_seq_result->max_seq ? $max_seq_result->max_seq : 0) + 1;
        }

        // Insert using raw SQL
        $sql = "INSERT INTO {local_status_approvers} 
                (step_id, user_id, sequence_order, is_required, is_active, timecreated) 
                VALUES (?, ?, ?, ?, 1, ?)";
        
        $params = [
            $step_id,
            $user_id,
            $sequence_order,
            $is_required ? 1 : 0,
            time()
        ];
        
        try {
            $DB->execute($sql, $params);
            
            // Get the inserted ID using raw SQL
            $id_result = $DB->get_record_sql(
                "SELECT id FROM {local_status_approvers} WHERE step_id = ? AND user_id = ? ORDER BY id DESC LIMIT 1",
                [$step_id, $user_id]
            );
            
            return $id_result ? $id_result->id : 0;
        } catch (Exception $e) {
            throw new moodle_exception('Unable to add approver record: ' . $e->getMessage());
        }
    }

    /**
     * Remove an approver from a workflow step
     * @param int $step_id Step ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function remove_step_approver(int $step_id, int $user_id): bool {
        global $DB;
        
        // Check if the table exists first using raw SQL
        try {
            $table_check = $DB->get_records_sql("SHOW TABLES LIKE 'mdl_local_status_approvers'");
            if (empty($table_check)) {
                // Try alternative table check method
                $sql = "SELECT 1 FROM {local_status_approvers} LIMIT 1";
                $DB->get_records_sql($sql);
            }
        } catch (Exception $e) {
            throw new moodle_exception('Table local_status_approvers does not exist. Please run database upgrade.');
        }
        
        // Check if the record exists using raw SQL
        $check_sql = "SELECT COUNT(*) as count FROM {local_status_approvers} WHERE step_id = ? AND user_id = ?";
        $result = $DB->get_record_sql($check_sql, [$step_id, $user_id]);
        
        if (!$result || $result->count == 0) {
            // Record doesn't exist, consider it already removed
            return true;
        }
        
        // Delete using raw SQL only
        try {
            $delete_sql = "DELETE FROM {local_status_approvers} WHERE step_id = ? AND user_id = ?";
            $DB->execute($delete_sql, [$step_id, $user_id]);
            return true;
        } catch (Exception $e) {
            throw new moodle_exception('Unable to delete approver record: ' . $e->getMessage());
        }
    }

    /**
     * Reorder step approvers
     * @param int $step_id Step ID
     * @param array $user_ids Array of user IDs in new order
     * @return bool
     */
    public static function reorder_step_approvers(int $step_id, array $user_ids): bool {
        global $DB;

        // Check if the table exists first
        if (!$DB->get_manager()->table_exists('local_status_approvers')) {
            throw new moodle_exception('Table local_status_approvers does not exist. Please run database upgrade.');
        }

        $transaction = $DB->start_delegated_transaction();

        try {
            $sequence = 1;
            foreach ($user_ids as $user_id) {
                $DB->update_record('local_status_approvers', [
                    'id' => $DB->get_field('local_status_approvers', 'id', [
                        'step_id' => $step_id,
                        'user_id' => $user_id
                    ]),
                    'sequence_order' => $sequence,
                    'timemodified' => time()
                ]);
                $sequence++;
            }
            
            $transaction->allow_commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback($e);
            return false;
        }
    }

    /**
     * Get step approvers
     * @param int $step_id Step ID
     * @param bool $active_only Whether to return only active approvers
     * @return array
     */
    public static function get_step_approvers(int $step_id, bool $active_only = true): array {
        global $DB;

        // Check if the table exists using raw SQL
        try {
            $table_check = $DB->get_records_sql("SHOW TABLES LIKE 'mdl_local_status_approvers'");
            if (empty($table_check)) {
                // Try alternative table check method
                $test_sql = "SELECT 1 FROM {local_status_approvers} LIMIT 1";
                $DB->get_records_sql($test_sql);
            }
        } catch (Exception $e) {
            // If table doesn't exist, return empty array instead of throwing error
            // This allows the UI to work even before database upgrade
            return [];
        }

        $sql = "SELECT sa.*, u.firstname, u.lastname, u.email 
                FROM {local_status_approvers} sa
                JOIN {user} u ON sa.user_id = u.id
                WHERE sa.step_id = ?";
        
        $params = [$step_id];
        
        if ($active_only) {
            $sql .= " AND sa.is_active = 1";
        }
        
        $sql .= " ORDER BY sa.sequence_order ASC";

        try {
            return $DB->get_records_sql($sql, $params);
        } catch (Exception $e) {
            // Return empty array if there's any issue
            return [];
        }
    }
} 