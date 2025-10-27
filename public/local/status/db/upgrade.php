<?php
// ============================================================================
//  Local Status – Database Upgrade Script
//  Handles database schema changes for enhanced workflow management
// ============================================================================

defined('MOODLE_INTERNAL') || die();

function xmldb_local_status_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Version 2025060206: Add dynamic approver management tables
    if ($oldversion < 2025060206) {

        // 1. Add approval_type field to local_status table
        $table = new xmldb_table('local_status');
        $field = new xmldb_field('approval_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'capability', 'is_active');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2. Create local_status_approvers table
        $table = new xmldb_table('local_status_approvers');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('step_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sequence_order', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('is_required', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('is_active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('step_fk', XMLDB_KEY_FOREIGN, ['step_id'], 'local_status', ['id']);
        $table->add_key('user_fk', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);

        // Add indexes
        $table->add_index('step_user_uq', XMLDB_INDEX_UNIQUE, ['step_id', 'user_id']);
        $table->add_index('step_sequence_ix', XMLDB_INDEX_NOTUNIQUE, ['step_id', 'sequence_order']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // 3. Add approver_sequence field to local_status_history table
        $table = new xmldb_table('local_status_history');
        $field = new xmldb_field('approver_sequence', XMLDB_TYPE_INTEGER, '5', null, null, null, null, 'user_id');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 4. Create local_status_instance table
        $table = new xmldb_table('local_status_instance');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('record_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('current_status_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('current_approver_sequence', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('type_fk', XMLDB_KEY_FOREIGN, ['type_id'], 'local_status_type', ['id']);
        $table->add_key('status_fk', XMLDB_KEY_FOREIGN, ['current_status_id'], 'local_status', ['id']);
        $table->add_key('created_by_fk', XMLDB_KEY_FOREIGN, ['created_by'], 'user', ['id']);

        // Add indexes
        $table->add_index('workflow_record_uq', XMLDB_INDEX_UNIQUE, ['type_id', 'record_id']);
        $table->add_index('status_sequence_ix', XMLDB_INDEX_NOTUNIQUE, ['current_status_id', 'current_approver_sequence']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2025060206, 'local', 'status');
    }

    // Version 2025060402: Make Display Name (Arabic) required and non-nullable
    if ($oldversion < 2025060402) {
        
        // First, update any existing records with NULL or empty Arabic display names
        // Set them to match the English display name as a fallback
        $DB->execute("
            UPDATE {local_status} 
            SET display_name_ar = display_name_en 
            WHERE display_name_ar IS NULL OR display_name_ar = ''
        ");

        // Also update workflow types table
        $DB->execute("
            UPDATE {local_status_type} 
            SET display_name_ar = display_name_en 
            WHERE display_name_ar IS NULL OR display_name_ar = ''
        ");

        // Now change the field to be NOT NULL for local_status table
        $table = new xmldb_table('local_status');
        $field = new xmldb_field('display_name_ar', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'display_name_en');
        
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        // Change the field to be NOT NULL for local_status_type table as well
        $table = new xmldb_table('local_status_type');
        $field = new xmldb_field('display_name_ar', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'display_name_en');
        
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025060402, 'local', 'status');
    }

    // Version 2025070801: Add participants_workflow for existing installations
    if ($oldversion < 2025070801) {
        
        // Check if participants_workflow already exists
        $existing = $DB->get_record('local_status_type', ['name' => 'participants_workflow']);
        
        if (!$existing) {
            // Add participants_workflow type
            $type = new stdClass();
            $type->name = 'participants_workflow';
            $type->display_name_en = 'Participants Management';
            $type->display_name_ar = 'إدارة المشاركين';
            $type->plugin_name = '';
            $type->is_active = 1;
            $type->sort_order = 9;
            $type->timecreated = time();
            
            $type_id = $DB->insert_record('local_status_type', $type);
            
            // Add workflow steps for participants_workflow
            $statuses = [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:participants_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:participants_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:participants_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:participants_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];

            $seq = 1;
            $step_ids = [];
            foreach ($statuses as $status_data) {
                [$step_name, $display_en, $display_ar, $color, $icon, $is_initial, $is_final, $capability, $approval_type] = $status_data;

                $record = new stdClass();
                $record->type_id = $type_id;
                $record->seq = $seq;
                $record->name = $step_name;
                $record->display_name_en = $display_en;
                $record->display_name_ar = $display_ar;
                $record->capability = $capability;
                $record->approval_type = $approval_type ?? 'capability';
                $record->color = $color;
                $record->icon = $icon;
                $record->is_initial = $is_initial;
                $record->is_final = $is_final;
                $record->is_active = 1;
                $record->timecreated = time();

                $step_id = $DB->insert_record('local_status', $record);
                $step_ids[] = $step_id;
                $seq++;
            }
            
            // Create transitions for participants_workflow
            // Forward transitions (approve)
            for ($i = 0; $i < count($step_ids) - 1; $i++) {
                $from_step = $DB->get_record('local_status', ['id' => $step_ids[$i]]);
                if ($from_step->name !== '-------------') { // Skip placeholder steps
                    $transition = new stdClass();
                    $transition->type_id = $type_id;
                    $transition->from_status_id = $step_ids[$i];
                    $transition->to_status_id = $step_ids[$i + 1];
                    $transition->transition_type = 'approve';
                    $transition->display_name_en = 'Approve';
                    $transition->display_name_ar = 'موافقة';
                    $transition->required_capability = $from_step->capability;
                    $transition->is_active = 1;
                    $transition->timecreated = time();
                    
                    $DB->insert_record('local_status_transition', $transition);
                }
            }
            
            // Reject transitions (from any step to initial)
            $initial_step_id = $step_ids[0];
            for ($i = 1; $i < count($step_ids) - 2; $i++) { // Skip initial, approved, and rejected steps
                $from_step = $DB->get_record('local_status', ['id' => $step_ids[$i]]);
                
                $transition = new stdClass();
                $transition->type_id = $type_id;
                $transition->from_status_id = $step_ids[$i];
                $transition->to_status_id = $initial_step_id;
                $transition->transition_type = 'reject';
                $transition->display_name_en = 'Reject';
                $transition->display_name_ar = 'رفض';
                $transition->required_capability = $from_step->capability;
                $transition->is_active = 1;
                $transition->timecreated = time();
                
                $DB->insert_record('local_status_transition', $transition);
            }
        }

        upgrade_plugin_savepoint(true, 2025070801, 'local', 'status');
    }

    return true;
} 